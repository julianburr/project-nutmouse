<?php

class Content {
	
	private $id;
	
	private $data = array();
	private $elements = array();
	
	public function loadFromUrl($url){
		// Loads content with given URL from database
		$sql = new SqlManager();
		// ...here server and language (both coming from controller if available) should be included!
		$sql->setQuery("SELECT * FROM content WHERE url='{{url}}'");
		$sql->bindParam("{{url}}", $url);
		$content = $sql->result();
		if(!isset($page['id'])){
			throw new Exception("No content for URL '{$url}' found!");
			return false;
		}
		$this->id = $content['id'];
		$this->data = $content;
		// Load other content data as well
		$this->elements = $this->loadElements();
		return true;
	}
	
	public function loadElements($parent=null){
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
			$elements[$element['element_position']][$index]['_children'] = $this->loadElements($element['id']);				
		}
		return $elements;
	}
	
	public function getID(){
		// Return id
		return $this->id;
	}
	
	public function getData(){
		// Return data array
		return $this->data;
	}
	
	public function getElements(){
		// Return elements array
		return $this->elements;
	}
	
	public function getTemplate(){
		// Return template set in content data if set
		if(isset($this->data['template'])){
			return $this->data['template'];
		}
		return null;
	}
	
}