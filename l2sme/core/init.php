<?php
	define('H', dirname(__FILE__).'/');
	
	ini_set("display_errors", true);
	ini_set("memory_limit", '64M');
	error_reporting(E_ALL);
	mb_internal_encoding('UTF-8');
	date_default_timezone_set('Europe/Moscow');
	set_time_limit(0);
	
	require_once H."stdlib.php";
	
	if (!isset($_SERVER['HTTP_USER_AGENT']))
		$_SERVER['HTTP_USER_AGENT'] = PHP_SAPI == "cli" ? PHP_SAPI : "";
	if (!isset($_SERVER['REQUEST_URI']))
		$_SERVER['REQUEST_URI'] = "/";
	
	// Автозагрузка классов
	spl_autoload_register(function ($class) {
		include_once H.'lib/'.str_replace("\\", "/", $class).".php";
	});
