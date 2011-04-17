<?php
$EXT_INF = array ( 'classname' => 'tuxSQLite');

class tuxSQLite extends KernelModule
{
	private $Sockets, $Kernel, $CurrentSocket, $AltDB;
	public $PREFIX, $PREFIX_Cache, $SocketType='sqlite3', $Type='objective-sqlite', $Debug=false, $Version='tuxSQLite 0.2';

	public function __construct ( &$Params, &$Kernel )
	{
		$this -> Kernel = &$Kernel;
		// will create new database if old does not exists

		if(!is_array($Params))
		{
			throw new Exception($this->Version. '::E_ERROR::ConnectDB:__construct: $CFG[\'db\'] in config.php or #2 parametr of modprobe is not an array');
		}

		//$this->ConnectDB($Params[0]['db']['db']);

		// create new default socket
		$this -> Sockets['default'] = new SQLite3($Params[0]['db']['db']);
		$this -> CurrentSocket = 'default';

		$this -> PREFIX = $Params[0]['db']['prefix'];
		$this -> Debug = @$Params[0]['db']['debug'];

		// alternative sockets
		$this -> AltDB = $Params[0]['db']['alt'];

		// default socket (just a binding)
		$this->AltDB['default'] = array('prefix' => $Params[0]['db']['prefix']);

		$Kernel -> setAsDefault ( 'sqlite', 'SQL' );
	}

	public function __get ( $Variable )
	{
		switch ( $Variable )
		{
			case 'DB':
				return $this->Sockets[$this->CurrentSocket];
			break;

			// removed "error" because its no longer supported
		}
	}

	private function ConnectDB($DBName)
	{
		if(!isset($this->AltDB[$DBName]))
			return false;

		$a = false;

		// creating new connection to database
		if(isset($this->AltDB[$DBName]['db']))
		{
			$this -> Kernel -> error_handler -> logString ( $this->Version. '::E_INFO::ConnectDB: Connecting to ' .$this->AltDB[$DBName]['db']. ' database.');

			// i think its important to add this debugging information
			if (!is_file($this->AltDB[$DBName]['db']))
			{
				$this -> Kernel -> error_handler -> logString ( $this->Version. '::E_ERROR::ConnectDB: Database "' .$this->AltDB[$DBName]['db']. '" does not exists, created new database');
			}

			// new connection
			$this -> Sockets[$DBName] = new SQLite3 ($this->AltDB[$DBName]['db']);

			// set connection as default
			$this->CurrentSocket = $DBName;
			$a = true;
		}

		// if its a prefix change
		if(isset($this->AltDB[$DBName]['prefix']))
		{
			$this->PREFIX = $this->AltDB[$DBName]['prefix'];

			if ($a == False)
			{
				$this->Sockets[$DBName] = &$this->Sockets[$this->CurrentSocket];
				// set connection as default
				$this->CurrentSocket = $DBName;
				$a = true;
			}
		}

		return $a;
	}

	// we are able to connect to many databases at one time and switch between them
	public function SwitchDB ($DBName)
	{
		if($this->CurrentSocket == $DBName)
			return true;

		// if socket already exists we will just set it as default
		if ($this->Sockets[$DBName])
		{
			$this->CurrentSocket = $DBName;

			// set prefix if present
			if(isset($this->AltDB[$DBName]['prefix']))
				$this->PREFIX = $this->AltDB[$DBName]['prefix'];
		} else {
			// if socket does not exists, we will create it here
			$this->ConnectDB($DBName);
		}
	}

	public function SwitchPrefixForOneQuery($Prefix)
	{
		$this->PREFIX_Cache = $this->PREFIX;
		$this->PREFIX = $Prefix;

		return true;
	}

	public function RestorePrefix()
	{
		if($this->PREFIX_CACHE != false)
		{
			$this->PREFIX = $this->PREFIX_Cache;
			$this->PREFIX_CACHE = false;
		}
	}

	public function query ( $Query )
	{
		if($this->Debug != false)
			$this->Kernel->error_handler->logString( $this->Version. '::E_DEBUG::query: Executing ' .$Query);

		$this->RestorePrefix();

		return $this -> Sockets [ $this -> CurrentSocket ] -> query ( $Query );
	}

