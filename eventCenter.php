<?php
//AEC接口  http://docs.starrtc.com/zh-cn/docs/aec-index.html 
//只能返回特定格式json:{"status":"x","data":"xxx"}
//其中status只能返回0和1，其中0表示失败

$aec_dir = dirname(__FILE__);
require_once($aec_dir . '/config.php');
require_once($aec_dir . '/include/group_create.php');
require_once($aec_dir . '/include/group_del.php');
require_once($aec_dir . '/include/group.php');
require_once($aec_dir . '/include/user.php');
require_once($aec_dir . '/include/channel.php');
require_once($aec_dir . '/include/room.php');





define('channelType_GROUP_PUBLIC',  5);
define('channelType_GROUP_SPECIFY', 1);

define('channelType_LOGIN_SPECIFY', 2);
define('channelType_GLOBAL_PUBLIC', 3);
define('channelType_LOGIN_PUBLIC',  4);



define('CHAT_ROOM_TYPE_PUBLIC', 1);
define('CHAT_ROOM_TYPE_LOGIN',  2);


	
define('NO_LIVE',  0);//无直播, 目前无此状态了
define('HAS_LIVE', 1);//有直播
define('LIVE_OFF', 2);//主播离开了, 目前无此状态了


define('MSG_PUSH_MODE_UNKNOW',        0);//未知（暂未获取到用户pushMode的状态，不推送）
define('MSG_PUSH_MODE_ALL_OFF',       1);//关闭所有推送
define('MSG_PUSH_MODE_ALL_ON',  	  2); //开启所有推送
define('MSG_PUSH_MODE_ONLY_CALLING',  3);//仅开启推送voip通话请求信息


$data = rawurldecode(array_key_exists('data', $_REQUEST) ? $_REQUEST['data'] : 0);
$sign = rawurldecode(array_key_exists('sign', $_REQUEST) ? $_REQUEST['sign'] : 0);
if(empty($data) || empty($sign)){
	echoMsg(0, 'missing args');
}

log_to_file($data);

$dataArr = json_decode($data, TRUE);

if(!is_array($dataArr)){
	echoMsg(0, 'json is invalid');
}

$signstr = generateSign($data, guardToken);

if(strcasecmp($sign, $signstr)){
	echoMsg(0, 'invalid sign');
}

$action = trimInput(array_key_exists('action', $dataArr) ? $dataArr['action'] : 0);
if(empty($action)){
	echoMsg(0, 'action为空');
}


$groupId   = trimInput(array_key_exists('groupId', $dataArr) ? $dataArr['groupId'] : 0);
$roomId    = array_key_exists('roomId', $dataArr) ? $dataArr['roomId'] : 0;
$userId    = array_key_exists('userId', $dataArr) ? $dataArr['userId'] : 0;
$channelId = array_key_exists('channelId', $dataArr) ? $dataArr['channelId'] : 0;

if($action == 'AEC_ACCESS_VALIDATION'){//接入验证	
	echoMsg(1, $dataArr['echostr']); 	
}


/////////////////////////////////////////////群事件通知/////////////////////////////////////////////
/////////////////////////////////////////////群事件通知/////////////////////////////////////////////
/////////////////////////////////////////////群事件通知/////////////////////////////////////////////
/*
{"action":"AEC_GROUP_CREATE","userId":"226967","addUsers":"226967","userDefineData":"12345"}
*/
if(!strcasecmp($action, 'AEC_GROUP_CREATE')){//创建群
	$userDefineData = array_key_exists('userDefineData',   $dataArr) ? $dataArr['userDefineData'] : '';
	$addUsers       = array_key_exists('addUsers',   $dataArr) ? $dataArr['addUsers'] : '';
	
	$groupName      = $userDefineData;		//demo里我们只传了群名	
	
	$ret = getGroupSeqId($g_writeMdb);//获取群号
	if($ret['ret'] != 0){	
		echoMsg(0, 'getGroupSeqId_failed:'.$ret['ret']);
	}
	$groupId = $ret['data'];	

	$ret = createGroup($g_writeMdb, $groupId, $groupName, $userId, $addUsers);
	if($ret['ret'] != 0){	
		echoMsg(0, 'createGroup failed:'.$ret['ret']); 		
	}else{
		echoMsg(1, $groupId); 	
	}	
}

