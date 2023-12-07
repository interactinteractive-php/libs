<?php
/**
 * FileDescription
 *
 * This file is part of ADOdb, a Database Abstraction Layer library for PHP.
 *
 * @package ADOdb
 * @link https://adodb.org Project's web site and documentation
 * @link https://github.com/ADOdb/ADOdb Source code and issue tracker
 *
 * The ADOdb Library is dual-licensed, released under both the BSD 3-Clause
 * and the GNU Lesser General Public Licence (LGPL) v2.1 or, at your option,
 * any later version. This means you can use it in proprietary products.
 * See the LICENSE.md file distributed with this source code for details.
 * @license BSD-3-Clause
 * @license LGPL-2.1-or-later
 *
 * @copyright 2000-2013 John Lim
 * @copyright 2014 Damien Regad, Mark Newnham and the ADOdb community
 * @author John Lim
 * @author George Fourlanos <fou@infomap.gr>
 */

// security - hide paths
if (!defined('ADODB_DIR')) die();

/*
NLS_Date_Format
Allows you to use a date format other than the Oracle Lite default. When a literal
character string appears where a date value is expected, the Oracle Lite database
tests the string to see if it matches the formats of Oracle, SQL-92, or the value
specified for this parameter in the POLITE.INI file. Setting this parameter also
defines the default format used in the TO_CHAR or TO_DATE functions when no
other format string is supplied.

For Oracle the default is dd-mon-yy or dd-mon-yyyy, and for SQL-92 the default is
yy-mm-dd or yyyy-mm-dd.

Using 'RR' in the format forces two-digit years less than or equal to 49 to be
interpreted as years in the 21st century (2000-2049), and years over 50 as years in
the 20th century (1950-1999). Setting the RR format as the default for all two-digit
year entries allows you to become year-2000 compliant. For example:
NLS_DATE_FORMAT='RR-MM-DD'

You can also modify the date format using the ALTER SESSION command.
*/

# define the LOB descriptor type for the given type
# returns false if no LOB descriptor
function sqlrelay_lob_desc($type) {
    switch ($type) {
        case OCI_B_BFILE:
        case OCI_B_CFILEE:
            return SQLR_CLOB; // Assuming CLOB type in SQL Relay
        case OCI_B_CLOB:
            return SQLR_CLOB;
        case OCI_B_BLOB:
            return SQLR_BLOB;
        case OCI_B_ROWID:
            return SQLR_STRING; // Assuming a string type for ROWID in SQL Relay
    }
    return false;
}


class ADODB_sqlrelay extends ADOConnection {
    var $databaseType = 'sqlrelay';
    var $dataProvider = 'sqlrelay';
    var $replaceQuote = "''"; // string to use to replace quotes
    var $concat_operator = '||';
    var $sysDate = "CURRENT_DATE"; // Use SQL Relay equivalent for current date
    var $sysTimeStamp = 'CURRENT_TIMESTAMP'; // Use SQL Relay equivalent for current timestamp
    var $metaDatabasesSQL = "SELECT DATABASE_NAME FROM SQLRelay.DATABASES"; // SQL Relay equivalent for listing databases
    var $_stmt;
    var $_commit = true; // Assuming auto-commit is true by default in SQL Relay
    var $_initdate = true; // init date to YYYY-MM-DD
    var $metaTablesSQL = "SHOW TABLES"; // SQL Relay equivalent for listing tables
    var $metaColumnsSQL = "DESCRIBE %s"; // SQL Relay equivalent for describing table columns
    var $_bindInputArray = true;
    var $hasGenID = true;
    var $_genIDSQL = "SELECT %s.NEXTVAL FROM DUAL";
    var $_genSeqSQL = "CREATE SEQUENCE %s START %s"; // SQL Relay equivalent for creating sequence
    var $_dropSeqSQL = "DROP SEQUENCE %s"; // SQL Relay equivalent for dropping sequence
    var $hasAffectedRows = true;
    var $random = "rand()"; // Assuming SQL Relay has a RAND() function
    var $noNullStrings = false;
    var $connectSID = false;
    var $_bind = false;
    var $_nestedSQL = true;
    var $_getarray = false; // currently not working
    var $leftOuter = '';  // SQL Relay wierdness, $col = $value (+) for LEFT OUTER
    var $session_sharing_force_blob = false; // alter session on updateblob if set to true
    var $firstrows = true; // enable first rows optimization on SelectLimit()
    var $selectOffsetAlg1 = 200000000; // when to use 1st algorithm of selectlimit.
    var $NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS';  // To include time, use 'RRRR-MM-DD HH24:MI:SS'
    var $dateformat = 'YYYY-MM-DD HH24:MI:SS'; // DBDate format
    var $useDBDateFormatForTextInput = false;
    var $datetime = false; // MetaType('DATE') returns 'D' (datetime==false) or 'T' (datetime == true)
    var $_refLOBs = array();
    
