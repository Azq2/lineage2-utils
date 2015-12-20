<?php
	function array_val($array, $key, $def_value) {
		return isset($array[$key]) ? $array[$key] : $def_value;
	}
	
	function L($text) {
		if (L2Int::$enabled)
			$text = L2Int::translate($text);
		
		if (func_num_args() > 1) {
			$params = func_get_args();
			return preg_replace_callback("/\{([\w\d_]+)\}/", function (&$m) use (&$params) {
				$key = is_numeric($m[1]) ? $m[1] + 1 : $m[1];
				return isset($params[$key]) ? $params[$key] : $m[0];
			}, $text);
		}
		return $text;
	}
	
	function un_null($str) {
		return str_replace("\0", "", $str);
	}
	
	function str_add_null($s) {
		return strlen($s) > 0 ? $s."\0" : "";
	}
	
	function uplaod_get_error($code) {
		$errors = [
			UPLOAD_ERR_OK => L("OK"), 
			UPLOAD_ERR_INI_SIZE => L("UPLOAD_ERR_INI_SIZE"), 
			UPLOAD_ERR_FORM_SIZE => L("UPLOAD_ERR_FORM_SIZE"), 
			UPLOAD_ERR_PARTIAL => L("файл получен частично"), 
			UPLOAD_ERR_NO_FILE => L("файл не получен"), 
			UPLOAD_ERR_NO_TMP_DIR => "UPLOAD_ERR_NO_TMP_DIR", 
			UPLOAD_ERR_CANT_WRITE => "UPLOAD_ERR_CANT_WRITE", 
			UPLOAD_ERR_EXTENSION => "UPLOAD_ERR_EXTENSION"
		];
		return isset($errors[$code]) ? $errors[$code] : "upload error #".$code;
	}
