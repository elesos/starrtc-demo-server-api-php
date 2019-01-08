<?php

//查询聊天室是否存在 , 存在时返回0，并返回创建者
function isRoomIdExist($mdb, $roomId){ 	
	//先从缓存中判断	
	//TODO 用id作查询条件
	$retArr = array();
    try{
		//$sql = sprintf("select creatorLoginName, maxNum from %d_chatRoom where roomId = ? limit 1", $familyId);	
		$sql = "select userId, maxNum from chatRoom where roomId = ? limit 1";	
        if(!($pstmt = $mdb->prepare($sql))){           
            $retArr['ret'] = 12;return $retArr; 
        }			
		if($pstmt->execute(array($roomId))){	
			$result = $pstmt->fetchAll();        
			$resNum = count($result);
			if($resNum == 0){
				$retArr['ret'] = 14;return $retArr;//不存在
			}	
		
			$retArr['ret']  = 0;//存在			
			$retArr['data'] = $result[0][0].','.$result[0][1];//aec要求的格式			
			return $retArr;					
		}else{
			$retArr['ret'] = 13;return $retArr;
		}	       		
    }catch(PDOException $e){
		$retArr['ret'] = 11;return $retArr;
    } 	
	$retArr['ret'] = 10;return $retArr;	
}


function create_chat_room($mdb, $roomId, $roomName, $roomType, $userId, $conCurrentNumber){	
	$ctime = date('Y-m-d H:i:s'); 	

	
	try{	
		$sql = "insert into chatRoom (roomId, roomName, roomType, ctime, maxNum, userId, lastOnlineTime) values (?,?,?,?,?,?,?)";
		if(!($pstmt = $mdb->prepare($sql))){           
			return 13;    
		} 
		if($pstmt->execute(array($roomId, $roomName, $roomType, $ctime, $conCurrentNumber, $userId, time()))){		
			return 0;
		}else{
			return 14;
		}	
	}catch(PDOException $e){
		return 11;
	}	
	return 10;
}




//更新chatroom类型
function update_chatroom_live_type($mdb, $roomId, $liveType){
	try{
		$sql = "update chatRoom set liveType = ? where roomId = ? limit 1";
		if(!($pstmt = $mdb->prepare($sql))){ 
			return 12;
		}   
		if($pstmt->execute(array($liveType, $roomId))){
			return 0;	
		}else{
			return 13;
		}		
	}catch(PDOException $e){
		return 11;
	}	
    return 10;
}






function delete_chat_room($mdb, $roomId, $userId){
	//返回0成功,先删除聊天室，然后删除聊天室(roomId)关联的 channel
	//为了防止函数在不同地方 弄错上下文，尽量将参数判断放在函数内部，这样即使外部没有判断，传了错误的参数，也会检测到。
	//同时函数为了通用尽量别输出日志
	if(empty($roomId) || empty($userId) ){
		return 12;	
	}		
	//聊天室创建者才能删除,现在创建者是主播
	try{			
		$sql = "select id, liveType from chatRoom where userId = ? and roomId = ? limit 1";
		if(!($pstmt = $mdb->prepare($sql))){           
            return 13;    
        }
		if($pstmt->execute(array($userId, $roomId))){
			$result = $pstmt->fetchAll();        
			$resNum = count($result);
			if($resNum == 0){
				return 0;//聊天室不存在,默认删除成功
			}						
			$id        = intval($result[0][0]);
			$liveType  = intval($result[0][1]);
			
			$sql = "delete from `chatRoom` where `id` = ? limit 1";						
			if(!($pstmt = $mdb->prepare($sql))){           
				return 16;    
			}
			if($pstmt->execute(array($id))){
				$ret = update_list_by_chatroom_live_type($mdb, $roomId, $liveType);
				/* 	$ret = delChannelByOwnerId($mdb, $roomId, $familyId);//删除绑定的channelId
				*/	
				if($ret != 0){
					return intval('18'.$ret);
				}	
				return 0;	
			}else{
				return 17;//删除失败
			}	
		}else{
			return 14;
		} 				   
	}catch(PDOException $e){		
		return 11;
	}	
	return 10;
}


function update_list_by_chatroom_live_type($mdb, $roomId, $liveType){	
	$table_name = '';
	switch($liveType){
		case liveType_meeting:
			$table_name = 'meeting_lists';
			break;
		case liveType_live:
			$table_name = 'live_lists';
			break;
		default:
			$table_name = 'im_chatroom_lists';
			break;
	}	
	try{	
		$sql = sprintf("delete from %s where roomId = ? limit 1", $table_name);
		if(!($pstmt = $mdb->prepare($sql))){         
            return 12;    
        } 
		if($pstmt->execute(array($roomId))){			
			return 0;
		}else{
			return 13;
		}		
	}catch(PDOException $e){
		return 11;
	}	
	return 10;	
}

