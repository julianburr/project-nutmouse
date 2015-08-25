<?php

class Version {
	
	public static function loadVersions($type, $id){
		$versions = Cache::load("version:" . $type . ":" . $id);
		if(!is_array($versions)){
			$sql = new SqlManager();
			$sql->setQuery("
				SELECT id, date FROM version
				WHERE object_table = '{{type}}'
					AND object_id = {{id}}
					AND draft IS NULL
				ORDER BY date ASC
				");
			$sql->bindParam("{{type}}", $type);
			$sql->bindParam("{{id}}", $id, "int");
			$sql->execute();
			$versions = array("_index" => array());
			while($row = $sql->fetch()){
				$versions['_index'][] = $row['id'];
				$versions[$row['id']] = $row;
			}
			Cache::save("version:" . $type . ":" . $id, $versions);
		}
		return $versions;
	}
	
	public static function load($type, $id, $version){
		$return = $this->loadVersions();
		if(isset($return[$version])){
			return $return[$version];
		}
		return null;
	}
	
	public static function save($type, $id, $data){
		$data['object_type'] = $type;
		$data['object_id'] = $id;
		$sql = new SqlManager();
		if(!isset($data['id'])){
			$sql->insert("version", $data);
		} else {
			$sql->setQuery("
				SELECT id FROM version
				WHERE id = {{id}}
				LIMIT 1
				");
			$sql->bindParam("{{id}}", $data['id']);
			$check = $sql->result();
			if(isset($check['id'])){
				$sql->update("version", $data);
			} else {
				$sql->insert("version", $data);
			}
		}	
	}
	
	public static function create($type, $id, $data){
		// Creates new data entry
		$data['object_type'] = $type;
		$data['object_id'] = $id;
		if(isset($data['id'])){
			unset($data['id']);
		}
		$sql->insert("version", $data);
	}
	
}