if(!strcasecmp($action, 'AEC_GROUP_DEL')){//删除群
	$ret = deleteGroup($g_writeMdb, $groupId, $userId);	
	if($ret != 0){	
		echoMsg(0, 'deleteGroup_failed:'.$ret); 		
	}else{
		echoMsg(1); 	
	}	
}
 
if(!strcasecmp($action, 'AEC_GROUP_ADD_USER')){//新增群成员
	$addUsers   = array_key_exists('addUsers',   $dataArr) ? $dataArr['addUsers'] : '';	
	//TODO：您可以判断userId是不是群里面的成员，如果不是群里面的成员，可以不让其邀请别人进群
	//或者判断userId是不是群的创建者，可以实现只允许创建者邀请别人进群
	$users_arr = explode(",", $addUsers);
	foreach($users_arr as $userId){
		$ret = joinGroup($g_writeMdb, $groupId, $userId);
		if($ret['ret'] != 0){	
			echoMsg(0, 'join group failed:'.$ret['ret']); 		
		}	
	}
	echoMsg(1); 	
}

if($action == 'AEC_GROUP_SYNC_ALL'){//同步全部群成员
	if(empty($groupId)){
		echoMsg(0, 'groupId is empty');
	}	
	$ret = getGroupList($g_readMdb, $groupId);
	if($ret['ret'] != 0){
		echoMsg(0, 'get GroupList err');	
	}

	$data = array();
	if(!empty($ret['data']['groupList'])){		
		$data['groupList']  = $ret['data']['groupList'];
	}
	if(!empty($ret['data']['ignoreList'])){
		$data['ignoreList'] = $ret['data']['ignoreList'];
	}	
	echoMsg(1, urlencode($data));	
}

if(!strcasecmp($action, 'AEC_GROUP_ADD_USER_AND_SYNC_ALL')){//新增群成员并同步全部群成员
	$addUsers   = array_key_exists('addUsers',   $dataArr) ? $dataArr['addUsers'] : '';	
	//TODO：您可以判断userId是不是群里面的成员，如果不是群里面的成员，可以不让其邀请别人进群
	//或者判断userId是不是群的创建者，可以实现只允许创建者邀请别人进群
	$users_arr = explode(",", $addUsers);
	foreach($users_arr as $userId){
		$ret = joinGroup($g_writeMdb, $groupId, $userId);
		if($ret['ret'] != 0){	
			echoMsg(0, 'join group failed:'.$ret['ret']); 		
		}	
	}
	
	
	$ret = getGroupList($g_readMdb, $groupId);
	if($ret['ret'] != 0){
		echoMsg(0, 'get GroupList err');	
	}

	$data = array();
	if(!empty($ret['data']['groupList'])){		
		$data['groupList']  = $ret['data']['groupList'];
	}
	if(!empty($ret['data']['ignoreList'])){
		$data['ignoreList'] = $ret['data']['ignoreList'];
	}		
	
	echoMsg(1, $data);	
}

if(!strcasecmp($action, 'AEC_GROUP_REMOVE_USER')){//删除群成员
	$removeUsers   = array_key_exists('removeUsers',   $dataArr) ? $dataArr['removeUsers'] : '';	
	$users_arr = explode(",", $removeUsers);
	//TODO 检查userId是不是群主,如果不是可以不让退出群
	foreach($users_arr as $userId){
		$ret = quitGroup($g_writeMdb, $groupId, $userId);
		if($ret['ret'] != 0){	
			echoMsg(0, 'failed:'.$ret['ret']); 		
		}
		
	}
	echoMsg(1); 		
}

