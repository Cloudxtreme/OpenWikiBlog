<?php
### Hooking module FOR tuxKernel by WebNuLL
### Licensed under AGPLv3 ( Affero GPLv3 )
# http://wiki.github.com/webnull/OpenWikiBlog/

$EXT_INF = array ( 'classname' => 'libhooks');

class libhooks extends KernelModule
{
	# this is a plugin current state, kernel must check if the module is ready to use
	public $state='module not ready';
	private $tpl, $alang, $hooks;

	public function __construct ( $Params, &$Kernel )
	{
		$this -> Kernel = $Kernel;
		$this->state = 'ready';
		$this->Site = addslashes($_GET['site']);
		#$this->alang = &$Kerne->alang;
	}

	public function start_hook ($Name, &$Array)
	{
		//if (!is_array($Array))
		//{
		//	$this -> Debug -> logString ( 'libhooks::E_WARNING::array_hook: Not an array');
		//	return false;
		//}

		if (!isset($this->hooks[$Name]))
		{
			return false;
		}

		foreach ($this->hooks[$Name]['bindings'] as $Key => $Value)
		{
			# moved to define_hook
			#if (method_exists($this->hooks[$Name]['object'], $this->hooks[$Name]['method']))
			#{
				// ==== COMPLICATED? I THINK YES
				$method = $Value['method'];
				$module = $Value['object'];
				$Array = $this->Kernel->$module->$method($Array);

				// ==== FREE ARRAY FROM MEMORY
				unset ($this->hooks[$Name]['bindings'][$Key]);
			#}
		}

		return true;
		
	}

	public function define_hook ( $Module, $Method, $Hookname )
	{
		if (method_exists($this->Kernel->$Module, $Method))
		{
			// if its first binded function
			if ( !$this->hook_exists ( $Hookname ) )
			{
				$this->create_hook ( $Hookname );
			}

			// add binded function
			$this->hooks[$Hookname]['bindings'][] = array ( 'object' => $Module, 'method' => $Method );

			return true;
		} else {
			return false;		
		}
	}

	public function hook_exists ( $Hook )
	{
		if ( !isset ( $this->hooks[$Hook]))
			return false;

		return true;
	}

	private function create_hook ( $Hook )
	{
		$this->hook[$Hook] = array('bindings', 'variable');
	}
}
?>