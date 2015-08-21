<?php

class Content {
	
	private $id;
	
	private $data = array();
	private $elements = array();
	
	public function loadFromUrl($url){
		// Loads content with given URL
		// Try from cache
		$cachekey = "content:" . $url;
		$content = Cache::load($cachekey);
		if(!$content['id']){
			// No cache found, load from database
			$sql = new SqlManager();
			// ...here server and language (both coming from controller if available) should be included!
			$sql->setQuery("SELECT * FROM content WHERE url='{{url}}'");
			$sql->bindParam("{{url}}", $url);
			$content = $sql->result();
			if(!isset($content['id'])){
				throw new Exception("No content for URL '{$url}' found!");
				return false;
			}
			$this->id = $content['id'];
			$this->data = $content;
			// Load other content data as well
			$this->elements = $this->loadElements();
		}
		return true;
	}
	
	public function loadElements($parent=null){
		// Returns elements loaded from the database for given parent id
		if(!isset($this->id)){
			throw new Exception("Cannot get content elements! No content found!");
			return;
		}
		// Try from cache first
		$elements = null;
		$cachekey = "elements:" . $this->id;
		$elements = Cache::load($cachekey);
		if(!is_array($elements)){
			// No cache found, load from database
			$sql = new SqlManager();
			if($parent > 0){
				$sql->setQuery("SELECT * FROM content_element WHERE content_id = {{id}} AND parent_id = {{parent}} ORDER BY sortkey");
				$sql->bindParam("{{parent}}", $parent, "int");
			} else {
				$sql->setQuery("SELECT * FROM content_element WHERE content_id = {{id}} AND parent_id IS NULL ORDER BY sortkey");
			}
			$sql->bindParam("{{id}}", $this->id, "int");
			$sql->execute();
			while($element = $sql->fetch()){
				if(!isset($elements[$element['position']])){
					$elements[$element['position']] = array();
				}
				$element['parameters'] = unserialize($element['parameters']);
				$index = count($elements[$element['position']]);
				$elements[$element['position']][$index] = $element;
				// Created parent-children array tree
				$elements[$element['position']][$index]['_children'] = $this->loadElements($element['id']);				
			}
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
		if(isset($this->data['type'])){
			return $this->data['type'];
		}
		return null;
	}
	
	// TODO create, update and delete handling
	
}