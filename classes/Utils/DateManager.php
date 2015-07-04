<?php

class DateManager {
	
	private static $fmt = "Y-d-m H:i:s";
	
	public static function now($fmt=null){
		if(!$fmt){
			$fmt = self::$fmt;
		}
		return date($fmt, time());
	}
	
}