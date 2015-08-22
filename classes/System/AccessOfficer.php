<?php

class AccessOfficer {
	
	public static function check($table, $id, User $user){
		// Check users access rights to any object
		$cachekey = "access:" . $table . ":" . $id . ":" . $user->getID();
		$access = Cache::get($cachekey);
		if(!isset($access)){
			// No cache found, load access rights from database
			$access = true;
			$sql = new SqlManager();
			$sql->setQuery("
				SELECT * FROM access
				WHERE object_table = {{table}}
					AND object_id = {{id}}
				");
			$sql->bindParam("{{table}}", $table);
			$sql->bindParam("{{id}}", $id, "int");
			$sql->execute();
			while($row = $sql->fetch()){
				switch($row['access_type']){
					case "password":
						// Check somehow if user entered password already
						break;
					case "usergroup":
						// Check if user is part of the usergroup	
						break;
					default:
						$access = false;
						break;
				}
				if($access){
					// If one of the access settings allows access, it's enough
					// Stop loop
					break;
				}
			}
			// Save determined access in cahce for later use
			Cache::save($cachekey, $access);
		}
		return $access;
	}
	
	public static function checkContent($url, User $user){
		// Check call for contents via URL
		$content = new Content();
		$content->loadFromUrl($url);
		$this->check("content", $content->getID(), $user);
	}
	
}