<?php

class Controller {
	
	private $url = null;
	private $server = null;
	
	private $get = array();
	private $post = array();
	private $request = array();
	
	private $response = array();
	
	private $session = null;
	
	public function __construct($url=null){
		// Create session object
		$this->session = new Session();
		if(isset($url)){
			// If URL is given, save and analyize request
			$this->url = $url;
			$this->analyzeRequest();
		}
	}
	
	public function setUrl($url){
		// Set URL to given string
		$this->url = $url;
	}
	
	public function analyzeRequest(){
		// Save request variables in controller
		$this->get = $_GET;
		$this->post = $_POST;
		$this->request = array_merge($this->post, $this->get);
		$this->server = $_SERVER['SERVER_NAME'];
		// ... here could come some checks and request var manipulation
		$this->analyzeUrl();
	}
	
	public function analyzeUrl(){
		if(is_null($this->url)){
			throw new Exception("No request URL set!");
			return;
		}
		// ... here could come redirects and rewrites including manipulation of request vars
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
	
}