if(!strcasecmp($action, 'AEC_GROUP_REMOVE_USER_AND_SYNC_ALL')){//删除群成员并同步全部群成员
	$removeUsers = array_key_exists('removeUsers',   $dataArr) ? $dataArr['removeUsers'] : '';	
	$users_arr   = explode(",", $removeUsers);
	//TODO 检查userId是不是群主,如果不是可以不让退出群
	foreach($users_arr as $userId){
		$ret = quitGroup($g_writeMdb, $groupId, $userId);
		if($ret['ret'] != 0){	
			echoMsg(0, 'join group failed:'.$ret['ret']); 		
		}
		
	}
	
	$ret = getGroupList($g_readMdb, $groupId);
	if($ret['ret'] != 0){
		echoMsg(0, 'get GroupList err');	
	}

	$data = array();
	if(!empty($ret['data']['groupList'])){		
		$data['groupList']  = $ret['data']['groupList'];
	}
	if(!empty($ret['data']['ignoreList'])){
		$data['ignoreList'] = $ret['data']['ignoreList'];
	}		
	echoMsg(1, urlencode($data));	
}

if(!strcasecmp($action, 'AEC_SET_GROUP_PUSH_IGNORE')){//设置免打扰
	$ret = pushIgnore($groupId, $userId, 1);
	if($ret != 0){
		echoMsg(0, 'pushIgnore failed');
	}
	echoMsg(1);
}

if(!strcasecmp($action, 'AEC_UNSET_GROUP_PUSH_IGNORE')){// 取消免打扰
	$ret = pushIgnore($groupId, $userId, 0);
	if($ret != 0){
		echoMsg(0, 'pushIgnore failed');
	}
	echoMsg(1);
}

if(!strcasecmp($action, 'AEC_UNSET_GROUP_PUSH_IGNORE_AND_SYNC_ALL')){//取消免打扰并同步群成员
	$ret = pushIgnore($groupId, $userId, 0);
	if($ret != 0){
		echoMsg(0, 'pushIgnore failed');
	}
	$ret = getGroupList($g_readMdb, $groupId);
	if($ret['ret'] != 0){
		echoMsg(0, 'get GroupList err');	
	}

	$data = array();
	if(!empty($ret['data']['groupList'])){		
		$data['groupList']  = $ret['data']['groupList'];
	}
	if(!empty($ret['data']['ignoreList'])){
		$data['ignoreList'] = $ret['data']['ignoreList'];
	}
		
	echoMsg(1, urlencode($data));	
	
}

if(!strcasecmp($action, 'AEC_SET_GROUP_PUSH_IGNORE_AND_SYNC_ALL')){//设置免打扰并同步群成员
	$ret = pushIgnore($groupId, $userId, 1);
	if($ret != 0){
		echoMsg(0, 'pushIgnore failed');
	}
	$ret = getGroupList($g_readMdb, $groupId);
	if($ret['ret'] != 0){
		echoMsg(0, 'get GroupList err');	
	}

	$data = array();
	if(!empty($ret['data']['groupList'])){		
		$data['groupList']  = $ret['data']['groupList'];
	}
	if(!empty($ret['data']['ignoreList'])){
		$data['ignoreList'] = $ret['data']['ignoreList'];
	}
		
	echoMsg(1, urlencode($data));	
}



//===============================聊天室事件通知===============================
//===============================聊天室事件通知===============================
//===============================聊天室事件通知===============================

if($action == 'AEC_CHATROOM_CREATE'){//新建聊天室请求		
	$roomType         = array_key_exists('roomType',         $dataArr) ? $dataArr['roomType'] : '';
	$conCurrentNumber = array_key_exists('conCurrentNumber', $dataArr) ? $dataArr['conCurrentNumber'] : '';
	//目前自定义字段里面传的是名称
	$roomName         = array_key_exists('userDefineData',   $dataArr) ? $dataArr['userDefineData'] : '';
	if(empty($roomType) || empty($conCurrentNumber) || empty($roomName)){
		echoMsg(0, 'invalid args');
	}	
	
	if(!strcasecmp($roomType,      'CHAT_ROOM_TYPE_PUBLIC')){
		$roomType = CHAT_ROOM_TYPE_PUBLIC;
	}elseif(!strcasecmp($roomType, 'CHAT_ROOM_TYPE_LOGIN')){
		$roomType = CHAT_ROOM_TYPE_LOGIN;	
	}
	//TODO 可以检查userId的用户权限，以确定是否有权限创建聊天室
	$ret = create_chat_room($g_writeMdb, $roomId, $roomName, $roomType, $userId, $conCurrentNumber);
	if($ret != 0){	
		echoMsg(0);
	}else{	
		echoMsg(1);
	}	
}

