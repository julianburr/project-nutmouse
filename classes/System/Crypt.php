<?php

class Crypt {
	
	private static $salt = "Cut down sodium while on a diet, girl";
	
	public static function encode($data, $password){
		// Encrypt data using initializatioln vector
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND); 
		$encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $password, $data, MCRYPT_MODE_ECB, $iv);
		return base64_encode($iv . $encrypted);

	}
	
	public static function decode($encrypted, $password){
		// Decrypt data
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$encrypted = base64_decode($encrypted);
    	$iv = substr($encrypted, 0, $iv_size);
    	$encrypted = substr($encrypted, $iv_size);
		return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $password, $encrypted, MCRYPT_MODE_ECB, $iv);
	}
	
	public static function createHash($data){
		// Create hash for crypt purpose e.g. for password hashs
		if(function_exists("password_hash")){
			return password_hash($data, PASSWORD_BCRYPT);
		}
		// For PHP < 5.5
		return md5($data . self::$salt);
	}
	
	public static function checkHash($data, $hash){
		// Verify data against hash crypted data
		if(function_exists("password_verify")){
			password_verify($data, $hash);
		}
		// For PHP < 5.5
		if(md5($data . self::$salt) == $hash){
			return true;
		}
		return false;
	}
	
	public static function compareStrings($string1, $string2){
		// Mainly to be used to avoid timing attacks
		// Basicly by comparing all characters with each other, 
		// no matter if a not matching one is found earlier or not
		$match = true;
		if(strlen($string1) != strlen($string2)){
			$match = false;
		}
		for($i=0; $i<strlen($string1); $i++){
			if(substr($string1, $i, $i+1) != substr($string2, $i, $i+1)){
				$match = false;
			}
		}
		return $match;
	}
	
}