<?php

class AccessOfficer {
	
	private $access = null;
	private $access_data = null;
	
	private $table = null;
	private $id = null;
	private $user = null;
	
	private $parents = array();
	
	public function __construct($table, $id, User $user, $parents=array()){
		// Set all important data to identify access information
		$this->setTable($table);
		$this->setID($id);
		$this->setUser($user);
		$this->setParents($parents);
	}
	
	public function setTable($table){
		// Set instances table
		$this->table = $table;
	}
	
	public function setID($id){
		// Set the ID to be requested
		$this->id = $id;
	}
	
	public function setUser(User $user){
		// Set user object to check access details
		$this->user = $user;
	}
	
	public function setUserID($id){
		// Set user by ID
		$this->user = new User($id);
	}
	
	public function setParents(array $parents){
		// Set parents tree in array from of requested ID
		$this->parents = $parents;
	}
	
	public function check(){
		// Check rights for setup instance
		// Check if neccessary fields are all set
		if(!$this->table){
			throw new Exception("Table not set!");
			return;
		}
		if(!$this->id){
			throw new Exception("ID not set!");
			return;
		}
		if(!is_object($this->user)){
			throw new Exception("User not set!");
			return;
		}
		// Check users access rights to any object
		$cachekey = "access:" . $this->table . ":" . $this->id . ":" . $this->user->getID();
		$this->access = Cache::load($cachekey);
		if(is_null($this->access)){
			// No cache found, load access rights from database
			$this->access = true;
			$sql = new SqlManager();
			$sql->setQuery("
				SELECT * FROM access
				WHERE object_table = '{{table}}'
					AND object_id = {{id}}
				");
			$sql->bindParam("{{table}}", $this->table);
			$sql->bindParam("{{id}}", $this->id, "int");
			$sql->execute();
			$rulescnt = 0;
			while($row = $sql->fetch()){
				$rulescnt++;
				switch($row['access_type']){
					case "password":
						// Check somehow if user entered password already
						$this->access = false;
						$this->access_data = $row;
						break;
					case "usergroup":
						// Check if user is part of the usergroup
						if(!in_array($row['access_key'], $this->user->getUserGroups())){
							$this->access = false;
							$this->access_data = $row;
						}
						break;
					default:
						$this->access = false;
						break;
				}
				if($this->access){
					// If one of the access settings allows access, it's enough
					// Stop loop
					$this->access_data = array();
					break;
				}
			}
			if($this->access && count($this->parents) > 0){
				foreach($this->parents as $parent){
					$check = $this->parents[count($this->parents) - 1];
					unset($this->parents[count($this->parents) - 1]);
					$check = new AccessOfficer($this->table, $check, $this->user, $this->parents);
					$this->access = $check->check();
					$this->access_data = $check->getAccessData();
				}
			}
			// Save determined access in cahce for later use
			Cache::save($cachekey, $this->access);
		}
		return $this->access;
	}
	
	public function checkContent(Content $content, User $user=null){
		// Check call for contents
		$this->setTable("content");
		$this->setID($content->getID());
		if(!is_null($user)) $this->setUser($user);
		$this->setParents($content->getParentsArray());
		return self::check();
	}
	
	public function checkContentFromUrl($url, User $user=null){
		// Check call for contents via URL
		$content = new Content(null, $url);
		return $this->checkContent($content, $user);	
	}
	
	public function checkContentFromID($id, User $user=null){
		// Check call for contents
		$content = new Content($id);
		return $this->checkContent($content, $user);	
	}
	
	public function getDeniedContentID(){
		// Return denied content id
		return $this->access_data['denied_content_id'];
	}
	
	public function isRedirect(){
		// Return denied content id
		if($this->access_data['redirect'] == 1 && $this->getDeniedContentID()){
			return true;
		}
		return false;
	}
	
	public function getAccessData(){
		// Return acces data array
		return $this->access_data;
	}
	
}