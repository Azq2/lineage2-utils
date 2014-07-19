<?php
	const L2_MODULUS  = "75b4d6de5c016544068a1acf125869f43d2e09fc55b8b1e289556daf9b8757635593446288b3653da1ce91c87bb1a5c18f16323495c55d7d72c0890a83f69bfd1fd9434eb1c02f3e4679edfa43309319070129c267c85604d87bb65bae205de3707af1d2108881abb567c3b3d069ae67c3a4c6a3aa93d26413d4c66094ae2039";
	const L2_EXP_PUB  = "1d";
	const L2_EXP_PRIV = "30b4c2d798d47086145c75063c8e841e719776e400291d7838d3e6c4405b504c6a07f8fca27f32b86643d2649d1d5f124cdd0bf272f0909dd7352fe10a77b34d831043d9ae541f8263c6fe3d1c14c2f04e43a7253a6dda9a8c1562cbd493c1b631a1957618ad5dfe5ca28553f746e2fc6f2db816c7db223ec91e955081c1de65";
	
	const L2_ORIG_MODULUS = "97df398472ddf737ef0a0cd17e8d172f0fef1661a38a8ae1d6e829bc1c6e4c3cfc19292dda9ef90175e46e7394a18850b6417d03be6eea274d3ed1dde5b5d7bde72cc0a0b71d03608655633881793a02c9a67d9ef2b45eb7c08d4be329083ce450e68f7867b6749314d40511d09bc5744551baa86a89dc38123dc1668fd72d83";
	const L2_ORIG_EXP_PUB = "35";
	
	function encode_rsa($data, $key, $exp, $raw = true) {
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
	
	function decode_rsa($data, $key, $exp, $raw = true) {
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

