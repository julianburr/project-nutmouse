<?php

class View {
	
	private $controller = null;
	private $model = null;
	private $content = null;
	private $elements = null;
	
	private $template = "default";
	private $templates_root_dir = "../templates";
	
	private $output = null;
	
	public $__ = array();
	
	public function __construct(Controller $controller=null, Model $model=null){
		if(isset($controller)){
			// Set contoller if given
			$this->controller = $controller;
		}
		if(isset($model)){
			// If model is given, load contents from it
			$this->model = $model;
			$this->content = $this->model->getContent();
			$this->elements = $this->content->getElements();
			$this->assign("title", "Testtitle");
			if($this->content->getTemplate() > ""){
				// Set template if given
				$this->template = $this->content->getTemplate();
			}
		}
	}
	
	public function createOutput(){
		// Create output by parsing template
		if(!$this->template){
			throw new Exception("Cannot create output! No template set!");
		}
		$this->parseTemplate();
		return $this->output;
	}
	
	public function setTemplate($template){
		$this->template = $template;
	}
	
	public function assign($name, $value){
		// Assign variable to templates varscope
		$this->__[$name] = $value;
	}
	
	public function assignArray(array $array){
		// Assign multiple varibales in form of an array
		$this->__ = array_merge($this->__, $array);
	}
	
	public function parseTemplate(){
		// Parse template output and save result string
		if(is_null($this->template)){
			throw new Exception("Cannot parse template! No template set!");
		}
		$filepath = $this->getTemplateFilePath();
		$this->output = "";
		ob_start();
			include($filepath);
			$this->output .= ob_get_contents();
		ob_end_clean();
		// Parse template output for simplified inside codes and tags
		$this->output = Template::parse($this->output, $this->elements, $this->__);
	}
	
	public function getTemplateFilePath(){
		// Get valid filepath from set template
		if(!$this->template){
			throw new Exception("Cannot get path! No template set!");
		}
		// ...here comes the theme config
		$templatefile = __DIR__ . "/" . $this->templates_root_dir . "/[THEME]/" . $this->template . ".tpl";
		if(!is_file($templatefile)){
			// Default theme name should be saved in var as well...
			$templatefile = __DIR__ . "/" . $this->templates_root_dir . "/default/" . $this->template . ".tpl";
			if(!is_file($templatefile)){
				throw new Exception("Template file '{$this->template}' not found!");
				return false;
			}
		}
		return $templatefile;
	}
	
	public function setHttpHeader($header){
		// Simply set HTTP header
		header($header);
	}
	
	public function setElements(array $elements){
		$this->elements = $elements;
	}
	
}	