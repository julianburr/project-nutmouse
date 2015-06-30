<?php

class Session {
	
	private $id = null;
	
	public function __construct(){
		// Init session environment
		$this->init();
	}
	
	public function init(){
		// Point variables to session array
		$this->id = &$_SESSION['id'];
		// If new session, create it
		if($this->id != session_id()){
			$this->create();
		}
	}
	
	private function create(){
		// Create a new session
		$this->id = session_id();
	}
	
	public function kill(){
		// Kill current session
		unset($_SESSION);
		session_regenerate_id();
		$this->init();
	}
	
}