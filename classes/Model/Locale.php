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
			$sql->execute();
			$locale = array();
			while($res = $sql->fetch()){
				$locale[$res['code']] = $res['text'];
			}
			// Save loaded locales into cache file for later use 
			Cache::save($cachekey,$locale);
		}
		return $locale;
	}
	
	public static function get($code, $lang=null, $defaultvalue=null){
		// Get specific locale value by given key
		if(!$lang){
			$lang = self::getLanguage();
		}
		$cachekey = "locale:" . $lang;
		$cache = Cache::load($cachekey);
		// If not found, create new locale with default value
		if(!isset($cache[$code])){
			self::save($code, $defaultvalue, $lang);
			$cache[$code] = $defaultvalue;
		}
		return $cache[$code];
	}
	
	public static function save($code, $value, $lang=null){
		// Save specific locale value by given key
		if(!$lang){
			$lang = self::getLanguage();
		}		
		// Check if locale already exists
		$sql = new SqlManager();
		$sql->setQuery("
			SELECT code FROM locale 
			WHERE code = '{{code}}' 
			AND language = {{lang}}
			LIMIT 1");
		$sql->bindParam('{{code}}', $code);
		$sql->bindParam('{{lang}}', $lang, "int");
		$check = $sql->result();
		$loc = array(
			'code' => $sql->escape($code),
			'language' => $sql->escape($lang, "int"),
			'text' => $sql->escape($value),
			'lastchanged' => date("Y-m-d H:i:s", time())
		);
		// Either update database or insert new entry for given locale
		if(!$check['code']){
			$sql->insert("locale", $loc);
		} else {
			$sql->update("locale", $loc);
		}
		// Refresh cache to make sure new locale entry will be used
		$cachekey = "locale:" . $lang;
		Cache::clear($cachekey);
		self::load($lang);
	}
	
	public static function getLanguage(){
		// TODO
		return 1;
	}
	
}