<?php

function generateSign($data, $guardToken){
	return base64_encode(hash_hmac('sha1', $data, $guardToken, TRUE));	
}

function addSuffix($ids){	
	$ids_arr = explode(",", $ids);
	$id_suffix_arr = array();
	foreach($ids_arr as $id){
		array_push($id_suffix_arr, $id.'_0');
	}
	$ids_suffix = implode(',', $id_suffix_arr);
	return $ids_suffix;
}


function getPrefix($prefix_suffix){	
	$index  = strpos($prefix_suffix, "_", 0); //family_xuas
	if($index){		
		$prefix = substr($prefix_suffix, 0, $index);
		return $prefix;
	}	
}
//获取后缀
function getSuffix($prefix_suffix){	
	$index  = strpos($prefix_suffix, "_", 0); //family_xuas
	if($index){
		$suffix   = substr($prefix_suffix, $index+1); // xuas			
		return $suffix;
	}	
}


function getGroupSeqId($mdb){
	$retArr = array();	
	try{
		$sql = 'REPLACE INTO `groupId` ( state ) VALUES ( ? )';			
		if(!($pstmt = $mdb->prepare($sql))){   
            $retArr['ret'] = 12;return $retArr;	
        } 
		
		$do_not_change = 100;
		if($pstmt->execute(array($do_not_change))){
			$retArr['ret'] = 0;
			$retArr['data'] = $mdb->lastInsertId();			
			return $retArr;						
		}else{
			$retArr['ret'] = 13;return $retArr;	
		}    
	}catch(PDOException $e){	
		$retArr['ret'] = 11;return $retArr;	
	}
	$retArr['ret'] = 10;return $retArr;	
}






//法二,递归对多维数组的值进行urlencode, 暂时未用到，用了recursive_urlencode
function array_urlencode($data){
    $new_data = array();
    foreach($data as $key => $val){
        //这里我对键也进行了urlencode
        //$new_data[urlencode($key)] = is_array($val) ? array_urlencode($val) : urlencode($val);
        $new_data[$key] = is_array($val) ? array_urlencode($val) : urlencode($val);
    }
    return $new_data;
}

function recursive_urlencode(&$value, $key){	
	$value = str_replace("<elesos>", "\\r\\n", $value);
	$value = urlencode($value);
}

function recursive_urldecode(&$value, $key){
	$value = urldecode($value);
}



//处理输入
function trimInput($input){
	$input = trim($input);	//移除字符串两侧的空白字符,返回类型是字符
	
	
	//去掉 换行符，windows下会是/r/n，在linux下是/n，在mac下是/r
	$input   = str_replace(PHP_EOL, '', $input);

	//去掉双引号，单引号
	return $input;	
}




//为了兼容 ipv6，用50字节保存ip var(50)
function getIp(){
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        $ip = getenv("HTTP_CLIENT_IP");
    else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
        $ip = getenv("REMOTE_ADDR");
    else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))//值得信赖
        $ip = $_SERVER['REMOTE_ADDR'];
    else
        $ip = "unknown";
    return($ip);
}
function get_os_type(){
    $iPod    = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");
    $iPhone  = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
    $iPad    = strpos($_SERVER['HTTP_USER_AGENT'],"iPad");
    $Darwin  = strpos($_SERVER['HTTP_USER_AGENT'],"Darwin");
    $android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
   
    if($iPad||$iPhone||$iPod||$Darwin){
		return 'ios';
        //return 1;
    }else if($android){
		return 'android';
        //return 2;
    }else{
		return 'unkown';
        //return 3;//未知的要记录下来
    }
}




function echoMsg($status, $data = '', $exit = 1){		
	$retArr = array();
	if(is_array($data)){
		array_walk_recursive($data, 'recursive_urlencode');	
		$retArr = array('status'=>(string)$status, 'data'=>$data);//string是因为如果是数字，要双引号
	}else{
        if($status != 1){
            log_to_file($data);
        }

		$retArr = array('status'=>(string)$status, 'data'=>urlencode($data));//中文json_encode之前最好编码
	}	
	$json = json_encode($retArr);	
	echo urldecode($json);	
	if($exit == 1){
		exit;
	}	
}

function echoK($data, $exit = 1){	
	$retArr = array();		
	if(is_array($data)){
		array_walk_recursive($data, 'recursive_urlencode');//成功时返回 TRUE， 或者在失败时返回 FALSE。	
		$retArr = array('status'=>1, 'data'=>$data);
	}else{
		$retArr = array('status'=>1, 'data'=>urlencode($data));//中文json_encode之前最好编码
	}	
	$json = json_encode($retArr);	
	echo urldecode($json);	
	if($exit == 1){
		exit;
	}	
}




function echoErr($data, $exit = 1){	
	log_to_file($data);
	$retArr = array();
	if(is_array($data)){
		array_walk_recursive($data, 'recursive_urlencode');
		/* foreach($data as $k => $v){
			$data[$k] = urlencode($v);			
		} */	
		$retArr = array('status'=>0, 'data'=>$data);
	}else{
		$retArr = array('status'=>0, 'data'=>urlencode($data));//中文json_encode之前最好编码
	}	
	$json = json_encode($retArr);	
	echo urldecode($json);	
	if($exit == 1){
		exit;
	}	
}








function echoDebug($data, $exit = 0){	
	echo '<pre>======<br/>';	
	print_r($data);
	echo '<br/>======</pre>';
	if($exit == 1){
		exit;
	}		
}




function log_to_file($data){	
	$fp = fopen(log_file, "a+");//读写方式打开，将文件指针指向文件末尾。如果文件不存在则尝试创建之。
	$time = date('Y-m-d H:i:s'); 
	fwrite($fp, $time.'  '.$data."\r\n");//记得a+w
	fclose($fp);	
}


