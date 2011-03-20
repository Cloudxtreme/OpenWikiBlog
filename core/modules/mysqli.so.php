<?php
$EXT_INF = array ( 'classname' => 'tuxMyDB');

class tuxMyDB extends KernelModule
{
	private $Sockets, $CurrentSocket, $Version='tuxMyDB 0.25', $Kernel, $AltDB;
	public $PREFIX, $SocketType='mysqli', $PREFIX_Cache, $Type='mysql', $Debug;

	public function __construct ( &$Params, &$Kernel )
	{
		$this -> Sockets['default'] = new mysqli ( $Params[0]['db']['host'], $Params[0]['db']['user'], $Params[0]['db']['passwd'], $Params[0]['db']['db'] );
		$this -> CurrentSocket = 'default';

		$this -> PREFIX = $Params[0]['db']['prefix'];
		$this -> Debug = @$Params[0]['db']['debug'];
		$this -> Kernel = &$Kernel;

		// alternative sockets
		$this -> AltDB = $Params[0]['db']['alt'];

		$Kernel -> setAsDefault ( 'mysqli', 'SQL' );
	}

	public function query ( $Query )
	{
		if($this->Debug != false)
			$this->Kernel->error_handler->logString( $this->Version. '::E_DEBUG::query: Executing ' .$Query);

		$this->RestorePrefix();

		return $this -> Sockets [ $this -> CurrentSocket ] -> query ( $Query );
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

	private function ConnectDB($DBName)
	{
		if(!isset($this->AltDB[$DBName]))
			return false;

		$a = false;

		// if its a prefix change
		if(isset($this->AltDB[$DBName]['prefix']))
		{
			$a = true;
			$this->PREFIX = $this->AltDB[$DBName]['prefix'];
		}

		// creating new connection to database
		if(isset($this->AltDB[$DBName]['host']))
		{
			$this -> Kernel -> error_handler -> logString ( $this->Version. '::E_INFO::ConnectDB: Connecting to ' .$DBName. ' database.');

			// i think its important to add this debugging information
			if (!is_file($DBName))
			{
				throw new Exception($this->Version. '::NO_DB::ConnectDB: Database "' .$DBName. '" does not exists');
			}

			// new connection
			$this -> Sockets[$DBName] = new mysqli ( $this->AltDB[$DBName]['host'], $this->AltDB[$DBName]['db']['user'], $this->AltDB[$DBName]['passwd'], $this->AltDB[$DBName]['db'] );

			// set connection as default
			$this->CurrentSocket = $DBName;

			return true;
		}

		// changing database
		if(isset($this->AltDB[$DBName]['db']))
		{
			$this->DB->select_db($this->AltDB[$DBName]['db']);
			return true;
		}
		

		return $a;
	}

	// we are able to connect to many databases at one time and switch between them
	public function SwitchDB ($DBName)
	{
		// if socket already exists we will just set it as default
		if (isset($this->Sockets[$DBName]))
		{
			$this->CurrentSocket = $DBName;
		} else {
			// if socket does not exists, we will create it here
			$this->ConnectDB($DBName);
		}
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

	public function Select ( $What, $From, $Where='', $OrderBy='', $POS='', $LimitFrom='', $LimitTo='' )
	{
		$What = $this->convertWhat ( $What );
		//# Example: SELECT * FROM TABLE WHERE asd ORDERBY
		
		//# What and From
		$SQL = 'SELECT ' .mysql_escape_string($What). ' FROM `' .$this->PREFIX.mysql_escape_string($From). '`';

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

	//# UPDATE `users` SET `id`="1" WHERE `name`="Damian" ORDER BY `id` ASC LIMIT 1,1
	//# WARNING! NO PREFIX IS USED HERE
	public function Update ( $What, $Set, $Where='', $OrderBy='', $POS='', $LimitFrom='', $LimitTo='' )
	{
		$Set = $this->convertWhere($Set);

		//# WARNING! NO PREFIX IS USED HERE!
		$SQL = 'UPDATE `' .mysql_escape_string($What). '` SET ' .$Set;

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
		$SQL = 'INSERT INTO `' .$this->PREFIX.mysql_escape_string($What). '` (' .$this->convertFields($Fields). ') VALUES (' .$this->convertFields($Values). ');';

		return new tuxMyDB_Object ( $this->query($SQL) );
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
				$SQL .= '"' .mysql_escape_string($Value). '"';

			if($Position == $TotalFields)
				continue;

			$SQL .= ',';
		}

		return $SQL;
	}

	//# DELETE FROM `users` WHERE `id`="1"
	public function Delete ($From, $Where='', $LimitFrom='', $LimitTo='')
	{

		$SQL = 'DELETE FROM `' .$this->PREFIX.mysql_escape_string($From). '`';

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
		return new tuxMyDB_Object ( $this->query('TRUNCATE TABLE `' .mysql_escape_string($Table). '`'));
	}

	//# DROP TABLE `table`
	public function DropTable ( $Table )
	{
		return new tuxMyDB_Object ( $this->query('DROP TABLE `' .mysql_escape_string($Table). '`'));
	}

	//# DROP TABLE `table`
	public function DropDatabase ( $Database )
	{
		return new tuxMyDB_Object ( $this->query('DROP DATABASE `' .mysql_escape_string($Database). '`'));
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
				$Value = mysql_escape_string($Value);
				$String .= mysql_escape_string($Key). '=' .substr($Value, 2, strlen ( $Value ) );
			} else
				$String .= mysql_escape_string($Key). '="' .mysql_escape_string($Value). '"';

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

	public function convertWhere ( &$What )
	{
		if ( get_class ( $What ) != 'tuxMyDB_WhereClause' )
		{
			$this -> Kernel -> error_handler -> logString ( $this->Version. '::E_ERROR::convertWhat: Invalid Where Clause type "' .get_class($What));
			return false;
		}


		return $What -> Show();
	}

	public function error()
	{
		// if error is serious
		if ( !empty ( $this -> Socket -> connect_error ) )
			return $this->Socket->connect_error;

		return $this -> Socket -> error;
	}
}

class tuxMyDB_Object
{
	private $Resource;

	public function __construct ( $SQL  )
	{
		$this -> Resource = $SQL;
	}

	public function __get ( $Variable )
	{
		switch ( $Variable )
		{
			case 'fetch_assoc':
				return $this->Resource->fetch_assoc();
			break;

			case 'num_rows':
				return $this->Resource->num_rows;
			break;

			case 'fetch_array':
				return $this->Resource->fetch_array();
			break;

			// removed "error" because its no longer supported
		}
	}
}

class tuxMyDB_WhereClause
{
	private $SQL=NuLL;
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
			$Value = mysql_escape_string($Value);
			$Value = substr($Value, 2, strlen ( $Value ) );
		} else
			$Value = '"' .mysql_escape_string($Value). '"';

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
