<?php
### ZIPArchive implementation for OpenWikiBlog FOR tuxKernel by WebNuLL
### Licensed under AGPLv3 ( Affero GPLv3 )
# http://wiki.github.com/webnull/OpenWikiBlog/

$EXT_INF = array ( 'classname' => 'libarchive_zip');

class libarchive_zip extends KernelModule
{
        private $Params, $Debug=false, $Archive;
	public $TMP='data/tmp/';

        public function __construct ( $Params='', &$Kernel )
        {
                $this -> Kernel = $Kernel;
                $this->state = 'ready';
                $this->Params = &$Params;

                if(!class_exists('ZipArchive'))
                	throw new Exception('libarchive_zip::ZIP_SUPPORT::__contruct(): PHP is compiled without ZIP functions, cannot continue, please recompile your PHP.');

                if($Params != '')
                {
                        if(!is_file($Params))
                	       throw new Exception('libarchive_zip::ZIP_NOFILE::__contruct(): Cant find "' .$Params. '" file.');

                        $this->Archive = new ZipArchive;
                        $this->Archive->open($Params);
                }

        }

	// create new empty archive
        public function createArchive($Filesystem_File)
        {
                $this->Archive = new ZipArchive($Filesystem_File, ZipArchive::CREATE);

                return true;
        }

