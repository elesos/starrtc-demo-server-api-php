<?php




function joinGroup($mdb, $groupId, $userId){
	$retArr   = array();		
	$userList = '';
	try{
		$sql = "select userList from groups where groupId = ? limit 1";
		if(!($pstmt = $mdb->prepare($sql))){
            $retArr['ret'] = 12;return $retArr;    
        } 
		if($pstmt->execute(array($groupId))){
			$result = $pstmt->fetchAll();        
			$resNum = count($result);
			if($resNum == 0){	
				$retArr['ret'] = 1;return $retArr;		
			}		
			$dbUserList   = $result[0][0];		
			if(!empty($dbUserList)){//判断是否已在群中	
				$dbUserListArr = explode(",", $dbUserList);	
				$pureDbUserListArr = array();
				foreach($dbUserListArr as $v){//去后缀标记
					$v = getPrefix($v);
					array_push($pureDbUserListArr, $v);
				}
				if(in_array($userId, $pureDbUserListArr)){//如果在群里，
					$retArr['ret'] = 0;return $retArr; 								
				}
				$userList = $dbUserList . ',' . $userId.'_0';			
			}else{
				$userList = $userId.'_0';						
			}	
			
		}else{
			$retArr['ret'] = 13;return $retArr;
		}
			 

		$sql = "select groupList from userGroup where userId = ? limit 1";
		if(!($pstmt = $mdb->prepare($sql))){
            $retArr['ret'] = 14;return $retArr;    
        } 		
		if($pstmt->execute(array($userId))){
			$result = $pstmt->fetchAll();        
			$resNum = count($result);
			
			if($resNum == 1){				
				$groupList = trim($result[0][0]);				
				if(!empty($groupList)){					
					//$groupArr = explode(",", $groupList);//判断用户是否已经入群，上面已经判断过一次了
					//if(in_array($groupId, $groupArr)){   //此时不判断了，不然还要把groupId后缀去掉
						//$retArr['ret'] = 2;return $retArr; 						
					//}
					$groupList = $groupList. ',' .$groupId.'_0';
				}else{	
					$groupList = $groupId.'_0';
				}
				$ret = update_user_group_by_userId($mdb, $groupList, $userId);
                if($ret != 0){
                    $retArr['ret'] = intval('17'.$ret);return $retArr;
                }
			}else{//记录不存在，没有加入任何群，插入
                $ret = insert_userGroup($mdb, $groupId, $userId);
                if($ret != 0){
                    $retArr['ret'] = intval('18'.$ret);return $retArr;
                }
			}

         
            $ret = updateGroup_by_add($mdb, $groupId, $userList);//更新群信息
            if($ret != 0){
                $retArr['ret'] = intval('20'.$ret);return $retArr;
            }
 
            $retArr['ret']  = 0;       
            return $retArr;
		}else{
			 $retArr['ret'] = 15;return $retArr;    
		}    
	}catch(PDOException $e){
		$retArr['ret'] = 11;return $retArr;
	}
	$retArr['ret'] = 10;return $retArr;
}




