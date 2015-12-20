<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title><?= htmlspecialchars($title) ?></title>
		
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
		<script src="static/js/soundmanager/script/soundmanager2-nodebug-jsmin.js"></script>
		<script type="text/javascript" src="static/js/colorpicker.js"></script>
		
		<script type="text/javascript" src="static/js/functions.js?<?= $rev ?>"></script>
		<script type="text/javascript" src="static/js/systemmsg.js?<?= $rev ?>"></script>
		
		<link rel="stylesheet" media="screen" type="text/css" href="static/css/main.css?<?= $rev ?>" />
		<link rel="stylesheet" media="screen" type="text/css" href="static/css/colorpicker.css" />
	</head>
	
	<body>
		<div class="lang_selector">
			<a href="?lang=ru&amp;back=<?= urlencode($_SERVER['REQUEST_URI']) ?>">Русский</a> | 
			<a href="?lang=ua&amp;back=<?= urlencode($_SERVER['REQUEST_URI']) ?>">Українська</a> | 
			<a href="?lang=en&amp;back=<?= urlencode($_SERVER['REQUEST_URI']) ?>">English</a>
		</div>
