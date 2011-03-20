<?php
### RARArchive implementation for OpenWikiBlog FOR tuxKernel by WebNuLL
### Licensed under AGPLv3 ( Affero GPLv3 )
# http://wiki.github.com/webnull/OpenWikiBlog/
# WARNING! THIS CLASS DONT ALLOW TO SAVE ARCHIVE (close() WILL ALWAYS RETURN FALSE BECAUSE THERE IS NO WAY TO SAVE RAR ARCHIVE IN PHP BECAUSE OF FUCKING PATENTS)

$EXT_INF = array ( 'classname' => 'libarchive_rar');

class libarchive_rar extends KernelModule
{
        private $Params, $Debug=false, $Archive;
	public $Error, $TMP='data/tmp/';

        public function __construct ( $Params='', &$Kernel )
        {
                $this -> Kernel = $Kernel;
                $this->state = 'ready';
                $this->Params = &$Params;

                if(!class_exists('RarArchive'))
                	throw new Exception('libarchive_rar::RAR_SUPPORT::__contruct(): PHP is compiled without RAR functions, cannot continue, please recompile your PHP.');

                if($Params != '')
                {
                        if(!is_file($Params))
                	       throw new Exception('libarchive_rar::RAR_NOFILE::__contruct(): Cant find "' .$Params. '" file.');

                        $this->Archive = RarArchive::open($Params);
                }

        }

	// cant create new archive
        public function createArchive($a) {$this->Error = 'ARCHIVE_READONLY'; return false;}
        public function addObject($a, $b) {$this->Error = 'ARCHIVE_READONLY'; return false;}
	public function mv($a,$b)         {$this->Error = 'ARCHIVE_READONLY'; return false;}
	public function rm($a)            {$this->Error = 'ARCHIVE_READONLY'; return false;}
	public function mkdir($a, $b)     {$this->Error = 'ARCHIVE_READONLY'; return false;}
	public function close($a)         {$this->Error = 'ARCHIVE_READONLY'; $this->Archive->close(); return false;}

	// lists items from archive
        public function ls($Dir='')
        {
                $this->checkResource();
		$Files = $this->Archive->getEntries();
                $FilesNum = $this->Archive->numFiles;
                $File = array();

                if($Dir == '')
                {
                      foreach ($Files as $Key => $Value)
		      {
                                $Name = $Value->getName();

				if($Name == false)
					continue;

                                $FileinfoGeneric = array('name' => $Name, 'realname' => $Name, 'hash' => $Value->getCrc(), 'id' => $Key, 'size' => $Value->getPackedSize(), 'mtime' => strtotime($Value->getFileTime()), 'comp_size' => $Value->getUnpackedSize());
                                $File[$Key] = $FileinfoGeneric;
                       }
		} elseif ($Dir == '/') {
			foreach ($Files as $Key => $Value) {
                                $Name = $Value->getName();

				if($Name == false)
					continue;

                                $POSd = strpos($Name, '/');

				if(($POSd+1) != strlen($Name) AND $POSd != false)
                                {
                                        continue; 
                                }

                                $FileinfoGeneric = array('name' => $Name, 'realname' => $Name, 'hash' => $Value->getCrc(), 'id' => $Key, 'size' => $Value->getPackedSize(), 'mtime' => strtotime($Value->getFileTime()), 'comp_size' => $Value->getUnpackedSize());

                                $File[$Key] = $FileinfoGeneric;
                        }
                } else {
			$Dir = $this->manipulateBackslash($Dir);

                        $DirLen = strlen($Dir);
                        //$Found = false; // tried to optimalize the script
                        //$Current = false;
			$DirEnd = $Dir[($DirLen-1)];

                        foreach ($Files as $Key => $Value) {
                                $Name = $Value->getName();

				if($Name[($DirLen-1)] == $DirEnd)
                                {
                                        if(substr($Name, 0, $DirLen) == $Dir)
                                        {
                                                $Name = substr($Name, $DirLen, strlen($Name));

						if($Name == false)
							continue;

                                                $NameLen = strlen($Name);

						$POSd = strpos($Name, '/');

						if(($POSd+1) != $NameLen AND $POSd != false)
                                                {
                                                        continue; 
                                                }

                                                $FileinfoGeneric = array('name' => $Name, 'realname' => $Name, 'hash' => $Value->getCrc(), 'id' => $Key, 'size' => $Value->getPackedSize(), 'mtime' => strtotime($Value->getFileTime()), 'comp_size' => $Value->getUnpackedSize());

                                                $File[$Key] = $FileinfoGeneric;

                                                //$Found = true;
                                                //$Current = true;
                                        }// else
                                                //$Current = false;
                                }// else 
                                        //$Current = false;

                                // if all files found, stop listing
                                //if($Found == true AND $Current == false)
					//break;
                        }
                }

                return $File;
        }

	// checking if all is right with RAR object
        private function checkResource()
        {
               if(!is_object($this->Archive))
               {
                       throw new Exception( 'libarchive_rar::RAR_RESOURCE::checkResource(): Existing RAR file is invalid, or cant be created.');
               }
        }

