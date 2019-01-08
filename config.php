<?php
//author: admin@elesos.com
function_exists("date_default_timezone_set")?date_default_timezone_set("Asia/Shanghai"):"";
header("Content-Type: text/html; charset=utf-8");//文件本身编码也需要是utf-8

ini_set("display_errors","On");//调试
error_reporting(E_ALL);

$api_config_dir = dirname(__FILE__);



define('appid',         'your_appid');//请注册后在星盒后台获取 
define('secret',        'your_secret');//请注册后在星盒后台获取 
define('guardToken',    'your_guardToken');      //请注册后在星盒后台获取 

//数据库配置
define('writeServer',  'localhost');
define('readServer',   'localhost');


define('database',     'starRTC_demo');
define('username',     'root');
define('password',     'password');

//需要自己手动创建，并给予写权限 
define('log_file',      $api_config_dir.'/log.txt');





define('liveType_meeting',        1);//聊天室表如果liveType为0，表示为纯聊天室
define('liveType_live',           2);

define('ownerType_GROUP_CHANNEL', 1);//ownerType,区分是群的直播还是其它的
define('ownerType_ROOM_CHANNEL',  2);

define('GROUP_DND',   			  1);//Do Not Disturb 群消息免打扰



define('channel_conn_limit',   	  50);//直播流的并发数


require_once($api_config_dir . '/include/pubFun.php');
require_once($api_config_dir . '/include/dbBase.php');




$ret = getReadMdb();
if($ret['ret'] != 0){	
	echoErr('get ReadMdb:'.$ret['ret']);  	
}
global $g_readMdb;
$g_readMdb = $ret['data'];

$ret = getWriteMdb();
if($ret['ret'] != 0){	
	echoErr('get WriteMdb:'.$ret['ret']);  	
}
global $g_writeMdb;
$g_writeMdb = $ret['data'];
