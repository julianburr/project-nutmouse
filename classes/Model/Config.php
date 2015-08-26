<?php

class Config {
	
	private $config = array();
	private $user = null;
	
	public function __construct($user=null){
		// Set user and load config
		$this->user = $user;
		$this->load();
	}
	
	public function load(){
		// Load config
		// Try from cache
		$cachekey = "config";
		if($this->user){
			$cachekey .= ":" . $this->user;
		}
		$this->config = Cache::load("config");
		if(!is_array($this->config) || $this->get('cache.active') != 1){
			// Load config from database
			$sql = new SqlManager();
			if(!is_null($this->user)){
				$sql->setQuery("SELECT * FROM config WHERE user_id = {{user}}");
				$sql->bindParam("{{user}}", $this->user);
			} else {
				$sql->setQuery("SELECT * FROM config WHERE user_id IS NULL");
			}
			$sql->execute();
			$this->config = array();
			while($row = $sql->fetch()){
				$this->config[$row['name']] = $row['value'];
			}
			if(!isset($this->config['cache.active']) || $this->config['cache.active'] != 1){
				// If cache is deactivated, clear possible cache file
				Cache::clear($cachekey);
			} else {
				// If cache is activeated, save config for later use
				Cache::save($cachekey, $this->config);
			}
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
		if(isset($this->config[$name])){
			return $this->config[$name];
		}
		return null;
	}
	
}