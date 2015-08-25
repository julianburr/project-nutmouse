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
		$newinput = array(
			"type" => 'form/input/' . $type,
			"position" => $area,
			"name" => $name,
			"value" => $value,
			"parameters" => array_merge($options, array(
				"type" => $type,
				"position" => $area,
				"name" => $name,
				"value" => $value
			))
		);
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
		$this->addInput("button", "", $text, $options);
	}
	
	public function addHidden($name, $value){
		$this->addInput("hidden", $name, $value);
	}
	
	public function addHiddenAction($name){
		$this->addInput("hidden", "do[]", $name);
	}
	
	public function setName($name){
		$this->name = $name;
	}
	
	public function setAction($action){
		$this->action = $action;
	}
	
	public function setMethod($method){
		$this->method = $method;
	}
	
	public function setOptions(array $options){
		$this->options = $options;
	}
	
	public function addOption($name, $value){
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
	
}