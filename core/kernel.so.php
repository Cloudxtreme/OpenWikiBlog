<?php
class tuxKernel
{
	public $Mods=array();
	public $SQL; # WE WILL KEEP DATABASE OBJECT HERE...
	private $CFG;
	protected $Version='tuxKernel 0.20';
	private $Apps;

	public function __construct ( &$CFG, &$MODS, &$HTML, &$DEFMODS )
	{
		$this -> CFG = array ( 'CFG' => $CFG, 'MODS' => $MODS, 'HTML' => $HTML, 'DEFMODS' => $DEFMODS );
	}

	//# KEEP THIS FUNCTION SMALL AND FASTER, BECAUSE ITS CALLED ALL TIMES USING KERNEL
	public function __get ( $Mod )
	{
		$tryCFG = substr($Mod, 1, strlen($Mod));

		// ==== DEFAULT MODULE ON DEMAND
		if ( !isset ( $this -> Apps [ $Mod ] ) )
		{
			if ( isset ( $this -> CFG ['DEFMODS'][$Mod] ) )
				$this->modprobe ( $this -> CFG ['DEFMODS'][$Mod], '' );
		}

		// ==== MODULE ON DEMAND FUNCTION
		if ( is_file ( 'core/modules/' .$Mod. '.so.php' ) AND !isset ( $this->Mods[$Mod] )  )
		{
			$this->modprobe($Mod, '');
		}

		if ( isset ( $this->Mods[$Mod] ) )
		{
			# return aceess to module
			return $this->Mods[$Mod];

		} elseif ( isset ( $CFG[$tryCFG] ) ) 
		{
			# will return configuration
			return $CFG[$tryCFG];

		} elseif ( isset ( $this-> Apps [ $Mod ] ) ) {
					
			# default apps	
			$APP = $this->Apps[$Mod];
		
			return $this->Mods [$APP];
		}
	}

	public function getVersion ()
	{
		return $this->Version;
	}

	# LOAD OUR MODULE
	public function modprobe ( $Module, $Params='' )
	{
		if ( $this -> isLoaded ( $Module ) )
		{
			throw new Exception ( 'tuxKernel::E_NOTICE::modprobe:: *Notice*: Module "' .$Module. '" already loaded.', 4 );
		}

		if ( !is_file ( 'core/modules/' .$Module. '.so.php' ) )
		{
			throw new Exception ( 'tuxKernel::E_ERROR::modprobe:: *Warning*: Module "' .$Module. '" not found.', 3 );
			return false;	
		}

		include ( 'core/modules/' .$Module. '.so.php' );

		if ( !isset ( $EXT_INF ) )
		{
			throw new Exception ( 'tuxKernel::E_ERROR::modprobe:: *Warning*: No information present about module "' .$Module. '"', 1 );
			return false;
		} 

		if ( !class_exists ( $EXT_INF [ 'classname'] ) )
		{
			throw new Exception ( 'tuxKernel::E_ERROR::modprobe:: *Warning*: Class "' .$EXT_INF['classname']. '" not found for module "' .$EXT_INF. '", aborting loading module.', 2 );
			return false;
		}

		# IF THE MODULE NEED ANY OTHER MODULE?
		if ( isset ( $EXT_INF['depends'] ) AND is_array ( $EXT_INF['depends'] ) )
		{
			if ( $this -> loadDeps ( $EXT_INF['depends'] ) == false )
			{
				throw new Exception ('tuxKernel::E_ERROR::modprobe:: *Warning*: Failed resolving dependencies for module "' .$Module. '"', 7 );
				return false;
			}
		}

		# LOAD THE WHOLE MODULE, YES WE FINISH CHECKING IT HERE!
		$this->Mods[$Module] = new $EXT_INF['classname']($Params, $this);

		if ( $this->Mods[$Module] -> state != 'ready' )
		{
			throw new Exception ( 'tuxKernel::E_ERROR::modprobe:: *Warning*: Failed loading "' .$Module. '", state: "' .$this->Mods[$Module]->state. '"', 5 );
			return false;
		}

		if ( isset ( $this->Apps['error_handler'] ) )
		{
			$this -> error_handler -> logString ( 'kernel.so.php::E_INFO::modprobe: ' .$Module. ' loaded successfully');	
		}
	}

	# this private function will load dependencies for modprobe
	private function loadDeps ( &$arDepends )
	{
		if ( is_array ( $arDepends ) )
		{
			foreach ( $arDepends as $Key => $Value )
			{
				# LOAD MODULE "$Key" WITH "$Value" parametrs
				if ( $this -> modprobe ( $Key, $Value ) == false )
				{
					throw new Exception ( 'tuxKernel::E_ERROR::loadDeps:: *Warning*: Cannot load dependency file "' .$Key. '"', 6 );
					return false;
				}
			}

			return true;
		}
	}

	# LINK MYSQL CLASS TO KERNEL - CONNECT KERNEL AND ITS MODULES TO DATABASE
	public function connect ( &$SQL )
	{
		if ( !is_object ( $this -> SQL ) )
		{
			if ( $SQL -> error() != '' )
			{
				die ( 'OOPS! Contact administrator! Database error: ' .$SQL->error());
			}

			$this -> SQL = $SQL;
			return true;
		}

		return false;
	}

	public function config ()
	{
		return $this -> CFG;
	}

	public function isLoaded ( $Module )
	{
		if ( isset ( $this -> Mods [ $Module ] ) )
		{
			return true;
		}
	}

	public function rmmod ( $Module )
	{
		if ( $this -> isLoaded ( $Module ) )
		{
			unset ( $this -> Mods [ $Module ] );

			if ( isset ( $this->Apps['error_handler'] ) )
			{
				$this -> error_handler -> logString ( 'kernel.so.php::E_INFO::rmmod: ' .$Module. ' unloaded');	
			}
		
			return true;
		}
	}

	public function setAsDefault ( $Tool, $Usage )
	{
		$this -> Apps [ $Usage ] = $Tool;
		return true;
	}
}

class KernelModule
{
	private $Kernel=NuLL;
	public $state='module not ready';
}
?>