if($action == 'AEC_CHATROOM_DELETE'){//删除(关闭)聊天室	
	$ret = delete_chat_room($g_writeMdb, $roomId, $userId);
	if($ret != 0){	
		echoMsg(0, $ret);
	}	
	echoMsg(1);
	
}

if($action == 'AEC_CHATROOM_IS_EXIST'){	//查询聊天室是否存在
	$ret = isRoomIdExist($g_readMdb, $roomId);
	if($ret['ret'] != 0){	
		if($ret['ret'] == 14){
			echoMsg(0, "chatroom($roomId) is not exist in db");
		}
		echoMsg(0, $ret['ret']);
	}else{
		echoMsg(1, urlencode($ret['data']));		
	}	
}




//////////////////////////////一对一，voip事件通知///////////////////
//////////////////////////////一对一，voip事件通知///////////////////
//////////////////////////////一对一，voip事件通知///////////////////

if(!strcasecmp($action, 'AEC_VOIP_USER_ONLINE')){//申请voip通话
	//userId 发起方，userId2 接听方
	//TODO 可检查用户余额是否足够   
    echoMsg(1, '成功上线');

}
if(!strcasecmp($action, 'AEC_VOIP_USER_PLAYING')){//voip通话正在进行中，每分钟调用一次
	//TODO 用户通话时会每隔1分钟回调一次
	//可以在此对用户的余额进行判断，如果余额不足，可以返回0断开一对一通话
    echoMsg(1, '成功playing');	
}

if(!strcasecmp($action, 'AEC_VOIP_USER_HANGUP')){//voip挂断	
	echoMsg(1, 'AEC_VOIP_USER_HANGUP');	
}




//////////////////////////直播事件通知///////////////////////////
//////////////////////////直播事件通知///////////////////////////
//////////////////////////直播事件通知///////////////////////////

if($action == 'AEC_LIVE_USER_ONLINE'){//用户开始观看直播
	//TODO:
	//1,获取channelId
	//2,判断是群直播还是其它直播，然后按各自的要求进行处理，如检查余额	
	echoMsg(1, 'AEC_LIVE_USER_ONLINE');			
}

if($action == 'AEC_LIVE_USER_PLAYING'){	//用户观看直播中，每分钟调用一次
	//TODO:
	//1,获取channelId
	//2,判断是群直播还是其它直播，然后按各自的要求进行处理，如扣费	
	//返回1代表此用户还可以继续观看直播，返回0表示因某种原因不能观看了	
	echoMsg(1, 'AEC_LIVE_USER_PLAYING');
}

if($action == 'AEC_LIVE_USER_OFFLINE'){	//用户停止观看直播
	echoMsg(1, 'offline success');
}

if(!strcasecmp($action, 'AEC_LIVE_CREATE_CHANNEL_GROUP_PUBLIC')){//创建GROUP_PUBLIC直播流
	//TODO 可以检查用户权限，如只让群创建者发起直播
	echoMsg(0, 'developing'); 
}

if(!strcasecmp($action, 'AEC_LIVE_CREATE_CHANNEL_GROUP_SPECIFY')){//创建GROUP_SPECIFY直播流,6个参数	
	echoMsg(0, 'developing'); 		
}

if(!strcasecmp($action, 'AEC_LIVE_CREATE_CHANNEL_GLOBAL_PUBLIC')){	
  	$channelId        = trimInput(array_key_exists('channelId', $dataArr) ? $dataArr['channelId'] : '');
	$conCurrentNumber = trimInput(array_key_exists('conCurrentNumber', $dataArr) ? $dataArr['conCurrentNumber'] : '');
	if(empty($channelId) || empty($conCurrentNumber)){
		echoMsg(0, 'invalid args'); 
	}	

	
	if($conCurrentNumber > channel_conn_limit || $conCurrentNumber <= 0){		
		echoMsg(0, 'conCurrentNumber超出限制'); 
	}
	$roomLiveType  = intval(trimInput(array_key_exists('roomLiveType', $dataArr) ? $dataArr['roomLiveType'] : 0));//可选
	$specify       = 0;
	$extra         = '';	
    $userId        = trimInput(array_key_exists('userId', $dataArr) ? $dataArr['userId'] : '');
	
	
	if($roomLiveType == 1){	
		$liveType = liveType_meeting;			
	}elseif($roomLiveType == 2){
		$liveType = liveType_live;			
	}else{
		echoMsg(0, 'invalid roomLiveType value:'.$roomLiveType);
	}	
	
	//check roomId
	$len = strlen($roomId);
	if($len != 16){
		echoMsg(0, 'invalid roomId:'.$roomId);		
	}
	
	$ret = create_channel($g_writeMdb, $channelId, channelType_GLOBAL_PUBLIC, $liveType, $roomId, ownerType_ROOM_CHANNEL, $specify, $extra, $conCurrentNumber, $userId);    
	if($ret != 0){		
		echoMsg(0, 'create channel:'.$ret);
	}
	$ret = update_chatroom_live_type($g_writeMdb, $roomId, $liveType);
	if($ret != 0){		
		echoMsg(0, 'update_chatroom_live_type:'.$ret);
	}
	echoMsg(1);
}

