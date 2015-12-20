<?php
	class BinaryReader {
		public $data;
		public $offset = 0;
		
		public function __construct($data = NULL) {
			if (!is_null($data))
				$this->setData($data);
		}
		
		public function getData() {
			return $this->data;
		}
		
		public function setData(&$data) {
			$this->data = $data;
			$this->offset = 0;
		}
		
		public function seek($offset) {
			if ($offset >= strlen($this->data))
				throw new Exception("Offset ".$offset." out of range!");
			$this->offset = $offset;
			return $this;
		}
		
		public function getOffset() {
			return $this->offset;
		}
		
		public function readByte() {
			if ($this->offset + 1 >= strlen($this->data))
				throw new Exception("Can't read byte! Unexpected EOF at ".$offset);
			return ord($this->data[$this->offset++]);
		}
		
		public function readSByte() {
			$c = $this->readByte();
			if ($c & 0x80)
				return $c - 0x10;
			return $c;
		}
		
		public function readUShort($be = false) {
			if ($this->offset + 2 >= strlen($this->data))
				throw new Exception("Can't read short! Unexpected EOF at ".$this->offset);
			$this->offset += 2;
			return $be ? 
				ord($this->data[$this->offset - 2]) << 8 | ord($this->data[$this->offset - 1]) : 
				ord($this->data[$this->offset - 1]) << 8 | ord($this->data[$this->offset - 2]);
		}
		
		public function readShort() {
			$c = $this->readUShort();
			if ($c & 0x8000)
				return $c - 0x1000;
			return $c;
		}
		
		public function readUInt($be = false) {
			if ($this->offset + 4 >= strlen($this->data))
				throw new Exception("Can't read int! Unexpected EOF at ".$this->offset);
			$this->offset += 4;
			return $be ? 
				ord($this->data[$this->offset - 4]) << 24 | ord($this->data[$this->offset - 3]) << 16 | ord($this->data[$this->offset - 2]) << 8 | ord($this->data[$this->offset - 1]) : 
				ord($this->data[$this->offset - 1]) << 24 | ord($this->data[$this->offset - 2]) << 16 | ord($this->data[$this->offset - 3]) << 8 | ord($this->data[$this->offset - 4]);
		}
		
		public function readInt() {
			$c = $this->readInt();
			if ($c & 0x80000000)
				return $c - 0x10000000;
			return $c;
		}
		
		public function readString($length = -1, $len_t = "s", $be = false) {
			if (!$length >= 0) {
				switch ($len_t) {
					case "b": $length = $this->readByte();      break;
					case "s": $length = $this->readUShort($be); break;
					case "i": $length = $this->readUInt($be);   break;
				}
			}
			if ($this->offset + $length >= strlen($this->data))
				throw new Exception("Can't read string (".$length.")! Unexpected EOF at ".$offset);
			$this->offset += $length;
			return substr($this->data, $this->offset - $length, $length);
		}
		
		public function readShortString($be = false) {
			$start = $this->offset;
			while (($c = $this->readUShort($be)) > 0);
			return substr($this->data, $start, $this->offset - $start);
		}
		
		public function readASCF() {
			if (!isset($this->data[$this->offset + 1]))
				throw new Exception("Can't read ASCF! Unexpected EOF at ".($this->offset + 1));
			
			$a = ord($this->data[$this->offset]);
			$b = ord($this->data[$this->offset + 1]);
			
			if ($a >= 0x80 && $a < 0xC0) {
				$len = $a & ~0x80;
				
				$start = $this->offset + 1;
				if ($start + $len * 2 + 1 >= strlen($this->data))
					throw new Exception("Can't read ASCF! Unexpected EOF at ".$start);
				
				$this->offset += 1 + $len * 2;
				return @iconv("UCS-2LE", "UTF-8", substr($this->data, $start, $len * 2));
			} else if ($a >= 0xC0 && $a <= 0xFF) {
				$n = ($b - ($b % 2)) / 2;
				$len = (($a & ~($b % 2 == 0 ? 0xC0 : 0x80)) + ($n * 0x80));
				
				$start = $this->offset + 2;
				if ($start + $len * 2 + 2 >= strlen($this->data))
					throw new Exception("Can't read ASCF! Unexpected EOF at ".$start);
				
				$this->offset += 2 + $len * 2;
				return @iconv("UCS-2LE", "UTF-8", substr($this->data, $start, $len * 2));
			} else if ($a < 0x40) {
				if (!$this->offset + 1 + $a >= strlen($this->data))
					throw new Exception("Can't read ASCF! Unexpected EOF at ".($this->offset + 1));
				
				$data = substr($this->data, $this->offset + 1, $a);
				$this->offset += 1 + $a;
				to_encoding($data, "UTF-8");
				return $data;
			} else if ($a >= 0x40) {
				$n = ($b - ($b % 2)) / 2;
				$len = (($a & ~($b % 2 == 0 ? 0xC0 : 0x80)) + ($n * 0x80));
				
				if (!$this->offset + 2 + $len >= strlen($this->data))
					throw new Exception("Can't read ASCF! Unexpected EOF at ".($this->offset + 2));
				
				$data = substr($this->data, $this->offset + 2, $len);
				$this->offset += 2 + $len;
				to_encoding($data, "UTF-8");
				return $data;
			}
		}
		
		public static function detectEncoding($string) { 
			static $list = array('ASCII', 'UTF-8', 'windows-1251');
			foreach ($list as $item) {
				if (strcmp(@iconv($item, $item, $string), $string) == 0)
					return $item;
			}
			return null;
		}
		
		public function eof() {
			return $this->offset + 1 >= strlen($this->data);
		}
	}
	
	function to_encoding(&$str, $to_enc) {
		if (!($enc = mb_detect_encoding($str)))
			$enc = BinaryReader::detectEncoding($str);
		if ($enc)
			$str = @iconv($enc, $to_enc, $str);
	}