	public function Select ( $What, $From, $Where='', $OrderBy='', $POS='', $LimitFrom='', $LimitTo='' )
	{
		$What = $this->convertWhat ( $What );
		//# Example: SELECT * FROM TABLE WHERE asd ORDERBY
		
		//# What and From
		$SQL = 'SELECT ' .$this->DB->escapeString($What). ' FROM `' .$this->PREFIX.$this->DB->escapeString($From). '`';

		//# Where
		if ( $Where != '' )
		{
			$Where = $this->convertWhere ( $Where );
			$SQL .= ' WHERE' .$Where;
		}

		//# OrderBy and POS
		if ( $OrderBy != '' )
		{
			$OrderBy = $this->convertWhat($OrderBy);
			$SQL .= ' ORDER BY ' .$OrderBy;

			//# Pos ( ASC, DESC )
			if ( $POS == 'DESC' )
				$SQL .= ' DESC'; # DESCENDING 
			else
				$SQL .= ' ASC'; # ASCENDING ( DEFAULT )
		}

		//# Limit
		if ( $LimitFrom != '' AND $LimitTo != '' )
		{
			$SQL .= ' LIMIT ' .intval($LimitFrom). ',' .intval($LimitTo);
		}
		
		return new tuxMyDB_Object ( $this->query($SQL) );
	
	}

	//# UPDATE `users` SET `id`="1" WHERE `name`="Dawid" ORDER BY `id` ASC LIMIT 1,1
	public function Update ( $What, $Set, $Where='', $OrderBy='', $POS='', $LimitFrom='', $LimitTo='' )
	{
		$Set = $this->convertWhere($Set);
		//# WARNING! NO PREFIX IS USED HERE!
		$SQL = 'UPDATE `' .$this->PREFIX.$this->DB->escapeString($What). '` SET ' .$Set;


		//# Where
		if ( $Where != '' )
		{
			$Where = $this->convertWhere ( $Where );
			$SQL .= ' WHERE ' .$Where;
		}

		//# OrderBy and POS
		if ( $OrderBy != '' )
		{
			$OrderBy = $this->convertWhat($OrderBy);
			$SQL .= ' ORDER BY ' .$OrderBy;

			//# Pos ( ASC, DESC )
			if ( $POS == 'DESC' )
				$SQL .= ' DESC'; # DESCENDING 
			else
				$SQL .= ' ASC'; # ASCENDING ( DEFAULT )
		}

		//# Limit
		if ( $LimitFrom != '' AND $LimitTo != '' )
		{
			$SQL .= ' LIMIT ' .intval($LimitFrom). ',' .intval($LimitTo);
		}

		return new tuxMyDB_Object ( $this->query($SQL) );
	}

	//# INSERT INTO `table` (`id`, `test`) VALUES (1, 'blahblah')
	public function Insert ($What='', $Fields, $Values)
	{
		$SQL = 'INSERT INTO `' .$this->PREFIX.$this->DB->escapeString($What). '` (' .$this->convertFields($Fields). ') VALUES (' .$this->convertFields($Values). ');';

		return new tuxMyDB_Object ( $this->query($SQL) );
	}


	//# DELETE FROM `users` WHERE `id`="1"
	public function Delete ($From, $Where='', $LimitFrom='', $LimitTo='')
	{

		$SQL = 'DELETE FROM `' .$this->PREFIX.$this->DB->escapeString($From). '`';

		//# Where
		if ( $Where != '' )
		{
			$Where = $this->convertWhere ( $Where );
			$SQL .= ' WHERE ' .$Where;
		}

		//# Limit
		if ( $LimitFrom != '' AND $LimitTo != '' )
		{
			$SQL .= ' LIMIT ' .intval($LimitFrom). ',' .intval($LimitTo);
		}

		return new tuxMyDB_Object ( $this->query($SQL) );
	}

	//# TRUNCATE TABLE `table`
	public function TruncateTable ( $Table )
	{
		return new tuxMyDB_Object ( $this->query('TRUNCATE TABLE `' .$this->DB->escapeString($Table). '`'));
	}

	//# DROP TABLE `table`
	public function DropTable ( $Table )
	{
		return new tuxMyDB_Object ( $this->query('DROP TABLE `' .$this->DB->escapeString($Table). '`'));
	}

	//# DROP TABLE `table`
	public function DropDatabase ( $Database )
	{
		return new tuxMyDB_Object ( $this->query('DROP DATABASE `' .$this->DB->escapeString($Database). '`'));
	}

	//# DUMMY FUNCTION, IF YOU WANT TO IMPLEMENT IT JUST DO IT...
	public function AlterTable ()
	{
		return true;
	}

/*
	# CONVERT ONLY ARRAYS TO STRING
	public function convertWhere ( $Where )
	{
		if ( !is_array ( $Where ) )
			return false;

		//# lets start the counter here
		$Count = count($Where);
		$i=0;

		foreach ( $Where as $Key => $Value )
		{
			//# tic-tac
			$i++;

			if ( $Value[0] == '+' AND $Value[1] == '_' )
			{
				$Value = $this->DB->escapeString($Value);
				$String .= $this->DB->escapeString($Key). '=' .substr($Value, 2, strlen ( $Value ) );
			} else
				$String .= $this->DB->escapeString($Key). '="' .$this->DB->escapeString($Value). '"';

			if ( $i == $Count )
				continue;
				
			$String .= ' AND ';
		}

		return $String;
	}
*/

