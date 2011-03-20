<?php
### Repository manager FOR tuxKernel by WebNuLL
### Licensed under AGPLv3 ( Affero GPLv3 )
# http://wiki.github.com/webnull/OpenWikiBlog/

$EXT_INF = array ( 'classname' => 'librepoconf');

class librepoconf extends KernelModule
{
	public $Error=false;
	private $Params, $Debug=false, $Archives, $Opts, $Mirrors=false, $Lines;

	public function __construct ( $Params, &$Kernel )
	{
		$this -> Kernel = $Kernel;
		$this->Params = &$Params;
	}

	private function parseMirrors()
	{
		if(!is_readable('data/core/repositories'))
			 throw new Exception( 'rconf::NO_MIRRORS_FILE::parseMirrors(): Cant find data/core/repositories file, or its not readable');

		$this->Lines = file('data/core/repositories');

		foreach ($this->Lines as $Key=>$Value)
		{
			// its a comment
			//if($Value[0] == '#')
			//	continue;

			// new mirror!
			if(strlen($Value) > 5)
			{
				$Exp = explode(' ', $Value);

				// not beigning with _h_ttp (http)?
				if($Value[0] != 'h' AND $Value[0] != 'H' AND $Value[1] != 'h' AND $Value[1] != 'H')
					continue;

				if($Value[0] == '#')
				{
					$this->Opts[$Key]['disabled'] = true;
				}

				$this->Mirrors[$Key] = trim($Exp[0]);

				// add additional flags (eg. trusted)
				if(count($Exp) > 1)
				{
					unset($Exp[0]);

					// remove empty values and add
					foreach ($Exp as $KeyT => $ValueT)
					{
						if($ValueT != '')
							$this->Opts[$Key][trim($ValueT)] = true;
					}

					
				}
			}
		}
	}

	public function listMirrors()
	{
		// if mirror list is not parsed, first it need to be loaded and parsed
		if($this->Mirrors == false)
			$this->parseMirrors();

		return $this->Mirrors;
	}

	public function listActiveMirrors()
	{
		// if mirror list is not parsed, first it need to be loaded and parsed
		if($this->Mirrors == false)
			$this->parseMirrors();

		$Mirrors = array();

		foreach ($this->Mirrors as $Key => $Value)
		{
			if($this->Opts[$Key]['disabled'] == true)
				continue;

			$Mirrors[$Key] = $Value;
		}

		return $Mirrors;
	}

	public function listDisabledMirrors()
	{
		// if mirror list is not parsed, first it need to be loaded and parsed
		if($this->Mirrors == false)
			$this->parseMirrors();

		$Mirrors = array();

		foreach ($this->Mirrors as $Key => $Value)
		{
			if($this->Opts[$Key]['disabled'] == true)
				$Mirrors[$Key] = $Value;
		}

		return $Mirrors;
	}

	public function commentMirror($ID)
	{
		if(is_array($ID) AND count($ID) == 1)
		{
			foreach ($ID as $Key => $Value) { $ID = $Key; break; }
		} elseif (is_int($ID)) {
			// WTF, nothing to do		
		} else
			return false;
		

		if(isset($this->Mirrors[$ID]))
		{
			if($this->Lines[$ID][0] != '#')
			{
				// comment line
				$this->Lines[$ID] = '#' .$this->Lines[$ID];

				// remove from list of mirrors
				unset($this->Mirrors[$ID]);

				return true;
			}
		}
	}

	public function addMirror($Adress, $Attributes='')
	{
		// _h_ttp - is $Adress an URL?
		if($Adress[0] == 'H' OR $Adress[0] == 'h')
		{
			if(is_array($Attributes))
				$Attributes = implode(' ', $Attributes);

			$NewLineID = (count($this->Lines)+1);

			// add new line
			$this->Lines[$NewLineID] = $Adress. ' ' .$Attributes;

			// add new mirror
			$this->Mirrors[$NewLineID] = $Adress;

			if(is_string($Attributes))
				$this->Opts[$NewLineID] = explode(' ', $Attributes); // string to array
			else
				$this->Opts[$NewLineID] = $Attributes; // array

			return true;
		} else
			return false;
	}

	public function unCommentMirror($ID)
	{
		if(is_array($ID) AND count($ID) == 1)
		{
			foreach ($ID as $Key => $Value) { $ID = $Key; break; }
		} elseif (is_int($ID)) {
			// WTF, nothing to do		
		} else 
			return false;

		if(isset($this->Lines[$ID]))
		{
			// empty line?
			if(strlen($this->Lines[$ID]) < 6)
				return false;

			if($this->Lines[$ID][0] == '#')
			{
				// delete "#" from beginning of the line ("uncommenting")
				$this->Lines[$ID] = substr($this->Lines[$ID], 1, strlen($this->Lines[$ID]));

				// add to list of mirrors
				$this->Mirrors[$ID] = $this->Lines[$ID];
				return true;
			}
		}
	}

	public function save()
	{
		if(!is_writable('data/core/repositories'))
			 throw new Exception( 'rconf::FILE_NOT_WRITABLE::save(): Cannot write to file data/core/repositories, check your permissions');

		$Contents = implode("", $this->Lines);

		$fp = fopen('data/core/repositories', 'w');
		fwrite($fp, $Contents);
		fclose($fp);

		return true;
	}

	public function getAttributes($ID)
	{
		if(is_array($ID) AND count($ID) == 1)
		{
			foreach ($ID as $Key => $Value) { $ID = $Key; break; }
		} elseif (is_int($ID)) {
			// WTF, nothing to do		
		} else 
			return false;

		if(isset($this->Opts[$ID]))
		{
			return $this->Opts[$ID];
		}
	}
}
?>
