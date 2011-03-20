<?php
### Variable binding module FOR tuxKernel by WebNuLL
### Licensed under AGPLv3 ( Affero GPLv3 )
# http://wiki.github.com/webnull/OpenWikiBlog/

$EXT_INF = array ( 'classname' => 'navigation');

class navigation extends KernelModule
{
	# this is a plugin current state, kernel must check if the module is ready to use
	public $state='module not ready'; // "ready" or any error string like "module not ready"
	private $tpl, $alang, $Params, $DB, $vBinds=array();

	public function __construct ( $Params, &$Kernel )
	{
		$this -> Kernel = $Kernel;
		$this->state = 'ready';
		$this->Params = $Params;
		$this->DB = &$Kernel->SQL;
	}

	public function setVariable($Variable, $Value)
	{
		$this->vBinds[$Variable] = $Value;
		return true;
	}

	public function mergeArray ($Original, $ID)
	{
		$Return = array_merge($Original, $this->vBinds[$ID]);
		return $Return;
	}
}
?>
