<?php

class Content {
	
	private $id;
	private $data = array('meta' => array(), 'elements' => array());
	
	public function __construct($id=null, $url=null){
		if(!is_null($id)){
			$this->loadFromID($id);
		} elseif(!is_null($url)){
			$this->loadFromUrl($url);
		}
	}
	
	public function loadFromUrl($url, $loadmeta=true, $loadelements=true){
		// Loads content with given URL
		// Try from cache
		$cachekey = "content:url:" . $url;
		$content = Cache::load($cachekey);
		if(!isset($content['id'])){
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
			$this->data['meta'] = $this->loadMeta();
			$this->data['elements'] = $this->loadElements();
			// Save cache for later
			Cache::save($cachekey, $this->data);
			Cache::save("content:id:" . $this->id, $this->data);
		}
		return true;
	}
	
	public function loadFromID($id, $loadmeta=true, $loadelements=true){
		// Loads content with given ID
		$cachekey = "content:id:" . $id;
		$content = Cache::load($cachekey);
		if(!isset($content['id'])){
			// No cache found, load from database
			$sql = new SqlManager();
			// ...here server and language (both coming from controller if available) should be included!
			$sql->setQuery("SELECT * FROM content WHERE id={{id}}");
			$sql->bindParam("{{id}}", $id, "int");
			$content = $sql->result();
			if(!isset($content['id'])){
				throw new Exception("No content for ID '{$id}' found!");
				return false;
			}
			$this->id = $content['id'];
			$this->data = $content;
			// Load other content data as well
			if($loadmeta) $this->data['meta'] = $this->loadMeta();
			if($loadelements) $this->data['elements'] = $this->loadElements();
			// Save cache for later
			Cache::save($cachekey, $this->data);
			Cache::save("content:url:" . $this->data['url'], $this->data);
		}
		return true;
	}
	
	public function loadMeta(){
		// Get meta data for loaded content
		if(!$this->id){
			throw new Exception("Cannot get meta data! No content set!");
			return;
		}
		return Meta::load("content", $this->id);
	}
	
	public function loadElements($parent=null){
		// Get elements to loaded content from the database for given parent id
		// Is used recursiv to load the hierarchical structure of the content elements
		if(!isset($this->id)){
			throw new Exception("Cannot get content elements! No content set!");
			return;
		}
		// Try from cache first
		$elements = array();
		// No cache found, load from database
		$sql = new SqlManager();
		if($parent > 0){
			$sql->setQuery("SELECT * FROM element WHERE object_table = 'content' AND object_id = {{id}} AND parent_id = {{parent}} ORDER BY sortkey");
			$sql->bindParam("{{parent}}", $parent, "int");
		} else {
			$sql->setQuery("SELECT * FROM element WHERE object_table = 'content' AND object_id = {{id}} AND parent_id IS NULL ORDER BY sortkey");
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
	
	public function getUrl(){
		// Return url string
		return $this->data['url'];
	}
	
	public function getElements(){
		// Return elements array
		return $this->data['elements'];
	}
	
	public function getMeta(){
		// Return meta data array
		return $this->data['meta'];
	}
	
	public function getTemplate(){
		// Return template set in content data if set
		if(isset($this->data['type'])){
			return $this->data['type'];
		}
		return null;
	}
	
	public function getServerID(){
		// Return server ID
		return $this->data['server'];
	}
	
	public function getParent(){
		// Return parent of current content instance
		if(isset($this->data['parent_id']) && $this->data['parent_id'] > 0){
			return $this->data['parent_id'];
		} 
		return null;
	}
	
	public function getParentsArray(){
		// Return array of parent tree of current content
		$parents = array();
		$content = new Content($this->id);
		while(true){
			if(is_null($content->getParent())){
				break;
			}
			$parents[] = $content->getParent();
			$content = new Content($content->getParent());
		}
		return $parents;
	}
	
	// TODO create, update and delete handling
	
}