<?php

class FileManager {
	
	private $filepath = null;
	private $content = null;
	private $content_ln = array();
	private $line = 0;
	
	public function __construct($path=null){
		if(isset($path)){
			// If path is given, select file
			$this->setPath($path);
		}
	}
	
	public function setPath($path){
		// Select file from given path
		$this->path = $path;
		if(!is_file($path)){
			$this->content = $this->read();
			$this->content_ln = explode("\n", $this->content);
		}
	}
	
	public function delete(){
		// Delete file
		if(!is_file($this->path)){
			return;
		}
		unlink($this->path);
	}
	
	public function setContent($content){
		// Set content of file
		$this->content = $content;
	}
	
	public function addContent($content){
		// Add content to the end of the file
		$this->content .= $content;
	}
	
	public function append($content){
		// Synonym for addContent()
		$this->addContent($content);
	}
	
	public function prepend($content){
		// Add content to the beginning of the file
		$this->content = $content . $this->content;
	}
	
	public function save(){
		// Save set content in selected file
		file_put_contents($this->path, $this->content);
	}
	
	public function read(){
		// Read content from selected file
		if(!is_file($this->path)){
			return;
		}
		return file_get_contents($this->path);
	}
	
	public function readLine($line=null){
		// Just read requested line of file
		if(!is_file($this->path)){
			return;
		} elseif(!isset($line)){
			$line = $this->line;
			$this->line++;
		}
		return $this->content_ln[$line];
	}
	
	public function setLinePointer($line){
		$this->line = $line;
	}
	
}