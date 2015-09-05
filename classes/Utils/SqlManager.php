<?php

class SqlManager {
	
	private $mysqlhost = "";
	private $mysqluser = "";
	private $mysqlpwd = "";
	private $mysqldb = "";
	
	private $connection;
	
	private $configfile =  "../../config.php";
	
	private $query;
	private $q_result;
	private $q_table;
	
	private $table_info = array();
	
	public function __construct(){
		// Load config
		$this->loadConfig();
		
		// Establish connection
		$this->connect();
	}
	
	private function loadConfig($path=null){
		/**
		 * Parameters
		 * path (optional): path to the config file (relative from document root!)
		 **/
		
		if(isset($path)){
			$this->configfile = $path;
		}
		
		// Load SQL configuration from config file
		if(is_file(__DIR__ . "/" . $this->configfile)){
			include(__DIR__ . "/" . $this->configfile);
		} else {
			throw new Exception("SQL config file not found!");
		}
		
		// Set class varibles
		$this->mysqlhost = $HOST;
		$this->mysqluser = $USER;
		$this->mysqlpwd = $PWD;
		$this->mysqldb = $DB;
	}
	
	private function connect(){
		// Check configurations
		if(!isset($this->mysqlhost) || !isset($this->mysqluser) || !isset($this->mysqlpwd) || !isset($this->mysqldb)){
			throw new Exception("Cannot connect to database. No configuration found!");
		}
		
		// Connect to database
		$this->connection = mysqli_connect($this->mysqlhost,$this->mysqluser,$this->mysqlpwd);
		if(!$this->connection){ 
			throw new Exception("SQL connection failed!");
		}
		
		$selection = mysqli_select_db($this->connection, $this->mysqldb);
		if(!$selection){
			throw new Exception("Unable to connect to selected database!");
		}
	}
	
	public function setQuery($query){
		/**
		 * Sets the SQL query for the instance
		 *
		 * Parameters
		 * query: Query string with placeholders for variable content
		 **/
		 
		if(empty($query)){
			throw new Exception("Query string must not be emtpty!");
		}
		
		$this->query = $query;
	}
	
	public function bindParam($string, $value, $type="string"){
		/**
		 * Ensures save use of variable data and user inserations
		 *
		 * Parameters
		 * string: Placeholder used in setQuery
		 * value: Value the placeholder should be replaced with
		 * type (optional): Type of content of the value (see below)
		 **/
		// Check placeholder
		if(empty($string)){
			throw new Exception("Placeholder must not be empty!");
		}
		
		// Escape and secure value
		$value = $this->escape($value, $type);
		
		// Replace placeholder
		$this->query = str_replace($string,$value,$this->query);
	}
	
	public function escape($value, $type=null){
		/**
		 * Escapes values to prevent SQL injection and simular attacks
		 *
		 * Parameters
		 * value: String (or other value) to be escaped
		 * type (optional): Content type of the value, e.g. string, int, html, date, etc.
		 *
		 * Returns
		 * value: The escaped value
		 **/ 
		 
		$value = mysqli_real_escape_string($this->connection, $value);
		switch($type){
			case "int":
				$value = (int)$value;
				break;
		}
		
		return $value;
	}
	
	public function execute(){
		/**
		 * Execute the prepared query
		 *
		 * Returns
		 * result: SQL result object (if no error occured!)
		 **/
		
		if(empty($this->query)){
			throw new Exception("Query string is empty and cannot be executed!");
		}
		
		$result = mysqli_query($this->connection, $this->query);
		
		if(!$result){
			throw new Exception("SQL execution error:\n\n{$this->query}\n\n" . mysqli_error($this->connection));
		} else {
			$this->q_result = $result;
			return $result;
		}
	}
	
	public function fetch($result=null){
		/**
		 * Fetching array from result ressource
		 *
		 * Parameters
		 * result (optional): SQL result ressource
		 *
		 * Returns
		 * fetch: fetched data from SQL ressource
		 **/
		 // Fetch data from ressource
		 $fetch = false;
		 if(!empty($result)){
			 $fetch = mysqli_fetch_array($result);
		 } else {
			if(!isset($this->q_result)){
				throw new Exception("No SQL ressource found. Cannot fetch data!");
			} else {
				$fetch = mysqli_fetch_array($this->q_result);
			}
		 }
		 
		 return $fetch;
	}
	
	public function fetchObject($result=null){
		/**
		 * Fetching object from result ressource
		 *
		 * for Parameters + Returns see fetch() above
		 **/
		 // Fetch data from ressource
		 $fetch = false;
		 if(!empty($result)){
			 $fetch = mysqli_fetch_object($result);
		 } else {
			if(!isset($this->q_result)){
				throw new Exception("No SQL ressource found. Cannot fetch data!");
			} else {
				$fetch = mysqli_fetch_object($this->q_result);
			}
		 }
		 
		 return $fetch;
	}
	
