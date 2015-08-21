<?php

class Session {
	
	private $id = null;
	
	private $data = array();
	
	private $user = null;
	private $config = null;
	
	private $loggedin = false;
	
	public function __construct($auto=false){
		if($auto){
			// Init session automatically
			$this->init();
		}
	}
	
	public function init(){
		// Point variables to session array
		$this->id = &$_SESSION['id'];
		$this->user = &$_SESSION['user'];
		$this->config = &$_SESSION['config'];
		$this->data = &$_SESSION['data'];
		$this->loggedin = &$_SESSION['loggedin'];
		// Load session and additional data from database
		if($this->id != session_id()){
			$this->load();
		}
	}
	
	private function create(){
		debug_print_backtrace();
		// Create a new session
		$this->id = session_id();
		// And write session into database
		$insert = array("phpkey" => $this->id, "created" => DateManager::now());
		$sql = new SqlManager();
		$sql->insert("session", $insert);
		// Save data in instance
		$this->data = $insert;
		$this->data['id'] = $sql->getLastInsertID();
		// Write meta data into database
		foreach($_SERVER as $key => $value){
			Meta::save("session", $this->data['id'], $key, $value);
		}
		// Set login status
		$this->loggedin = false;
		// Create user and config object for session
		$this->user = new User();
		$this->config = new Config();
	}
	
	private function load(){
		// Load session data from database
		$sql = new SqlManager();
		$sql->setQuery("SELECT * FROM session WHERE phpkey = '{{id}}'");
		$sql->bindParam("{{id}}", $this->id);
		$this->data = $sql->result();
		if(!isset($this->data['id'])){
			// No session found, create new session
			$this->create();
		}
		// Load meta data
		$this->data['meta'] = Meta::load("session", $this->data['id']);
		// Hook for post loading manipulations
		$this->data = HookPoint::call("Session.Load", $this->data);
	}
	
	public function kill(){
		// Kill current session
		unset($_SESSION['id']);
		session_regenerate_id();
		$this->init();
	}
	
	public function login($username, $password){
		// Log user into session
		$this->user->loadByLogin($username, $password);
		if(!is_null($this->user->getID())){
			$this->loggedin = true;
			return true;
		}
		return false;
	}
	
	public function logout(){
		// Log user out of session
		$this->user = new User();
		$this->loggedin = false;
	}
	
	public function isLoggedIn(){
		// Check if session has a user logged in
		return $this->loggedin;
	}
		
}