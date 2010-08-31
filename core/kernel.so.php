<?php
class tuxKernel
{
	private $Mods=array();
	public $SQL; // WE WILL KEEP DATABASE OBJECT HERE...
	private $CFG;
	protected $Version='tuxKernel 0.25';
	private $Apps;

	public function __construct ( &$CFG, &$MODS, &$HTML, &$DEFMODS )
	{
		$this -> CFG = array ( 'CFG' => $CFG, 'MODS' => $MODS, 'HTML' => $HTML, 'DEFMODS' => $DEFMODS );
	}

	// ===== BUG FIXED ( PATCH: $this->$Apps to $this->Apps ), THANKS TO MICHAL SRODEK ( www.srodek.info )
	public function ReturnSelfDumped()
	{
		// make sure there are all passwords erased
		unset ( $this->CFG );
		unset ( $this->SQL );

		return array ( 'private:apps' => $this->Apps, 'protected:Version' => $this->Version, 'private:Mods' => $this->Mods);
	}

	//# KEEP THIS FUNCTION SMALL AND FASTER, BECAUSE ITS CALLED ALL TIMES USING KERNEL
	public function __get ($Mod)
	{
		$tryCFG = substr($Mod, 1, strlen($Mod));

		// ==== DEFAULT MODULE ON DEMAND
		if (!isset($this->Apps[$Mod]))
		{
			if (isset($this->CFG['DEFMODS'][$Mod]))
				$this->modprobe($this->CFG['DEFMODS'][$Mod], '');
		}

		// ==== THIS IS SOLUTION FOR DEPENDENCIES, SO THE DEPENDENCIES OPTION WAS REMOVED
		// ==== MODULE ON DEMAND FUNCTION
		if (is_file('core/modules/' .$Mod. '.so.php') AND !isset($this->Mods[$Mod]))
		{
			$this->modprobe($Mod, '');
		}

		if (isset($this->Mods[$Mod]))
		{
			# return aceess to module
			return $this->Mods[$Mod];

		} elseif (isset($CFG[$Mod])) 
		{
			# will return configuration
			return $CFG[$Mod];

		} elseif (isset($this->Apps[$Mod])){
					
			# default apps
			$APP = $this->Apps[$Mod];
		
			return $this->Mods[$APP];
		}
	}

	public function getVersion ()
	{
		return $this->Version;
	}

	// ==== THIS FUNCTION WILL CHECK TRIGGERS IN ARGUMENTS AND RETURN TRUE IF MODULE WILL BE LOADED, FALSE WHEN NOT
	private function checkParams ( &$Params )
	{
		// ==== On k_TRIGGERS we will operate
		// ==== Our array looks like Params [ param1, param2, k_TRIGGERS[1 = [ 1='1' 2='1' ]] ]
		if (is_array($Params['k_TRIGGERS']))
		{
			// init variable to avoid errors, its a good thing like in c, c++ and other fast languages
			$True=0;

			foreach ($Params['k_TRIGGERS'] as $Key => $Value)
			{
				// ==== k_TRIGGERS[1 = [ 1='1' 2='1' ]], in easy way in PHP: k_TRIGGERS[1] = array ( 1 => 1, 2 => 1)
				// ==== AND IF First index equals secound index we have a match
				if ($Value[1] == $Value[2])
				{
					$True++;
				}
			}

			// if matches are at 100%
			if ($True == count($Params['k_TRIGGERS']))
			{
				return true;
			}

		} else {
			// there are no arguments to parse, module will always load without need to any trigger
			return true;
		}
	}

	# LOAD OUR MODULE
	public function modprobe ( $Module, $Params='' )
	{
		// ==== CALLING SUPPORT THROUGH KERNEL MODPROBE FUNCTION
		if (is_int($Module))
		{
			return $this->CallThroughtKernel($Params);
		}

		// external function will decide if we are continuing to load the module
		if (!$this->checkParams($Params))
		{
			return false;
		}

		if ( $this -> isLoaded ( $Module ) )
		{
			throw new Exception ( 'tuxKernel::E_NOTICE::modprobe:: *Notice*: Module "' .$Module. '" already loaded.', 4 );
			// we will cancel it, because it can erase whole data, use rmmod and re-modprobe instead
			return false;
		}

		/*
		 * KERNEL OPTIMALIZATIONS
		 *
		if ( !is_file ( 'core/modules/' .$Module. '.so.php' ) )
		{
			throw new Exception ( 'tuxKernel::E_ERROR::modprobe:: *Warning*: Module "' .$Module. '" not found.', 3 );
			return false;	
		}*/

		@include ( 'core/modules/' .$Module. '.so.php' ); // faster solution is to add "@"

		/*
		 * KERNEL OPTIMALIZATIONS
		 *
		if ( !isset ( $EXT_INF ) )
		{
			throw new Exception ( 'tuxKernel::E_ERROR::modprobe:: *Warning*: No information present about module "' .$Module. '"', 1 );
			return false;
		} 
		*/

		if ( !class_exists ( $EXT_INF [ 'classname'] ) )
		{
			throw new Exception ( 'tuxKernel::E_ERROR::modprobe:: *Warning*: Class "' .$EXT_INF['classname']. '" not found for module "' .$EXT_INF. '", aborting loading module.', 2 );
			return false;
		}

		// ==== DEPENDENCY SUPPORT IS CANCELED, THERE IS ANOTHER *FASTER* WAY TO RESOLVE DEPENDENCIES AND LOAD THEM WHEN THEY ARE NEEDED
		// ==== IF THE MODULE NEED ANY OTHER MODULE?
		/*if ( isset ( $EXT_INF['depends'] ) AND is_array ( $EXT_INF['depends'] ) )
		{
			if ( $this -> loadDeps ( $EXT_INF['depends'] ) == false )
			{
				throw new Exception ('tuxKernel::E_ERROR::modprobe:: *Warning*: Failed resolving dependencies for module "' .$Module. '"', 7 );
				return false;
			}
		}*/

		// ==== LOAD THE WHOLE MODULE, YES WE FINISH CHECKING IT HERE!
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

	private function CallThroughtKernel (&$Params)
	{
		// ===== speed up the kernel, this if is not needed - there is method_exists() to check if function is valid
		// ===== selected module is not loaded
		/*if (!$this->isLoaded($Params[1]))
		{
			return false;
		}*/

		if (!method_exists($this->Mods[$Params[0]], $Params[1]))
		{
			$this -> error_handler -> logString ( 'kernel.so.php::E_ERROR::CallThroughtKernel: ' .$Params[0]. '::' .$Params[1]. '('.gettype($Params[2]).') - method not found.');
			return false;
		}

		$this->Mods[$Params[0]]->$Params[1]($Params[2]); // index no. 0 is class name, and no. 2 is arguments in array
		
	}

	/*
	//# ===== DEPENDENCY SUPPORT IS CANCELED, ITS TOO SLOWLY - THERE IS ANOTHER FASTER WAY TO RESOLVE DEPENDENCIES
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
					throw new Exception ( 'tuxKernel::E_ERROR::loadDeps:: *Warning*: Cannot load dependency file "' .$Key. '"', 6 );#
					return false;
				}
			}

			return true;
		}
	}
	*/

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

			// ==== TO AVOID HUGE CRASH WE MUST HAVE IF ERROR_HANDLER IS LOADED
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