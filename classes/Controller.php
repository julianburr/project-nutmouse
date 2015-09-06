<?php

class Controller {
	
	private $url = null;
	private $server = null;
	
	private $content_id = null;
	private $content_parents = array();
	
	private $get = array();
	private $post = array();
	private $request = array();
	
	private $redirect_post = array();
	
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
		$urlparts = split("\?", $url);
		$this->url = $urlparts[0];
		$_GET['url'] = $urlparts[0];
		$parameters = array();
		if(isset($urlparts[1])){
			$parameters = split("&", $urlparts[1]);
		}
		$i = 0;
		$name = null;
		foreach($parameters as $parameter){
			$nameval = split("=", $parameter);
			if(!isset($nameval[1])){
				$nameval[1] = true;
			}
			if(substr($nameval[0], -2) == "[]"){
				$nameval[0] = substr($nameval[0], 0, -2);
				$_GET[$nameval[0]][] = $nameval[1];
			} else {
				$_GET[$nameval[0]] = $nameval[1];
			}
		}
		$content = new Content(null, $this->url);
		$this->content_id = $content->getID();
		$this->content_parents = $content->getParentsArray();
	}
	
	public function analyzeRequest($forward=true){
		// Save request variables in controller
		$this->get = $_GET;
		$this->post = array_merge($_POST, $this->getRedirectPost());
		$this->request = array_merge($this->post, $this->get);
		$this->response = array();
		$this->server = $_SERVER['SERVER_NAME'];
		// ... here could come some checks and request var manipulation
		// Run requested actions
		$this->runActions();
		if($forward){
			// Analyze requested url
			$this->analyzeUrl();
		}
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
			if(!$rewrite->transportQueryParameter()){
				$_GET = array();
				$_POST = array();	
			}
			if($rewrite->isRedirect()){
				$this->redirectToUrl($rewrite->getTargetUrl(), $_POST);
			}
			$this->setUrl($rewrite->getTargetUrl());
			$this->analyzeRequest(!$rewrite->isLastForward());
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
			// Should parameters be transported through the redirects / rewrites
			if(!$access->transportQueryParameter()){
				$_GET = array();
				$_POST = array();	
			}
			// Show 403 error page or redirect if neccessary
			$newcontent = new Content($access->getDeniedContentID());
			if($access->isRedirect() && $newcontent->getID() != $this->content_id){
				// Redirect, but keep requested URL as origin parameter
				// TODO: keep origin request parameters as well!
				$this->redirectToUrl("/" . $newcontent->getUrl() . "?origin=" . $this->url, $_POST);
			}
			$this->setUrl($newcontent->getUrl());
			$this->analyzeRequest(!$access->isLastForward());
		}
		// TODO: HookPoints to manipulate this behaviour
	}
	
	public function redirectToUrl($url, array $post=array()){
		// Redirect browser to given url
		// TODO: external URLs + status code handling
		$this->setRedirectPost($post);
		header('Location:' . $url);
	}
	
	public function getUrl(){
		// Get requested URL
		return $this->url;
	}
	
	public function getServer(){
		// Get requested server
		return $this->server();
	}
	
	public function getSession(){
		// Get current session instance
		return $this->session;
	}
	
	public function getRequest($key=null){
		// Get request array or specific request variable value
		if(is_null($key)){
			return $this->request;
		} elseif(isset($this->request[$key])){
			return $this->request[$key];
		}
		return null;
	}
	
	public function getGet($key=null){
		// Get get parameter array or specific request variable value
		if(is_null($key)){
			return $this->get;
		} elseif(isset($this->get[$key])){
			return $this->get[$key];
		}
		return null;
	}
	
	public function getPost($key=null){
		// Get post parameter array or specific request variable value
		if(is_null($key)){
			return $this->post;
		} elseif(isset($this->post[$key])){
			return $this->post[$key];
		}
		return null;
	}
	
	public function getResponse(){
		// Get response array
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
					// Run action + catch returned response
					$this->response[$do] = $action->run();
					if(isset($this->response[$do][0]['redirect']['url'])){
						// If redirect requested by action, do so
						if(!isset($this->response[$do][0]['redirect']['post']) || !is_array($this->response[$do][0]['redirect']['post'])){
							$this->response[$do][0]['redirect']['post'] = array();
						}
						$this->redirectToUrl($this->response[$do][0]['redirect']['url'], $this->response[$do][0]['redirect']['post']);
					}
					if(isset($this->request['formname'])){
						// If action was triggert by a from, save response in form array as well
						// Just for easier message handling
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
		// All actions must be registered here preferably in form of sub class method calls
		$this->session->registerActions();
	}
	
	public function isCurrentContent(Content $content){
		// Check if content is currently shown content
		$this->isCurrentContentID($content->getID());
	}
	
	public function isCurrentContentID($id){
		// Check if content id is equal to id of currently shown content
		if($id == $this->content_id){
			return true;
		}
		return false;
	}
	
	public function isCurrentContentParent($id){
		// Check if id is parent (of any level) of currently shown content
		if(in_array($id, $this->content_parents)){
			return true;
		}
		return false;
	}
	
	public function setRedirectPost(array $post){
		$_SESSION['redirect']['post'] = $post;
	}
	
	public function getRedirectPost(){
		if(isset($_SESSION['redirect']['post']) && is_array($_SESSION['redirect']['post'])){
			$this->redirect_post = $_SESSION['redirect']['post'];
			unset($_SESSION['redirect']['post']);
		}
		return $this->redirect_post;
	}
	
}