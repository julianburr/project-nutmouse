<?php

class Locale {
	
	public static function load($lang=null){
		if(!$lang){
			$lang = self::getLanguage();
		}
		 
		$cachekey = "locale:" . $lang;
		$locale = Cache::load($cachekey);
	
		if(!is_array($locale)){
			// No cache available -> load from database
			$sql = new SqlManager();
			$sql->setQuery("SELECT * FROM locale WHERE language = {{lang}}");
			$sql->bindParam('{{lang}}', $lang, "int");
			$result = $sql->execute();
			$locale = array();
			while($res = mysql_fetch_array($result)){
				$locale[$res['key']] = $res['text'];
			}
			
			// Save loaded locales into cache file for later use 
			Cache::save($cachekey,$locale);
		}
		
		return $locale;
	}
	
	public static function get($key, $lang=null, $defaultvalue=null){
		// Get specific locale value by given key
		if(!$lang){
			$lang = self::getLanguage();
		}
		$cachekey = "locale:" . $lang;
		$cache = Cache::loadCache($cachekey);
		
		// If not found, create new locale with default value
		if(empty($cache[$key])){
			self::save($key, $defaultvalue, $lang);
			$cache[$key] = $defaultvalue;
		}
		
		$value = $cache[$key];
		return $value;
	}
	
	public static function save($key, $value, $lang=null){
		// Save specific locale value by given key
		if(!$lang){
			$lang = self::getLanguage();
		}
		
		$sql = new SqlManager();
		
		// Check if locale exists
		$sql->setQuery("
			SELECT key FROM locale 
			WHERE key = '{{key}}' 
			AND language = '{{lang}}'
			LIMIT 1");
		$sql->bindParam('{{key}}', $key);
		$sql->bindParam('{{lang}}', $lang, "int");
		$check = $sql->result();
		
		$loc = array(
			'key' => $sql->escape($key),
			'language' => $sql->escape($lang, "int"),
			'text' => $sql->escape($value),
			'lastchanged' => date("Y-m-d H:i:s", time())
		);

		// Either update database or insert new entry for given locale
		if(!$check['key']){
			$sql->insert("locale", $loc);
		} else {
			$sql->update("locale", $loc);
		}
		
		// Refresh cache to make sure new locale entry will be used
		$cachekey = "locale:" . $lang;
		Cache::clearCache($cachekey);
		self::load($lang);
	}
	
	public static function getLanguage(){
		// TODO
		return 1;
	}
	
}