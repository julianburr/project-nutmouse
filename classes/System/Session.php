<?php

class Session {
	
	private $id = null;
	
	private $user = array();
	private $config = array();
	
	private $loggedin = false;
	
	public function __construct(){
		// Init session environment
		$this->init();
	}
	
	public function init(){
		// Point variables to session array
		$this->id = &$_SESSION['id'];
		$this->user = &$_SESSION['user'];
		$this->config = &$_SESSION['config'];
		// If new session, create it
		if($this->id != session_id()){
			$this->create();
		}
	}
	
	private function create(){
		// Create a new session
		$this->id = session_id();
		// And write session into database
		$insert = array("key" => $this->id, "created" => DateManager::now());
		$sql = new SqlManager();
		$sql->insert("session", $insert);
		// Create all session objects
		$this->user = new User();
		$this->config = new Config();	
	}
	
	public function kill(){
		// Kill current session
		unset($_SESSION);
		session_regenerate_id();
		$this->init();
	}
	
	public function login($username, $password){
		// Log user into session
		$this->user->loadByLogin($username, $password);
		if(!is_null($this->user->getID())){
			$this->loggendin = true;
		}
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