	// allows to easy delete/place backlashes from begin or end of string
	private function manipulateBackslash($Dir, $Action='')
	{
		$DirLen = strlen($Dir);

		if($Action == 'strip-begin')
		{
			if($Dir[0] == '/')
				$Dir = substr($Dir, 1, $DirLen);
		} else {
			if($Dir[($DirLen-1)] != '/')
				$Dir = $Dir. '/';
		}

		return $Dir;
	}

	// extract to $Destination specified Entries in $Entries (can be skipped, format is array('item1', 'item2')) return true or false
	public function extract($Destination, $Entries='')
	{
		$this->checkResource();
		$Destination = $this->manipulateBackslash($Destination);

		// extract whole archive
		if($Entries == '')
		{
			$Files = $this->Archive->getEntries();

			foreach ($Files as $Key => $Value)
			{
				$Value->extract($Destination);
			}
			return true;
		} elseif (is_array($Entries)) {
			foreach ($Entries as $Key => $Value)
			{
				$Entry = $this->Archive->getEntry($this->manipulateBackslash($Value, 'strip-begin'));

				if($Entry->isDirectory())
				{
					$this->extractDirectory($Destination, $Value);

					//echo "mv ".$this->TMP.$Value." ".$Destination.basename($Value)."<br/>";
					$this->copyDirectory($this->TMP.$Value, $Destination.basename($Value));

					//echo "rm -rf " .$this->TMP.$this->getFirstDir($Value)."<br/>";
					$this->deleteDirectory($this->TMP.$this->getFirstDir($Value));
					continue;
				}

				if($Entry)
				{
					// get directory where is our file (when we want to unpack eg. docs/index.html from archive - the RarEntry->extract() will create docs directory but we dont want this directory, we want index.html)
					$FirstDir = $this->getFirstDir($Value);
					$Entry -> extract($this->TMP); // extract to temporary directory
					rename($this->TMP.$Value, $Destination.basename($Value)); // move to destination
					//echo "mv ".$this->TMP.$Value." ".$Destination.basename($Value)."<br/>";

					// remove unpacked directory from temp (not wanted)
					if($FirstDir != false AND $FirstDir != '')
					{
						$this->deleteDirectory($this->TMP.$FirstDir);
						//echo "rm -rf ".$this->TMP.$FirstDir."<br/>";
					}
				}
			}

			return true;
		} //else
			//return false;
	}

	private function copyDirectory($Source, $Destination)
	{
		if(is_dir($Source))
		{
			@mkdir($Destination);
			//echo '@mkdir ' .$Destination. '<br/>';
			$Files = scandir($Source);
			
			foreach ($Files as $Key => $Value)
			{
				if($Value == '.' OR $Value == '..')
					continue;

				//echo 'Trying: ' .$Source.$Value. '<br/>';
				if(is_dir($Source.$Value))
				{
					$this->copyDirectory($Source. '/' .$Value, $Destination. '/' .$Value);
					continue;
				}

				//echo '# copying ' .$Source. '/' .$Value. ' to ' .$Destination. '/' .$Value. '<br/>';
				copy($Source. '/' .$Value, $Destination. '/' .$Value);
			}

		} else {
			copy($Source,$Destination);		
		}
	}

	// remove directory recursively (rm -rf /tmp/directory)
	private function deleteDirectory($Dir)
	{
		$Items = scandir($Dir);

		foreach ($Items as $Key => $Value)
		{
			if($Value == '..' OR $Value == '.')
				continue;

			if(is_dir($Dir."/".$Value))
			{
				$this->deleteDirectory($Dir."/".$Value);
				@rmdir($Dir."/".$Value);
			} else {
				unlink($Dir."/".$Value);
			}
		}

		@rmdir($Dir);

		if(!is_dir($Dir))
			return true;
	}

	private function extractDirectory($Destination, $Directory)
	{
		$Entries = $this->Archive->getEntries();
		sort($Entries);
		$DirLen = strlen($Directory);

		foreach ($Entries as $Key => $Value)
		{
			$Name = $Value->getName();


			if(substr($Name, 0, $DirLen) == $Directory)
				// directory
				if($Value->isDirectory())
				{
					@mkdir($this->TMP.$Name);
					//echo 'mkdir ' .$this->TMP.$Name. '<br/>';
				} else { // file
					//echo 'touch ' .$this->TMP. '<br/>';
					$Value->extract($this->TMP);
				}
			}
		}
	}

	private function getFirstDir($Dir)
	{
		$Exp = explode('/', $Dir);

		if(count($Exp) == 0)
			return false;

		if($Exp[0] != '')
			return $Exp[0];
		else
			return $Exp[1];
	}

	// Read file and return its contents
	// We cant provide stream as result because other archives will not support this, Boxman is universal archive interface
	public function readFile($File)
	{
		$this->checkResource();

		$Contents = false;
		// if input is an array
		if(is_array($File))
		{
			$Entry = $this->Archive->getEntry($File['realname']);
		} else { // or its just a string with fullname
			$Entry = $this->Archive->getEntry($File);
	
		}

		$fp = $Entry->getStream();

		if($fp)
		{
			while (!feof($fp))
			{
				$Contents .= fread($fp, 8192);	
			}

			fclose($fp);
		}

		return $Contents;
	}
}
?>