if(!strcasecmp($action, 'AEC_LIVE_CREATE_CHANNEL_LOGIN_PUBLIC')){		
	$channelId        = trimInput(array_key_exists('channelId', $dataArr) ? $dataArr['channelId'] : '');
	$conCurrentNumber = trimInput(array_key_exists('conCurrentNumber', $dataArr) ? $dataArr['conCurrentNumber'] : '');
	if(empty($channelId) || empty($conCurrentNumber)){
		echoMsg(0, 'invalid args'); 
	}	
	
	if($conCurrentNumber > channel_conn_limit || $conCurrentNumber <= 0){		//TODO
		echoMsg(0, 'conCurrentNumber超出限制'); 
	}
	$roomLiveType     = intval(trimInput(array_key_exists('roomLiveType', $dataArr) ? $dataArr['roomLiveType'] : 0));//可选
	$specify          = 0;
	$extra            = '';	
    $userId			  = trimInput(array_key_exists('userId', $dataArr) ? $dataArr['userId'] : '');
	
	
	if($roomLiveType == 1){	
		$liveType = liveType_meeting;			
	}elseif($roomLiveType == 2){
		$liveType = liveType_live;			
	}else{
		echoMsg(0, 'invalid roomLiveType value:'.$roomLiveType);
	}	
	
	//check roomId
	$len = strlen($roomId);
	if($len != 16){
		echoMsg(0, 'invalid roomId:'.$roomId);		
	}
	
	$ret = create_channel($g_writeMdb, $channelId, channelType_LOGIN_PUBLIC, $liveType, $roomId, ownerType_ROOM_CHANNEL, $specify, $extra, $conCurrentNumber, $userId);    
	if($ret != 0){		
		echoMsg(0, 'create channel:'.$ret);
	}
	$ret = update_chatroom_live_type($g_writeMdb, $roomId, $liveType);
	if($ret != 0){		
		echoMsg(0, 'update_chatroom_live_type:'.$ret);
	}	
	echoMsg(1);
}

if(!strcasecmp($action, 'AEC_LIVE_CREATE_CHANNEL_LOGIN_SPECIFY')){		
	echoMsg(1, 'developing'); 	
}

if($action == 'AEC_LIVE_DELETE_CHANNEL'){//删除直播流	
	//TODO：检查userId是否为channel的创建者,以决定是否有权限删除
	//删除channel	
	$ret = deleteChannel($g_writeMdb, $channelId, $userId);
	if($ret != 0){					  	
		echoMsg(0);				
	}else{
		echoMsg(1);
	} 
}

if($action == 'AEC_LIVE_CLOSE_CHANNEL'){//关闭直播流
	//TODO： 检查权限
	echoMsg(1);	
}

if($action == 'AEC_LIVE_APPLY_DOWNLOAD_CHANNEL'){//申请下载直播流	
	$channelId = trimInput(array_key_exists('channelId', $dataArr) ? $dataArr['channelId'] : 0);
	if(empty($channelId)){
		echoMsg(0, 'channelId is empty');
	}	
	echoMsg(1);
}

if($action == 'AEC_LIVE_SET_CHANNEL_UPLOADER'){//权限验证: 申请设置上传者
	//TODO：检查权限，返回1表示通过
	echoMsg(1);	
	
}

