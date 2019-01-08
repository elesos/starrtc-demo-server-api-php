<?php



function set_user_msg_push_mode($userShardMdb, $uid, $push_mode){
	try{
		$sql = "update i_user set msg_push_mode = ? where uid = ? limit 1";
		if(!($pstmt = $userShardMdb->prepare($sql))){ 
			return 12;
		}   
		if($pstmt->execute(array($push_mode, $uid))){
			return 0;	
		}else{
			return 13;
		}		
	}catch(PDOException $e){
		return 11;
	}	
    return 10;
}

 