<?php

class HookPoint {
	
	public static function register($hook, $function){
		// Register function for specific hookpoint
		Stack::add("hook:" . $hook, $function);
	}
	
	public static function call($hook, $args=null){
		// Run all registered functions of called hookpoint
		$functions = Stack::get("hook:" . $hook);
		foreach($functions as $function){
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
		// Return possibly modified arguments
		return $args;
	}
	
}