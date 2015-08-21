<?php

class HookPoint {
	
	private static $register = array();
	
	public static function register($hook, $function){
		// Register function for specific hookpoint
		if(!isset(self::$register[$hook]) || !is_array(self::$register[$hook])){
			self::$register[$hook] = array();
		}
		self::$register[$hook][] = $function;
	}
	
	public static function call($hook, $args=null){
		// Run all registered functions of called hookpoint
		if(isset(self::$register[$hook]) && is_array(self::$register[$hook])){
			foreach(self::$register[$hook] as $function){
				if(is_array($function)){
					if(is_string($function[0]) && class_exists($function[0])){
						$instance = new $function[0];
					} else {
						$instance = $function[0];
					}
					if(method_exists($instance, $function[1])){
						$args = $instance->$function[1]($args);
					} else {
						throw new Exception("Hooked method '{$function[1]}' of class '{$function[0]}' for hookpoint '{$hook}' not found!");
					}
				} else {
					if(function_exists($function)){
						$args = $function($args);
					} else {
						throw new Exception("Hooked function '{$function}' for hookpoint '{$hook}' not found!");
					}
				}
			}
		}
		// Return possibly modified arguments
		return $args;
	}
	
}