    // Add additional SQL Relay-specific configuration here
    // ...
}


	// var $ansiOuter = true; // if oracle9

	/*
	 * Legacy compatibility for sequence names for emulated auto-increments
	 */
    public $useCompactAutoIncrements = false;

	/*
	 * Defines the schema name for emulated auto-increment columns
	 */
	public $schema = false;

	/*
	 * Defines the prefix for emulated auto-increment columns
	 */
	public $seqPrefix = 'SEQ_';

	/*  function MetaColumns($table, $normalize=true) added by smondino@users.sourceforge.net*/
    function MetaColumns($table, $normalize=true)
    {
        global $ADODB_FETCH_MODE;
    
        $schema = '';
        $this->_findschema($table, $schema);
    
        $save = $ADODB_FETCH_MODE;
        $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
        if ($this->fetchMode !== false) {
            $savem = $this->SetFetchMode(false);
        }
    
        // Adjust the SQL query based on SQL Relay syntax
        if ($schema) {
            $rs = $this->Execute(sprintf($this->metaColumnsSQL2, strtoupper($schema), strtoupper($table)));
        } else {
            $rs = $this->Execute(sprintf($this->metaColumnsSQL, strtoupper($table)));
        }
    
        if (isset($savem)) {
            $this->SetFetchMode($savem);
        }
        $ADODB_FETCH_MODE = $save;
        if (!$rs) {
            return false;
        }
        $retarr = array();
        while (!$rs->EOF) {
            $fld = new ADOFieldObject();
            $fld->name = $rs->fields[0];
            $fld->type = $rs->fields[1];
            $fld->max_length = $rs->fields[2];
            $fld->scale = $rs->fields[3];
            if ($rs->fields[1] == 'NUMBER') {
                if ($rs->fields[3] == 0) {
                    $fld->type = 'INT';
                }
                $fld->max_length = $rs->fields[4];
            }
            $fld->not_null = (strncmp($rs->fields[5], 'NOT',3) === 0);
            $fld->binary = (strpos($fld->type, 'BLOB') !== false);
            $fld->default_value = $rs->fields[6];
    
            if ($ADODB_FETCH_MODE == ADODB_FETCH_NUM) {
                $retarr[] = $fld;
            } else {
                $retarr[strtoupper($fld->name)] = $fld;
            }
            $rs->MoveNext();
        }
        $rs->Close();
        if (empty($retarr)) {
            return false;
        }
        return $retarr;
    }
    

	function Time()
{
    $rs = $this->Execute("SELECT CURRENT_TIMESTAMP"); // Adjust the SQL query based on SQL Relay syntax

    if ($rs && !$rs->EOF) {
        // Extract the timestamp value from the result set
        $timestampValue = reset($rs->fields);

        // Convert the timestamp value to Unix timestamp using the appropriate conversion function
        return $this->ConvertTimestampToUnix($timestampValue); // Replace with the correct conversion function
    }

    return false;
}


	/**
	 * Multiple modes of connection are supported:
	 *
	 * a. Local Database
	 *    $conn->Connect(false,'scott','tiger');
	 *
	 * b. From tnsnames.ora
	 *    $conn->Connect($tnsname,'scott','tiger');
	 *    $conn->Connect(false,'scott','tiger',$tnsname);
	 *
	 * c. Server + service name
	 *    $conn->Connect($serveraddress,'scott,'tiger',$service_name);
	 *
	 * d. Server + SID
	 *    $conn->connectSID = true;
	 *    $conn->Connect($serveraddress,'scott,'tiger',$SID);
	 *
	 * @param string|false $argHostname DB server hostname or TNS name
	 * @param string $argUsername
	 * @param string $argPassword
	 * @param string $argDatabasename Service name, SID (defaults to null)
	 * @param int $mode Connection mode, defaults to 0
	 *                  (0 = non-persistent, 1 = persistent, 2 = force new connection)
	 *
	 * @return bool
	 */
	function _connect($argHostname, $argUsername, $argPassword, $argDatabasename = null, $mode = 0)
{
    if (!function_exists('sqlrc_pconnect')) {
        return null;
    }

    $this->_errorMsg = false;
    $this->_errorCode = false;

    if ($argHostname) {
        // Adjust logic for handling host and database name based on SQL Relay configuration
        // ...

        // Replace Oracle-specific connection parameters with SQL Relay equivalents
        $argDatabasename = "your_sql_relay_dsn"; // Adjust with your SQL Relay DSN

        $argPassword = encryptedToDecrypt($argPassword);

        if ($mode == 1) {
            // Replace oci_pconnect with SQL Relay equivalent
            $this->_connectionID = sqlrc_pconnect($argUsername, $argPassword, $argDatabasename);
            if ($this->_connectionID && $this->autoRollback) {
                // Adjust the rollback logic based on SQL Relay
                // ...
            }
        } elseif ($mode == 2) {
            // Replace oci_new_connect with SQL Relay equivalent
            $this->_connectionID = sqlrc_connect($argUsername, $argPassword, $argDatabasename);
        } else {
            // Replace oci_connect with SQL Relay equivalent
            $this->_connectionID = sqlrc_connect($argUsername, $argPassword, $argDatabasename);
        }

        if (!$this->_connectionID) {
            return false;
        }

        if ($this->_initdate) {
            // Adjust the NLS_DATE_FORMAT setting based on SQL Relay syntax
            $this->Execute("SET DATEFORMAT 'YYYY-MM-DD'"); // Replace with the appropriate syntax
        }

        // Adjust version-specific checks if necessary
        // ...

        return true;
    }
}


    function ServerInfo()
    {
        // Replace this with the appropriate SQL Relay queries or functions
        $compatibilityLevel = $this->GetOne('SELECT value FROM sys.database_compatible_level');
        $description = '';  // Replace with SQL Relay equivalent for server version retrieval
        $version = '';      // Replace with SQL Relay equivalent for parsing server version
    
        $arr['compat'] = $compatibilityLevel;
        $arr['description'] = $description;
        $arr['version'] = $version;
    
        return $arr;
    }
    
		// returns true or false
        function _pconnect($argHostname, $argUsername, $argPassword, $argDatabasename)
        {
            // Replace this with the appropriate SQL Relay connection code
            $sqlrConnection = sqlrconncache($argHostname, 9000, $argDatabasename);
        
            if ($sqlrConnection) {
                $this->_connectionID = $sqlrConnection;
                return true;
            }
        
            return false;
        }
        

	// returns true or false
    function _nconnect($argHostname, $argUsername, $argPassword, $argDatabasename)
    {
        // Replace this with the appropriate SQL Relay connection code
        $sqlrConnection = sqlrconncache($argHostname, 9000, $argDatabasename);
    
        if ($sqlrConnection) {
            $this->_connectionID = $sqlrConnection;
            return true;
        }
    
        return false;
    }
    

	function _affectedrows()
{
    // Assuming $sqlrConnection is your SQL Relay connection variable
    if ($sqlrConnection) {
        // Replace this with the actual method or property to get affected rows in SQL Relay
        return $sqlrConnection->getAffectedRows();
    }
    return 0;
}


    function IfNull($field, $ifNull)
    {
        return "COALESCE($field, $ifNull)";
    }
    
        
    function IfNull2($field, $ifNull, $ifNotNull)
    {
        return "CASE WHEN $field IS NULL THEN $ifNull ELSE $ifNotNull END";
    }
    
        
    function listAgg($field, $symbol, $sortOrder = null, $tableName = null)
    {
        $sortOrderString = '';
        if ($sortOrder) {
            $sortOrderString = ' ORDER BY ' . $sortOrder;
        }
    
        return "GROUP_CONCAT($field ORDER BY $field $sortOrderString SEPARATOR '$symbol')";
    }
    
        
    function trim($field)
    {
        return "TRIM($field)"; // if SQL Relay
    }
    
    function groupBy($fields1, $fields2 = null)
    {
        return "GROUP BY $fields1";
    }
    
    function length($field)
    {
        return "LENGTH($field)";
    }
    
    function toDate($field, $format = 'YYYY-MM-DD')
    {
        // Replace the TO_DATE function with the equivalent SQL Relay function
        return "DATE_FORMAT($field, '$format')"; // toDate in SQL Relay
    }
    

    protected function _insertID($table = '', $column = '')
    {
        if ($this->schema) {
            $t = strpos($table, '.');
            if ($t !== false) {
                $tab = substr($table, $t + 1);
            } else {
                $tab = $table;
            }
    
            if ($this->useCompactAutoIncrements) {
                // Adjust the logic for compact auto-increments in SQL Relay
                $tab = sprintf('%u', crc32(strtolower($tab)));
                $seqname = $this->schema . '.' . $this->seqPrefix . $tab;
            } else {
                $seqname = $this->schema . '.' . $this->seqPrefix . $tab;
            }
        } else {
            if ($this->useCompactAutoIncrements) {
                // Adjust the logic for compact auto-increments in SQL Relay
                $table = sprintf('%u', crc32(strtolower($table)));
                $seqname = $this->seqPrefix . $table;
            } else {
                $seqname = $this->seqPrefix . $table;
            }
        }
    
        if (strlen($seqname) > 30) {
            // Handle the case where the sequence name is too long
            /*
            * We cannot successfully identify the sequence
            */
            return false;
        }
    
        // Use the appropriate SQL Relay function to retrieve the current value of the sequence
        return $this->getOne("SELECT " . $seqname . ".currval FROM dual");
    }
    
	// format and return date string in database date format
	function DBDate($d, $isfld = false)
{
    if (empty($d) && $d !== 0) {
        return 'null';
    }

    if ($isfld) {
        $d = _adodb_safedate($d);
        // Replace with SQL Relay date formatting functions
        return "CAST($d AS DATE)";
    }

    if (is_string($d)) {
        $d = ADORecordSet::UnixDate($d);
    }

    if (is_object($d)) {
        $ds = $d->format($this->fmtDate);
    } else {
        $ds = adodb_date($this->fmtDate, $d);
    }

    // Replace with SQL Relay date formatting functions
    return "CAST($ds AS DATE)";
}


    function BindDate($d)
    {
        $d = ADOConnection::DBDate($d);
        if (strncmp($d, "'", 1)) {
            return $d;
        }
    
        return substr($d, 1, strlen($d)-2);
    }    

    function BindTimeStamp($ts)
    {
        if (empty($ts) && $ts !== 0) {
            return 'null';
        }
    
        if (is_string($ts)) {
            $ts = ADORecordSet::UnixTimeStamp($ts);
        }
    
        if (is_object($ts)) {
            $tss = $ts->format("'Y-m-d H:i:s'");
        } else {
            // Assuming you want to bind the timestamp directly without additional formatting
            $tss = adodb_date("'Y-m-d H:i:s'", $ts);
        }
    
        return $tss;
    }
    

	// format and return date string in database timestamp format
	function DBTimeStamp($ts, $isfld = false)
	{
		if (empty($ts) && $ts !== 0) {
			return 'null';
		}
	
		if ($isfld) {
			return 'CAST(SUBSTRING(' . $ts . ", 1, 19) AS TIMESTAMP)";
		}
	
		if (is_string($ts)) {
			$ts = ADORecordSet::UnixTimeStamp($ts);
		}
	
		if (is_object($ts)) {
			$tss = $ts->format("'Y-m-d H:i:s'");
		} else {
			$tss = date("'Y-m-d H:i:s'", $ts);
		}
	
		return 'CAST(' . $tss . " AS TIMESTAMP)";
	}
	

	function RowLock($tables, $where, $col = '1 as adodbignore')
	{
		if ($this->autoCommit) {
			$this->BeginTrans();
		}
	
		// Assuming you have a valid SQL Relay connection
		$sqlrConnection = sqlrconncache("your_sql_relay_host", 9000, "/path/to/your/sqlrelay.sock");
	
		$tables = strtoupper($tables);
		$where = strtoupper($where);
	
		// Use SELECT...FOR UPDATE equivalent in SQL Relay
		$query = "SELECT $col FROM $tables WHERE $where FOR UPDATE NOWAIT";
	
		$result = $this->GetOne($query);
	
		// Close the SQL Relay connection
		sqlrclose($sqlrConnection);
	
		return $result;
	}
	

	function MetaTables($ttype = false, $showSchema = false, $mask = false)
{
    if ($mask) {
        $save = $this->metaTablesSQL;
        $mask = $this->qstr(strtoupper($mask));
        $this->metaTablesSQL .= " AND upper(table_name) LIKE $mask";
    }

    // Assuming you have a valid SQL Relay connection
    $sqlrConnection = sqlrconncache("your_sql_relay_host", 9000, "/path/to/your/sqlrelay.sock");

    // Get the list of tables using SQL Relay
    $ret = array();
    $query = "SELECT TABLE_NAME FROM USER_TABLES";

    if ($mask) {
        $query .= " WHERE UPPER(TABLE_NAME) LIKE $mask";
    }

    $rs = $this->Execute($query);

    if ($rs) {
        while ($row = $rs->FetchRow()) {
            $ret[] = $row[0];
        }
    } else {
        // Handle error if needed
    }

    // Close the SQL Relay connection
    sqlrclose($sqlrConnection);

    if ($mask) {
        $this->metaTablesSQL = $save;
    }

    return $ret;
}


	// Mark Newnham
	function MetaIndexes($table, $primary = false, $owner = false)
{
    // Save old fetch mode
    global $ADODB_FETCH_MODE;

    $save = $ADODB_FETCH_MODE;
    $ADODB_FETCH_MODE = ADODB_FETCH_NUM;

    if ($this->fetchMode !== false) {
        $savem = $this->SetFetchMode(false);
    }

    // Get index details
    $table = strtoupper($table);

    // Get primary index
    $primary_key = '';

    $rs = $this->Execute(sprintf("SELECT * FROM user_constraints WHERE UPPER(TABLE_NAME)='%s' AND CONSTRAINT_TYPE='P'", $table));

    if (!is_object($rs)) {
        if (isset($savem)) {
            $this->SetFetchMode($savem);
        }
        $ADODB_FETCH_MODE = $save;
        return false;
    }

    if ($row = $rs->FetchRow()) {
        $primary_key = $row[1]; // constraint_name
    }

    if ($primary == true && $primary_key == '') {
        if (isset($savem)) {
            $this->SetFetchMode($savem);
        }
        $ADODB_FETCH_MODE = $save;
        return false; // There is no primary key
    }

    $rs = $this->Execute(sprintf("SELECT INDEX_NAME, UNIQUENESS, COLUMN_POSITION, COLUMN_NAME FROM user_indexes, user_ind_columns WHERE UPPER(TABLE_NAME)='%s' AND user_ind_columns.INDEX_NAME = user_indexes.INDEX_NAME", $table));

    if (!is_object($rs)) {
        if (isset($savem)) {
            $this->SetFetchMode($savem);
        }
        $ADODB_FETCH_MODE = $save;
        return false;
    }

    $indexes = array();
    // Parse index data into array

    while ($row = $rs->FetchRow()) {
        if ($primary && $row[0] != $primary_key) {
            continue;
        }
        if (!isset($indexes[$row[0]])) {
            $indexes[$row[0]] = array(
                'unique' => ($row[1] == 'UNIQUE'),
                'columns' => array()
            );
        }
        $indexes[$row[0]]['columns'][$row[2] - 1] = $row[3];
    }

    // Sort columns by order in the index
    foreach (array_keys($indexes) as $index) {
        ksort($indexes[$index]['columns']);
    }

    if (isset($savem)) {
        $this->SetFetchMode($savem);
        $ADODB_FETCH_MODE = $save;
    }
    return $indexes;
}


	function BeginTrans()
	{
		if ($this->transOff) {
			return true;
		}
	
		$this->transCnt += 1;
		$this->autoCommit = false;
		$this->_commit = SQLR_TRANS_START;
	
		if ($this->_transmode) {
			$ok = $this->Execute("SET TRANSACTION " . $this->_transmode);
		} else {
			$ok = true;
		}
	
		return $ok ? true : false;
	}
	
	function CommitTrans($ok = true)
	{
		if ($this->transOff) {
			return true;
		}
	
		if (!$ok) {
			return $this->RollbackTrans();
		}
	
		if ($this->transCnt) {
			$this->transCnt -= 1;
		}
	
		// Assuming you have a valid SQL Relay connection
		$sqlrConnection = sqlrconncache("your_sql_relay_host", 9000, "/path/to/your/sqlrelay.sock");
	
		// Commit the transaction
		$ret = sqlrcommit($sqlrConnection);
	
		// Set the commit mode and auto-commit
		sqlrcommitmode($sqlrConnection, SQLR_COMMIT_ON_SUCCESS);
		sqlrautocommit($sqlrConnection, true);
	
		// Close the SQL Relay connection
		sqlrclose($sqlrConnection);
	
		return $ret;
	}
	

