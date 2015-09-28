<?php
	$files = explode("\n", trim(shell_exec("find . -iname '*.php'")));
	
	$messages = array();
	
	foreach ($files as $file) {
		$data = file_get_contents($file);
		$n = preg_match_all("/L\s*\(\"(.*?)\"|L\s*\(\'(.*?)\'/", $data, $langs);
		
		for ($i = 0; $i < $n; ++$i) {
			if ($langs[1][$i] !== "") {
				$lang = $langs[1][$i];
			} elseif ($langs[2][$i] !== "") {
				$lang = $langs[2][$i];
			} else {
				echo "[warn] empty L() at $file\n";
				continue;
			}
			
			$lang = stripslashes($lang);
			
			$msg_id = sprintf("%08X", crc32($lang));
			
			if (isset($messages[$msg_id]) && $messages[$msg_id] !== $lang) {
				echo "Коллизия! Пора менять хэш. (".$lang." != ".$messages[$msg_id].")\n";
				exit;
			}
			$messages[$msg_id] = $lang;
		}
	}
	
	$languages = array("en", "ua");
	foreach ($languages as $lang) {
		echo "[$lang]\n";
		if (file_exists("www/lang/$lang.ini")) {
			$lang_data = parse_ini_file("www/lang/$lang.ini");
			foreach ($lang_data as $id => $msg) {
				if (!isset($messages[$id])) {
					echo "  -$id\n";
					unset($lang_data[$id]);
				}
			}
		} else
			$lang_data = array();
		foreach ($messages as $id => $msg) {
			if (!isset($lang_data[$id])) {
				$lang_data[$id] = $msg;
				echo "  +$id\n";
			}
		}
		
		$ini_data = "";
		foreach ($lang_data as $id => $msg) {
			$lang_escaped = addcslashes($msg, "\"\\\n\r\t\v\0");
			$lang_escaped_orig = addcslashes($messages[$id], "\"\\\n\r\t\v\0");
			$ini_data .= "; $lang_escaped_orig\n";
			$ini_data .= "$id = \"$lang_escaped\"\n";
		}
		file_put_contents("www/lang/$lang.ini", $ini_data);
	}
