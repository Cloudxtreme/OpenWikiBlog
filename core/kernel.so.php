<?php
class tuxKernel
{
        private $Mods=array();
        //public $SQL; // WE WILL KEEP DATABASE OBJECT HERE...
        private $CFG;
        public $Version='tuxKernel 0.32';
        private $Apps, $LastModule, $stringCache;
        
        // ===== Where are framework core files?
        public $LD_LIBRARY_PATH='';

        public function __construct ( &$CFG )
        {
                //$this -> CFG = array ( 'CFG' => $CFG, 'MODS' => $MODS, 'HTML' => $HTML, 'DEFMODS' => $DEFMODS );
                $this -> CFG = $CFG;
        }

        // ===== BUG FIXED ( PATCH: $this->$Apps to $this->Apps ), THANKS TO MICHAL SRODEK ( www.srodek.info )
        public function ReturnSelfDumped()
        {
                // make sure there are all passwords erased
                unset ( $this->CFG );

                return array ( 'private:apps' => $this->Apps, 'private:Mods' => $this->Mods);
        }

        //# KEEP THIS FUNCTION SMALL AND FASTER, BECAUSE ITS CALLED ALL TIMES USING KERNEL
        public function __get ($Mod)
        {
                $tryCFG = substr($Mod, 1, strlen($Mod));

                // ==== DEFAULT MODULE ON DEMAND
                if (!isset($this->Apps[$Mod]))
                {
                        if (isset($this->CFG['defmods'][$Mod]))
                                $this->modprobe($this->CFG['defmods'][$Mod], '');
                }

                // ==== THIS IS SOLUTION FOR DEPENDENCIES, SO THE DEPENDENCIES OPTION WAS REMOVED
                // ==== MODULE ON DEMAND FUNCTION

                if (is_file($this->findDir('core/modules/' .$Mod. '.so.php')) AND !isset($this->Mods[$Mod]))
                {
                        $this->modprobe($Mod, '');
                }

                if (isset($this->Mods[$Mod]))
                {
                        # return aceess to module
                        return $this->Mods[$Mod];

                } elseif (isset($CFG['mods'][$Mod])) 
                {
                        # will return configuration
                        return $CFG['mods'][$Mod];

                } elseif (isset($this->Apps[$Mod])){
                                        
                        # default apps
                        $APP = $this->Apps[$Mod];
                
                        return $this->Mods[$APP];
                }
        }