function RollbackTrans()
{
    if ($this->transOff) {
        return true;
    }

    if ($this->transCnt) {
        $this->transCnt -= 1;
    }

    // Assuming you have a valid SQL Relay connection
    $sqlrConnection = sqlrconncache("your_sql_relay_host", 9000, "/path/to/your/sqlrelay.sock");

    // Rollback the transaction
    $ret = sqlrrollback($sqlrConnection);

    // Set the commit mode and auto-commit
    sqlrcommitmode($sqlrConnection, SQLR_COMMIT_ON_SUCCESS);
    sqlrautocommit($sqlrConnection, true);

    // Close the SQL Relay connection
    sqlrclose($sqlrConnection);

    return $ret;
}
	
	function ErrorMsg()
	{
		if ($this->_errorMsg !== false) {
			return $this->_errorMsg;
		}
	
		// Assuming you have a valid SQL Relay connection
		$sqlrConnection = sqlrconncache("your_sql_relay_host", 9000, "/path/to/your/sqlrelay.sock");
	
		// Check if the statement is a resource
		if (is_resource($this->_stmt)) {
			$arr = sqlrerror($this->_stmt);
		}
	
		// If the statement resource did not contain an error, check the connection
		if (empty($arr)) {
			if (is_resource($this->_connectionID)) {
				$arr = sqlrerror($this->_connectionID);
			} else {
				$arr = sqlrerror();
	
				// If there is still no error, return an empty string
				if ($arr === false) {
					return '';
				}
			}
		}
	
		$this->_errorMsg = $arr['message'];
		$this->_errorCode = $arr['code'];
	
		// Close the SQL Relay connection
		sqlrclose($sqlrConnection);
	
		return $this->_errorMsg;
	}
	

	function ErrorNo()
	{
		if ($this->_errorCode !== false) {
			return $this->_errorCode;
		}
	
		// Assuming you have a valid SQL Relay connection
		$sqlrConnection = sqlrconncache("your_sql_relay_host", 9000, "/path/to/your/sqlrelay.sock");
	
		// Check if the statement is a resource
		if (is_resource($this->_stmt)) {
			$arr = sqlrerror($this->_stmt);
		}
	
		// If the statement resource did not contain an error, check the connection
		if (empty($arr)) {
			$arr = sqlrerror($this->_connectionID);
	
			// If there is still no error, try checking the default connection
			if ($arr === false) {
				$arr = sqlrerror();
			}
	
			// If there is still no error, return an empty string
			if ($arr === false) {
				return '';
			}
		}
	
		$this->_errorMsg = $arr['message'];
		$this->_errorCode = $arr['code'];
	
		// Close the SQL Relay connection
		sqlrclose($sqlrConnection);
	
		return $arr['code'];
	}
	

	/**
	 * SQLRELAY Format date column in sql string given an input format that understands Y M D
	 */
	function SQLDate($fmt, $col = false, $toType = 'TO_CHAR')
{
    if (!$col) {
        $col = $this->sysTimeStamp;
    }

    $s = $toType . '(' . $col . ",'";

    $len = strlen($fmt);
    for ($i = 0; $i < $len; $i++) {
        $ch = $fmt[$i];
        switch ($ch) {
            case 'Y':
            case 'y':
                $s .= 'YYYY';
                break;
            case 'Q':
            case 'q':
                $s .= 'Q';
                break;
            case 'M':
                $s .= 'Mon';
                break;
            case 'm':
                $s .= 'MM';
                break;
            case 'D':
            case 'd':
                $s .= 'DD';
                break;
            case 'H':
                $s .= 'HH24';
                break;
            case 'h':
                $s .= 'HH';
                break;
            case 'i':
                $s .= 'MI';
                break;
            case 's':
                $s .= 'SS';
                break;
            case 'a':
            case 'A':
                $s .= 'AM';
                break;
            case 'w':
                $s .= 'D';
                break;
            case 'l':
                $s .= 'DAY';
                break;
            case 'W':
                $s .= 'WW';
                break;
            default:
                // handle escape characters...
                if ($ch == '\\') {
                    $i++;
                    $ch = substr($fmt, $i, 1);
                }
                if (strpos('-/.:;, ', $ch) !== false) {
                    $s .= $ch;
                } else {
                    $s .= '"' . $ch . '"';
                }
        }
    }
    return $s . "')";
}


	#SQLRELAY GetRandRow

	function GetRandRow($sql, $arr = false)
	{
		// Assuming you have a valid SQL Relay connection
		$sqlrConnection = sqlrconncache("your_sql_relay_host", 9000, "/path/to/your/sqlrelay.sock");
	
		// Construct the SQL statement based on SQL Relay syntax
		$sql = "SELECT * FROM ($sql ORDER BY RANDOM()) LIMIT 1";
	
		// Execute the statement
		$result = sqlrquery($sqlrConnection, $sql);
	
		// Check if the query was successful
		if (!$result) {
			sqlrclose($sqlrConnection);
			return false;
		}
	
		// Fetch the random row
		$row = sqlrfetchassoc($result);
	
		// Close the SQL Relay connection
		sqlrclose($sqlrConnection);
	
		return $row;
	}
	

	/**
	 * This algorithm makes use of
	 *
	 * a. FIRST_ROWS hint
	 * The FIRST_ROWS hint explicitly chooses the approach to optimize response
	 * time, that is, minimum resource usage to return the first row. Results
	 * will be returned as soon as they are identified.
	 *
	 * b. Uses rownum tricks to obtain only the required rows from a given offset.
	 * As this uses complicated sql statements, we only use this if $offset >= 100.
	 * This idea by Tomas V V Cox.
	 *
	 * This implementation does not appear to work with oracle 8.0.5 or earlier.
	 * Comment out this function then, and the slower SelectLimit() in the base
	 * class will be used.
	 *
	 * Note: FIRST_ROWS hinting is only used if $sql is a string; when
	 * processing a prepared statement's handle, no hinting is performed.
	 */
	<?php
