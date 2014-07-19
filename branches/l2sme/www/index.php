<?php
	const STATIC_REVISOIN = 1;
	
	error_reporting(0);
	mb_internal_encoding('UTF-8');
	
	include 'rsa.php';
	include 'binary.php';
	
	$action = isset($_GET['action']) ? $_GET['action'] : '';
	switch ($action) {
		case "download":
			$file_md5 = isset($_GET['file_id']) ? preg_replace("/[^a-f0-9]/", "", $_GET['file_id']) : '';
			$file_name = isset($_GET['file_name']) ? trim(preg_replace("/[\r\n\\x00]\"/", "", $_GET['file_name'])) : '';
			if (!strlen($file_name))
				$file_name = 'systemmsg-ru.dat';
			
			$file_path = "tmp/".$file_md5;
			if (!file_exists($file_path) || !is_file($file_path)) {
				header("Location: ?");
				exit;
			}
			
			if (isset($_GET['raw'])) {
				$blob = open_l2_file($file_path)->getData();
				$size = strlen($blob);
			} else {
				$size = filesize($file_path);
				$fp = fopen($file_path, "r");
				flock($fp, LOCK_EX);
				$blob = fread($fp, $size);
				flock($fp, LOCK_UN);
				fclose($fp);
			}
			header("Content-Type: application/octet-stream");
			header("Content-Length: ".$size);
			header('Content-Disposition: attachment; filename="'.$file_name.'"; size='.$size);
			echo $blob;
			exit;
		break;
		
		case "update_file":
			if (!isset($_POST['id'])) die;
			
			$errors = array();
			
			$id = isset($_POST['id']) ? abs($_POST['id']) : 0;
			$message = isset($_POST['message']) ? $_POST['message'] : "";
			$sub_message = isset($_POST['sub_message']) ? $_POST['sub_message'] : "";
			$color = isset($_POST['color']) ? $_POST['color'] : "";
			$opacity = isset($_POST['opacity']) ? abs($_POST['opacity']) : 0;
			$position = isset($_POST['position']) ? abs($_POST['position']) : 0;
			$message_timeout = isset($_POST['message_timeout']) ? $_POST['message_timeout'] : 0;
			$message_show_speed = isset($_POST['message_show_speed']) ? $_POST['message_show_speed'] : 0;
			$music = isset($_POST['music']) ? $_POST['music'] : 0;
			$head_on_message = isset($_POST['head_on_message']) && $_POST['head_on_message'] ? 1 : 0;
			
			if ($opacity > 0xFF)
				$errors[] = 'Неверное значение прозрачности ('.$opacity.')';
			elseif ($position > 8)
				$errors[] = 'Неверное значение позиции текста ('.$opacity.')';
			elseif (!preg_match("/^[A-F0-9]{6}$/i", $color) > 0)
				$errors[] = 'Неверное значение цвета ('.htmlspecialchars($color).')';
			
			if (!$errors) {
				$file_md5 = isset($_GET['file_id']) ? preg_replace("/[^a-f0-9]/", "", $_GET['file_id']) : '';
				$file_path = "tmp/".$file_md5;
				try {
					if (!file_exists($file_path) || !is_file($file_path))
						throw new Exception("Файл не найден! Истекло время его хранения. Откройте его заново. ");
					
					$bb = open_l2_file($file_path);
					L2SystemMsg::parse($bb, $systemmsgs);
					
					$color = hexdec($color);
					$r = $color >> 16 & 0xFF;
					$g = $color >> 8 & 0xFF;
					$b = $color & 0xFF;
					
					if (!isset($systemmsgs['strings'][$id]))
						throw new Exception("Сообщение ".$id." не найдено!");
					$m = &$systemmsgs['strings'][$id];
					
					$m[L2SystemMsg::POSITION] = $position;
					$m[L2SystemMsg::DURATION] = $message_timeout;
					$m[L2SystemMsg::DELAY_SPEED] = $message_show_speed;
					$m[L2SystemMsg::HEAD] = $head_on_message;
					$m[L2SystemMsg::SOUND] = str_add_null($music);
					$m[L2SystemMsg::MESSAGE] = str_add_null($message);
					$m[L2SystemMsg::SUB_MSG] = str_add_null($sub_message);
					$m[L2SystemMsg::R] = $r;
					$m[L2SystemMsg::G] = $g;
					$m[L2SystemMsg::B] = $b;
					$m[L2SystemMsg::A] = $opacity;
					
					unset($m);
					
					$w = new BinaryWriter();
					L2SystemMsg::save($w, $systemmsgs);
					
					write_l2_file($file_path, $w->getData());
				} catch (Exception $e) {
					$errors[] = 'Внезапная ошибка разбора файла ('.get_class($e).'): '.$e->getMessage();
					write_log("exception (update file): ".get_class($e).": ".$e->getMessage());
				}
			}
			
			ob_clean();
			header("Content-Type: application/json");
			echo json_encode(array('errors' => $errors));
			exit;
		break;
		
		case "edit_file":
			$file_md5 = isset($_GET['file_id']) ? preg_replace("/[^a-f0-9]/", "", $_GET['file_id']) : '';
			$file_name = isset($_GET['file_name']) ? $_GET['file_name'] : '';
			
			$file_path = "tmp/".$file_md5;
			if (!file_exists($file_path) || !is_file($file_path)) {
				header("Location: ?");
				exit;
			}
		
			$b = open_l2_file($file_path);
			try {
				L2SystemMsg::parse($b, $systemmsgs);
			} catch (Exception $e) {
				write_log("exception (edit_file): ".get_class($e).": ".$e->getMessage());
				die('Внезапная ошибка разбора файла ('.get_class($e).'): '.$e->getMessage());
			}
			$sounds = file("files/sounds.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			unset($b);
			
			start_html_page($file_name);
			echo tpl("msg_table.xhtml", array(
				"messages" => &$systemmsgs['strings'], 
				"sounds" => &$sounds, 
				"file_id" => $file_md5, 
				"file_name" => $file_name, 
			));
			end_html_page();
		break;
		
		default:
			$errors = array();
			if (isset($_FILES['file'])) {
				if ($_FILES['file']['error'] > 0)
					$errors[] = 'Ошибка загрузки файла #'.$_FILES['file']['error'];
				elseif ($_FILES['file']['size'] >= 1024 * 1024)
					$errors[] = 'Максимальный размер файла - 1 Mb. ';
				else {
					$r = open_l2_file($_FILES['file']['tmp_name']);
					try {
						L2SystemMsg::parse($r, $systemmsgs);
						$md5 = md5(md5_file($_FILES['file']['tmp_name']).":".uniqid().":".time());
						$file_name = "tmp/".$md5;
						write_l2_file($file_name, $r->getData());
						
					//	move_uploaded_file($_FILES['file']['tmp_name'], $file_name);
						
						$file_name = basename($_FILES['file']['name']);
						header("Location: ?action=edit_file&file_id=".$md5."&file_name=".urlencode($file_name));
						exit;
					} catch (Exception $e) {
						$errors[] = 'Ошибка разбора файла ('.get_class($e).'): '.$e->getMessage();
						write_log("exception: ".get_class($e).": ".$e->getMessage());
					}
				}
			}
			
			start_html_page("Редактор SystemMsg-ru.dat/SystemMsg-e.dat для Lineage II High Five");
			echo tpl("index.xhtml", array(
				"errors" => $errors
			));
			end_html_page(false);
		break;
	}
	
	class L2SystemMsg {
		const ID = 0;
		const UNK = 1;
		const MESSAGE = 2;
		const GROUP = 3;
		const B = 4;
		const G = 5;
		const R = 6;
		const A = 7;
		const SOUND = 8;
		const SYS_REF = 9;
		const POSITION = 10;
		const UNK1 = 11;
		const DURATION = 12;
		const DELAY_SPEED = 13;
		const HEAD = 14;
		const SUB_MSG = 15;
		const TYPE = 16;
		
		public static function parse($b, &$data) {
			$data = array(
				'total' => $b->readUInt(), 
				'strings' => array(), 
				'pkg' => ''
			);
			for ($i = 0; $i < $data['total']; ++$i) {
				$id = $b->readUInt();
				$data['strings'][$id] = array(
					$id, // ID
					$b->readUInt(), // UNK
					$b->readASCF(), // Message
					$b->readUInt(), // Group
					
					$b->readByte(), // B
					$b->readByte(), // G
					$b->readByte(), // R
					$b->readByte(), // A
					
					$b->readASCF(), // item_sound
					$b->readASCF(), // sys_msg_ref
					
					$b->readUInt(), 
					$b->readUInt(), 
					$b->readUInt(), 
					$b->readUInt(), 
					$b->readUInt(), 
					
					$b->readASCF(), // sub_msg
					$b->readASCF(), // type
				);
			}
			$data['pkg'] = $b->readASCF();
		}
		
		public static function save($b, &$data) {
			$b->writeUInt(count($data['strings']));
			foreach ($data['strings'] as &$m) {
				$b->writeUInt($m[L2SystemMsg::ID]); // ID
				$b->writeUInt($m[L2SystemMsg::UNK]); // UNK
				$b->writeASCF($m[L2SystemMsg::MESSAGE]); // Message
				$b->writeUInt($m[L2SystemMsg::GROUP]); // Group
				
				$b->writeByte($m[L2SystemMsg::B]); // B
				$b->writeByte($m[L2SystemMsg::G]); // G
				$b->writeByte($m[L2SystemMsg::R]); // R
				$b->writeByte($m[L2SystemMsg::A]); // A
				
				$b->writeASCF($m[L2SystemMsg::SOUND]); // item_sound
				$b->writeASCF($m[L2SystemMsg::SYS_REF]); // sys_msg_ref
				
				$b->writeUInt($m[L2SystemMsg::POSITION]); 
				$b->writeUInt($m[L2SystemMsg::UNK1]); 
				$b->writeUInt($m[L2SystemMsg::DURATION]); 
				$b->writeUInt($m[L2SystemMsg::DELAY_SPEED]); 
				$b->writeUInt($m[L2SystemMsg::HEAD]); 
				
				$b->writeASCF($m[L2SystemMsg::SUB_MSG]); // sub_msg
				$b->writeASCF($m[L2SystemMsg::TYPE]); // type
			}
			$b->writeASCF($data['pkg']);
		}
	}
	
	function write_l2_file($file_name, &$data) {
		$fp = fopen($file_name, "w+");
		flock($fp, LOCK_EX);
		fwrite($fp, iconv("ASCII", "UCS-2", "Lineage2Ver413"));
		$data = &encode_rsa(pack("V", strlen($data)).gzcompress($data), L2_MODULUS, L2_EXP_PRIV, false);
		fwrite($fp, $data);
		fwrite($fp, pack("VVVVV", 0, 0, 0, crc32($data), 0));
		flock($fp, LOCK_UN);
		fclose($fp);
	}
	
	function open_l2_file($file_name) {
		$fp = fopen($file_name, "r");
		flock($fp, LOCK_EX);
		$blob = fread($fp, filesize($file_name));
		flock($fp, LOCK_UN);
		fclose($fp);
		
		if (!($data = @gzuncompress(substr(decode_rsa(substr($blob, 28, strlen($blob) - 20), L2_MODULUS, L2_EXP_PUB, false), 4))))
			if (!($data = @gzuncompress(substr(decode_rsa(substr($blob, 28, strlen($blob) - 20), L2_ORIG_MODULUS, L2_ORIG_EXP_PUB, false), 4))))
				if (!($data = @gzuncompress(substr($blob, 4))))
					return new BinaryReader($blob);
		return new BinaryReader($data);
	}
	
	function normalize_music_name($name) {
		$name = preg_replace("/^.*?\./", "", $name);
		$name = str_replace("_", " ", $name);
		return ucwords($name);
	}
	
	function get_music_path($name) {
		return str_replace(".", "/", $name).".mp3";
	}
	
	function tpl($__file, $args = array()) {
		extract($args);
		ob_start();
		include "templates/".$__file.".php";
		return ob_get_clean();
	}
	
	function un_null($str) {
		return str_replace("\0", "", $str);
	}
	
	function start_html_page($title) {
		ob_start();
		
		header("Content-Type: text/html; charset=UTF-8");
		echo tpl("header.xhtml", array(
			"title" => $title, 
			"rev" => STATIC_REVISOIN
		));
	}
	
	function end_html_page($noindex = true) {
		ob_start();
		echo tpl("footer.xhtml", array('noindex' => $noindex));
	}
	
	function write_log($data) {
		$fp = fopen("tmp/log.txt", "a+");
		flock($fp, LOCK_EX);
		fwrite($fp, date("[d/m/Y h:i:s] ").$data."\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}
	
	function str_add_null($s) {
		return strlen($s) > 0 ? $s."\0" : "";
	}