	public function convertWhat ( $What )
	{
		if ( $What == '*' )
			return '*';
		elseif ( is_array ( $What ) ) {
			$Count = count ( $What );
			$String = NuLL;

			//# lets start the counter here
			$i=0;
			foreach ( $What as $Key => $Value )
			{
				//# tic-tac
				$i++;
				$String .= '`' .$Value. '`';

				if ( $i == $Count )
					continue;
				
				$String .= ',';
			}		

			return $String;
		} elseif ( is_string ( $What ) ) {
			return $What;		
		}
	}

	/* CONVERT ARRAY TO FIELDS/VALUES SYNTAX IN SQL LANGUAGE, example: input - array('id', 'name') or values input - array(1, 'Anna') */
	public function convertFields ($Array)
	{
		$TotalFields = count($Array);
		$Position = 0;
		foreach ($Array as $Key => $Value)
		{
			$Position++;

			if($Value == NuLL OR $Value == "NULL")
				$SQL .= 'NULL';
			elseif(is_numeric($Value))
				$SQL .= $Value; 
			elseif(is_int($Value))
				$SQL .= $Value;
			else
				$SQL .= '"' .$this->DB->escapeString($Value). '"';

			if($Position == $TotalFields)
				continue;

			$SQL .= ',';
		}

		return $SQL;
	}

	public function convertWhere ( &$What )
	{
		if ( get_class ( $What ) != 'tuxMyDB_WhereClause' )
		{
			$this -> Kernel -> error_handler -> logString ( $this->Version. '::E_ERROR::convertWhere: Invalid Where Clause type "' .get_class($What));
			return false;
		}


		return $What -> Show();
	}

	public function error()
	{
		// if error is serious
		if ( !empty ( $this -> Socket -> connect_error ) )
			return $this -> Sockets [ $this -> CurrentSocket ] ->connect_error;

		return $this -> Sockets [ $this -> CurrentSocket ] -> error;
	}
}

class tuxMyDB_Object
{
	private $Resource, $AssocArray=NuLL;

	public function __construct ( $SQL  )
	{
		$this -> Resource = $SQL;
	}

	public function __get ( $Variable )
	{
		if($this->Resource == false)
			return false;

		switch ( $Variable )
		{
			case 'fetch_assoc':
				if (is_null($this->AssocArray))
				{
					$i=0;
					while ($Entry = $this->Resource->fetchArray(SQLITE3_ASSOC))
					{
						$i++;
						$Array[] = $Entry;
					}

					// return values like MySQL
					if ($i == 1)
					{
						unset($i);
						return $Array[0];
					}
					

					return $Array;
				} else {
					return $this->AssocArray;				
				}
			break;

			case 'num_rows':
				// this is'nt best way, but its necessary because SQLite3 class doesnt support num_rows function at now
				$i=0;
				while ($Entry = $this->Resource->fetchArray(SQLITE3_ASSOC))
				{
					$i++;
					$Array[] = $Entry;
				}

				// return values like MySQL
				if ($i == 1)
				{
					$this->AssocArray = $Array[0];
				} else {
					$this->AssocArray = $Array;				
				}

				unset($Array);

				return $i;
			break;

			case 'fetch_array':
				while ($Entry = $this->Resource->fetchArray(SQLITE3_BOTH))
				{
					$Array[] = $Entry;
				}

				return $Array;
			break;

			case 'DB':
				return $this->Resource;
			break;

			// removed "error" because its no longer supported
		}
	}
}

class tuxMyDB_WhereClause
{
	private $SQL=NuLL, $DB;

	public function escapeString($String)
	{
		return str_ireplace('ALTER TABLE', '', addslashes($String));
	}

	public function Add ( $Statement, $Column, $Equals, $Value )
	{
		$Equals_list = array ( '=' , '!=', '<', '>', '<=', '>=', 'LIKE' );

		if ( !in_array ( $Equals, $Equals_list ) ) //# Needle, haystack...
			return false;

		if ($Equals == 'LIKE')
			$Equals = ' LIKE '; // to be valid with syntax

		$Statement_list = array ( 'OR', 'AND', '', ',' );
	
		if ( !in_array ( $Statement, $Statement_list ) ) //# Needle, haystack...
			return false;

		if ( $this -> SQL == NuLL )
		{
			$Statement = '';	
		}

		// PATH ALLOWING OPERATIONS LIKE column=column+1
		if ( $Value[0] == '+' AND $Value[1] == '_' )
		{
			$Value = $this->escapeString($Value);
			$Value = substr($Value, 2, strlen ( $Value ) );
		} else
			$Value = '"' .$this->escapeString($Value). '"';

		//          AND                     id           ==            1
		$this->SQL .= $Statement. ' `' .$Column. '`' .$Equals. '' .$Value. ' ';

		return true;
	}

	public function Show ()
	{
		return $this->SQL;
	}
}
?>
