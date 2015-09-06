<?php

class Locale {
	
	private static $locales = array();
	
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
				self::$locales[$lang][$res['code']] = $res['text'];
			}
			// Save loaded locales into cache file for later use 
			Cache::save($cachekey,$locale);
		}
	}
	
	public static function get($code, $lang=null, $defaultvalue=null){
		// Get specific locale value by given key
		if(!$lang){
			$lang = self::getLanguage();
		}
		self::load($lang);
		// If not found, create new locale with default value
		if(!isset(self::$locales[$lang][$code])){
			if(!is_null($defaultvalue)){
				self::save($code, $defaultvalue, $lang);
			}
			self::$locales[$lang][$code] = $defaultvalue;
		}
		return self::$locales[$lang][$code];
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
			'lastchanged' => DateManager::now()
		);
		// Either update database or insert new entry for given locale
		if(!$check['code']){
			$loc['created'] = DateManager::now();
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