<?php

class Rewrite {
	
	private $request_url = null;
	private $target_url = null;
	
	private $id = null;
	private $data = array();
	
	public function __construct($url=null){
		$this->setUrl($url);
	}
	
	public function getTargetUrl(){
		return $this->target_url;
	}
	
	public function setUrl($url){
		$this->request_url = $url;
		$this->target_url = $url;
	}
	
	public function applyRules(){
		$sql = new SqlManager();
		$sql->setQuery("
			SELECT * FROM rewrite
			WHERE request = '{{url}}'
			");
		$sql->bindParam("{{url}}", $this->request_url);
		$rule = $sql->result();
		if(isset($rule['rewrite'])){
			$this->target_url = $rule['rewrite'];
			$this->data = $rule;
		}
	}
	
	public function isRedirect(){
		if(isset($this->data['redirect']) && $this->data['redirect'] == 1){
			return true;
		}
		return false;
	}
	
	public function isLastForward(){
		if(isset($this->data['last']) && $this->data['last'] == 1){
			return true;
		}
		return false;
	}
	
	public function transportQueryParameter(){
		if(isset($this->data['transportquery']) && $this->data['transportquery'] == 1){
			return true;
		}
		return false;
	}
	
}