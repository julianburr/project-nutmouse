<?php

class Model {
	
	private $controller = null;
	private $content = null;
	
	public function __construct(Controller $controller=null){
		// Init model content object
		$this->content = new Content();
		if(isset($controller)){
			// If controller is given, get requested URL and load content
			$this->controller = $controller;
			$this->content->getFromUrl($this->controller->getUrl());
		}
	}
	
	public function getContent(){
		// Return content object
		return $this->content;
	}
	
	public function getContentArray(){
		// Return array with all content infos
		return array(
			"id" => $this->getContentID(),
			"data" => $this->getContentData(),
			"elements" => $this->getContentElements()
		);
	}
	
	public function getContentID(){
		// Return content id
		return $this->content->getID();
	}
	
	public function getContentData(){
		// Return content data array
		return $this->content->getData();
	}
	
	public function getContentElements(){
		// Return array with content elements
		return $this->content->getElements();
	}
	
}