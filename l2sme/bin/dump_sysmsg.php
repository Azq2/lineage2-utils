<?php
	chdir(dirname(__FILE__));
	include '../core/init.php';
	
	use L2File\SystemMsg;
	
	$b = L2File::read($argv[1]);
	$total = $b->readUInt();
	
	echo "total: ".$total."\n";
	
	for ($i = 0; $i < $total; ++$i) {
		$id = $b->readUInt();
		printf(
			"id: %d\n".
			"UNK0: %08X\n".
			"Message: %s\n".
			"Group: %d\n".
			"Color: %02X%02X%02X%02X\n".
			"Item Sound: %s\n".
			"Sys Msg Ref: %s\n".
			"Position: %d\n".
			"UNK1: %d\n".
			"Duration: %d\n".
			"Delay: %d\n".
			"Head: %d\n".
			"Sub Msg: %s\n".
			"Type: %s\n".
			"--------------------------------------------\n", 
			$id, 
			$b->readUInt(), // UNK
			un_null($b->readASCF()), // Message
			$b->readUInt(), // Group
			
			$b->readByte(), // B
			$b->readByte(), // G
			$b->readByte(), // R
			$b->readByte(), // A
			
			un_null($b->readASCF()), // item_sound
			un_null($b->readASCF()), // sys_msg_ref
			
			$b->readUInt(), 
			$b->readUInt(), 
			$b->readUInt(), 
			$b->readUInt(), 
			$b->readUInt(), 
			
			un_null($b->readASCF()), // sub_msg
			un_null($b->readASCF())  // type
		);
	}
