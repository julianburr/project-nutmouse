<?php

class Action {
	
	private $name = null;
	private $args = array();
	
	public function __construct($name=null, $args=array()){
		// Init action name if given
		if(!is_null($name)) $this->init($name, $args);
	}
	
	public function init($name, $args=array()){
		// Init action name and args from given
		$this->name = $name;
		$this->args = $args;
	}
	
	public static function register($name, $function){
		// Save new action in register
		Stack::add("action:" . $name, $function);
	}
	
	public function run(){
		// Run action of current instance
		$functions = Stack::get("action:" . $this->name);
		if(is_array($functions)){
			$response = array();
			foreach($functions as $function){
				if(is_array($function)){
					$instance = null;
					if(is_object($function[0])){
						$instance = $function[0];
					} elseif(is_string($function[0]) && class_exists($function[0])){
						$instance = new $function[0];
					}
					if(is_object($instance)){
						if(method_exists($instance, $function[1])){
							$response[] = $instance->$function[1]($this->args);
						} else {
							throw new Exception("Method '{$function[1]}' of object '{$function[0]}' for action '{$this->name}' not found!");
						}
					} else {
						throw new Exception("Object for action '{$this->name}' not found!");
					}
				} else {
					if(function_exists($function)){
						$response[] = $function($this->args);	
					} else {
						throw new Exception("Function '{$function}' for action '{$this->name}' not found!");
					}
				}
			}
		} else {
			throw new Exception("Action '{$this->name}' not found in register!");
		}
		return $response;
	}
	
}