	// lists items from archive
        public function ls($Dir='')
        {
                $this->checkResource();

                $FilesNum = $this->Archive->numFiles;
                $File = array();

                if($Dir == '')
                {
                        for ($i=0; $i<$FilesNum; $i++) {
                                $Name = $this->Archive->getNameIndex($i);
                                $Fileinfo = $this->Archive->statIndex($i);
                                $FileinfoGeneric = array('name' => $Name, 'realname' => $Fileinfo['realname'], 'hash' => $Fileinfo['crc'], 'id' => $Fileinfo['index'], 'size' => $Fileinfo['size'], 'mtime' => $Fileinfo['mtime'], 'comp_size' => $Fileinfo['comp_size']);

				if($Name != false)
                                        $File[$i] = $FileinfoGeneric;
                        }
		} elseif ($Dir == '/') {
			for ($i=0; $i<$FilesNum; $i++) {
                                $Name = $this->Archive->getNameIndex($i);

                                $POSd = strpos($Name, '/');

				if(($POSd+1) != strlen($Name) AND $POSd != false)
                                {
                                        continue; 
                                }

                                $Fileinfo = $this->Archive->statIndex($i);
                                                $FileinfoGeneric = array('name' => $Name, 'realname' => $Fileinfo['name'], 'hash' => $Fileinfo['crc'], 'id' => $Fileinfo['index'], 'size' => $Fileinfo['size'], 'mtime' => $Fileinfo['mtime'], 'comp_size' => $Fileinfo['comp_size']);

				if($Name != false)
                                        $File[$i] = $FileinfoGeneric;
                        }
                } else {
			$Dir = $this->manipulateBackslash($Dir);
                        $DirLen = strlen($Dir);
                        //$Found = false; // tried to optimalize the script
                        //$Current = false;
			$DirEnd = $Dir[($DirLen-1)];

                        for ($i=0; $i<$FilesNum; $i++) {
                                $Name = $this->Archive->getNameIndex($i);

				if($Name[($DirLen-1)] == $DirEnd)
                                {
                                        if(substr($Name, 0, $DirLen) == $Dir)
                                        {
                                                $Name = substr($Name, $DirLen, strlen($Name));
                                                $NameLen = strlen($Name);

						$POSd = strpos($Name, '/');

						if(($POSd+1) != $NameLen AND $POSd != false)
                                                {
                                                        continue; 
                                                }

                                                $Fileinfo = $this->Archive->statIndex($i);
                                                $FileinfoGeneric = array('name' => $Name, 'realname' => $Fileinfo['name'], 'hash' => $Fileinfo['crc'], 'id' => $Fileinfo['index'], 'size' => $Fileinfo['size'], 'mtime' => $Fileinfo['mtime'], 'comp_size' => $Fileinfo['comp_size']);

						if($Name != false)
                                                        $File[$i] = $FileinfoGeneric;

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

	// checking if all is right with ZIP object
        private function checkResource()
        {
               if(!is_object($this->Archive))
               {
                       throw new Exception( 'libarchive_zip::ZIP_RESOURCE::checkResource(): Existing ZIP file is invalid, or cant be created.');
               }
        }

	// This function is built to add whole directories and files recursively
        public function addObject($Filesystem_File, $VirtualName)
        {
		$this->checkResource();

		// add single file to archive
                if(is_file($Filesystem_File) AND is_readable($Filesystem_File)) {
			return $this->Archive->addFile($Filesystem_File, $VirtualName);
		} elseif (is_dir($Filesystem_File)) { // add whole directory and its subdirectories
			$this->addEmptyDirsByRecursion($VirtualName);
			$this->addDirByRecursion($Filesystem_File, $Filesystem_File, $VirtualName);
			return true;
		} else {
			return false; // file does not exists
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

	// this special function is adding full directories of files recursively
	private function addDirByRecursion($Dir, $OriginalDir, $VirtualDir)
	{
		// delete backslash to avoid path coliding in script
		$DirLen = strlen($Dir);
		$OriginalDirLen = strlen($OriginalDir);

		if($Dir[($DirLen-1)] == '/')
			$Dir = substr($Dir, 0, ($DirLen-1));

		$Items = scandir($Dir);

		foreach ($Items as $Key => $Value)
		{
			if($Value == '.' OR $Value == '..')
				continue;

			if(is_dir($Dir. '/' .$Value))
			{
				$inArchivePath = $this->manipulateBackslash($VirtualDir).substr($this->manipulateBackslash($Dir).$Value, $OriginalDirLen, $DirLen);
				$this->Archive->addEmptyDir($inArchivePath); // create new directory
				//echo 'mkdir ' .$inArchivePath. '<br/>'; // debugging informations
				$this->addDirByRecursion($Dir. '/' .$Value, $OriginalDir, $VirtualDir); // search for new directories to add...
			} elseif (is_file($Dir. '/' .$Value) AND is_readable($Dir. '/' .$Value)) {
				$inArchivePath = $this->manipulateBackslash($VirtualDir).$this->manipulateBackslash(substr($this->manipulateBackslash($Dir).$Value, $OriginalDirLen, $DirLen), 'strip-begin');
				//echo 'cp ' .$Dir. '/' .$Value. ' ' .$inArchivePath. ' - test: ' .$this->manipulateBackslash($VirtualDir). '<br/>'; // debugging informations
				$this->Archive->addFile($Dir. '/' .$Value, $inArchivePath); // add file from current directory
			}
		}
	}

	// Create empty directories recursively from string eg. root-directory/subdirectory1/subdirectory2/subdirectory3
	private function addEmptyDirsByRecursion ($Dir)
	{
		$Exp = explode('/', $Dir);
		$NewDir = '';

		foreach ($Exp as $Key => $Value)
		{
			if(empty($Value))
				continue;

			$NewDir .= $Value. '/';
			$this->Archive->addEmptyDir($NewDir);
		}

		return true;
	}

	// Rename file (or move)
	public function mv($Source,$Destination)
	{
		$this->checkResource();
		return $this->Archive->renameName($Source,$Destination);
	}

	// extract to $Destination specified Entries in $Entries (can be skipped, format is array('item1', 'item2')) return true or false
	public function extract($Destination, $Entries='')
	{
		$this->checkResource();

		if($Entries == '')
			return $this->Archive->extractTo($Destination);
		else {
			// check if its array
			if(!is_array($Entries))
				return false;

			$FilesNum = $this->Archive->numFiles;

			foreach ($Entries as $Key => $Value)
			{
				$ValueLen = (strlen($Value)-1);
				$Entry = $this->Archive->statName($Value);

				if($Value[$ValueLen] == '/' /*AND $Entry['size'] == 0 AND $Entry['crc'] == 0*/) // is a directory?
				{
					$Exp = explode('/', $Value);
					$ExpCount = count($Exp);

					if($Exp[0] == '')
						$Exp[0] = $Value;

					$this->extractDir($this->TMP, $Value, $Exp[0]);
					$this->copyDirectory($this->TMP.$Value. '/', $Destination);
					//echo '# cp ' .$this->TMP.$Value. '/ ' .$Destination. '<br/>';
					$this->deleteDirectory($this->TMP.$Exp[0]);
					//echo 'rm ' .$this->TMP.$Exp[0]. '<br/>';
				} else {
					$this->Archive->extractTo($this->TMP, $Value);
					#echo 'rename ' .$this->TMP. '/' .$Value. ' to ' .$Destination. '/' .basename($Value). '<br/>';
					rename($this->TMP. '/' .$Value, $Destination. '/' .basename($Value));
					$Exp = explode('/', $Value);

					if($Exp[0] != '')
						$this->deleteDirectory($this->TMP. '/' .$Exp[0]);
				}

				//echo $Value;
			}
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

	// I was going throught hell implementing this feature...
	private function extractDir($Destination, $Value, $Origin)
	{
		$FilesNum = $this->Archive->numFiles;
                $File = array();
		$ValueLen = (strlen($Value)-1);

		//$Exp = explode('/', $Value);
		//var_dump($Exp[count($Exp)-2]);

                for ($i=0; $i<$FilesNum; $i++) {
                	$Name = $this->Archive->getNameIndex($i);

			if($Name[$ValueLen] == $Value[$ValueLen])
			{
				if(substr($Name, 0, ($ValueLen+1)) == $Value)
				{
					$NameLen = strlen($Name);
					$NewDir = substr($Name, ($ValueLen+1), strlen($Name));

					// extracting directories
					if($Name[($NameLen-1)] == '/')
					{
						if($NewDir == '') // is root directory? ($Name == $Value)
						{
							continue; // mkdir -p will be used instead of manual directory creating 
							//$TMP = explode('/', $Name);
							//mkdir($Destination. '/' .$TMP[(count($TMP)-1)]. '/');
							//echo 'mkdir ' .$Destination. '/' .$TMP[(count($TMP)-1)]. '/<br/>';
						} else {
							@mkdir($Destination.$Origin. '/' .$NewDir, 0755, true);
							//echo 'mkdir -p ' .$Destination.$Origin. '/' .$NewDir. '<br/>';
						}

					} else {
						// extracting files
						//echo 'touch ' .$Destination.$Origin. '/' .$NewDir. ' | Archive->extractTo("' .$Destination. '", "' .$Name. '");<br/>';
						$this->Archive->extractTo($Destination, $Name);
					}
				}
			}
		}
	}

	// create empty directory inside of archive
	public function mkdir($Directory, $Real=true)
	{
		$this->checkResource();
		return $this->Archive->addEmptyDir($Directory);
	}

	// delete file from archive
	public function rm($Index)
	{
		// handle array to get index number from it
		if(is_array($Index))
		{
			$ID = $Index['id'];

			if(!is_int($ID))
				return false;
		} else {// or just index number
			$ID = $Index;
		}

		return $this->Archive->deleteIndex($ID);
	}

	// Read file and return its contents
	// We cant provide stream as result because other archives will not support this, Boxman is universal archive interface
	public function readFile($File)
	{
		$Contents = false;
		// if input is an array
		if(isset($File['realname']))
		{
			return $this->Archive->getFromName($File['realname']);
		} else { // or its just a string with fullname
			return $this->Archive->getFromName($File);
		}
	}

	// return true when file saved, remember to set correct read-write permissions to file and *directory* where file is placed for PHP user
	public function close()
	{
		return $this->Archive->close();
	}
}
?>
