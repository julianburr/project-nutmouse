<?php

class View {
	
	private $controller = null;
	private $model = null;
	private $content = null;
	
	private $config = null;
	
	private $template = "default";
	private $templates_root_dir = "../templates";
	
	private $output = null;
	
	private $vars = array();
	private $parameters = array();
	private $elements = array();
	
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
			$this->assignVars($this->content->getData());
			if($this->content->getTemplate() > ""){
				// Set template if given
				$this->template = $this->content->getTemplate();
			}
		}
		$this->config = new Config();
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
	
	public function assignVar($name, $value){
		// Assign variable to templates varscope
		$this->vars[$name] = $value;
	}
	
	public function assignVars(array $array){
		// Assign multiple varibales in form of an array
		$this->vars = array_merge($this->vars, $array);
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
		// $this->output = Template::parse($this->output, $this->elements, $this->__, $this->parameters);
	}
	
	public function getTemplateFilePath(){
		// Get valid filepath from set template
		if(!$this->template){
			throw new Exception("Cannot get path! No template set!");
		}
		// ...here comes the theme config
		$templatefile = __DIR__ . "/" . $this->templates_root_dir . "/" . $this->config->get("site.theme") . "/" . $this->template . ".tpl";
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
	
	public function assignParam($name, $value){
		$this->parameters[$name] = $value;
	}
	
	public function assignParams(array $params){
		$this->parameters = array_merge($this->parameters, $params);
	}
	
	public function getVar($name){
		// Get value of requested var
		$parts = split("\.", $name);
		$vars = $this->vars;
		foreach($parts as $part){
			if(!isset($vars[$part])){
				return;
			}
			$vars = $vars[$part];
		}
		return $vars;
	}
	
	public function getParam($name){
		// Same for parameters
		$parts = split("\.", $name);
		$params = $this->parameters;
		foreach($parts as $part){
			if(!isset($params[$part])){
				return;
			}
			$params = $params[$part];
		}
		return $params;
	}
	
	public function getElements($area){
		// Get output of elements of requested area
		// Building output recursive by creating a view instance in this view
		$content = "";
		if(isset($this->elements[$area])){
			foreach($this->elements[$area] as $element){
				$view = new View();
				if(!isset($element['type'])){
					throw new Exception("No template type set!");
				}
				$view->setTemplate('element/' . $element['type']);
				$view->assignVars($this->vars);
				$view->assignVar('element', $element);
				if(is_array($element['parameters'])){
					$view->assignParams($element['parameters']);
				}
				if(isset($element['_children'])){
					$view->setElements($element['_children']);
				}
				$content .= $view->createOutput();
			}
		}
		return $content;
	}
	
	public function printIfSet($var, $output){
		// Return output if var isset (!is_null)
		if(is_array($var)){
			foreach($var as $v){
				if(is_null($v)){
					return;
				}
			}
		} elseif(is_null($var)){
			return;
		}
		return $output;
	}
		
	
}	