function quitGroup($mdb, $groupId, $userId){
	$retArr = array();
	//是否在群中，退出后要更新操作记录
	try{		
		//从群中删除此成员
		$sql = "select userList, creator from groups where groupId = ? limit 1";
		if(!($pstmt = $mdb->prepare($sql))){
			$retArr['ret'] = 21;return $retArr;
		}
		if($pstmt->execute(array($groupId))){
			$result = $pstmt->fetchAll();
			$resNum = count($result);
			if($resNum == 1){				
				$userList     = $result[0][0];
				$creator     = $result[0][1];
				if($userId == $creator){
					$retArr['ret'] = 30;return $retArr;
				}

				$userList = del_from_groupList($userList, $userId);
				$ret      = updateGroup_by_del($mdb, $groupId, $userList);//更新群
				if($ret != 0){
					return intval('20'.$ret);
				}		

			}else{
				$retArr['ret'] = 23;return $retArr;
			}
		}else{
			$retArr['ret'] = 22;return $retArr;
		}
				
		
		$sql = "select id, groupList from userGroup where userId = ? limit 1";
		if(!($pstmt = $mdb->prepare($sql))){
            $retArr['ret'] = 12;return $retArr;    
        } 
		if($pstmt->execute(array($userId))){ 
			$result = $pstmt->fetchAll();        
			$resNum = count($result);
            if($resNum == 0){
                $retArr['ret'] = 14;return $retArr;    ;//该用户未加入任何群
            }

            $tid        = intval($result[0][0]);
            $groupList  = trim($result[0][1]);
         
            if(empty($groupList)){
                $retArr['ret'] = 15;return $retArr;    ;//该用户未加入任何群,  也应该返回成功吧？
            }

            $ret = del_from_groupList_arr($groupList, $groupId);
            if($ret['ret'] != 0){
                if($ret['ret'] == 1){                   
                    $retArr['ret']   = 0;               
                    return $retArr;
                }
            }
            $groupList = $ret['data'];
            $ret = update_user_group($mdb, $groupList, $tid);
            if($ret != 0){
                $retArr['ret'] = intval('18'.$ret);return $retArr;
            }

            $retArr['ret']   = 0;			
			return $retArr;

		}else{
			$retArr['ret'] = 13;return $retArr;    
		}
	}catch(PDOException $e){
		$retArr['ret'] = 11;return $retArr;    
	}
	$retArr['ret'] = 10;return $retArr;    
}





function del_from_groupList_arr($groupList, $groupId){
    $retArr = array();
    $groupArr = explode(',', $groupList);
    $pureGroupArr = array();
    foreach($groupArr as $v){
        $v = getPrefix($v);
        array_push($pureGroupArr, $v);
    }
    $key      = array_search($groupId, $pureGroupArr);//没找到返回false
    if(FALSE === $key){//BUG,不能用if(!$key)作为判断，因为如果刚好序号为0，则会误报没有
        $retArr['ret'] = 1;//不在里面
        $retArr['data'] = $groupList;
        return $retArr;
    }else{
        array_splice($groupArr, $key, 1);		//从数组的第Start开始删除Len个元素；
        $groupList = implode(',', $groupArr);
    }
    $retArr['ret']  = 0;
    $retArr['data'] = $groupList;
    return $retArr;
}




//获取群成员,需要返回昵称userId
function getGroupList($mdb, $groupId){
	$retArr = array();
    try{
		$sql = "select userList from groups where groupId = ? limit 1";
		if(!($pstmt = $mdb->prepare($sql))){
            $retArr['ret'] = 12;return $retArr;
        } 
		if($pstmt->execute(array($groupId))){
			$result = $pstmt->fetchAll();        
			$resNum = count($result);
			if($resNum == 0){
				$retArr['ret'] = 14;return $retArr;
			}
		
			$userIdList = '';
			$ignoreList = '';			
			$userList = trim($result[0][0]);		
			if(!empty($userList)){				
				$memberArr = explode(",", $userList);
				$pureMemberArr = array();
				$ignoreListArr = array();
				foreach($memberArr as $v){					
					$prefix  = getPrefix($v);
					$suffix  = getSuffix($v);
					if($suffix & GROUP_DND ){					
						array_push($ignoreListArr, $prefix);
					}
					array_push($pureMemberArr, $prefix);
				}
				
				foreach ($pureMemberArr as $userId){					
					if(in_array($userId , $ignoreListArr)){
						$ignoreList = $userId .','. $ignoreList;		
					}
					$userIdList = $userId .','. $userIdList;								
				}
				$userIdList   = rtrim($userIdList, ',');//去掉结尾的逗号
				$ignoreList   = rtrim($ignoreList, ',');					
				
			}			
			$data = array();
			$data['groupList']  = $userIdList;				
			$data['ignoreList'] = $ignoreList;				
			$retArr['ret']  = 0;
			$retArr['data'] = $data;return $retArr;			
		}else{
			$retArr['ret'] = 13;return $retArr;//查询失败
		}         
    }catch(PDOException $e){       
		$retArr['ret'] = 11;return $retArr;
    }
	$retArr['ret'] = 10;return $retArr;
}







