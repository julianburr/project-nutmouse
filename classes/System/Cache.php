<?php

class Cache {

	public static $cache_root = '/../../_cache/';
	
	public static function load($key, $ttl="no_ttl"){
		if($key != "config"){
			$config = new Config();
			if($config->get("cache.active") != 1){
				// Cache function deactivated
				return false;
			}
		}
		// Create file name as hash from given key
		$filename = md5($key);
		$path = __DIR__ . self::$cache_root . $filename;		
		if(is_file($path)){
			if($ttl == "no_ttl" || time() < (filemtime($path) + (int)$ttl)){
				// Cache file found and valid
				$file = new FileManager($path);
				$cache = unserialize($file->read());
				return $cache;
			}
			// Cache file outdated -> delete cache file
			$file = new FileManager($path);
			$file->delete();
			return false;
		}
		// no cache file found!
		return false;
	}
	
	public static function save($key, $data){
		$config = new Config();
		if($config->get("cache.active") != 1){
			// Cache function deactivated
			return false;
		}
		// Create cache file and save given data serialized
		$filename = md5($key);
		$data = serialize($data);
		$path = __DIR__ . self::$cache_root . $filename;
		$file = new FileManager($path);
		$file->setContent($data);
		$file->save();
	}
	
	public static function clear($key){
		// Clear cache by deleting requested file
		$filename = md5($key);
		$path = __DIR__ . self::$cache_root . $filename;
		$file = new FileManager($path);
		$file->delete();
	}
	
}