        // check if file exists in framework directory on in core
        public function findDir($Directory)
        {
                // framework and application is in same directory
                if($this->LD_LIBRARY_PATH == '' OR $this->LD_LIBRARY_PATH == './')
                {
                        return $Directory;
                }

                // file is in framework dir (example. ../framework/core/modules/sqlite.so.php)
                if(file_exists($this->LD_LIBRARY_PATH.$Directory))
                        return $this->LD_LIBRARY_PATH.$Directory;
                elseif (file_exists($Directory)) // file is on overlay (example. core/modules/custom-module.so.php, but framework is in ../framework/)
                        return $Directory;
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
                if (is_array(@$Params['k_TRIGGERS']))
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
                //if ($Module == '')
                        //return 0;


                // ==== CALLING SUPPORT THROUGH KERNEL MODPROBE FUNCTION
                if (is_int($Module))
                {
                        $this -> error_handler -> logString ( 'kernel.so.php::E_INFO::modprobe: Calling throught kernel x: ' .$Params[0]. ', y: ' .$Params[1]);
                        return $this->CallThroughtKernel($Params);
                }

                if ($Module==NuLL)
                {
                        return false;
                }

                if($Module == $this->LastModule AND $Module != '')
		{
			throw new Exception ( 'tuxKernel::E_ERROR::modprobe:: Cannot try to insert same module again, the module is in invalid format.', 2 );
			return false;
		}

                // external function will decide if we are continuing to load the module
                if (!$this->checkParams($Params))
                {
                        return false;
                }

                if ( $this -> isLoaded ( $Module ) )
                {
                        //throw new Exception ( 'tuxKernel::E_NOTICE::modprobe:: *Notice*: Module "' .$Module. '" already loaded.', 4 );
                        // we will cancel it, because it can erase whole data, use rmmod and re-modprobe instead
                        return false;
                }

                /*
                 * KERNEL OPTIMIZATIONS
                 *
                if ( !is_file ( 'core/modules/' .$Module. '.so.php' ) )
                {
                        throw new Exception ( 'tuxKernel::E_ERROR::modprobe:: *Warning*: Module "' .$Module. '" not found.', 3 );
                        return false;        
                }*/
                
                @include ( $this->findDir('core/modules/' .$Module. '.so.php') ); // faster solution is to add "@"

                /*
                 * KERNEL OPTIMIZATIONS
                 *
                if ( !isset ( $EXT_INF ) )
                {
                        throw new Exception ( 'tuxKernel::E_ERROR::modprobe:: *Warning*: No information present about module "' .$Module. '"', 1 );
                        return false;
                } 
                */

                if ( !class_exists ( $EXT_INF['classname']))
                {
                        $this->LastModule = $EXT_INF['classname'];
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

                // alternative module name defined in metadata
		if(isset($EXT_INF['modulename']))
		{
                        if(!isset($this->Mods[$EXT_INF['modulename']]))
				$Module = $EXT_INF['modulename'];
			else
				$this -> error_handler -> logString ( 'kernel.so.php::E_WARNING::modprobe: ' .$Module. ' cant be named as ' .$EXT_INF['modulename']. ' because this name was already taken');
		}

                // ==== LOAD THE WHOLE MODULE, YES WE FINISH CHECKING IT HERE!
                $this->Mods[$Module] = new $EXT_INF['classname']($Params, $this);

                /*if ( $this->Mods[$Module] -> state != 'ready' )
                {
                        throw new Exception ( 'tuxKernel::E_ERROR::modprobe:: *Warning*: Failed loading "' .$Module. '", state: "' .$this->Mods[$Module]->state. '"', 5 );
                        $this->LastModule = $EXT_INF['classname'];
                        return false;
                }*/

		if (!is_object($this->Mods[$Module]))
		{
			throw new Exception ( 'tuxKernel::E_ERROR::modprobe:: *Warning*: Failed loading "' .$Module. '", state: "' .$this->Mods[$Module]->state. '"', 5 );
                        $this->LastModule = $EXT_INF['classname'];
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

        public function moduleExists ($Module)
        {
                if($this->findDir('core/modules/' .$Module. '.so.php'))
                {
                        return true;
                }
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

        public function lsmod()
        {
                $Out = array();
                foreach ($this->Mods as $Key => $Value)
                {
                        $Out[] = $Key;
                }
                return $Out;
        }

	/* ===================================================================================== */
	/* CACHING SUPPORT (PRIMALY WAS PLANNED AS MODULE BUT NOW IS INTEGRATED INTO KERNEL)     */
	/* ===================================================================================== */

	// cache expired? usage: $Kernel->fileCacheExpired('cache-name', 100); - check if cache lifetime rearched 100 seconds
	public function fileCacheExpired($File, $LifeTime) // LifeTime in seconds
	{
		if((filectime('data/cache/kernel/' .$File. '.cache')+$LifeTime) >= mktime() OR !is_file('data/cache/kernel/' .$File. '.cache'))
			return true;
	}

	public function stringCacheExpired($String, $LifeTime)
	{
		$this->cacheLoad();

		if(isset($this->stringCache[$String]))
		{
			if(($this->stringCache[$String]['time']+$LifeTime) > mktime())
			{
				return False;
			} else
				return True;
		} else
			return true;
	}

	public function stringCacheRead($String)
	{
		return $this->stringCache[$String]['content'];
	}

	public function fileCacheLoad($File)
	{
		if(is_readable('data/cache/kernel/' .$File. '.cache'))
		{
			return @file_get_contents($File);
		}
	}

	public function fileCacheWrite($File, $Content)
	{
		if(is_writable('data/cache/kernel/'))
		{
			$fp = fopen('data/cache/kernel/' .addslashes($File). '.cache', 'r+');

			if (@flock($fp, LOCK_EX))
			{
				ftruncate($fp, 0);
				fwrite($fp, $Content);
				flock($fp, LOCK_UN);
				return true;
			} else {
				return false;			
			}

			@fclose($fp);
		}
	}

	public function stringCacheWrite($String, $Content)
	{
		$this->stringCache[$String] = array ('time' => mktime(), 'content' => $Content);
		return true;
	}

	private function cacheLoad()
	{
		// load cache only one time
		if(!isset($this->stringCache))
		{
			if(is_file('data/cache/kernel/stringCache'))
			{
				$Unserialized = unserialize(file_get_contents('data/cache/kernel/stringCache'));

				if(!is_array($Unserialized))
					$Unserialized = array();

				$this->stringCache = $Unserialized;
			} else {
				if(is_writable('data/cache/kernel/'))
				{
					stringCacheMake();
					if ( isset ( $this->Apps['error_handler'] ) )
				                $this -> error_handler -> logString ( 'kernel.so.php::E_INFO::cacheLoad: Created stringCache');
				}
			}
		}
	}

	private function stringCacheMake()
	{
		if(!is_file('data/cache/kernel/stringCache'))
		{
			$fp = @fopen('data/cache/kernel/stringCache', 'w');
			@fwrite($fp, serialize(array()));
			@fclose($fp);
		}
	}

	public function __destruct()
	{
		// save cache to stringArray file
		if(is_array($this->stringCache))
		{
			$fp = fopen('data/cache/kernel/stringCache', 'r+');
			if (@flock($fp, LOCK_EX))
			{
				ftruncate($fp, 0);
				fwrite($fp, serialize($this->stringCache));
				flock($fp, LOCK_UN);
				return true;
			} else {
				return false;			
			}

			@fclose($fp);
		}
	}
}

class KernelModule
{
        private $Kernel=NuLL;
}
?>
