<?php

//channelType:GLOBAL_PUBLIC, 
//liveType:liveType_live, 
//ownerType: ROOM_CHANNEL, 
function create_channel($mdb, $channelId, $channelType, $liveType, $ownerId, $ownerType, $specify, $extra, $conNum, $userId){	
	$ctime = date('Y-m-d H:i:s'); 	
	
	try{	
		$sql = "insert into channels (channelId, channelType, liveType, ownerId, ownerType, specify, extra, ctime, conNum, userId, lastOnlineTime) values (?,?,?,?,?,?,?,?,?,?,?)";
		if(!($pstmt = $mdb->prepare($sql))){           
            return 13;    
        } 
		if($pstmt->execute(array($channelId, $channelType, $liveType, $ownerId, $ownerType, $specify, $extra, $ctime, $conNum, $userId, time()))){				
			return 0;
		}else{
			return 14;
		}		
	}catch(PDOException $e){
		return 11;
	}	
	return 10;
}






function deleteChannel($mdb, $channelId, $userId){			
	try{	
		$sql = "delete from `lives` where `channelId` = ? and userId = ? limit 1";			
		if(!($pstmt = $mdb->prepare($sql))){         
            return 12;    
        } 
		if($pstmt->execute(array($channelId, $userId))){			
			return 0;
		}else{
			return 13;
		}		
	}catch(PDOException $e){
		return 11;
	}	
	return 10;	
}

//根据拥有者的id,删除channel
//ownerId = 群id(数字) 或 roomId(字符串)
//TODO :用id作查询条件
function delChannelByOwnerId($mdb, $ownerId, $familyId){		
	try{	
		$sql = sprintf("delete from %d_liveChannel where ownerId = ? limit 1", $familyId);
		if(!($pstmt = $mdb->prepare($sql))){         
            return 12;    
        } 
		if($pstmt->execute(array($ownerId))){			
			return 0;
		}else{
			return 13;
		}		
	}catch(PDOException $e){
		return 11;
	}	
	return 10;	
}



function get_channel_info($mdb, $channelId){
    $retArr = array();
    try{  
        $sql = "select id, conNum, userId, liveType, ownerId from channels where channelId = ? limit 1";
        if(!($pstmt = $mdb->prepare($sql))){
            $retArr['ret'] = 12;return $retArr;
        }
        if($pstmt->execute(array($channelId))){
            $result = $pstmt->fetchAll();
            $resNum = count($result);
            if($resNum == 0){
                $retArr['ret']  = 14;
                $retArr['data'] = 0;
                return $retArr;//查找不到，说明已删除,可能已经调用了delete后面又调close
            }
			
            $channelInfo = array();
            $channelInfo['id']       = $result[0][0];
            $channelInfo['conNum']   = $result[0][1];
            $channelInfo['userId']   = $result[0][2];
            $channelInfo['liveType'] = $result[0][3];
			$channelInfo['ownerId']  = $result[0][4];
            $retArr['ret']  = 0;
            $retArr['data'] = $channelInfo;
            return $retArr;
        }else{
            $retArr['ret'] = 13;return $retArr;
        }
    }catch(PDOException $e){
        $retArr['ret'] = 11;return $retArr;
    }
    $retArr['ret'] = 10;return $retArr;
}

function update_live_state($mdb, $channelId, $liveState){	
	$ret = update_lists_live_state($mdb, $channelId, $liveState);
	if($ret != 0){
		return intval('20'.$ret);
	}
	
	$ret = update_audio_lists_live_state($mdb, $channelId, $liveState);
	if($ret != 0){
		return intval('30'.$ret);
	}
	
	$ret = update_channel_live_state($mdb, $channelId, $liveState);
	if($ret != 0){
		return intval('40'.$ret);
	}
	$ret = update_chatroom_last_ts($mdb, $channelId);
	if($ret != 0){
		return intval('50'.$ret);
	}
    return 0;
}

//更新直播列表里面的状态
function update_lists_live_state($mdb, $channelId, $liveState){	
	try{
		$sql = "update live_lists set liveState = ? where channelId = ? limit 1";
		if(!($pstmt = $mdb->prepare($sql))){ 
			return 12;
		}   
		if($pstmt->execute(array($liveState,  $channelId))){
			return 0;	
		}else{
			return 13;
		}		
	}catch(PDOException $e){
		return 11;
	}	
    return 10;
}

function update_audio_lists_live_state($mdb, $channelId, $liveState){	
	try{
		$sql = "update audio_lists set liveState = ? where channelId = ? limit 1";
		if(!($pstmt = $mdb->prepare($sql))){ 
			return 12;
		}   
		if($pstmt->execute(array($liveState,  $channelId))){
			return 0;	
		}else{
			return 13;
		}		
	}catch(PDOException $e){
		return 11;
	}	
    return 10;
}

function update_channel_live_state($mdb, $channelId, $liveState){	
	$last_online_ts = time();
	try{
		$sql = "update channels set liveState = ?, lastOnlineTime = ? where channelId = ? limit 1";
		if(!($pstmt = $mdb->prepare($sql))){ 
			return 12;
		}   
		if($pstmt->execute(array($liveState, $last_online_ts, $channelId))){
			return 0;	
		}else{
			return 13;
		}		
	}catch(PDOException $e){
		return 11;
	}	
    return 10;
}

function update_chatroom_last_ts($mdb, $channelId){		
	$last_online_ts = time();
	try{
		$ret = get_channel_info($mdb, $channelId);
		if($ret['ret'] != 0){
			return intval('20'.$ret['ret']);
		}		
		$roomId = $ret['data']['ownerId'];
		$sql = "update chatRoom set lastOnlineTime = ? where roomId = ? limit 1";
		if(!($pstmt = $mdb->prepare($sql))){ 
			return 12;
		}   
		if($pstmt->execute(array($last_online_ts, $roomId))){
			return 0;	
		}else{
			return 13;
		}		
	}catch(PDOException $e){
		return 11;
	}	
    return 10;
}