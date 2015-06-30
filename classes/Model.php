<?php

class Model {
	
	private $controller = null;
	
	private $content = array(
		"id" => null,
		"data" => array(),
		"meta" => array(),
		"elements" => array()
	);
	
	public function __construct(Controller $controller=null){
		if(isset($controller)){
			// If controller is given, get requested URL and load content
			$this->controller = $controller;
			$this->getContentFromUrl($this->controller->getUrl());
		}
	}
	
	public function getContentFromUrl($url){
		// Loads content with given URL from database
		$sql = new SqlManager();
		// ...here server and language (both comeing from controller if available) should be included!
		$sql->setQuery("SELECT * FROM content WHERE url='{{url}}'");
		$sql->bindParam("{{url}}", $url);
		$content = $sql->result();
		if(!isset($page['id'])){
			throw new Exception("No content for URL '{$url}' found!");
			return false;
		}
		$this->content['id'] = $content['id'];
		$this->content['data'] = $content;
		// Load other content data as well
		$this->loadMeta();
		$this->loadElements();
		return true;
	}
	
	public function loadMeta(){
		// Loads meta information to current content from database
		if(!isset($this->content['id'])){
			throw new Exception("Cannot load meta! No content found!");
			return;
		}
		$sql = new SqlManager();
		$sql->setQuery("SELECT * FROM meta WHERE object_table = 'content' AND object_id = {{id}}");
		$sql->bindParam("{{id}}", $this->content['id'], "int");
		$sql->execute();
		while($meta = $sql->fetch()){
			if(!isset($this->content['meta'][$meta['name']])){
				$this->content['meta'][$meta['name']] = array();
			}
			$this->content['meta'][$meta['name']][] = $meta['value'];
		}
	}
	
	public function loadElements(){
		// Loads elements into array
		$this->content['elements'] = $this->getElements();
	}
	
	private function getElements($parent=null){
		// Returns elements loaded from the database for given parent id
		if(!isset($this->content['id'])){
			throw new Exception("Cannot get content elements! No content found!");
			return;
		}
		$elements = array();
		$sql = new SqlManager();
		if($parent > 0){
			$sql->setQuery("SELECT * FROM content_element WHERE content_id = {{id}} AND parent_id = {{parent}} ORDER BY sortkey");
			$sql->bindParam("{{parent}}", $parent, "int");
		} else {
			$sql->setQuery("SELECT * FROM content_element WHERE content_id = {{id}} AND parent_id = NULL ORDER BY sortkey");
		}
		$sql->bindParam("{{id}}", $this->content['id'], "int");
		$sql->execute();
		while($element = $sql->fetch()){
			if(!isset($elements[$element['element_position']])){
				$elements[$element['element_position']] = array();
			}
			$index = count($elements[$element['element_position']]);
			$elements[$element['element_position']][$index] = $element;
			// Created parent-children array tree
			$elements[$element['element_position']][$index]['_children'] = $this->getElements($element['id']);				
		}
		return $elements;
	}
	
	public function getContent(){
		return $this->content;
	}
	
	public function getContentID(){
		return $this->content['id'];
	}
	
	public function getContentData(){
		return $this->content['data'];
	}
	
	public function getContentMeta(){
		return $this->content['meta'];
	}
	
	public function getContentElements(){
		return $this->content['elements'];
	}
	
}