if($action == 'AEC_LIVE_UNSET_CHANNEL_UPLOADER'){//取消上传权限
	echoMsg(1);	
}

if($action == 'AEC_LIVE_APPLY_UPLOAD_CHANNEL'){//申请上传直播流
	$channelId = trimInput(array_key_exists('channelId', $dataArr) ? $dataArr['channelId'] : 0);
    $userId    = trimInput(array_key_exists('userId', $dataArr) ? $dataArr['userId'] : 0);    
	if(empty($channelId) || empty($userId)){
		echoMsg(0, 'channelId is empty');
	}		
	
	$ret = get_channel_info($g_readMdb, $channelId);
	if($ret['ret'] != 0){
	    if($ret['ret'] == 14){
            echoMsg(0, 'channel not exist');
        }
		echoMsg(0, 'get_channel_info_failed:'.$ret['ret']);
	}

	$channelInfo                = $ret['data'];	
	
	$liveType                   = $channelInfo['liveType'];
	if($liveType == liveType_live){//直播必须是主播才能上传
		$creator                = $channelInfo['userId'];	
		if(strcasecmp($creator, $userId)){
			echoMsg(0, 'userId not the live creator');
		}		
	}
	$retArr = array();	
    $retArr['conCurrentNumber'] = $channelInfo['conNum'];
    echoMsg(1, $retArr);
}

if($action == 'AEC_LIVE_UPLOADER_UPLOADING'){//上传者正在上传中，每分钟会调用一次此事件 
	//TODO
	/*
	1,获取channelId
	2,视频服务没有和业务绑定,需要根据channelId判断该channel是属于群的直播流还是属于其它的直播流
    3,更新直播状态
	*/	
	$channelId = trimInput(array_key_exists('channelId', $dataArr) ? $dataArr['channelId'] : 0);
	if(empty($channelId)){
		echoMsg(0, 'channelId is empty');
	}
	$ret = update_live_state($g_writeMdb, $channelId, HAS_LIVE);//如果有新的列表，需要新加一个更新列表的
	if($ret != 0){
		echoMsg(0, 'update_live_state_failed');
	}	
	echoMsg(1);
}

if($action == 'AEC_LIVE_UPLOADER_DISCONNECT'){//上传者断开连接（离开）
	//TODO 
	// 检查是不是自己的channel    	
	$channelId = trimInput(array_key_exists('channelId', $dataArr) ? $dataArr['channelId'] : 0);
	$userId    = trimInput(array_key_exists('userId', $dataArr) ? $dataArr['userId'] : 0);    
	if(empty($channelId)){
		echoMsg(0, 'channelId is empty');
	}
	
	$ret = get_channel_info($g_readMdb, $channelId);
	if($ret['ret'] != 0){
	    if($ret['ret'] == 14){
            echoMsg(0, 'channel not exist');
        }
		echoMsg(0, 'get_channel_info_failed:'.$ret['ret']);
	}
	$channelInfo        = $ret['data'];
    $channel_creator    = $channelInfo['userId'];
	if($channel_creator == $userId){
		$ret = update_live_state($g_writeMdb, $channelId, NO_LIVE);
		if($ret != 0){
			echoMsg(0, 'update_live_state_failed');
		}
	}	
	echoMsg(1);				
}

//////////////////////////////其它事件通知///////////////////
//////////////////////////////其它事件通知///////////////////
//////////////////////////////其它事件通知///////////////////
if(!strcasecmp($action, 'AEC_MSG_SERVER_GET_PUSH_MODE')){
	//获取用户的推送模式
    echoMsg(1, MSG_PUSH_MODE_ALL_ON);   
}

if(!strcasecmp($action, 'AEC_MSG_SERVER_SET_PUSH_MODE')){	
	$pushMode = trimInput(array_key_exists('pushMode', $dataArr) ? $dataArr['pushMode'] : '');//传数字
	if(empty($pushMode)){
		echoMsg(0, 'pushMode err'); 
	}
	$ret = set_user_msg_push_mode($g_writeMdb, $uid, $pushMode);
	if($ret != 0){
		echoMsg(0, 'set_user_msg_push_mode');     
	}
	echoMsg(1);
}



echoMsg(0, 'unkown action:'.$action);