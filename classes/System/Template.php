<?php

class Template {
	
	public static function parse($content, $elements=array(), $vars=array()){
		// Parse set content by defined rules
		if(!$content){
			return $content;
		}
		if(count($elements) > 0){
			// Parse area calls -> {{area CODE(|TYPE ARGS)}}
			$content = self::parseArea($content, $elements, $vars);
		}
		 
		// Parse locale tags -> {{locale CODE}}DEFAULTTEXT{{/locale}}
		// TODO
		
		if(count($vars) > 0){
			// Parse parameter calls -> {{param CODE}}
			$content = self::parseParams($content, $vars);
			// Parse simple variables -> {{var CODE}}
			$content = self::parseVars($content, $vars);
		}
		
		// Parse template calls -> {{template PATH}}
		// TODO
		
		// Get absolute template path from relative path -> {{templatepath PATH}}
		// TODO
		
		// Get absolute file path from relative path -> {{filepath PATH}}
		// TODO
		
		// Parse if-loops from inner to outer loops -> {{if VAR OP VAR2}}...{{else}}...{{endif}}
		$content = self::parseIfStatement($content);

		if(!isset($content)){
			throw new Exception("Text is not set!");
		}
		
		return $content;
	}
	
	public static function parseArea($content, $elements, $vars){
		// Parse area calls -> {{area CODE(|TYPE ARGS)}}
		$cnt = preg_match_all("/\{\{area (.+?)(\|*)\}\}/", $content, $areas);
		for($i=0; $i<$cnt; $i++){
			$key = array();
			$key = split("\|",$areas[1][$i]);
			// TODO filter handling
			$replace = '';
			foreach($elements[$key[0]] as $sortkey => $element){
				// Assign variables and parse element template
				$view = new View();
				$view->setTemplate('element/' . $element['type']);
				$view->assignArray($vars);
				$view->assign('element', $element);
				if(isset($element['_children'])){
					$view->setElements($element['_children']);
				}
				$output = self::parseParams($view->createOutput(), $element);
				$replace .= $output;
			}
			$content = str_replace($areas[0][$i], $replace, $content);
		}		
		return $content;
	}
	
	public function parseParams($content, $element){
		// Parse template parameter variables -> {{param CODE}}
		$cnt = preg_match_all("/\{\{param (.+?)\}\}/", $content, $matches);
		for($i=0; $i<$cnt; $i++){
			$split = split("\.", $matches[1][$i]);
			$replace = $element['parameters'][$split[0]];
			for($j=1; $j<count($split); $j++){
				$replace = $replace[$split[$j]];
			}
			$content = str_replace($matches[0][$i], $replace, $content);
		}
		return $content;
	}
	
	public function parseVars($content, $vars){
		// Parse simple variables -> {{var CODE}}
		$cnt = preg_match_all("/\{\{var (.+?)\}\}/", $content, $matches);
		for($i=0; $i<$cnt; $i++){
			$split = split("\.", $matches[1][$i]);
			$replace = $vars[$split[0]];
			for($j=1; $j<count($split); $j++){
				$replace = $replace[$split[$j]];
			}
			$content = str_replace($matches[0][$i], $replace, $content);
		}
		return $content;
	}
	
	public function parseIfStatement($content){
		// Parse if-loops from inner to outer loops -> {{if VAR OP VAR2}}...{{else}}...{{endif}}
		// Parse until there are no if-loops left
		while(strpos($content, '{{endif}}') !== false){
			// Get all loops without inner loops
			$cnt = preg_match_all("/\{\{if (((?!\}).)+?)\}\}(((?!\{\{endif\}\})(?!\{\{if).)*?)\{\{endif\}\}/s", $content, $loops);
			
			for($i=0; $i<$cnt; $i++){
				// Seperate the condition statements
				$condition = explode(" ", $loops[1][$i]);
				if(count($condition) < 1 || count($condition) > 3){
					throw new Exception("Parsing Error: to many parameters for if statement");	
				}
				if(!$condition[1]){
					$condition[1] = "true";
				}
				
				$ret = false;
				switch($condition[1]){
					case "==":
						// Equal
						if($condition[0] == $condition[2]) $ret = true;
						break;
					case "!=":
						// Not equal
						if($condition[0] != $condition[2]) $ret = true;
						break;
					case ">":
						// Greater
						if($condition[0] > $condition[2]) $ret = true;
						break;
					case ">=":
						// Greater or equal
						if($condition[0] >= $condition[2]) $ret = true;
						break;
					case "<":
						// Smaller
						if($condition[0] > $condition[2]) $ret = true;
						break;
					case "<=":
						// Smaller or equal
						if($condition[0] >= $condition[2]) $ret = true;
						break;
					case "eq":
						// Numerical equal
						if((int)$condition[0] == (int)$condition[2]) $ret = true;
						break;
					case "ne":
						// Numerical unequal
						if((int)$bed[0] != (int)$bed[2]) $ret = true;
						break;
					case "gt":
						// Greater
						if((int)$condition[0] > (int)$condition[2]) $ret = true;
						break;
					case "ge":
						// Greater or equal
						if($condition[0] >= $condition[2]) $ret = true;
						break;
					case "lt":
						// Smaller
						if($condition[0] > $condition[2]) $ret = true;
						break;
					case "le":
						// Smaller or equal
						if($condition[0] >= $condition[2]) $ret = true;
						break;
					case "true":
						// True
						if($condition[0]) $ret = true;
						break;
					case "false":
						// False
						if(!$condition[0]) $ret = true;
						break;
					default:
						// Unknown operator
						throw new Exception("Parsing Error: unknown operator!");
				}
				
				$ifelse = array();
				$replace = $loops[3][$i];
				
				// Check if there is an else-loop
				if(strpos($replace, "{{else}}") !== false){
					preg_match("/^(.*?)\{\{else\}\}(.*?)$/s", $replace, $ifelse);
					if($ret === true){
						$replace = $ifelse[1];
					} else {
						$replace = $ifelse[2];
					}
				}
				
				// If without else and condition is false
				if(count($ifelse) < 2 && !$ret){
					$replace = "";
				}
				
				// Finally do the replacement in the text
				$content = str_replace($loops[0][$i], $replace, $content);
			}
		}
		return $content;
	}
	
}