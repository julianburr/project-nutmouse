<?php

class Meta {
	
	public static function load($table, $id, $name=null, $single=false){
		// Load meta data from database
		$sql = new SqlManager();
		$sql->setQuery("SELECT * FROM meta WHERE object_table = '{{table}}' AND object_id = {{id}}");
		$sql->bindParam("{{table}}", $table);
		$sql->bindParam("{{id}}", $id, "int");
		$sql->execute();
		$return = array();
		while($meta = $sql->fetch()){
			if(!is_null($name) && $meta['name'] != $name){
				continue;
			}
			if(!$single){
				if(!isset($return[$meta['name']])){
					$return[$meta['name']] = array();
				}
				$return[$meta['name']][] = $meta['value'];
			} else {
				$return[$meta['name']] = $meta['value'];
			}
		}
		return $return;
	}
	
	public static function loadUnique($table, $id, $name=null){
		// Synonym for load(?,?,?,true)
		return self::load($table, $id, $name, true);
	}
	
	public static function get($table, $id, $name, $single=false){
		// Synonym for load() with given name
		return self::load($table, $id, $name, $single);
	}
	
	public static function save($table, $id, $name, $value){
		// Insert new meta dataset into database
		$sql = new SqlManager();
		$insert = array(
			"object_table" => $table,
			"object_id" => $id,
			"name" => $name,
			"value" => $value
		);
		$sql->insert("meta", $insert);
	}
	
	public static function update($table, $id, $name, $value){
		// Update meta dataset(s!!) in database
		// Notice: All datasets with the given name will be updated!
		$sql = new SqlManager();
		$sql->setQuery("
			UPDATE meta SET value = '{{value}}' 
			WHERE object_table = '{{table}}'
				AND object_id = {{id}}
				AND name = '{{name}}'
			");
		$sql->bindParam("{{value}}", $value);
		$sql->bindParam("{{table}}", $tavle);
		$sql->bindParam("{{id}}", $id, "int");
		$sql->bindParam("{{name}}", $name);
		$sql->execute();
	}
	
	public static function updateFromID($metaid, $value){
		// Update single meta dataset by using the meta id
		$update = array("id" => $metaid, "value" => $value);
		$sql = new SqlManager();
		$sql->update("meta", $update);
	}
	
	public static function updateSingle($metaid, $value){
		// Synonym for udpateFromID()
		self::updateFromID($metaid, $value);
	}

}