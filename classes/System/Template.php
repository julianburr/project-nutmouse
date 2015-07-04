<?php

class Template {
	
	private $content = null;
	
	public function __construct($content = null){
		// Set content if given
		$this->setContent($content);
	}
	
	public function setContent($content){
		// Set given content to object variable
		$this->content = $content;
	}
	
	public function getContent(){
		// Return content
		return $this->content;
	}
	
	public function parse(){
		// Parse set content by defined rules
	}
	
}