	public function result($returntype="array"){
		/**
		 * Returns the result of a SELECT query with only one result directly in the wished form of data
		 *
		 * Parameters
		 * returntype (optional): either array or object (default=array)
		 *
		 * Returns
		 * result: The SQL result as an array or object (see above)
		 **/
		// Check query string
		if(!$this->query){
			throw new Exception("Query string is empty and cannot be executed!");
		}
		
		// Fetch data
		$result = null;
		$query = $this->execute();
		
		switch($returntype){
			case "array":
				while($row = $this->fetch()){
					$result = $row;
				}
				break;
			case "object":
				while($row = $this->fetchObject()){
					$result = $row;
				}
				break;
			default:
				throw new Exception("Unknown return type. Cannot fetch data!");
				break;
		}
		
		// Return data
		return $result;
	}
	
	public function insert($table, $data_array){
		/**
		 * Inserts a new data row into the selected table
		 *
		 * Parameters
		 * table: Tablename as a string, in which the data should be inserted
		 * data_array: Array with the data to be inserted, fieldname as key, fieldvalue as value
		 **/
		// Check parameters
		if(empty($table)){
			throw new Exception("Table name must not be empty. Data cannot be inserted!");
			return;
		} elseif(!is_array($data_array)){
			throw new Exception("Data to be inserted must be an array. Data cannot be inserted!");
			return;
		}
		
		$this->q_table = $table;
		
		// Validate and prepare data array
		$insert = $this->getDataObject($data_array);
		if(count($insert) < 1){
			// no valid data found, so nothing to insert
			return;
		}
		
		// Build up the SQL statement
		$statement = "INSERT INTO " . $this->escape($this->q_table) . " (";
		$sep = "";
		foreach($insert as $key => $value){
			$statement .= $sep . $this->escape($key);
			$sep = ",";
		}
		$statement .= ") VALUES (";
		$sep = "";
		foreach($insert as $key => $value){
			$value = stripslashes($value);
			$statement .= $sep . " '" . $this->escape($value) . "'";
			$sep = ",";
		}
		$statement .= ")";
		
		// and execute it
		$this->setQuery($statement);
		$this->execute();
	}
	
	public function getLastInsertID(){
		/**
		 * Basicly just returns the last inserted ID via the core function
		 *
		 * Returns
		 * mysqli_insert_id(): PHP core function
		 **/
		 
		return mysqli_insert_id($this->connection);
	}
	
	public function getLineCount($table){
		/**
		 * Returns the row number of the requested table
		 *
		 * Parameters
		 * table: Requested table
		 *
		 * Returns
		 * result[cnt]: Row cnt of the requested table
		 **/
		
		$this->setQuery("SELECT COUNT(*) AS cnt FROM " . $this->escape($table));
		$result = $this->result();
		return $result['cnt'];
	}
	
	public function update($table,$data_array){
		/**
		 * Update table data from the given array
		 *
		 * Parameters
		 * table: Name of the SQL table to be updated
		 * data_array: Array of the new data, fieldnames as key and fieldvalues as values
		 *
		 * Returns
		 * boolean if the update run well
		 **/
		// Check parameters
		if(empty($table)){
			throw new Exception("Table name must not be empty. Data cannot be updated!");
			return;
		} elseif(!is_array($data_array)){
			throw new Exception("Data to be inserted must be an array. Data cannot be updated!");
			return;
		}
		
		$this->q_table = $table;
		
		// Get valid data array
		$update = $this->getDataObject($data_array);
		foreach($this->table_info['primarykey'] as $key){
			if(!$update[$key]){
				return;
			}
		}
		
		// Build up the SQL statement
		$statement = "UPDATE {$this->q_table} SET ";
		$sep = "";
		foreach($update as $key => $value){
			$value = stripslashes($value);
			$statement .= "${sep} " . $this->escape($key) . "='" . $this->escape($value) . "'";
			$sep = ",";
		}
		$statement .= "WHERE ";
		$sep = "";
		foreach($this->table_info['primarykey'] as $key){
			$statement .= "${sep} ${key}='" . $this->escape($update[$key]) . "'";
			$sep = "AND";
		}
		$statement .= " LIMIT 1";
		
		// Execute statement
		$this->setQuery($statement);
		$this->execute();
	}
	
