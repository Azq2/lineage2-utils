<?php
	const STATIC_REVISOIN = 1;
	
	include '../core/init.php';
	define('L2_TMP_DIR', "/tmp/");
	
	use L2File\SystemMsg;
	
	$languages = array("ru" => 1, "en" => 1, "ua" => 1);
	
	$sys_lang = 'en';
	if (!isset($_COOKIE['lang']) || !isset($languages[$_COOKIE['lang']])) {
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
			$sys_lang = stripos('ru', $_SERVER['HTTP_ACCEPT_LANGUAGE']) >= 0 ? 'ru' : 'en';
	} else
		$sys_lang = $_COOKIE['lang'];
	
	if (isset($_GET['lang']) && isset($languages[$_GET['lang']])) {
		if (isset($_GET['back']) && substr($_GET['back'], 0, 1) == '/') {
			setcookie('lang', $_GET['lang'], time() + 3600 * 24 * 365 * 3, '/');
			header("Location: ".$_GET['back']);
			exit;
		}
	}
	L2Int::setLang($sys_lang);
	
	$action = isset($_GET['action']) ? $_GET['action'] : '';
	switch ($action) {
		case "download":
			$file_md5 = isset($_GET['file_id']) ? preg_replace("/[^a-f0-9]/", "", $_GET['file_id']) : '';
			$file_name = isset($_GET['file_name']) ? trim(preg_replace("/[\r\n\\x00]\"/", "", $_GET['file_name'])) : '';
			if (!strlen($file_name))
				$file_name = 'systemmsg-ru.dat';
			
			$file_path = L2_TMP_DIR.$file_md5;
			if (!file_exists($file_path) || !is_file($file_path)) {
				header("Location: ?");
				exit;
			}
			
			if (isset($_GET['raw'])) {
				$blob = L2File::read($file_path)->getData();
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
			$errors = array();
			
			$type = isset($_GET['type']) ? $_GET['type'] : "";
			$id = isset($_POST['id']) ? abs($_POST['id']) : 0;
			$ids = isset($_POST['ids']) ? explode(",", $_POST['ids']) : [];
			$message = isset($_POST['message']) ? $_POST['message'] : "";
			$sub_message = isset($_POST['sub_message']) ? $_POST['sub_message'] : "";
			$color = isset($_POST['color']) ? $_POST['color'] : "";
			$opacity = isset($_POST['opacity']) ? abs($_POST['opacity']) : 0;
			$position = isset($_POST['position']) ? abs($_POST['position']) : 0;
			$message_timeout = isset($_POST['message_timeout']) ? $_POST['message_timeout'] : 0;
			$message_show_speed = isset($_POST['message_show_speed']) ? $_POST['message_show_speed'] : 0;
			$music = isset($_POST['music']) ? $_POST['music'] : 0;
			$head_on_message = isset($_POST['head_on_message']) && $_POST['head_on_message'] ? 1 : 0;
			
			$bulk_ops = new StdClass;
			foreach (["delete_chat_msg", "delete_scr_msg", "delete_sound", "change_color"] as $op)
				$bulk_ops->{$op} = isset($_POST[$op]) && $_POST[$op];
			
			$merge_ops = new StdClass;
			foreach (["copy_message", "copy_sub_msg", "copy_position", "copy_delay_speed", "copy_duration", "copy_sound", "copy_color", "copy_opacity", "copy_head"] as $op)
				$merge_ops->{$op} = isset($_POST[$op]) && $_POST[$op];
			
			if ($type != "merge") {
				if ($opacity > 0xFF) {
					$errors[] = L('Неверное значение прозрачности ({0})', $opacity);
				} elseif ($position > 8) {
					$errors[] = L('Неверное значение позиции текста ({0})', $opacity);
				} elseif (!preg_match("/^[A-F0-9]{6}$/i", $color) > 0) {
					$errors[] = L('Неверное значение цвета ({0})', htmlspecialchars($color));
				} else {
					$color = hexdec($color);
					$r = $color >> 16 & 0xFF;
					$g = $color >> 8 & 0xFF;
					$b = $color & 0xFF;
				}
			}
			
			if (!$errors) {
				$file_md5 = isset($_GET['file_id']) ? preg_replace("/[^a-f0-9]/", "", $_GET['file_id']) : '';
				$file_path = L2_TMP_DIR.$file_md5;
				try {
					if (!file_exists($file_path) || !is_file($file_path))
						throw new Exception(L("Файл не найден! Истекло время его хранения. Откройте его заново. "));
					
					$bb = L2File::read($file_path);
					SystemMsg::parse($bb, $systemmsgs);
					
					switch ($type) {
						case "merge":
							$donor_md5 = isset($_GET['donor_id']) ? preg_replace("/[^a-f0-9]/", "", $_GET['donor_id']) : '';
							$donor_path = L2_TMP_DIR.$donor_md5;
							
							if (!file_exists($donor_path) || !is_file($donor_path))
								throw new Exception(L("Файл не найден! Истекло время его хранения. Откройте его заново. "));
							
							$bb2 = L2File::read($donor_path);
							SystemMsg::parse($bb2, $systemmsgs2);
							
							foreach ($ids as $id) {
								if (!is_numeric($id))
									continue;
								$id = (int) $id;
								
								if (!isset($systemmsgs2['strings'][$id]))
									throw new Exception(L("Сообщение {0} не найдено!", $id));
								
								// Копируем!
								if (!isset($systemmsgs['strings'][$id])) {
									$systemmsgs['strings'][$id] = $systemmsgs2['strings'][$id];
								} else {
									$m = &$systemmsgs['strings'][$id];
									$m2 = &$systemmsgs2['strings'][$id];
									
									if ($merge_ops->copy_message)
										$m[SystemMsg::MESSAGE] = $m2[SystemMsg::MESSAGE];
									if ($merge_ops->copy_sub_msg)
										$m[SystemMsg::SUB_MSG] = $m2[SystemMsg::SUB_MSG];
									if ($merge_ops->copy_position)
										$m[SystemMsg::POSITION] = $m2[SystemMsg::POSITION];
									if ($merge_ops->copy_delay_speed)
										$m[SystemMsg::DELAY_SPEED] = $m2[SystemMsg::DELAY_SPEED];
									if ($merge_ops->copy_duration)
										$m[SystemMsg::DURATION] = $m2[SystemMsg::DURATION];
									if ($merge_ops->copy_sound)
										$m[SystemMsg::SOUND] = $m2[SystemMsg::SOUND];
									if ($merge_ops->copy_color) {
										$m[SystemMsg::R] = $m2[SystemMsg::R];
										$m[SystemMsg::G] = $m2[SystemMsg::G];
										$m[SystemMsg::B] = $m2[SystemMsg::B];
									}
									if ($merge_ops->copy_opacity)
										$m[SystemMsg::A] = $m2[SystemMsg::A];
									if ($merge_ops->copy_head)
										$m[SystemMsg::HEAD] = $m2[SystemMsg::HEAD];

								}
							}
							
							uasort($systemmsgs['strings'], function ($a, $b) {
								if ($a == $b)
									return 0;
								return ($a < $b) ? -1 : 1;
							});
						break;
						
						case "bulk":
							foreach ($ids as $id) {
								if (!is_numeric($id))
									continue;
								$id = (int) $id;
								
								if (!isset($systemmsgs['strings'][$id]))
									throw new Exception(L("Сообщение {0} не найдено!", $id));
								
								$m = &$systemmsgs['strings'][$id];
								
								if ($bulk_ops->delete_chat_msg)
									$m[SystemMsg::MESSAGE] = str_add_null("");
								if ($bulk_ops->delete_scr_msg)
									$m[SystemMsg::POSITION] = 0;
								if ($bulk_ops->delete_sound)
									$m[SystemMsg::SOUND] = str_add_null("");
								if ($bulk_ops->change_color) {
									$m[SystemMsg::R] = $r;
									$m[SystemMsg::G] = $g;
									$m[SystemMsg::B] = $b;
									$m[SystemMsg::A] = $opacity;
								}
								unset($m);
							}
						break;
						
						default:
							if (!isset($systemmsgs['strings'][$id]))
								throw new Exception(L("Сообщение {0} не найдено!", $id));
							$m = &$systemmsgs['strings'][$id];
							
							$m[SystemMsg::POSITION] = $position;
							$m[SystemMsg::DURATION] = $message_timeout;
							$m[SystemMsg::DELAY_SPEED] = $message_show_speed;
							$m[SystemMsg::HEAD] = $head_on_message;
							$m[SystemMsg::SOUND] = str_add_null($music);
							$m[SystemMsg::MESSAGE] = str_add_null($message);
							$m[SystemMsg::SUB_MSG] = str_add_null($sub_message);
							$m[SystemMsg::R] = $r;
							$m[SystemMsg::G] = $g;
							$m[SystemMsg::B] = $b;
							$m[SystemMsg::A] = $opacity;
							
							unset($m);
						break;
					}
					
					$w = new BinaryWriter();
					SystemMsg::save($w, $systemmsgs);
					
					L2File::write($file_path, $w->getData());
				} catch (Exception $e) {
					$errors[] = L('Внезапная ошибка разбора файла ({0}): {1}', get_class($e), $e->getMessage());
					write_log("exception (update file): ".get_class($e).": ".$e->getMessage());
				}
			}
			
			ob_clean();
			header("Content-Type: application/json");
			echo json_encode(array('errors' => $errors));
			exit;
		break;
		
		case "diff_file":
		case "edit_file":
			$is_diff = $action == 'diff_file';
			
			$file_md5 = isset($_GET['file_id']) ? preg_replace("/[^a-f0-9]/", "", $_GET['file_id']) : '';
			$donor_md5 = isset($_GET['donor_id']) ? preg_replace("/[^a-f0-9]/", "", $_GET['donor_id']) : '';
			
			$file_name = isset($_GET['file_name']) ? $_GET['file_name'] : '';
			$donor_name = isset($_GET['donor_name']) ? $_GET['donor_name'] : '';
			
			$file_path = L2_TMP_DIR.$file_md5;
			$donor_path = L2_TMP_DIR.$donor_md5;
			
			if (!file_exists($file_path) || !is_file($file_path) || 
					($is_diff && (!file_exists($donor_path) || !is_file($donor_path)))) {
				header("Location: ?404");
				exit;
			}
			
			$b = L2File::read($file_path);
			if ($is_diff)
				$b2 = L2File::read($donor_path);
			try {
				SystemMsg::parse($b, $systemmsgs);
				if ($is_diff)
					SystemMsg::parse($b2, $systemmsgs2);
			} catch (Exception $e) {
				write_log("exception (edit_file): ".get_class($e).": ".$e->getMessage());
				die(L('Внезапная ошибка разбора файла ({0}): {1}', get_class($e), $e->getMessage()));
			}
			unset($b);
			unset($b2);
			
			if (!$is_diff)
				$sounds = file("files/sounds.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			
			if ($is_diff) {
				$ids = array_unique(array_merge(array_keys($systemmsgs['strings']), array_keys($systemmsgs2['strings'])));
				sort($ids);
				
				$messages = [];
				$compare = [
					SystemMsg::POSITION, 
					SystemMsg::DURATION, 
					SystemMsg::DELAY_SPEED, 
					SystemMsg::HEAD, 
					SystemMsg::SOUND, 
					SystemMsg::MESSAGE, 
					SystemMsg::SUB_MSG, 
					SystemMsg::A, 
					"color", 
				];
				
				$diff_map = []; $visible_cnt = 0;
				foreach ($ids as $id) {
					$a = isset($systemmsgs['strings'][$id]) ? $systemmsgs['strings'][$id] : NULL;
					$b = isset($systemmsgs2['strings'][$id]) ? $systemmsgs2['strings'][$id] : NULL;
					
					$loc_msg_diff = []; $msg_diff = false; $hide = true;
					foreach ($compare as $field) {
						if ($field == "color") {
							$diff = $a[SystemMsg::R] != $b[SystemMsg::R] || 
								$diff = $a[SystemMsg::G] != $b[SystemMsg::G] || 
								$diff = $a[SystemMsg::B] != $b[SystemMsg::B];
						} elseif ($field == "opacity") {
							
						} elseif ($field == SystemMsg::MESSAGE || $field == SystemMsg::SUB_MSG) {
							$diff = strcasecmp(trim($a[$field]), trim($b[$field])) !== 0;
							if ($diff)
								$hide = false;
						} else {
							$diff = $a[$field] != $b[$field];
						}
						$loc_msg_diff[] = $diff ? 1 : 0;
						
						if ($diff)
							$msg_diff = true;
					}
					
					if ($msg_diff) {
						$diff_map[$id] = !$a || !$b ? [] : $loc_msg_diff;
						$messages[$id] = [
							$b ? [
								$b[L2File\SystemMsg::MESSAGE], 
								$b[L2File\SystemMsg::SUB_MSG], 
								sprintf('%02X%02X%02X', $b[L2File\SystemMsg::R], $b[L2File\SystemMsg::G], $b[L2File\SystemMsg::B]), 
								$b[L2File\SystemMsg::A] / 255
							] : false, 
							$a ? [
								$a[L2File\SystemMsg::MESSAGE], 
								$a[L2File\SystemMsg::SUB_MSG], 
								sprintf('%02X%02X%02X', $a[L2File\SystemMsg::R], $a[L2File\SystemMsg::G], $a[L2File\SystemMsg::B]), 
								$a[L2File\SystemMsg::A] / 255
							] : false, 
							$hide
						];
						if (!$hide)
							++$visible_cnt;
					
					}
				}
				
				start_html_page($file_name.' vs '.$donor_name);
				echo tpl("msg_table.xhtml", array(
					"search" => $diff_map, 
					"metadata" => (object) [], 
					"messages" => &$messages, 
					"sounds" => [], 
					"file_id" => $file_md5, 
					"file_name" => $file_name, 
					"donor_id" => $donor_md5, 
					"donor_name" => $donor_name, 
					"total" => max($systemmsgs['total'], $systemmsgs2['total']), 
					"visible_cnt" => $visible_cnt, 
					"is_diff" => true
				));
				end_html_page();
			} else {
				start_html_page($file_name);
				echo tpl("msg_table.xhtml", array(
					"search" => [], 
					"metadata" => create_metadata($systemmsgs), 
					"messages" => &$systemmsgs['strings'], 
					"sounds" => &$sounds, 
					"file_id" => $file_md5, 
					"file_name" => $file_name, 
					"is_diff" => false
				));
				end_html_page();
			}
		break;
		
		default:
			$tab = isset($_GET['tab']) && in_array($_GET['tab'], ["edit", "diff"]) ? $_GET['tab'] : "edit";
			$need_files = min(2, max(1, isset($_GET['files']) ? (int) $_GET['files'] : 1));
			
			$errors = array();
			if (isset($_FILES['file_0'])) {
				$parsed = [];
				for ($i = 0; $i < $need_files; ++$i) {
					if (!isset($_FILES['file_'.$i])) {
						$errors[$i] = L('Не все файлы загружены!');
						break;
					}
					
					$file = &$_FILES['file_'.$i];
					if ($file['error'] > 0) {
						$errors[$i] = L('Ошибка загрузки файла: {0}', uplaod_get_error($file['error']));
						break;
					} else if ($file['size'] < 1024) {
						$errors[$i] = L('Файл слишком маленький. Это не systemmsg.dat');
						break;
					} else if ($file['size'] > 1024 * 1024) {
						$errors[$i] = L('Файл слишком большой. Это не systemmsg.dat');
						break;
					} else {
						$r = L2File::read($file['tmp_name']);
						try {
							SystemMsg::parse($r, $systemmsgs);
							$parsed[] = array(
								'reader' => $r, 
								'md5' => md5_file($file['tmp_name']), 
								'name' => basename(str_replace("\\", "/", $file['name']))
							);
						} catch (Exception $e) {
							$errors[$i] = L('Ошибка разбора файла ({0}): {1}', get_class($e), $e->getMessage());
							write_log("exception: ".get_class($e).": ".$e->getMessage());
							break;
						}
					}
				}
				
				if ($parsed && !$errors) {
					$params = array(
						'action' => isset($_GET['redirect']) ? $_GET['redirect'] : 'edit_file', 
						'file_name' => $file_name
					);
					foreach ($parsed as $key => $file) {
						$uniq_id = md5(md5_file($file['md5']).":".uniqid().":".time());
						$file_name = L2_TMP_DIR.$uniq_id;
						L2File::write($file_name, $file['reader']->getData());
						$params[(isset($_POST['key_'.$key]) ? $_POST['key_'.$key] : 'file_'.$key.'_id')] = $uniq_id;
						$params[(isset($_POST['key_name_'.$key]) ? $_POST['key_name_'.$key] : 'file_'.$key.'_name')] = $file['name'];
					}
					
					if (!$errors) {
						header("Location: ?".http_build_query($params, '', '&'));
						exit;
					}
				}
			}
			
			start_html_page(L("Редактор SystemMsg-ru.dat/SystemMsg-e.dat для Lineage II High Five"));
			echo tpl("index.xhtml", array(
				"errors" => $errors, 
				"tab" => $tab
			));
			end_html_page(false);
		break;
	}
	
	function &create_metadata($systemmsgs) {
		$metadata = [];
		foreach ($systemmsgs['strings'] as &$msg) {
			$metadata[$msg[L2File\SystemMsg::ID]] = array(
				$msg[L2File\SystemMsg::POSITION], 
				$msg[L2File\SystemMsg::DURATION], 
				$msg[L2File\SystemMsg::DELAY_SPEED], 
				$msg[L2File\SystemMsg::HEAD], 
				$msg[L2File\SystemMsg::A], 
				un_null($msg[L2File\SystemMsg::SOUND])
			);
		}
		return $metadata;
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
		$fp = fopen(L2_TMP_DIR."log.txt", "a+");
		flock($fp, LOCK_EX);
		fwrite($fp, date("[d/m/Y h:i:s] ").$data."\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}
	