//从groupList中删除groupId
//有可能只有一个，删除后为空，外面不能根据empty进行判断
function del_from_groupList($groupList, $groupId){
    $groupArr = explode(',', $groupList);
    $pureMemberArr = array();
    foreach($groupArr as $v){
        $v = getPrefix($v);
        array_push($pureMemberArr, $v);
    }
    $key      = array_search($groupId, $pureMemberArr);//没找到返回false
    if(FALSE === $key){//不能用if(!$key)作为判断，因为如果刚好序号为0，则会误报没有

    }else{
        array_splice($groupArr, $key, 1);		//从数组的第Start开始删除Len个元素；
        $groupList = implode(',', $groupArr);
    }
    return $groupList;
}






//uidList 可以直接入库的用户列表
function updateGroup_by_del($familyShardMdb, $groupId, $uidList){
    try{
        $sql = "update `groups` set `userList` = ?, curNum=curNum-1 where `groupId` = ? limit 1";
        if(!($pstmt = $familyShardMdb->prepare($sql))){
            return 12;
        }
        if($pstmt->execute(array($uidList, $groupId))){
            return 0;
        }else{
            return 13;
        }
    }catch(PDOException $e){
        return 11;
    }
    return 10;
}


function updateGroup_by_add($mdb, $groupId, $userList){
    try{
		$sql = "update `groups` set `userList` = ?, curNum=curNum+1 where `groupId` = ? limit 1"; 
        if(!($pstmt = $mdb->prepare($sql))){
            return 12;
        }
        if($pstmt->execute(array($userList, $groupId))){
            return 0;
        }else{
            return 13;
        }
    }catch(PDOException $e){
        return 11;
    }
    return 10;
}

function update_user_group($familyShardMdb, $groupList, $userGroupTid){
    try{
        $sql = "update `userGroup` set `groupList` = ? where `id` = ?";
        if(!($pstmt = $familyShardMdb->prepare($sql))){
            return 12;
        }
        if($pstmt->execute(array($groupList, $userGroupTid))){
            return 0;
        }else{
            return 13;
        }
    }catch(PDOException $e){
        return 11;
    }
    return 10;
}

function update_user_group_by_userId($familyShardMdb, $groupList, $userId){
    try{       
		$sql = "update `userGroup` set `groupList` = ? where `userId` = ?";
        if(!($pstmt = $familyShardMdb->prepare($sql))){
            return 12;
        }
        if($pstmt->execute(array($groupList, $userId))){
            return 0;
        }else{
            return 13;
        }
    }catch(PDOException $e){
        return 11;
    }
    return 10;
}

function insert_userGroup($mdb, $groupId, $userId){
    try{
        $sql = "insert into userGroup (userId, groupList) values (?,?)";
        if(!($pstmt = $mdb->prepare($sql))){
            return 12;
        }
        $version = 1;
        if($pstmt->execute(array($userId, $groupId.'_0'))){
            return 0;
        }else{
            return 13;
        }
    }catch(PDOException $e){
        return 11;
    }
    return 10;
}


function del_group($mdb, $groupId){
    try{
        $sql = "delete from `groups` where `groupId` = ? limit 1";
        if(!($pstmt = $mdb->prepare($sql))){
            return 12;
        }
        if($pstmt->execute(array($groupId))){
            return 0;
        }else{
            return 13;
        }
    }catch(PDOException $e){
        return 11;
    }
    return 10;
}





