<?php

class View {
	
	private $controller = null;
	private $model = null;
	private $content = null;
	
	private $config = null;
	
	private $template = "default";
	private $theme = null;
	private $templates_root_dir = "../templates";
	
	private $output = null;
	
	private $vars = array();
	private $parameters = array();
	private $defined_parameters = array();
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
	
	public function setTemplate($template, $theme=null){
		$this->template = $template;
		$this->theme = $theme;
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
		return $this->getThemeFilePath($this->template . ".tpl", true);
	}
	
	public function getThemeFilePath($path, $abs=false){
		$file = $this->getThemeFile($path);
		if($abs){
			return $file->getPath();
		}
		return $file->getUrl();
	}
	
	public function getThemeFile($path){
		// Get current theme from config
		$theme = $this->config->get("site.theme");
		if(!is_null($this->theme)){
			// or the forced theme if set
			$theme = $this->theme;
		}
		$themefile = __DIR__ . "/" . $this->templates_root_dir . "/" . $theme . "/" . $path;
		$file = new FileManager($themefile);
		if(!$file->isFile()){
			$file->setPath(__DIR__ . "/" . $this->templates_root_dir . "/default/" . $path);
			if(!$file->isFile()){
				throw new Exception("File '{$path}' not found!");
				return false;
			}
		}
		return $file;
	}
	
	public function getThemeFileUrl($path){
		$this->getThemeFilePath($path);
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
	
	public function getVars(){
		// Returns all variables assigned to view instance
		return $this->vars;
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
				$view = new View($this->controller, $this->model);
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
	
	public function countElements($area){
		// Get number of elements assigned to specific area
		if(!isset($this->elements[$area]) || !is_array($this->elements[$area])){
			return 0;
		}
		return count($this->elements[$area]);	
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
			
	public function getUrl($id){
		// Get canonical URL from ID
		$content = new Content();
		$content->loadFromID($id, false, false);
		if($content->getID() != $id){
			return "#";
		}
		$rootdir = str_replace(realpath(dirname(__DIR__ . "/../index.php")), "", realpath($_SERVER['DOCUMENT_ROOT']));
		return "/" . $rootdir . $content->getUrl();
	}
	
	public function getContent(){
		// Return content object
		return $this->content;
	}
	
	public function getHomeUrl(){
		// TODO
		return "/";
	}
	
	public function getTitle($id){
		// Get page title from ID
		$content = new Content();
		$content->loadFromID($id, true, false);
		$data = $content->getData();
		if(!isset($data['meta']['title'][0])){
			$title = "na";
		} else {
			$title = $data['meta']['title'][0];
		}
		return $title;
	}
	
	public function getUrlFromURL($url){
		// Get canonical URL from URL
		$content = new Content();
		$content->loadFromUrl($url, false, false);
		if($content->getUrl() != $url){
			return "#";
		}
		$rootdir = str_replace(dirname(__DIR__ . "/../../nutmouse.php"), "", $_SERVER['DOCUMENT_ROOT']);
		return "/" . $rootdir . $content->getUrl();
	}
	
	public function getTitleFromUrl($url){
		// Get page title from URL
		$content = new Content();
		$content->loadFromUrl($url, false, true);
		$data = $content->getData();
		if(!isset($data['meta']['title'])){
			$title = "na";
		} else {
			$title = $data['meta']['title'];
		}
		return $title;
	}
	
	public function includeTemplate($path, $params=array(), $elements=array(), $theme=null){
		// Include template with parameter options and return parsed output
		$view = new View($this->controller, $this->model);
		$view->setTemplate($path, $theme);
		$view->assignVars($this->vars);
		$view->assignParams($params);
		$view->setElements($elements);
		return $view->createOutput();
	}
	
	public function extendTemplate($path, $theme=null){
		// Include template with same vars, params, etc.
		return $this->includeTemplate($path, $this->parameters, $this->elements, $theme);
	}
	
	public function getPageTitle(){
		// Set page and site title together
		if(is_null($this->content)){
			//return;
		}
		$title = $this->getVar("meta.title.0");
		$title .= $this->printIfSet(array(Locale::get("Frontend.Server" . $this->content->getServerID() . ".Title"), $this->getVar("meta.title.0")), " | ");
		$title .= Locale::get("Frontend.Server" . $this->content->getServerID() . ".Title"); 
		return $title;
	}
	
	public function defineParam($name, $type, $default=null){
		// Define params for this template (e.g. for automated backend masks)
		$this->defined_parameters[$name] = array("name" => $name, "type" => $type, "default" => $default);
		if(!is_null($default) && isset($this->parameters[$name])){
			// Set default as fallback
			$this->parameters[$name] = $default;
		}
	}
	
	public function getScripts($type, $env="frontend"){
		// Get stacked scripts of requested type
		$scripts = Stack::get("scripts:" . $type);
        $included = array();
		$output = "";
        foreach($scripts as $script){
        	if(( isset($script['exclude']) && isset($script['exclude']['frontend'][$env]) && $script['exclude'][$env] ) || 
                !isset($script['filepath']) || ( isset($script['id']) && in_array($script['id'], $included)) ){
            	continue;
            }
			// If set, save id as included to prevent multiple inclusion of the same file
			if(isset($script['id'])){
				$included[] = $script['id'];
			}
			switch($type){
            	case "js":
                	$output .= "/t<script src='" . $script['filepath'] . "'></script>/n";
                    break;
                case "css":
                	$output .= "/t<link rel='stylesheet' href='" . $script['filepath'] . "'>\n";
                 	break;
            }
        }
		return $output;
	}
	
	public function getContentDescription($maxlength=100){
		// Get meta description of content
		$desc = $this->getVar('meta.description');
		if(is_null($desc)){
			// Build desc from the scretch
			// TODO
		}
		return $desc;
	}
	
}