function SelectLimit($sql, $nrows = -1, $offset = -1, $inputarr = false, $secs2cache = 0)
{
    $nrows = (int) $nrows;
    $offset = (int) $offset;

    // Assuming you have a valid SQL Relay connection
    $sqlrConnection = sqlrconncache("your_sql_relay_host", 9000, "/path/to/your/sqlrelay.sock");

    // Since SQL Relay supports placeholders, we can use prepared statements
    $stmt = sqlrprepare($sqlrConnection, $sql);

    // Bind parameters if provided
    if (is_array($inputarr)) {
        foreach ($inputarr as $key => $value) {
            sqlrbinder($stmt, ":" . $key, SQLR_STRING, $value);
        }
    }

    // Execute the statement
    $result = sqlrexecute($stmt);

    // Check if the update was successful
    if (!$result) {
        sqlrclosecursor($stmt);
        sqlrclose($sqlrConnection);
        return false;
    }

    // Fetch column names
    $cols = sqlrfieldnames($stmt);

    // Free the statement cursor
    sqlrclosecursor($stmt);

    // Generate the SQL for limiting rows
    $hint = '';
    if ($nrows > 0) {
        $nrows += $offset;
        $offset += 1; // in Oracle rownum starts at 1
        $fields = implode(',', $cols);

        $sql = "SELECT $hint $fields FROM (
            SELECT $fields, ROWNUM AS adodb_rownum
            FROM ($sql) WHERE ROWNUM <= :adodb_nrows
        ) WHERE adodb_rownum >= :adodb_offset";

        // Bind parameters for the new SQL
        $stmt = sqlrprepare($sqlrConnection, $sql);
        sqlrbinder($stmt, ':adodb_nrows', SQLR_INT, $nrows);
        sqlrbinder($stmt, ':adodb_offset', SQLR_INT, $offset);

        // Bind parameters if provided
        if (is_array($inputarr)) {
            foreach ($inputarr as $key => $value) {
                sqlrbinder($stmt, ":" . $key, SQLR_STRING, $value);
            }
        }

        // Execute the limited statement
        $result = sqlrexecute($stmt);

        // Check if the query was successful
        if (!$result) {
            sqlrclosecursor($stmt);
            sqlrclose($sqlrConnection);
            return false;
        }
    }

    // Process the result as needed

    // Close the statement
    sqlrclosecursor($stmt);

    // Close the SQL Relay connection
    sqlrclose($sqlrConnection);

    return $result;
}

// Example usage:
$sql = "SELECT * FROM your_table WHERE your_condition";
$nrows = 10;
$offset = 0;
$inputarr = array('param1' => 'value1', 'param2' => 'value2');
$secs2cache = 0;

$result = SelectLimit($sql, $nrows, $offset, $inputarr, $secs2cache);

// Process the result as needed
if ($result) {
    while ($row = sqlrfetchassoc($result)) {
        // Process each row
        print_r($row);
    }
} else {
    echo "Query execution failed.";
}
?>

	/**
	 * Usage:
	 * Store BLOBs and CLOBs
	 *
	 * Example: to store $var in a blob
	 *    $conn->Execute('insert into TABLE (id,ablob) values(12,empty_blob())');
	 *    $conn->UpdateBlob('TABLE', 'ablob', $varHoldingBlob, 'ID=12', 'BLOB');
	 *
	 * $blobtype supports 'BLOB' and 'CLOB', but you need to change to 'empty_clob()'.
	 *
	 * to get length of LOB:
	 *    select DBMS_LOB.GETLENGTH(ablob) from TABLE
	 *
	 * If you are using CURSOR_SHARING = force, it appears this will case a segfault
	 * under oracle 8.1.7.0. Run:
	 *    $db->Execute('ALTER SESSION SET CURSOR_SHARING=EXACT');
	 * before UpdateBlob() then...
	 */
	<?php
	function UpdateBlob($table, $column, $val, $where, $blobtype = 'BLOB')
	{
		// Assuming you have a valid SQL Relay connection
		$sqlrConnection = sqlrconncache("your_sql_relay_host", 9000, "/path/to/your/sqlrelay.sock");
	
		if (mb_strlen($val) < 4000) {
			// If the length is less than 4000, use regular update
			$sql = "UPDATE $table SET $column = ? WHERE $where";
			$stmt = sqlrprepare($sqlrConnection, $sql);
			sqlrbinder($stmt, 1, SQLR_STRING, $val);
			$result = sqlrexecute($stmt);
			sqlrclosecursor($stmt);
	
			// Close the SQL Relay connection
			sqlrclose($sqlrConnection);
	
			return $result != false;
		}
	
		switch (strtoupper($blobtype)) {
			default:
				ADOConnection::outp("<b>UpdateBlob</b>: Unknown blobtype=$blobtype");
				sqlrclose($sqlrConnection);
				return false;
			case 'BLOB':
				$type = SQLR_BINARY;
				break;
			case 'CLOB':
				$type = SQLR_STRING;
				break;
		}
	
		// Construct the SQL statement based on SQL Relay syntax
		$sql = "UPDATE $table SET $column = ? WHERE $where RETURNING $column INTO ?";
		$stmt = sqlrprepare($sqlrConnection, $sql);
	
		// Bind parameters
		sqlrbinder($stmt, 1, $type, $val);
		sqlrbinder($stmt, 2, $type);
	
		// Execute the statement
		$result = sqlrexecute($stmt);
	
		// Check if the update was successful
		$rez = sqlraffectedrows($result) > 0;
	
		// Close the statement
		sqlrclosecursor($stmt);
	
		// Close the SQL Relay connection
		sqlrclose($sqlrConnection);
	
		return $rez;
	}
	
	// Example usage:
	$table = "your_table";
	$column = "your_blob_column";
	$val = "content_of_blob";
	$where = "your_condition";
	$blobtype = "BLOB";
	
	$result = UpdateBlob($table, $column, $val, $where, $blobtype);
	
	// Process the result as needed
	if ($result) {
		echo "Blob update successful!";
	} else {
		echo "Blob update failed.";
	}
	?>
	

	/**
	 * Usage:  store file pointed to by $val in a blob
	 */
