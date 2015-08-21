<?php

class User {
	
	private $id = null;
	
	private $data = array();
	private $config = null;
	
	private $meta = array();
	
	public function __construct($id=null){
		if(!is_null($id)){
			// If id is given, load user from id
			$this->load($id);
		}
	}
	
	public function load($id){
		// Load user from database
		$sql = new SqlManager();
		$sql->setQuery("SELECT * FROM user WHERE id = {{id}}");
		$sql->bindParam("{{id}}", $id);
		$user = $sql->result();
		if(isset($user['id']) && $user['id'] == $id){
			$this->id = $id;
			$this->data = $user;
			$this->meta = Meta::load("user", $this->id);
			$this->config = new Config($this->id);
		}
	}
	
	public function loadByLogin($username, $password){
		// Load user from database by given login data
		$sql = new SqlManager();
		$sql->setQuery("SELECT id FROM user WHERE username = '{{username}}' AND password = '{{password}}'");
		$sql->bindParam("{{username}}", $username);
		$sql->bindParam("{{password}}", md5($password));
		$user = $sql->result();
		if(isset($user['id'])){
			$this->load($user['id']);
		}
	}
	
	public function update(array $data){
		// Update user in database from given data array
		$sql = new SqlManager();
		$sql->update("user", $data);
	}
	
	public function delete(array $data){
		// Delete user from data array
		$sql = new SqlManager();
		$sql->delete("user", $data);
	}
	
	public function deleteByID(int $id){
		// Delete user by from given id
		$data = array("id" => $id);
		$this->delete($data);
	}
	
	public function create(array $data){
		// Create new user from given data array
		$sql = new SqlManager();
		$sql->insert("user", $data);
		// Return database ID of added user
		return $sql->getLastInsertID();
	}
	
	public function getID(){
		// Get current users ID (NULL if no user has been loaded!)
		return $this->id;
	}
	
}