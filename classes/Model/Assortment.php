<?php

class Assortment {
	
	public static function getTree($name, $levels=1){
		$cachekey = "assortment:tree:" . $name;
		$assortment = Cache::load($cachekey);
		if(!is_array($assortment)){
			$assortment = self::load($name, null, 0, $levels);
			Cache::save($cachekey, $assortment);
		}
		return $assortment;
			
	}
	
	public static function get($id, $name=null){
		
	}
	
	public static function load($name, $parent=null, $level=0, $maxlevel=0){
		$assortment = array();
		$sql = new SqlManager();
		if(is_null($parent)){
			$sql->setQuery("
				SELECT assortment.*, COUNT(*) AS cnt FROM assortment
				LEFT JOIN meta ON (meta.name = 'assortment' AND meta.value = assortment.id)
				WHERE assortment.name = '{{name}}'
					AND assortment.parent_id IS NULL
				GROUP BY assortment.id
				ORDER BY assortment.sortkey ASC
				");
		} else {
			$sql->setQuery("
				SELECT assortment.*, COUNT(*) AS count FROM assortment
				LEFT JOIN meta ON (meta.name = 'assortment' AND meta.value = assortment.id)
				WHERE assortment.name = '{{name}}'
					AND assortment.parent_id = {{parent}}
				GROUP BY assortment.id
				ORDER BY assortment.sortkey ASC
				");
			$sql->bindParam("{{parent}}", $parent, "int");	
		}
		$sql->bindParam("{{name}}", $name);
		$sql->execute();
		$i = 0;
		while($row = $sql->fetch()){
			$assortment[$i] = $row;
			if($level < $maxlevel){
				$assortment[$i]['_children'] = self::load($name, $row['id'], $level+1, $maxlevel);
				$assortment[$i]['count'] += $assortment[$i]['_children']['cnt'];
			}
			$i++;
		}
		return $assortment;
	}
	
	// TODO other handling functions to create and save assortments
	
}