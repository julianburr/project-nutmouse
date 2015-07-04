<?php

class Config {
	
	private $config = array();
	private $user = null;
	
	public function __construct($user=null){
		// Set user and init config
		$this->user = $user;
		$this->init();
	}
	
	public function init(){
		// Init config array and point it to session
		if(!is_null($this->user)){
			$this->config = &$_SESSION['__CONFIG' . $this->user . '__'];
		} else {
			$this->config = &$_SESSION['__CONFIG__'];
		}
		if(!isset($this->config) || !is_array($this->config)){
			// Load config if not done yet
			$this->load();
		}
	}
	
	public function load(){
		// Load config from database
		$sql = new SqlManager();
		if(!is_null($this->user)){
			$sql->setQuery("SELECT * FROM config WHERE user_id = {{user}}");
			$sql->bindParam("{{user}}", $this->user);
		} else {
			$sql->setQuery("SELECT * FROM config WHERE user_id = NULL");
		}
		$sql->execute();
		$this->config = array();
		while($row = $sql->fetch()){
			$this->config[$row['name']] = $row['value'];
		}
	}
	
	public function set($name, $value){
		// Set config and update database
		$sql = new SqlManager();
		$set = array("name" => $name, "user_id" => $this->user, "value" => $value);
		if(isset($this->config[$name])){
			$sql->update("config", $set);
		} else {
			$sql->insert("config", $set);
		}
		$this->config[$name] = $value;
	}
	
	public function get($name){
		// Get config from currently loaded config array
		return $this->config[$name];
	}
	
}