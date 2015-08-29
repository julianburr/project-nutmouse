<?php

class FormManager {
	
	private $id = null;
	
	private $action = null;
	private $name = null;
	private $method = null;
	
	private $options = array();
	
	private $inputs = array();
	
	public function __construct($id=null){
		if(!isset($id)){
			$this->load($id);
		}
	}
	
	public function addInput($type, $name, $value, array $options=array(), $pos=null, $area="inputs"){
		// Add input to form instance
		$newinput = array(
			"type" => 'form/input/' . $type,
			"position" => $area,
			"name" => $name,
			"value" => self::escape($value),
			"parameters" => array_merge($options, array(
				"type" => $type,
				"position" => $area,
				"name" => $name,
				"value" => $value
			))
		);
		// Set it to requested position if given
		if(!isset($pos)){
			$this->inputs[$area][] = $newinput;
		} elseif(is_array($this->inputs[$area][$pos])){
			$tmp = array();
			foreach($this->inputs[$area] as $key => $value){
				if($key >= $pos){
					$key++;
				}
				$tmp[$key] = $value;
			}
			$tmp[$pos] = $newinput;
			$this->inputs[$area] = array();
			$this->inputs[$area] = $tmp;
		} else {
			$this->inputs[$area][$pos] = $newinput;
		}
	}
	
	public function addButton($text, array $options=array()){
		// Synonym for addInput("button",...)
		$this->addInput("button", "", $text, $options);
	}
	
	public function addHidden($name, $value){
		// Synonym for addInput("hidden",...)
		$this->addInput("hidden", $name, $value);
	}
	
	public function addHiddenAction($name){
		// Synonym for addInput("hidden","do[]",...)
		$this->addInput("hidden", "do[]", $name);
	}
	
	public function setName($name){
		// Set form name
		$this->name = $name;
	}
	
	public function setAction($action){
		// Set form action
		$this->action = $action;
	}
	
	public function setMethod($method){
		// Set form method
		// TODO: file uploads for method post
		$this->method = $method;
	}
	
	public function setOptions(array $options){
		// Set addition options for form tag
		$this->options = $options;
	}
	
	public function addOption($name, $value){
		// Add an option
		$this->options[$name] = $value;
	}
	
	public function save(){
	}
	
	public function load(){
	}
	
	public function createOutput(){
		// Parse all set inputs and return output string
		$form = new View();
		$form->setTemplate("element/form/tag");
		$form->setElements($this->inputs);
		return $form->createOutput();
	}
	
	public function send($id){
	}
	
	public static function createInput($type, $name, $value, array $options=array()){
		// Print single input directly instead of gathering them in the instance first
		$params = array_merge($options, array(
			"type" => $type,
			"name" => $name,
			"value" => self::escape($value)
			));
		$input = new View();
		$input->setTemplate("element/form/input/" . $type);
		$input->assignParams($params);
		return $input->createOutput();
	}
	
	public static function escape($value){
		// Escape string to be used as input value of any kind
		return htmlspecialchars($value);
	}
	
}