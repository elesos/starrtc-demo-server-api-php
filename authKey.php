<?php
//获取authKey


$dir = dirname(__FILE__);
require_once($dir . '/config.php');



$userid = array_key_exists('userid', $_REQUEST) ? $_REQUEST['userid'] : 0;
if(empty($userid)){
	echoErr('missing args');	
}
//log_to_file($userid);




$url    = 'https://api.starRTC.com/aec/authKey';

$post_data = array (
		'appid'  => appid,
		'secret' => secret,
		'userid' => $userid		
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

$result = curl_exec($ch);
curl_close($ch);
$data = json_decode($result, TRUE);
//echoDebug($result);
if($data['status'] == 1){
	$authKey = $data['data'];
	echoK($authKey);
}else{
	echoErr($data['data']);	
}
return;
