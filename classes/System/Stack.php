<?php

class Stack {
	
	private static $stack = array();
	
	public static function add($id, $data){
		// Add data to specified stack
		if(!isset(self::$stack[$id]) || !is_array(self::$stack[$id])){
			self::$stack[$id] = array();
		}
		$index = count(self::$stack[$id]);
		self::$stack[$id][$index] = $data;
		return $index;
	}
	
	public static function remove($id, $index){
		// Remove entry from stack identified by id and index
		if(isset(self::$stack[$id][$index])){
			unset(self::$stack[$id][$index]);
		}
	}
	
	public static function trash($id){
		// Remove all stacked data from specified stack
		self::$stack[$id] = array();
	}
	
	public static function get($id){
		// Return array of stacked data
		if(isset(self::$stack[$id])){
			return self::$stack[$id];
		}
		return array();
	}
	
}