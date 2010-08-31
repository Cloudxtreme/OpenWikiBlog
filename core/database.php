<?php
class tuxMyDB 
{
	private $Socket, $SID, $Version='tuxMyDB 0.25', $Kernel;
	public $PREFIX, $SocketType='mysqli', $Type='mysql', $Debug;

	public function __construct ( &$DB, &$CFG, &$Kernel )
	{
		$this -> Socket = new mysqli ( $DB['host'], $DB['user'], $DB['passwd'], $DB['db'] );
		$this -> SID = $CFG [ 'site_id' ];
		$this -> PREFIX = $DB['prefix'];
		$this -> Kernel = &$Kernel;

	}

	public function query ( $Query )
	{
		$SQL = $this -> Socket -> query ( $Query );
		
		return $SQL;
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
			$Where = $this->convertWhere ( &$Where );
			$SQL .= ' WHERE' .$Where;
		}

		//# OrderBy and POS
		if ( $OrderBy != '' )
		{
			$OrderBy = $this->convertWhat(&$OrderBy);
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
		$Set = $this->convertWhere(&$Set);

		//# WARNING! NO PREFIX IS USED HERE!
		$SQL = 'UPDATE `' .mysql_escape_string($What). '` SET ' .$Set;

		//# Where
		if ( $Where != '' )
		{
			$Where = $this->convertWhere ( &$Where );
			$SQL .= ' WHERE ' .$Where;
		}

		//# OrderBy and POS
		if ( $OrderBy != '' )
		{
			$OrderBy = $this->convertWhat(&$OrderBy);
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

	//# DELETE FROM `users` WHERE `id`="1"
	public function Delete ($What='', $From, $Where='', $OrderBy='', $POS='', $LimitFrom='', $LimitTo='')
	{
		$What = $this->convertWhat ( &$What );

		$SQL = 'DELETE ' .$What. ' FROM `' .mysql_escape_string($From). '`';

		//# Where
		if ( $Where != '' )
		{
			$Where = $this->convertWhere ( &$Where );
			$SQL .= ' WHERE ' .$Where;
		}

		//# OrderBy and POS
		if ( $OrderBy != '' )
		{
			$OrderBy = $this->convertWhat(&$OrderBy);
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
		$Equals_list = array ( '=' , '!=', '<', '>', '<=', '>=' );
		if ( !in_array ( $Equals, $Equals_list ) ) //# Needle, haystack...
			return false;

		$Statement_list = array ( 'OR', 'AND', '' );
	
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