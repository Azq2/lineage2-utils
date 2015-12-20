<?php
	class RSA {
		public static function encode($data, $key, $exp, $raw = true) {
			$out = "";
			
			$len = strlen($data);
			$key_length = $raw ? strlen($key) : strlen($key) / 2;
			$exp = gmp_init($raw ? bin2hex($exp) : $exp, 16);
			$key = gmp_init($raw ? bin2hex($key) : $key, 16);
			
			$block_size = $key_length - 4;
			for ($i = 0; $i < $len; $i += $block_size) {
				$block = substr($data, $i, $block_size);
				$size = strlen($block);
				
				if ($size == $key_length - 4) {
					$enc_data = chr($size >> 32 & 0xFF).chr($size >> 16 & 0xFF).chr($size >> 8 & 0xFF).chr($size & 0xFF);
					$enc_data .= $block;
				} else {
					$pos = (($key_length - $size) & ($key_length - 4));
					$enc_data = chr($size >> 32 & 0xFF).chr($size >> 16 & 0xFF).chr($size >> 8 & 0xFF).chr($size & 0xFF);
					$enc_data .= str_repeat("\0", $pos - 4).$block;
					$enc_data .= str_repeat("\0", $key_length - ($pos + strlen($block)));
				}
				$hex = gmp_strval(gmp_powm('0x'.bin2hex($enc_data), $exp, $key), 16);
				$out .= pack('H*', str_repeat("0", $key_length * 2 - strlen($hex)).$hex);
			}
			return $out;
		}
		
		public static function decode($data, $key, $exp, $raw = true) {
			$out = "";
			
			$key_length = $raw ? strlen($key) : strlen($key) / 2;
			$exp = gmp_init($raw ? bin2hex($exp) : $exp, 16);
			$key = gmp_init($raw ? bin2hex($key) : $key, 16);
			
			$blocks = floor(strlen($data) / $key_length);
			for ($i = 0; $i < $blocks; ++$i)  {
				$block = substr($data, $i * $key_length, $key_length);
				$hex = gmp_strval(gmp_powm('0x'.bin2hex($block), $exp, $key), 16);
				$s = pack('H*', str_repeat("0", $key_length * 2 - strlen($hex)).$hex);
				$size = ord($s[0]) << 32 | ord($s[1]) << 16 | ord($s[2]) << 8 | ord($s[3]);
				if ($size == $key_length - 4)
					$out .= substr($s, -$size);
				else
					$out .= substr($s, (($key_length - $size) & ($key_length - 4)), $size);
			}
			return $out;
		}
	}