//1，通知爱思，2，设置群成员列表里的用户状态，3，设置用户的群状态
function pushIgnore($groupId, $userId, $op){
    global $g_writeMdb;
	//echoDebug($groupId);
	//echoDebug($familyId);
	//echoDebug($uid);
	//echoDebug($op);
	try{
		$sql = "select userList from groups where groupId = ? limit 1";		
		if(!($pstmt = $g_writeMdb->prepare($sql))){
            return 12;
        } 
		if($pstmt->execute(array($groupId))){
			$result = $pstmt->fetchAll();        
			$resNum = count($result);
			if($resNum == 0){
				return 14;	//不存在		  
			}		
			$userIdList = '';	
			$userList   = trim($result[0][0]);
			if(!empty($userList)){
				$memberArr = explode(",", $userList);
				$pureMemberArr = array();				
				foreach($memberArr as $v){
					$v = getPrefix($v);
					array_push($pureMemberArr, $v);			
				}
		
				$key = array_search($userId, $pureMemberArr);//没找到返回false
				if(FALSE === $key){		//该用户不在群里					
					return 16;
				}
				//取出来
				$dbUserId = $memberArr[$key];
				$dbValue  = getSuffix($dbUserId);
				if($op == 0){//取消免打扰
					if($dbValue & GROUP_DND ){//如果已经是免打扰状态,要取消
						$dbValue = $dbValue & (~GROUP_DND);						
					}else{
						return 0;						
					}					
				}else if($op == 1){//设置免打扰
					if($dbValue & GROUP_DND ){//如果已经是免打扰状态
						return 0;
					}else{
						$dbValue = $dbValue| GROUP_DND;//设置成免打扰						
					}					
				}
				$dbUserId = $userId.'_'.$dbValue;
				array_splice($memberArr, $key, 1);		//从数组的第Start开始删除Len个元素；	
				array_push($memberArr, $dbUserId);	
				$uidList = implode(',', $memberArr);			
				//更新群成员
				$sql = "update `groups` set `userList` = ? where `groupId` = ?";
				if(!($pstmt = $g_writeMdb->prepare($sql))){
					return 17;    
				} 			 
				if($pstmt->execute(array($uidList, $groupId))){	
					
				}else{
					return 18;
				}						
				//设置用户的群状态
				$ret = updateUserGroupState($userId, $groupId, $op);
				if($ret != 0){
					return intval('19'.$ret);       
				}else{
					return 0;
				}				
			}else{
				return 15;
			}			  	
		}else{
			return 13; //查询失败
		}		
	}catch(PDOException $e){
		return 11;
	}	
	return 10;
}


function updateUserGroupState($userId, $groupId, $op){
    global $g_writeMdb;
	//是否在群中，退出后要更新操作记录
	try{
		$sql = "select id, groupList from userGroup where userId = ? limit 1";
		if(!($pstmt = $g_writeMdb->prepare($sql))){
            return 12;    
        }  
		if($pstmt->execute(array($userId))){ 
			$result = $pstmt->fetchAll();        
			$resNum = count($result);
			if($resNum == 0){	
				return 14;//该用户未加入任何群
			}		
			$tid     = intval($result[0][0]);
			$groupList        = trim($result[0][1]);				
			if(!empty($groupList)){					
				$groupArr = explode(',', $groupList);
				$prefixGroupArr = array();
				foreach($groupArr as $v){
					$v = getPrefix($v);
					array_push($prefixGroupArr, $v);
				}					
				$key      = array_search($groupId, $prefixGroupArr);//没找到返回false				
				if(FALSE === $key){//BUG,不能用if(!$key)作为判断，因为如果刚好序号为0，则会误报没有							
					//该用户未加入该群
					return 0;//没有
				}		
				$dbGroupId = $groupArr[$key];
				$dbValue  = getSuffix($dbGroupId);
				
				if($op == 0){//取消免打扰
					if($dbValue & GROUP_DND ){//如果已经是免打扰状态,要取消
						$dbValue = $dbValue & (~GROUP_DND);						
					}else{
						return 0;						
					}					
				}else if($op == 1){//设置免打扰
					if($dbValue & GROUP_DND ){//如果已经是免打扰状态
						return 0;
					}else{
						$dbValue = $dbValue| GROUP_DND;//设置成免打扰						
					}					
				}			
		
					
				$dbGroupId = $groupId.'_'.$dbValue;
				array_splice($groupArr, $key, 1);		//从数组的第Start开始删除Len个元素；	
				array_push($groupArr, $dbGroupId);	
				$groupList = implode(',', $groupArr);			
			
				$sql = "update `userGroup` set `groupList` = ? where `id` = ?";
				if(!($pstmt = $g_writeMdb->prepare($sql))){
					return 16;    
				}			
				if($pstmt->execute(array($groupList, $tid))){	
					return 0;
				}else{
					return 17;
				}  						
				
			}else{						
				//return 0;
				return 15;//该用户未加入任何群
			}		  
		}else{
			return 13;
		}
	}catch(PDOException $e){
		return 11;
	}
	return 10;	
}