	public function delete($table,$data_array){
		/**
		 * Delete table row by given primary key
		 *
		 * Parameters
		 * table: Requested table name, the data should be deleted from
		 * data_array: Array with (at least) the primary key, fieldname as key, fieldvalue as value
		 **/
		// Check parameters
		if(empty($table)){
			throw new Exception("Table name must not be empty. Data cannot be deleted!");
			return;
		} elseif(!is_array($data_array)){
			throw new Exception("Data to be inserted must be an array. Data cannot be deleted!");
			return;
		}
		
		$this->q_table = $table;
		
		// Get vaild data array
		$delete = $this->getDataObject($object);
		foreach($this->table_info['primarykey'] as $key){
			if(!$delete[$key]){
				return;
			}
		}
		
		// Build up SQL statement
		$statement = "DELETE FROM {$this->q_table} WHERE ";
		$sep = "";
		foreach($this->table_info['primarykey'] as $key){
			$delete[$key] = stripslashes($delete[$key]);
			$statement .= "${sep} ${key}='" . $this->escape($delete[$key]) . "'";
			$sep = "AND";
		}
		$statement .= " LIMIT 1";
		
		// Execute statement
		$this->setQuery($statement);
		$this->execute();
	}
	
	public function get($table,$keyfield,$keyvalue){
		/**
		 * Get specific SQL data row from the requested table
		 *
		 * Parameters
		 * table: Name of the SQL table
		 * keyfield: Name of the primary key field
		 * keyvalue: Requested primary key value of the data row
		 *
		 * Returns
		 * result: Array with the requested data
		 **/
		 
		$result = array();
		$keyvalue = stripslashes($keyvalue);
		$this->setQuery("
			SELECT * FROM " . $this->escape($table) . " 
			WHERE " . $this->escape($keyfield) . " = '" . $this->escape($keyvalue) . "' 
			LIMIT 1
			");
		$result = $this->result();
		return $result;
	}
	
	public function getDataObject($object){
		/**
		 * Verify and modify the given data array according to the given SQL table
		 * The table name is set in $this->q_table!
		 *
		 * Parameters
		 * data_array: Array with data (e.g. to insert into a table)
		 *
		 * Returns
		 * data: Validated data array
		 **/
		// Is there a table set to get the data from
		if(!$this->q_table){
			return;
		}
		
		// Create array with only valid data according to the currently selected table
		$data = array();
		$this->table_info = $this->getTableInfo($this->q_table);
		foreach($object as $key => $value){
			if(in_array($key,$this->table_info['fields']) !== false){
				$data[$key] = $value;
			}
		}
		return $data;
	}
	
	public function getTableInfo($table){
		/**
		 * Load table information data from requested table
		 *
		 * Parameters
		 * table: Name of the SQL table
		 *
		 * Returns
		 * info: Array with the gathered table information
		 **/
		
		$this->setQuery("DESCRIBE " . $this->escape($table));
		$result = $this->execute();
		
		$info['keys'] = array();
		$info['primarykey'] = array();
		$info['fields'] = array();
		$info['field'] = array();
		
		while($row = mysqli_fetch_array($result)){
			$info['fields'][] = $row['Field'];
			$info['field'][$row['Field']] = $row;
			if($row['Key']){
				$info['keys'][] = $row['Field'];
				if($row['Key'] == "PRI"){
					$info['primarykey'][] = $row['Field'];
				}
			}
		}
		
		return $info;
	}
	
	public function executeSqlFile($filepath){
		/**
		 * Execute (multiple) SQL statement(s) from given file
		 *
		 * Parameters
		 * filepath: File path to the SQL file that should be executed (relative path from document root!)
		 **/
		
		if(!is_file($filepath)){
			throw new Exception("File not found. Cannot execute SQL file!");
			return;
		}
		
		// Extract queries from file
		$queries = $this->getQueriesFromFile($filepath);
		
		// and execute them 
		foreach($queries as $q){
			$this->setQuery($q)->execute();
		}
	}
	
	public function getQueriesFromFile($filepath){
		/**
		 * Extract SQL queries from SQL file
		 * NOTE: Queries are seperated by following sheme: ";\n", so it only works it the queries
		 * are seperated that way too and no subqueries distract this scanning sheme!
		 *
		 * Parameters
		 * filepath: File path to the SQL file (relative path from document root!)
		 *
		 * Returns
		 * queries: Array with extracted queries
		 **/
		
		if(!is_file($filepath)){
			throw new Exception("File not found. Cannot extract queries!");
			return;
		}
		
		// Collect queries in array to return them
		$queries = array();
		$handle = fopen($filepath, 'rb');
		while (!feof($handle)) {
			$queries[] = stream_get_line($handle, 1000000, ";\n");
		}
		return $queries;
	}
	
	public function arrayToInString(array $array){
		// Transforms an array of values to a string to be used in an IN statement
		$string = "";
		$sep = "";
		foreach($array as $value){
			if(is_numeric($value)){
				$string .= $sep . $value;
			} else {
				$string .= $sep . "'" . $value . "'";
			}
			$sep = ",";
		}
		return $string;
	}
}