<?php
function UpdateBlobFile($table, $column, $val, $where, $blobtype = 'BLOB')
{
    // Assuming you have a valid SQL Relay connection
    $sqlrConnection = sqlrconncache("your_sql_relay_host", 9000, "/path/to/your/sqlrelay.sock");

    switch (strtoupper($blobtype)) {
        default:
            ADOConnection::outp("<b>UpdateBlob</b>: Unknown blobtype=$blobtype");
            sqlrclose($sqlrConnection);
            return false;
        case 'BLOB':
            $type = SQLR_BINARY;
            break;
        case 'CLOB':
            $type = SQLR_STRING;
            break;
    }

    // Construct the SQL statement based on SQL Relay syntax
    $sql = "UPDATE $table SET $column = ? WHERE $where";
    $stmt = sqlrprepare($sqlrConnection, $sql);

    // Bind parameters
    sqlrbinder($stmt, 1, $type, $val);

    // Execute the statement
    $result = sqlrexecute($stmt);

    // Check if the update was successful
    $rez = sqlraffectedrows($result) > 0;

    // Close the statement
    sqlrclosecursor($stmt);

    // Close the SQL Relay connection
    sqlrclose($sqlrConnection);

    return $rez;
}

// Example usage:
$table = "your_table";
$column = "your_blob_column";
$val = "content_of_blob";
$where = "your_condition";
$blobtype = "BLOB";

$result = UpdateBlobFile($table, $column, $val, $where, $blobtype);

// Process the result as needed
if ($result) {
    echo "Blob update successful!";
} else {
    echo "Blob update failed.";
}
?>


	<?php
	// Assuming you have a valid SQL Relay connection
	$sqlrConnection = sqlrconncache("your_sql_relay_host", 9000, "/path/to/your/sqlrelay.sock");
	
	function Execute($sql, $inputarr = false)
	{
		global $sqlrConnection;
	
		if ($inputarr !== false) {
			// Check if the input array is an associative array
			$isAssocArray = is_array($inputarr) && array_keys($inputarr) !== range(0, count($inputarr) - 1);
	
			// Prepare the SQL statement
			$stmt = sqlrprepare($sqlrConnection, $sql);
	
			// Bind parameters if $inputarr is an associative array
			if ($isAssocArray) {
				foreach ($inputarr as $key => $value) {
					sqlrbinderbyname($stmt, ':' . $key, $value);
				}
			}
	
			// Execute the statement
			$result = sqlrexecute($stmt);
	
			// Close the statement
			sqlrclosecursor($stmt);
	
			return $result;
		} else {
			// Execute the SQL without parameters
			return sqlrquery($sqlrConnection, $sql);
		}
	}
	
	// Example usage:
	$sql = "SELECT * FROM your_table WHERE column_name = :value";
	$inputarr = array('value' => 'some_value');
	
	$result = Execute($sql, $inputarr);
	
	// Process the result as needed
	while ($row = sqlrfetchassoc($result)) {
		// Process each row
		print_r($row);
	}
	
	// Close the SQL Relay connection
	sqlrclose($sqlrConnection);
	?>
	

	/*
	 * Example of usage:
	 *    $stmt = $this->Prepare('insert into emp (empno, ename) values (:empno, :ename)');
	*/
	function Prepare($sql, $cursor = false)
{
    static $BINDNUM = 0;

    // Assuming $this->_connectionID is the SQL Relay connection ID

    $stmt = sqlrc_exec($this->_connectionID, $sql);

    if (!$stmt) {
        $this->_errorMsg = false;
        $this->_errorCode = false;
        $this->_errorMsg = sqlrc_last_errmsg($this->_connectionID);
        $this->_errorCode = sqlrc_last_errno($this->_connectionID);
        return false;
    }

    $BINDNUM += 1;

    // SQL Relay doesn't have an equivalent to oci_statement_type, you might need to handle this differently based on your SQL statements

    if ($cursor) {
        // Assuming that you want to use a cursor for certain types of statements
        $cursorID = sqlrc_cursor($this->_connectionID);
        return array($sql, $stmt, 0, $BINDNUM, $cursorID);
    }

    return array($sql, $stmt, 0, $BINDNUM);
}

