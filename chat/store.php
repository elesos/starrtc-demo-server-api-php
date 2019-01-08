<?php

$dir = dirname(dirname(__FILE__));
require_once($dir . '/config.php');


//ID是channelId+chatroomId， 共32位,但是纯聊天室只有chatroomId,16位
$ID      = trimInput(array_key_exists('ID', $_REQUEST) ? $_REQUEST['ID'] : 0);
$Name    = trimInput(array_key_exists('Name', $_REQUEST) ? $_REQUEST['Name'] : 0);
$Creator = trimInput(array_key_exists('Creator', $_REQUEST) ? $_REQUEST['Creator'] : 0);

if(empty($ID) || empty($Name) || empty($Creator)){	 
	echoErr('missing args');
}	


$ret = store($g_writeMdb, $Name, $Creator, $ID);
if($ret != 0){
	echoErr('store:'.$ret);
}else{
	echoK('success');
}

function store($mdb, $Name, $Creator, $ID){	
	$ctime = date('Y-m-d H:i:s'); 	
	try{	
		$sql = "select id from im_chatroom_lists where roomId = ? limit 1";
		if(!($pstmt = $mdb->prepare($sql))){
			return 12;					
		}
		if($pstmt->execute(array($ID))){
			$result = $pstmt->fetchAll();        
			$resNum = count($result);	
			if($resNum == 1){			
				return 16;				
			}else{	
				$sql = "insert into im_chatroom_lists (roomId, name, userId, ctime) values (?,?,?,?)";
				if(!($pstmt = $mdb->prepare($sql))){           
					return 15;    
				} 
				if($pstmt->execute(array($ID, $Name, $Creator, $ctime))){				
					return 0;
				}else{
					return 14;
				}
			}			
		}else{
			return 13;
		}				
	}catch(PDOException $e){
		return 11;
	}	
	return 10;
}