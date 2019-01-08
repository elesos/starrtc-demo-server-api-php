<?php
//   prepare 返回PDOStatement ，然后调用execute（Returns TRUE on success or FALSE on failure.）
/*
 * 
 *
 * @author admin#elesos.com 
 * @param  
 * @return 成功返回0,其它-1:-3
 *		   
 */


//换掉所有位于select 与 from之间的东西，移除order by后的东西
// 出现select 嵌套现象未测试！

function get_rows($g_readMdb, $sql, $value_arr){
    $selectStr = trim($sql);
    $selectStr = preg_replace('~^SELECT\s.*\sFROM~si', 'SELECT COUNT(*) FROM', $selectStr);
    $selectStr = preg_replace('~ORDER\s+BY.*?$~sDi', '', $selectStr);

    //echoDebug($selectStr);
   // echoDebug($value_arr);

    $retArr = array();
    try{
        if(!($pstmt = $g_readMdb->prepare($selectStr))){
            $retArr['ret'] = 31;return $retArr;
        }
        if($pstmt->execute($value_arr)){
            $result = $pstmt->fetchAll();
            $resNum = count($result);
            if($resNum == 1){
                //echoDebug($result);
                $rows_num = $result[0][0];
                //echoDebug($rows_num);
                $retArr['ret']  = 0;
                $retArr['data'] = $rows_num;
                return $retArr;
            }
            $retArr['ret'] = 33;return $retArr;
        }else{
            $retArr['ret'] = 32;return $retArr;
        }
    }catch(PDOException $e){
        $retArr['ret'] = 30;return $retArr;
    }
    $retArr['ret'] = 10;return $retArr;
}

function get_offset($g_readMdb, $sql, $value_arr, $offset, $asc=1){//默认是升序
    $selectStr = trim($sql);
    $selectStr = preg_replace('~^SELECT\s.*\sFROM~si', 'SELECT id FROM', $selectStr);
    $selectStr = preg_replace('~ORDER\s+BY.*?$~sDi', '', $selectStr);

    if($asc == 0){
        $selectStr .= ' order by id desc limit ?, 1';
    }else{
        $selectStr .= ' order by id asc limit ?, 1';
    }


    //echoDebug($selectStr);
    $new_arr = $value_arr;
    array_push($new_arr, $offset);
    $retArr = array();
    try{
        if(!($pstmt = $g_readMdb->prepare($selectStr))){
            $retArr['ret'] = 31;return $retArr;
        }
        if($pstmt->execute($new_arr)){
            $result = $pstmt->fetchAll();
            $resNum = count($result);
            if($resNum == 1){
                $id = $result[0][0];
               // echoDebug($new_arr);
                //echoDebug($id);
                $retArr['ret']  = 0;
                $retArr['data'] = $id;
                return $retArr;
            }
            $retArr['ret'] = 33;return $retArr;
        }else{
            $retArr['ret'] = 32;return $retArr;
        }
    }catch(PDOException $e){
        $retArr['ret'] = 30;return $retArr;
    }
    $retArr['ret'] = 10;return $retArr;
}


function linkDatabase($host, $dbname, $usr, $password){	
    $dsn    = "mysql:host=".$host.";dbname=".$dbname;	
				
	$retArr = array();
	try {		
		$db = new PDO($dsn, $usr, $password);//create a PDO instance representing a connection to a database
		if(!($db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false))){//防SQL注入 Returns TRUE on success or FALSE on failure.
			$retArr['ret'] = 10;$retArr['data'] = 'PDO setAttribute failed';return $retArr;
		}
		if(false === ($db->exec("set names 'utf8'"))){
			$retArr['ret'] = 11;$retArr['data'] = 'PDO exec failed';return $retArr;
		}		
	}catch (PDOException $e) {
	    log_to_file($e->getMessage());
		$retArr['ret'] = 12;
		$retArr['data'] = $e->getMessage();		
		return $retArr;
	}	
	$retArr['ret'] = 0;
	$retArr['data'] = $db;	
	return $retArr;   
}



//connect and select db
function getDbConn($host, $dbName, $usr, $password){
	$con = mysql_connect($host, $usr, $password);
	if (!$con)
	{
		//die("Could not connect: " . mysql_error());
		return null;
	}
	$ret = mysql_query("SET NAMES UTF8", $con);
	if(!ret){
		return null;
	}
	
	$ret = mysql_select_db($dbName, $con);
	
	if(!ret){
		//die ("Can't use ".$dbName.": " . mysql_error());
		return null;
	}
	return $con;
}

function createDb($host, $dbName, $usr, $password){
	$con = mysql_connect($host, $usr, $password);
	if(!$con){
		// echo "Could not connect: " . mysql_error();
		return false;
	}
	
	$dbSelect = mysql_select_db($dbName,$con);
	
	if($dbSelect){
		//数据库已存在
		return true;
	}
	
	
	//数据库不存在，新建
    try {
        $dbh = new PDO("mysql:host=".$host, $usr, $password);
		
        if(!$dbh->exec("CREATE DATABASE ".$dbName.";")){
			return false;
		}

    } catch (PDOException $e) {
        return false;
    }
	
	mysql_close($con);
}




function getWriteMdb(){	
	$retArr =array();
	$ret    = linkDatabase(writeServer, database, username, password);
	if($ret['ret'] != 0){
		$retArr['ret'] = intval('10'.$ret['ret']);
		return $retArr;
	}
	$mdb = $ret['data'];
	$retArr['ret']  = 0;
	$retArr['data'] = $mdb;
	return $retArr;
}

function getReadMdb(){	
	$retArr =array();
	$ret    = linkDatabase(readServer, database, username, password);
	if($ret['ret'] != 0){
		$retArr['ret'] = intval('10'.$ret['ret']);
		return $retArr;
	}
	$mdb = $ret['data'];
	$retArr['ret']  = 0;
	$retArr['data'] = $mdb;
	return $retArr;
}


