<?php

function deleteGroup($mdb, $groupId, $creator){//op_uid操作用户
    //创建者才能删除，现在已改为agent能删除一切群
    try{
        $sql = "select userList from groups where groupId = ? and creator = ? limit 1";
        if(!($pstmt = $mdb->prepare($sql))){
            return 12;
        }
        if($pstmt->execute(array($groupId, $creator))){
            $result = $pstmt->fetchAll();
            $resNum = count($result);
            if($resNum == 0){
                return 14;
            }
            $userList = $result[0][0];         
            $ret = del_group($mdb, $groupId);   //删除群
            if($ret != 0){
                return intval('15'.$ret);
            }
            
            $pureMemberArr = array();
            if(!empty($userList)){
                $memberArr = explode(",", $userList);
                foreach($memberArr as $v){
                    $prefix  = getPrefix($v);
                    array_push($pureMemberArr, $prefix);
                }
              
                foreach ($pureMemberArr as $userId){
                    $ret = delUserFromUserGroup($mdb, $userId, $groupId);//出群, 如果要返回调用者的群版本，需要比较op_uid
                    if($ret != 0){
                        return intval('22'.$ret);
                    }
                }
            }
            return 0;
        }else{
            return 13;
        }
    }catch(PDOException $e){
        return 11;
    }
    return 10;
}


function delUserFromUserGroup($mdb, $userId, $groupId){	
	try{
		$sql = "select id, groupList from userGroup where userId = ? limit 1";
		if(!($pstmt = $mdb->prepare($sql))){          
            return 12;    
        }  
		if($pstmt->execute(array($userId))){ 
			$result = $pstmt->fetchAll();        
			$resNum = count($result);
            if($resNum == 0){
                return 0;//return 14;//该用户未加入任何群
            }

            $tid              = $result[0][0];
            $groupList        = trim($result[0][1]);           
                           
            if(empty($groupList)){
                return 0;
            }
            $groupList = del_from_groupList($groupList, $groupId);			
            $sql = "update `userGroup` set `groupList`=? where `id` = ?";
            if(!($pstmt = $mdb->prepare($sql))){
                return 16;
            }
            if($pstmt->execute(array($groupList, $tid))){            
                return 0;          
            }else{
                return 17;
            }
		}else{
			return 13;
		}
	}catch(PDOException $e){
		return 11;
	}
	return 10;	
}