<?php

class Log {
	
	public static function write($type, $message){
		// Write new log message into database
		$sql = new SqlManager();
		$log = array(
			'type' => $type,
			'time' => DateManager::now(),
			'trace' => serialize(debug_backtrace()),
			'session' => null,
			'user' => null,
			'message' => $message
		);
	}
	
	// Synonyms for the different log types
	
	public static function info($message){
		self::write("INF", $message);
	}
	
	public static function debug($message){
		self::write("DBG", $message);
	}
	
	public static function error($message){
		self::write("ERR", $message);
	}
	
	public static function warning($message){
		self::write("WRN", $message);
	}
	
}