<?php



//更新群信息
function updateGroup($familyShardMdb, $uid, $groupId, $db_uidList, $ctime, $groupVersion){
	try{
		$sql = "update `114_familyGroup` set version = version + 1, `userList` = ?, curNum = curNum + 1 where `groupId` = ?";
		if(!($pstmt = $familyShardMdb->prepare($sql))){
			return 12;
		} 				
		if($pstmt->execute(array($db_uidList, $groupId))){
			$sql = "insert into 114_groupActions (groupId, version, addAction, ctime) values (?,?,?,?)";
			if(!($pstmt = $familyShardMdb->prepare($sql))){
				return 14;
			} 
			if($pstmt->execute(array($groupId, $groupVersion, $uid, $ctime))){					
				return 0;//成功
			}else{
				return 15;//添加群操作记录失败
			}						  
		}else{
			return 13;
		}
	}catch(PDOException $e){
		return 11;
	}
	return 10;
} 

