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
