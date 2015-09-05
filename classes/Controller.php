<?php

class Controller {
	
	private $url = null;
	private $server = null;
	
	private $content_id = null;
	private $content_parents = array();
	
	private $get = array();
	private $post = array();
	private $request = array();
	
	private $response = array();
	
	private $session = null;
	
	private $plugin_root = "../plugins";
	
	public function __construct($url=null){
		// Create session object
		$this->session = new Session();
		// Register core actions
		$this->registerActions();
		// Init plugins
		$this->initPlugins();
		// Init Session
		$this->session->init();
		// If URL is given, save and analyize request
		if(isset($url)){
			$this->setUrl($url);
			$this->analyzeRequest();
		}
	}
	
	public function setUrl($url){
		// Set URL to given string
		$this->url = $url;
		$content = new Content();
		$content->loadFromUrl($this->url);
		$this->content_id = $content->getID();
		$this->content_parents = $content->getParentsArray();
	}
	
	public function analyzeRequest(){
		// Save request variables in controller
		$this->get = $_GET;
		$this->post = $_POST;
		$this->request = array_merge($this->post, $this->get);
		$this->response = array();
		$this->server = $_SERVER['SERVER_NAME'];
		// ... here could come some checks and request var manipulation
		// Run requested actions
		$this->runActions();
		// Analyze requested url
		$this->analyzeUrl();
	}
	
	public function analyzeUrl(){
		// Check if URL is set in general
		if(is_null($this->url)){
			throw new Exception("No request URL set!");
			return;
		}
		// Check for rewrtite rules
		$rewrite = new Rewrite($this->url);
		$rewrite->applyRules();
		if($rewrite->getTargetUrl() != $this->url){
			if($rewrite->isRedirect()){
				$this->redirectToUrl($rewrite->getTargetUrl());
			}
			$this->setUrl($rewrite->getTargetUrl());
		}
		// Check access rights to requested content
		$access = new AccessOfficer("content", $this->content_id, $this->session->getUser(), $this->content_parents);
		if(!$access->check()){
			// Access denied
			if(!$access->getDeniedContentID()){
				// No content to be shown as 403 page set
				// TODO set up default 403 (and 404) templates
				throw new Exception("Access denied and no denied content defined!");
				return;
			}
			// Show 403 error page or redirect if neccessary
			$newcontent = new Content($access->getDeniedContentID());
			if($access->isRedirect() && $newcontent->getID() != $this->content_id){
				// Redirect, but keep requested URL as origin parameter
				// TODO: keep origin request parameters as well!
				$this->redirectToUrl("/" . $newcontent->getUrl() . "?origin=" . $this->url);
			}
			$this->setUrl($newcontent->getUrl());
		}
		// ... here could come redirects and rewrites including manipulation of request vars
	}
	
	public function redirectToUrl($url){
		header('Location:' . $url);
	}
	
	public function getUrl(){
		return $this->url;
	}
	
	public function getServer(){
		return $this->server();
	}
	
	public function getSession(){
		return $this->session;
	}
	
	public function getRequest($key=null){
		if(is_null($key)){
			return $this->request;
		} elseif(isset($this->request[$key])){
			return $this->request[$key];
		}
		return null;
	}
	
	public function getResponse(){
		return $this->response;
	}
	
	public function runActions(){
		// Run requested actions and save responses in array
		if(isset($this->request['formname'])){
			$this->response['_form'][$this->request['formname']] = array();
		}
		if(isset($this->request['do'])){
			$action = new Action();
			if(!is_array($this->request['do'])){
				$action->init($this->request['do']);
				$this->response[$this->request['do']] = $action->run();
				if(isset($this->request['formname'])){
					$this->response['_form'][$this->request['formname']] = $this->response[$do];
				}
			} else {
				foreach($this->request['do'] as $do){
					$action->init($do, $this->request);
					$this->response[$do] = $action->run();
					if(isset($this->request['formname'])){
						$this->response['_form'][$this->request['formname']] = array_merge($this->response['_form'][$this->request['formname']], $this->response[$do]);
					}
				}
			}
		}
	}
	
	public function initPlugins(){
		// Init installed and actived plugins
		$sql = new SqlManager();
		$sql->setQuery("SELECT * FROM plugin WHERE active = 1");
		$sql->execute();
		while($plugin = $sql->fetch()){
			// Check plugin core file
			$path = __DIR__ . "/" . $this->plugin_root . "/" . strtolower($plugin['name']) . "/" . $plugin['name'] . ".php";
			if(is_file($path)){
				// ... and include it
				include_once($path);
				// Then check for class and init method and call if possible
				if(class_exists($plugin['name'])){
					$instance = new $plugin['name'];
					if(method_exists($instance, "init")){
						$instance->init();
					} else {
						throw new Exception("Init method in plugin '{$plugin['name']}' core file not found!");
					}
				} else {
					throw new Exception("Class for plugin '{$plugin['name']} not found!");
				}
			} else {
				throw new Exception("Core file for plugin '{$plugin['name']}' not found!");
			}
		}
	}
	
	private function registerActions(){
		$this->session->registerActions();
	}
	
	public function isCurrentContent(Content $content){
		$this->isCurrentContentID($content->getID());
	}
	
	public function isCurrentContentID($id){
		if($id == $this->content_id){
			return true;
		}
		return false;
	}
	
	public function isCurrentContentParent($id){
		if(in_array($id, $this->content_parents)){
			return true;
		}
		return false;
	}
	
}