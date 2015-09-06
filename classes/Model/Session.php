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
	
	public function login($username, $password, array $groups=array()){
		// Log user into session
		$this->user->loadByLogin($username, $password, $groups);
		if(!is_null($this->user->getID())){
			$this->loggedin = true;
			return true;
		}
		return false;
	}
	
	public function userLogin($request){
		// Action method to login user by request username and password
		if(empty($request['username']) || empty($request['password'])){
			$missing_fields = array();
			if(empty($this->request['username'])) $missing_fields[] = "username";
			if(empty($this->request['password'])) $missing_fields[] = "password";
			return array(
				"message" => array(
					"type" => "error", 
					"code" => "MandatoryInputMissing"
				),
				"missing_fields" => $missing_fields					
			);
		}
		if(!isset($request['usergroups']) || !is_array($request['usergroups'])){
			$request['usergroups'] = array();
		}
		$success = $this->login($request['username'], $request['password'], $request['usergroups']);
		if(!$success){
			return array(
				"message" => array(
					"type" => "error", 
					"code" => "LoginFailed"
				)
			);
		}
		return true;
	}
	
	public function userRegister($request){
		// Register new user from request
		if(empty($request['username'])){
			return array("message" => array(
					"type" => "error", 
					"code" => "MandatoryInputMissing"
				),
				"missing_fields" => "username"
			);
		}
		$sql = new SqlManager();
		$sql->setQuery("SELECT * FROM user WHERE username = '{{username}}'");
		$sql->bindParam("{{username}}", $request['username']);
		$check = $sql->result();
		if(isset($check['id'])){
			return array("message" => array(
					"type" => "error", 
					"code" => "UsernameUsed"
				)
			);
		}
		$newuser = new User();
		$id = $newuser->create(array("username" => $request['username']));
		if(!$id){
			return array("message" => array(
					"type" => "error", 
					"code" => "UnknownError"
				)
			);
		}
		$newuser->load($id);
		$groups = array();
		if(isset($request['usergroups'])){
			$groups = split(",", $request['usergroups']);
		}
		foreach($groups as $group){
			Meta::save("user", $newuser->getID(), "usergroup", $group);
		}
		$newuser->createAuthCode();
		return array(
			"redirect" => array(
				"url" => "/bestaetige-account", 
				"post" => array(
					"username" => $request['username']
				)
			)
		);
	}
	
	public function userAuth($request){
		// Check entered auth code and activate user if successful
		if(empty($request['authcode'])){
			return array("message" => array(
					"type" => "error", 
					"code" => "MandatoryInputMissing"
				),
				"missing_fields" => "authcode"
			);
		}
		if(empty($request['username'])){
			return array("message" => array(
					"type" => "error", 
					"code" => "DataCorrupt"
				)
			);
		}
	}
	
	public function logout(){
		// Log user out of session
		$this->user = new User();
		$this->loggedin = false;
		return array(
			"message" => array(
				"type" => "success", 
				"code" => "LogoutSuccess"
			)
		);
	}
	
	public function isLoggedIn(){
		// Check if session has a user logged in
		return $this->loggedin;
	}
	
	public function registerActions(){
		// Register actions for session instance
		Action::register("killMe", array($this, "kill"));
		Action::register("userLogin", array($this, "userLogin"));
		Action::register("userLogout", array($this, "logout"));
		Action::register("userRegister", array($this, "userRegister"));
	}
	
	public function getUser(){
		// Return user object
		return $this->user;
	}

}