function releaseStatement(&$stmt)
{
    if (is_array($stmt) && isset($stmt[1])) {
        // Assuming that $stmt[1] contains the SQL Relay statement handle

        sqlrc_close($stmt[1]); // Use the appropriate SQL Relay function to close the statement

        // Clearing the resource to avoid it being of type Unknown
        $stmt[1] = null;

        return true;
    }

    // Not a valid statement handle
    return false;
}


	/*
		Call an oracle stored procedure and returns a cursor variable as a recordset.
		Concept by Robert Tuttle robert@ud.com

		Example:
			Note: we return a cursor variable in :RS2
			$rs = $db->ExecuteCursor("BEGIN adodb.open_tab(:RS2); END;",'RS2');

			$rs = $db->ExecuteCursor(
				"BEGIN :RS2 = adodb.getdata(:VAR1); END;",
				'RS2',
				array('VAR1' => 'Mr Bean'));

	*/
    function ExecuteCursor($sql, $cursorName = 'rs', $params = false)
    {
        if (is_array($sql)) {
            $stmt = $sql;
        } else {
            $stmt = Prepare($sql, true); // Assuming Prepare is the function to prepare statements in SQL Relay
        }
    
        if (is_array($stmt) && sizeof($stmt) >= 5) {
            $hasref = true;
            $ignoreCur = false;
    
            // Assuming $this->Parameter is a function that sets parameters for SQL Relay
            $this->Parameter($stmt, $ignoreCur, $cursorName, false, -1, SQLRC_DATATYPE_CURSOR);
    
            if ($params) {
                foreach ($params as $k => $v) {
                    // Assuming $this->Parameter is a function that sets parameters for SQL Relay
                    $this->Parameter($stmt, $params[$k], $k);
                }
            }
        } else {
            $hasref = false;
        }
    
        /** @var ADORecordSet_sqlrelay $rs */
        $rs = $this->Execute($stmt); // Assuming Execute is a function to execute statements in SQL Relay
    
        if ($rs) {
            if ($rs->databaseType == 'sqlrelay') {
                // Assuming sqlrc_cursor is a function to get the cursor ID in SQL Relay
                $cursorID = sqlrc_cursor($rs->_connectionID);
                $rs->_refcursor = $cursorID; // Set the cursor ID in the recordset
            } elseif ($hasref) {
                $rs->_refcursor = $stmt[4];
            }
        }
    
        return $rs;
    }
    
    

	/**
	 * Bind a variable -- very, very fast for executing repeated statements in oracle.
	 *
	 * Better than using
	 *    for ($i = 0; $i < $max; $i++) {
	 *        $p1 = ?; $p2 = ?; $p3 = ?;
	 *        $this->Execute("insert into table (col0, col1, col2) values (:0, :1, :2)", array($p1,$p2,$p3));
	 *    }
	 *
	 * Usage:
	 *    $stmt = $DB->Prepare("insert into table (col0, col1, col2) values (:0, :1, :2)");
	 *    $DB->Bind($stmt, $p1);
	 *    $DB->Bind($stmt, $p2);
	 *    $DB->Bind($stmt, $p3);
	 *    for ($i = 0; $i < $max; $i++) {
	 *        $p1 = ?; $p2 = ?; $p3 = ?;
	 *        $DB->Execute($stmt);
	 *    }
	 *
	 * Some timings to insert 1000 records, test table has 3 cols, and 1 index.
	 * - Time 0.6081s (1644.60 inserts/sec) with direct oci_parse/oci_execute
	 * - Time 0.6341s (1577.16 inserts/sec) with ADOdb Prepare/Bind/Execute
	 * - Time 1.5533s ( 643.77 inserts/sec) with pure SQL using Execute
	 *
	 * Now if PHP only had batch/bulk updating like Java or PL/SQL...
	 *
	 * Note that the order of parameters differs from oci_bind_by_name,
	 * because we default the names to :0, :1, :2
	 */
    function Bind(&$stmt, &$var, $size = 4000, $type = false, $name = false, $isOutput = false)
    {
        if (!is_array($stmt)) {
            return false;
        }
    
        if ($type == SQLRC_DATATYPE_CURSOR && sizeof($stmt) >= 5) {
            // Assuming $stmt[1] is the SQL Relay statement handle
            return sqlrc_bind_param($stmt[1], ":" . $name, $stmt[4], SQLRC_DATATYPE_CURSOR);
        }
    
        if ($name === false) {
            $stmt[2] += 1;
    
            // Assuming $stmt[1] is the SQL Relay statement handle
            if ($type !== false) {
                return sqlrc_bind_param($stmt[1], ":" . $stmt[2], $var, $size, $type);
            } else {
                return sqlrc_bind_param($stmt[1], ":" . $stmt[2], $var, $size);
            }
        } elseif (oci_lob_desc($type)) {
            if ($this->debug) {
                ADOConnection::outp("<b>Bind</b>: name = $name");
            }
    
            // Creating a new Descriptor here
            $numlob = count($this->_refLOBs);
            $this->_refLOBs[$numlob]['LOB'] = sqlrc_lob_alloc($this->_connectionID, $type);
            $this->_refLOBs[$numlob]['TYPE'] = $isOutput;
    
            $tmp = $this->_refLOBs[$numlob]['LOB'];
            // Assuming $stmt[1] is the SQL Relay statement handle
            $rez = sqlrc_bind_param($stmt[1], ":" . $name, $tmp, -1, $type);
    
            if ($this->debug) {
                ADOConnection::outp("<b>Bind</b>: descriptor has been allocated, var (" . $name . ") binded");
            }
    
            // If type is input, write data to lob now
            if ($isOutput == false) {
                $var = $this->BlobEncode($var);
                // Assuming $stmt[1] is the SQL Relay statement handle
                sqlrc_lob_write($stmt[1], $tmp, $var);
                $this->_refLOBs[$numlob]['VAR'] = &$var;
    
                if ($this->debug) {
                    ADOConnection::outp("<b>Bind</b>: LOB has been written to temp");
                }
            } else {
                $this->_refLOBs[$numlob]['VAR'] = &$var;
            }
    
            $rez = $tmp;
        } else {
            if ($this->debug) {
                ADOConnection::outp("<b>Bind</b>: name = $name");
            }
    
            // Assuming $stmt[1] is the SQL Relay statement handle
            if ($type !== false) {
                $rez = sqlrc_bind_param($stmt[1], ":" . $name, $var, $size, $type);
            } else {
                $rez = sqlrc_bind_param($stmt[1], ":" . $name, $var, $size);
            }
        }
    
        return $rez;
    }
    


	function Param($name,$type='C')
	{
		return ':'.$name;
	}

	/**
	 * Usage:
	 *    $stmt = $db->Prepare('select * from table where id =:myid and group=:group');
	 *    $db->Parameter($stmt,$id,'myid');
	 *    $db->Parameter($stmt,$group,'group');
	 *    $db->Execute($stmt);
	 *
	 * @param $stmt Statement returned by {@see Prepare()} or {@see PrepareSP()}.
	 * @param $var PHP variable to bind to
	 * @param $name Name of stored procedure variable name to bind to.
	 * @param bool $isOutput Indicates direction of parameter 0/false=IN  1=OUT  2= IN/OUT. This is ignored in oci8.
	 * @param int $maxLen Holds an maximum length of the variable.
	 * @param mixed $type The data type of $var. Legal values depend on driver.
	 *
	 * @link http://php.net/oci_bind_by_name
	*/
    function Parameter(&$stmt, &$var, $name, $isOutput = false, $maxLen = 4000, $type = false)
    {
        if ($this->debug) {
            $prefix = ($isOutput) ? 'Out' : 'In';
            $ztype = (empty($type)) ? 'false' : $type;
            ADOConnection::outp("{$prefix}Parameter(\$stmt, \$php_var='$var', \$name='$name', \$maxLen=$maxLen, \$type=$ztype);");
        }
    
        // Assuming $stmt[1] is the SQL Relay statement handle
        return $this->Bind($stmt, $var, $maxLen, $type, $name, $isOutput);
    }
    

	/**
	 * Execute a query.
	 *
	 * this version supports:
	 *
	 * 1. $db->execute('select * from table');
	 *
	 * 2. $db->prepare('insert into table (a,b,c) values (:0,:1,:2)');
	 *    $db->execute($prepared_statement, array(1,2,3));
	 *
	 * 3. $db->execute('insert into table (a,b,c) values (:a,:b,:c)',array('a'=>1,'b'=>2,'c'=>3));
	 *
	 * 4. $db->prepare('insert into table (a,b,c) values (:0,:1,:2)');
	 *    $db->bind($stmt,1); $db->bind($stmt,2); $db->bind($stmt,3);
	 *    $db->execute($stmt);
	 *
	 * @param string|array $sql        Query to execute.
	 * @param array        $inputarr   An optional array of parameters.
	 *
	 * @return mixed|bool Query identifier or true if execution successful, false if failed.
	 */
	function _query($sql, $inputarr = false)
{
    if (is_array($sql)) { // is prepared sql
        $stmt = $sql[1];

        // Assuming $sql[3] is the bind position
        $bindpos = $sql[3];

        if (is_array($inputarr)) {
            if (isset($this->_bind[$bindpos])) {
                // all tied up already
                $bindarr = $this->_bind[$bindpos];
            } else {
                // one statement to bind them all
                $bindarr = array();
                foreach ($inputarr as $k => $v) {
                    $bindarr[$k] = $v;

                    // Assuming $stmt is the SQL Relay statement handle
                    sqlrc_bind_param($stmt, ":$k", $bindarr[$k], is_string($v) && strlen($v) > 4000 ? -1 : 4000);
                }
                $this->_bind[$bindpos] = $bindarr;
            }
        }
    } else {
        // Assuming $this->_connectionID is the SQL Relay connection ID
        $stmt = sqlrc_exec($this->_connectionID, $sql);
    }

    $this->_stmt = $stmt;

    if (!$stmt) {
        return false;
    }

    if (defined('ADODB_PREFETCH_ROWS')) {
        // Assuming $stmt is the SQL Relay statement handle
        @sqlrc_set_prefetch($stmt, ADODB_PREFETCH_ROWS);
    }

    if (is_array($inputarr)) {
        foreach ($inputarr as $k => $v) {
            if (is_array($v)) {
                // suggested by g.giunta@libero.
                if (sizeof($v) == 2) {
                    // Assuming $stmt is the SQL Relay statement handle
                    sqlrc_bind_param($stmt, ":$k", $inputarr[$k][0], $v[1]);
                } else {
                    // Assuming $stmt is the SQL Relay statement handle
                    sqlrc_bind_param($stmt, ":$k", $inputarr[$k][0], $v[1], $v[2]);
                }

                if ($this->debug == 99) {
                    if (is_object($v[0])) {
                        echo "name=:$k", ' len=' . $v[1], ' type=' . $v[2], '<br>';
                    } else {
                        echo "name=:$k", ' var=' . $inputarr[$k][0], ' len=' . $v[1], ' type=' . $v[2], '<br>';
                    }
                }
            } else {
                $len = -1;
                if ($v === ' ') {
                    $len = 1;
                }

                if (isset($bindarr)) {
                    // is prepared sql, so no need to sqlrc_bind_param again
                    $bindarr[$k] = $v;
                } else {
                    // dynamic sql, so rebind every time
                    // Assuming $stmt is the SQL Relay statement handle
                    sqlrc_bind_param($stmt, ":$k", $inputarr[$k], $len);
                }
            }
        }
    }

    $this->_errorMsg = false;
    $this->_errorCode = false;

    // Assuming $stmt is the SQL Relay statement handle
    if (sqlrc_execute($stmt, $this->_commit)) {
        if (count($this->_refLOBs) > 0) {
            foreach ($this->_refLOBs as $key => $value) {
                if ($this->_refLOBs[$key]['TYPE'] == true) {
                    // Assuming $stmt is the SQL Relay statement handle
                    $tmp = sqlrc_lob_load($stmt, $this->_refLOBs[$key]['LOB']);
                    if ($this->debug) {
                        ADOConnection::outp("<b>OUT LOB</b>: LOB has been loaded. <br>");
                    }
                    $this->_refLOBs[$key]['VAR'] = $tmp;
                } else {
                    // Assuming $stmt is the SQL Relay statement handle
                    sqlrc_lob_save($stmt, $this->_refLOBs[$key]['LOB'], $this->_refLOBs[$key]['VAR']);
                    sqlrc_lob_free($this->_refLOBs[$key]['LOB']);
                    unset($this->_refLOBs[$key]);
                    if ($this->debug) {
                        ADOConnection::outp("<b>IN LOB</b>: LOB has been saved. <br>");
                    }
                }
            }
        }

        // Assuming $stmt is the SQL Relay statement handle
        switch (sqlrc_statement_type($stmt)) {
            case SQLRC_STMT_SELECT:
                return $stmt;

            case SQLRC_STMT_DECLARE:
            case SQLRC_STMT_BEGIN:
                if (is_array($sql) && !empty($sql[4])) {
                    $cursor = $sql[4];
                    if (is_resource($cursor)) {
                        // Assuming $cursor is the SQL Relay cursor handle
                        $ok = sqlrc_execute($cursor);
                        return $cursor;
                    }
                    return $stmt;
                } else {
                    if (is_resource($stmt)) {
                        // Assuming $stmt is the SQL Relay statement handle
                        sqlrc_free_statement($stmt);
                        return true;
                    }
                    return $stmt;
                }
                break;
            default:
                return true;
        }
    }

    return false;
}


	// From Oracle Whitepaper: PHP Scalability and High Availability
	function IsConnectionError($err)
	{
		switch($err) {
			case 378: /* buffer pool param incorrect */
			case 602: /* core dump */
			case 603: /* fatal error */
			case 609: /* attach failed */
			case 1012: /* not logged in */
			case 1033: /* init or shutdown in progress */
			case 1043: /* Oracle not available */
			case 1089: /* immediate shutdown in progress */
			case 1090: /* shutdown in progress */
			case 1092: /* instance terminated */
			case 3113: /* disconnect */
			case 3114: /* not connected */
			case 3122: /* closing window */
			case 3135: /* lost contact */
			case 12153: /* TNS: not connected */
			case 27146: /* fatal or instance terminated */
			case 28511: /* Lost RPC */
			return true;
		}
		return false;
	}

