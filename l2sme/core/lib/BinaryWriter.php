<?php
	class BinaryWriter {
		public $data;
		public $offset = 0;
		
		public function __construct($data = "") {
			$this->setData($data);
		}
		
		public function setData($data) {
			$this->data = $data;
			$this->offset = 0;
		}
		
		public function &getData() {
			return $this->data;
		}
		
		public function getOffset() {
			return $this->offset;
		}
		
		public function writeByte($b) {
			$this->data .= chr($b);
			return $this;
		}
		
		public function readSByte($c) {
			$this->data .= pack("c", $c);
			return $this;
		}
		
		public function writeUShort($s, $be = false) {
			$this->data .= !$be ? 
				chr($i >> 8  & 0xFF).chr($i      & 0xFF) : 
				chr($i       & 0xFF).chr($i >>  8 & 0xFF);
			return $this;
		}
		
		public function writeShort($s, $be = false) {
			$this->data .= !$be ? 
				chr($i >> 8  & 0xFF).chr($i      & 0xFF) : 
				chr($i       & 0xFF).chr($i >>  8 & 0xFF);
			return $this;
		}
		
		public function writeUInt($i, $be = false) {
			$this->data .= $be ? 
				chr($i >> 24 & 0xFF).chr($i >> 16 & 0xFF).chr($i >> 8  & 0xFF).chr($i      & 0xFF) : 
				chr($i       & 0xFF).chr($i >>  8 & 0xFF).chr($i >> 16 & 0xFF).chr($i >> 24 & 0xFF);
			return $this;
		}
		
		public function writeInt($i, $be = false) {
			$this->data .= $be ? 
				chr($i >> 24 & 0xFF).chr($i >> 16 & 0xFF).chr($i >> 8  & 0xFF).chr($i      & 0xFF) : 
				chr($i       & 0xFF).chr($i >>  8 & 0xFF).chr($i >> 16 & 0xFF).chr($i >> 24 & 0xFF);
			return $this;
		}
		
		public function writeString($str, $len_t = "s", $be = false) {
			$length = strlen($str);
			switch ($len_t) {
				case "b": $this->writeByte($length);        break;
				case "s": $this->writeUShort($be, $length); break;
				case "i": $this->writeUInt($be, $length);   break;
			}
			$this->data .= $str;
			return $this;
		}
		
		public function write($str) {
			$this->data .= $str;
			return $this;
		}
		
		public function writeASCF($str) {
			if (!($enc = mb_detect_encoding($str)))
				$enc = BinaryReader::detectEncoding($str);
			$ascii = true;
			if ($enc && $enc != 'ASCII') {
				$str = @iconv($enc, "UCS-2LE", $str);
				$ascii = false;
			}
			$this->data .= $ascii ? self::encodeASCFLengthASCII(strlen($str)) : self::encodeASCFLengthUNICODE(mb_strlen($str, "UCS-2LE"));
			$this->data .= $str;
			return $this;
		}
		
		public static function encodeASCFLengthASCII($len) {
			$n = floor($len / 0x80);
			$b = floor($len / 0x40);
			if ($b < 1)
				return chr($len);
			$a = ($len - $n * 0x80) | 0x40;
			/*
			$len2 = (($a & ~($b % 2 == 0 ? 0xC0 : 0x80)) + ($n * 0x80));
			
			echo "$a $b $len2 $len\n";
			if ($len2 != $len) die;
			*/
			return chr($a).chr($b);
		}
		
		public static function encodeASCFLengthUNICODE($len) {
			$n = floor($len / 0x80);
			$b = floor($len / 0x40);
			if ($b < 1)
				return chr(0x80 + $len);
			$a = ($len - $n * 0x80) + ($b % 2 == 0 ? 0xC0 : 0x80);
			/*
			$len2 = (($a & ~($b % 2 == 0 ? 0xC0 : 0x80)) + ($n * 0x80));
			
			echo "$a $b $len2 $len\n";
			if ($len2 != $len) die;
			*/
			return chr($a).chr($b);
		}
		
		public function eof() {
			return $this->offset + 1 >= strlen($this->data);
		}
	}

