<?php
### Universal Archive manager FOR tuxKernel by WebNuLL
### Licensed under AGPLv3 ( Affero GPLv3 )
# http://wiki.github.com/webnull/OpenWikiBlog/

$EXT_INF = array ( 'classname' => 'libboxman');

class libboxman extends KernelModule
{
	public $Error=false;
	private $Params, $Debug=false, $Archives;

	public function __construct ( $Params, &$Kernel )
	{
		$this -> Kernel = $Kernel;
		$this->state = 'ready';
		$this->Params = &$Params;

		// set as default translating module
		$Kernel->setAsDefault('boxman', 'archives');
	}

	public function error($String, $Function='?')
	{
		$this->Kernel->error_handler->logString('boxman::E_WARNING::' .$Function. ': ' .$String);
		$this->Error = $String;
		return true;
	}

	public function load($File)
	{
		// parse file name
		$Pathinfo = pathinfo($File);
		$Pathinfo['extension'] = strtolower($Pathinfo['extension']);

		$Module = 'boxman_' .$Pathinfo['extension'];

		if($this->Kernel->moduleExists($Module))
		{
			$DIR = $this->Kernel->findDir('core/modules/boxman_' .$Pathinfo['extension']. '.so.php');

                        if(!$DIR)
                        {
                                $this->error('Cant find "' .$DIR. '" module for "' .$File. '" file.', 'load'); 
                                return false;
                        }

			include($DIR);

			if(class_exists($EXT_INF['classname']))
			{
                                $this->Archives[md5($File)] = new $EXT_INF['classname']($File, &$this->Kernel);
                                return $this->Archives[md5($File)];
			} else {
                                $this->error('Module "' .$Module. '" has invalid metadata (classname)', 'load');
                                return false;
			}
		} else {
			$this->error('Archive type not supported for extension "' .$Pathinfo['extension']. '"', 'load');
				
			return false;
		}
	}
}
?>
