<?php
	namespace L2File;
	
	class SystemMsg {
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
		const UNK2 = 17;
		const UNK3 = 18;
		
		public static function parse($b, &$data) {
			try {
				$data = array(
					'total' => $b->readUInt(), 
					'strings' => array(), 
					'pkg' => '', 
					'rev' => 0
				);
				for ($i = 0; $i < $data['total']; ++$i) {
					$id = $b->readUInt();
					
					$data['strings'][$id] = array(
						self::ID		=> $id, // ID
						self::UNK		=> $b->readUInt(), // UNK
						self::MESSAGE	=> $b->readASCF(), // Message
						self::GROUP 	=> $b->readUInt(), // Group
						
						self::B		=> $b->readByte(), // B
						self::G		=> $b->readByte(), // G
						self::R		=> $b->readByte(), // R
						self::A		=> $b->readByte(), // A
						
						self::SOUND		=> $b->readASCF(), // item_sound
						self::SYS_REF	=> $b->readASCF(), // sys_msg_ref
						
						self::POSITION		=> $b->readUInt(), 
						self::UNK1			=> $b->readUInt(), 
						self::DURATION		=> $b->readUInt(), 
						self::DELAY_SPEED	=> $b->readUInt(), 
						self::HEAD			=> $b->readUInt(), 
						
						self::SUB_MSG	=> $b->readASCF(), // sub_msg
						self::TYPE		=> $b->readASCF(), // type
					);
				}
			} catch (\Exception $e) {
				$b->offset = 0;
				$data = array(
					'total' => $b->readUInt(), 
					'strings' => array(), 
					'pkg' => '', 
					'rev' => 1
				);
				for ($i = 0; $i < $data['total']; ++$i) {
					$id = $b->readUInt();
					$data['strings'][$id] = array(
						self::ID		=> $id, // ID
						self::UNK		=> $b->readUInt(), // UNK
						self::MESSAGE	=> $b->readASCF(), // Message
						self::GROUP 	=> $b->readUInt(), // Group
						
						self::B		=> $b->readByte(), // B
						self::G		=> $b->readByte(), // G
						self::R		=> $b->readByte(), // R
						self::A		=> $b->readByte(), // A
						
						self::SOUND		=> $b->readUInt(), // item_sound
						self::SYS_REF	=> $b->readUInt(), // sys_msg_ref
						
						self::POSITION		=> $b->readUInt(), 
						self::UNK1			=> $b->readUInt(), 
						self::DURATION		=> $b->readUInt(), 
						self::DELAY_SPEED	=> $b->readUInt(), 
						self::HEAD			=> $b->readUInt(), 
						
						self::UNK2		=> $b->readASCF(), // UNK2
						self::UNK3		=> $b->readASCF(), // UNK3
						
						self::SUB_MSG	=> $b->readASCF(), // sub_msg
						self::TYPE		=> $b->readASCF(), // type
					);
				}
			}
			$data['pkg'] = $b->readASCF();
		}
		
		public static function save($b, &$data) {
			if ($data['rev'] == 1) {
				$b->writeUInt(count($data['strings']));
				foreach ($data['strings'] as &$m) {
					$b->writeUInt($m[self::ID]); // ID
					$b->writeUInt($m[self::UNK]); // UNK
					$b->writeASCF($m[self::MESSAGE]); // Message
					$b->writeUInt($m[self::GROUP]); // Group
					
					$b->writeByte($m[self::B]); // B
					$b->writeByte($m[self::G]); // G
					$b->writeByte($m[self::R]); // R
					$b->writeByte($m[self::A]); // A
					
					$b->writeUInt($m[self::SOUND]); // item_sound
					$b->writeUInt($m[self::SYS_REF]); // sys_msg_ref
					
					$b->writeUInt($m[self::POSITION]); 
					$b->writeUInt($m[self::UNK1]); 
					$b->writeUInt($m[self::DURATION]); 
					$b->writeUInt($m[self::DELAY_SPEED]); 
					$b->writeUInt($m[self::HEAD]); 
					
					$b->writeASCF($m[self::UNK2]); // UNK2
					$b->writeASCF($m[self::UNK3]); // UNK3
					$b->writeASCF($m[self::SUB_MSG]); // sub_msg
					$b->writeASCF($m[self::TYPE]); // type
				}
				$b->writeASCF($data['pkg']);
			} else {
				$b->writeUInt(count($data['strings']));
				foreach ($data['strings'] as &$m) {
					$b->writeUInt($m[self::ID]); // ID
					$b->writeUInt($m[self::UNK]); // UNK
					$b->writeASCF($m[self::MESSAGE]); // Message
					$b->writeUInt($m[self::GROUP]); // Group
					
					$b->writeByte($m[self::B]); // B
					$b->writeByte($m[self::G]); // G
					$b->writeByte($m[self::R]); // R
					$b->writeByte($m[self::A]); // A
					
					$b->writeASCF($m[self::SOUND]); // item_sound
					$b->writeASCF($m[self::SYS_REF]); // sys_msg_ref
					
					$b->writeUInt($m[self::POSITION]); 
					$b->writeUInt($m[self::UNK1]); 
					$b->writeUInt($m[self::DURATION]); 
					$b->writeUInt($m[self::DELAY_SPEED]); 
					$b->writeUInt($m[self::HEAD]); 
					
					$b->writeASCF($m[self::SUB_MSG]); // sub_msg
					$b->writeASCF($m[self::TYPE]); // type
				}
				$b->writeASCF($data['pkg']);
			}
		}
	}
