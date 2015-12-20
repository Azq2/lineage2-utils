<?php
	class L2Int {
		public static $lang = 'ru', $enabled = false, $cache;
		
		public static function setLang($lang) {
			self::$enabled = $lang != 'ru';
			self::$lang = $lang;
		}
		
		public static function translate($text) {
			if (!self::$enabled)
				return $text;
			
			if (!isset(self::$cache[self::$lang])) {
				self::$cache = parse_ini_file(H."lang/".self::$lang.".ini");
			}
			$msg_id = sprintf("%08X", crc32($text));
			
			return isset(self::$cache[$msg_id]) ? self::$cache[$msg_id] : 'UNK_LANG('.$text.')';
		}
	}