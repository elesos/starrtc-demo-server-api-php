<?php






//创建群时选的主播列表和好友列表是互斥的。
//是将参数判断放在函数里呢，还是函数里只负责函数的主要逻辑。

//外面要去重 而且保证userList包含actorList和creator

//new:
//addUsers里是需要添加的人，其它不用管,sdk需要将创建者放在里面 
function createGroup($mdb, $groupId, $groupName, $creator, $addUsers){	
	$users_arr = explode(",", $addUsers);
	$curNum = count($users_arr);	
    $ctime  = date('Y-m-d H:i:s');
	
	$userList_suffix = addSuffix($addUsers);
	
    try{
        $sql = "insert into groups (groupId, groupName, userList, creator, ctime, curNum) values (?,?,?,?,?,?)";
        if(!($pstmt = $mdb->prepare($sql))){
            return 12;
        }
        if($pstmt->execute(array($groupId, $groupName, $userList_suffix,  $creator, $ctime, $curNum))){			
			foreach($users_arr as $userId){
				$ret = updateUserGroupList($mdb, $userId, $groupId);
				if($ret['ret'] != 0){
					return intval('16'.$ret['ret']);
				}				
			}
            return 0;
        }else{
            return 13;	//创建群失败
        }
    }catch(PDOException $e){
        return 11;
    }
    return 10;
}



function updateUserGroupList($mdb, $userId, $groupId){
    try{       
        $sql = "select groupList from userGroup where userId = ? limit 1";
        if(!($pstmt = $mdb->prepare($sql))){
            return 12;
        }
        if($pstmt->execute(array($userId))){
            $result = $pstmt->fetchAll();
            $resNum = count($result);
            if($resNum == 1){
                $groupList = trim($result[0][0]);  

                if(!empty($groupList)){
                    $groupList = $groupList. ',' .$groupId.'_0';
                }else{
                    $groupList =                  $groupId.'_0';
                }
    
                $sql = "update `userGroup` set `groupList` = ? where `userId` = ?";
                if(!($pstmt = $mdb->prepare($sql))){
                    return 14;
                }
                if($pstmt->execute(array($groupList, $userId))){
					return 0;
                }else{
                    return 15;
                }
            }else{//记录不存在，插入
                $sql = "insert into userGroup (userId, groupList) values (?,?)";
                if(!($pstmt = $mdb->prepare($sql))){
                    return 16;
                }
                if($pstmt->execute(array($userId,  $groupId.'_0'))){
					return 0;
                }else{
                    return 17; //记录 用户群列表 失败
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