// Returns true or false
function _close()
{
    if (!$this->_connectionID) {
        return false;
    }

    if (!$this->autoCommit) {
        // Assuming $this->_connectionID is the SQL Relay connection ID
        sqlrc_rollback($this->_connectionID);
    }

    if (count($this->_refLOBs) > 0) {
        foreach ($this->_refLOBs as $key => $value) {
            // Assuming $this->_refLOBs[$key]['LOB'] is the SQL Relay LOB handle
            sqlrc_lob_free($this->_refLOBs[$key]['LOB']);
            unset($this->_refLOBs[$key]);
        }
    }

    // Assuming $this->_connectionID is the SQL Relay connection ID
    sqlrc_close($this->_connectionID);

    $this->_stmt = false;
    $this->_connectionID = false;

    return true;
}


function MetaPrimaryKeys($table, $owner = false, $internalKey = false)
{
    if ($internalKey) {
        return array('ROWID');
    }

    // tested with oracle 8.1.7
    $table = strtoupper($table);
    if ($owner) {
        $owner_clause = "AND ((a.OWNER = b.OWNER) AND (a.OWNER = UPPER('$owner')))";
        $ptab = 'ALL_';
    } else {
        $owner_clause = '';
        $ptab = 'USER_';
    }

    // Assuming $this->_connectionID is the SQL Relay connection ID
    $sql = "
        SELECT DISTINCT /*+ RULE */ b.column_name
        FROM {$ptab}CONSTRAINTS a
            , {$ptab}CONS_COLUMNS b
        WHERE ( UPPER(b.table_name) = ('$table'))
            AND (UPPER(a.table_name) = ('$table') and a.constraint_type = 'P')
            $owner_clause
            AND (a.constraint_name = b.constraint_name)
    ";

    $rs = $this->Execute($sql);

    if ($rs && !$rs->EOF) {
        $arr = $rs->GetArray();
        $primaryKeys = array();
        
        foreach ($arr as $v) {
            $primaryKeys[] = reset($v);
        }
        
        return $primaryKeys;
    } else {
        return false;
    }
}


	/**
	 * Returns a list of Foreign Keys associated with a specific table.
	 *
	 * @param string $table
	 * @param string $owner
	 * @param bool   $upper       discarded
	 * @param bool   $associative discarded
	 *
	 * @return string[]|false An array where keys are tables, and values are foreign keys;
	 *                        false if no foreign keys could be found.
	 */
	public function metaForeignKeys($table, $owner = '', $upper = false, $associative = false)
{
    global $ADODB_FETCH_MODE;

    $save = $ADODB_FETCH_MODE;
    $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
    $table = $this->qstr(strtoupper($table));

    if (!$owner) {
        $owner = $this->user;
        $tabp = 'user_';
    } else {
        $tabp = 'all_';
        $owner = ' and owner=' . $this->qstr(strtoupper($owner));
    }

    // Assuming $this is an instance of ADODB connection class
    $sql = "
        SELECT constraint_name, r_owner, r_constraint_name
        FROM {$tabp}constraints
        WHERE constraint_type = 'R' AND table_name = $table $owner
    ";

    $constraints = $this->GetArray($sql);
    $arr = false;

    foreach ($constraints as $constr) {
        $cons = $this->qstr($constr[0]);
        $rowner = $this->qstr($constr[1]);
        $rcons = $this->qstr($constr[2]);

        $cols = $this->GetArray("
            SELECT column_name
            FROM {$tabp}cons_columns
            WHERE constraint_name=$cons $owner
            ORDER BY position
        ");

        $tabcol = $this->GetArray("
            SELECT table_name, column_name
            FROM {$tabp}cons_columns
            WHERE owner=$rowner AND constraint_name=$rcons
            ORDER BY position
        ");

        if ($cols && $tabcol) {
            for ($i = 0, $max = sizeof($cols); $i < $max; $i++) {
                $arr[$tabcol[$i][0]] = $cols[$i][0] . '=' . $tabcol[$i][1];
            }
        }
    }

    $ADODB_FETCH_MODE = $save;

    return $arr;
}



	function CharMax()
	{
		return 4000;
	}

	function TextMax()
	{
		return 4000;
	}

	/**
	 * Correctly quotes a string so that all strings are escaped.
	 * We prefix and append to the string single-quotes.
	 * An example is  $db->qstr("Don't bother");
	 *
	 * @param string $s            The string to quote
	 * @param bool   $magic_quotes This param is not used since 5.21.0.
	 *                             It remains for backwards compatibility.
	 *
	 * @return string Quoted string to be sent back to database
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	function qStr($s, $magic_quotes = false)
{
    if ($this->noNullStrings && strlen($s) == 0) {
        $s = ' ';
    }

    // Replace a single backslash with two backslashes
    if ($this->replaceQuote[0] == '\\') {
        $s = str_replace('\\', '\\\\', $s);
    }

    // Replace single quotes with the configured replacement
    $s = str_replace("'", $this->replaceQuote, $s);

    // Assuming $this->_connectionID is the SQL Relay connection ID
    return sqlrc_escape_string($this->_connectionID, $s);
}


/*--------------------------------------------------------------------------------------
	Class Name: Recordset
--------------------------------------------------------------------------------------*/

class ADODB_sqlrelay  extends ADORecordSet {

    var $databaseType = 'sqlrelay';
	var $bind=false;
	var $_fieldobjs;

	/** @var resource Cursor reference */
	var $_refcursor;

	function __construct($queryID, $mode = false)
{
    if ($mode === false) {
        global $ADODB_FETCH_MODE;
        $mode = $ADODB_FETCH_MODE;
    }

    // Assuming $this->_connectionID is the SQL Relay connection ID
    switch ($mode) {
        case ADODB_FETCH_ASSOC:
            $this->fetchMode = sqlrc_assoc;
            break;
        case ADODB_FETCH_DEFAULT:
        case ADODB_FETCH_BOTH:
            $this->fetchMode = sqlrc_num | sqlrc_assoc;
            break;
        case ADODB_FETCH_NUM:
        default:
            $this->fetchMode = sqlrc_num;
            break;
    }

    // Additional flags for SQL Relay fetch mode
    $this->fetchMode |= sqlrc_nulls | sqlrc_lobs;

    // Set the ADODB fetch mode
    $this->adodbFetchMode = $mode;

    // Assuming $this->_queryID is the SQL Relay query ID
    $this->_queryID = $queryID;
}


	/**
	* Overrides the core destructor method as that causes problems here
	*
	* @return void
	*/
	function __destruct() {}

	function Init()
{
    if ($this->_inited) {
        return;
    }

    $this->_inited = true;

    if ($this->_queryID) {
        $this->_currentRow = 0;

        // Assuming $this->_queryID is the SQL Relay query ID
        $this->_numOfFields = sqlrc_num_fields($this->_queryID);
        $this->_numOfRows = sqlrc_num_rows($this->_queryID);

        if ($this->_numOfFields) {
            $this->EOF = !$this->_fetch();
        } else {
            $this->EOF = true;
        }

        /*
        // Uncomment the following code if you want to log Oracle errors during debugging
        $err = sqlrc_error($this->_queryID);
        if ($err && $this->connection->debug) {
            ADOConnection::outp($err);
        }
        */

        if (!is_array($this->fields)) {
            $this->fields = array();
        }
    } else {
        $this->fields = array();
        $this->_numOfRows = 0;
        $this->_numOfFields = 0;
        $this->EOF = true;
    }
}


function _initrs()
{
    $this->_numOfRows = -1;
    $this->_numOfFields = sqlrc_num_fields($this->_queryID);

    if ($this->_numOfFields > 0) {
        $this->_fieldobjs = array();
        $max = $this->_numOfFields;

        for ($i = 0; $i < $max; $i++) {
            $this->_fieldobjs[] = $this->_FetchField($i);
        }
    }
}

	/**
	 * Get column information in the Recordset object.
	 * fetchField() can be used in order to obtain information about fields
	 * in a certain query result. If the field offset isn't specified, the next
	 * field that wasn't yet retrieved by fetchField() is retrieved
	 *
	 * @return object containing field information
	 */
	function _FetchField($fieldOffset = -1)
{
    $fld = new ADOFieldObject;
    $fieldOffset += 1;

    // Assuming $this->_queryID is the SQL Relay query ID
    $fld->name = sqlrc_field_name($this->_queryID, $fieldOffset);

    if (ADODB_ASSOC_CASE == ADODB_ASSOC_CASE_LOWER) {
        $fld->name = strtolower($fld->name);
    }

    // Assuming $this->_queryID is the SQL Relay query ID
    $fld->type = sqlrc_field_type($this->_queryID, $fieldOffset);
    $fld->max_length = sqlrc_field_size($this->_queryID, $fieldOffset);

    switch ($fld->type) {
        case 'NUMBER':
            // Assuming $this->_queryID is the SQL Relay query ID
            $p = sqlrc_field_precision($this->_queryID, $fieldOffset);
            $sc = sqlrc_field_scale($this->_queryID, $fieldOffset);
            if ($p != 0 && $sc == 0) {
                $fld->type = 'INT';
            }
            $fld->scale = $p;
            break;

        case 'CLOB':
        case 'NCLOB':
        case 'BLOB':
            $fld->max_length = -1;
            break;
    }

    return $fld;
}

	/* For some reason, oci_field_name fails when called after _initrs() so we cache it */
	function FetchField($fieldOffset = -1)
	{
		return $this->_fieldobjs[$fieldOffset];
	}


function MoveNext()
{
    // Assuming $this->_queryID is the SQL Relay query ID
    if ($this->fields = sqlrc_fetch_array($this->_queryID, $this->fetchMode)) {
        $this->_currentRow += 1;
        $this->_updatefields();
        return true;
    }

    if (!$this->EOF) {
        $this->_currentRow += 1;
        $this->EOF = true;
    }

    return false;
}

	// Optimize SelectLimit() by using oci_fetch()
	function GetArrayLimit($nrows, $offset = -1)
{
    if ($offset <= 0) {
        // Assuming $this->_queryID is the SQL Relay query ID
        $arr = sqlrc_fetch_all($this->_queryID, $this->fetchMode, $nrows);
        return $arr;
    }

    $results = array();
    for ($i = 1; $i < $offset; $i++) {
        // Assuming $this->_queryID is the SQL Relay query ID
        if (!sqlrc_fetch($this->_queryID)) {
            return $results;
        }
    }

    // Assuming $this->_queryID is the SQL Relay query ID
    while ($this->fields = sqlrc_fetch_array($this->_queryID, $this->fetchMode)) {
        $this->_updatefields();
        $results[] = $this->fields;
        if (--$nrows == 0) {
            break;
        }
    }

    return $results;
}



	// Use associative array to get fields array
	function Fields($colname)
	{
		if (!$this->bind) {
			$this->bind = array();
			for ($i=0; $i < $this->_numOfFields; $i++) {
				$o = $this->FetchField($i);
				$this->bind[strtoupper($o->name)] = $i;
			}
		}

		return $this->fields[$this->bind[strtoupper($colname)]];
	}


	function _seek($row)
	{
		return false;
	}

    function _fetch()
    {
        // Assuming $this->_queryID is the SQL Relay query ID
        $this->fields = sqlrc_fetch_array($this->_queryID, $this->fetchMode);
        
        if ($this->fields !== false) {
            $this->_updatefields();
            return $this->fields;
        }
    
        return false;
    }
    

	/**
	 * close() only needs to be called if you are worried about using too much
	 * memory while your script is running. All associated result memory for the
	 * specified result identifier will automatically be freed.
	 */
	function _close()
{
    // Assuming $this->_queryID is the SQL Relay query ID
    if ($this->connection->_stmt === $this->_queryID) {
        $this->connection->_stmt = false;
    }
    if (!empty($this->_refcursor)) {
        // Assuming $this->_refcursor is the SQL Relay refcursor
        sqlrc_close($this->_refcursor);
        $this->_refcursor = false;
    }
    if (is_resource($this->_queryID)) {
        // Assuming $this->_queryID is the SQL Relay query ID
        @sqlrc_free($this->_queryID);
    }
    $this->_queryID = false;
}


	/**
	 * not the fastest implementation - quick and dirty - jlim
	 * for best performance, use the actual $rs->MetaType().
	 *
	 * @param	mixed	$t
	 * @param	int		$len		[optional] Length of blobsize
	 * @param	bool	$fieldobj	[optional][discarded]
	 * @return	str					The metatype of the field
	 */
	function MetaType($t, $len = -1, $fieldobj = false)
{
    if (is_object($t)) {
        $fieldobj = $t;
        $t = $fieldobj->type;
        $len = $fieldobj->max_length;
    }

    $t = strtoupper($t);

    if (array_key_exists($t, $this->connection->customActualTypes)) {
        return $this->connection->customActualTypes[$t];
    }

    switch ($t) {
        case 'VARCHAR':
        case 'VARCHAR2':
        case 'CHAR':
        case 'VARBINARY':
        case 'BINARY':
        case 'NCHAR':
        case 'NVARCHAR':
        case 'NVARCHAR2':
            if ($len <= $this->blobSize) {
                return 'C';
            }

        case 'NCLOB':
        case 'LONG':
        case 'LONG VARCHAR':
        case 'CLOB':
            return 'X';

        case 'LONG RAW':
        case 'LONG VARBINARY':
        case 'BLOB':
            return 'B';

        case 'DATE':
            return ($this->connection->datetime) ? 'T' : 'D';

        case 'TIMESTAMP':
            return 'T';

        case 'INT':
        case 'SMALLINT':
        case 'INTEGER':
            return 'I';

        default:
            return ADODB_DEFAULT_METATYPE;
    }
}


class  ADORecordSet_ext_sqlrelay extends ADODB_sqlrelay  {

	function MoveNext()
	{
		return adodb_movenext($this);
	}
}