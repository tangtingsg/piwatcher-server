<?php
define("TABLE_USER", "User");
define("TABLE_DATA", "Data");
define("TABLE_PUSH", "PushData");
define("COLUMN_UUID", "UUID");
define("COLUMN_PSW", "PSW");
define("COLUMN_TIME", "MOVE_TIME");
define("COLUMN_ALIAS", "ALIAS");

$con = null;

function db_connect(){
	global $con;
	$con = mysqli_connect(SAE_MYSQL_HOST_M, SAE_MYSQL_USER, SAE_MYSQL_PASS, SAE_MYSQL_DB, SAE_MYSQL_PORT);

	if (!$con) {
		return false;
	}
	return true;
}

function db_close(){
	global $con;
	mysqli_close($con);
}

function _db_verify_account_common($uuid, $psw){
	global $con;
	if(!db_connect()) return false;

	$sql = "SELECT ".COLUMN_PSW." FROM ".TABLE_USER." WHERE ".COLUMN_UUID."='$uuid'";
	$result = mysqli_query($con, $sql);
	$row = mysqli_fetch_array($result);
	if($row && $row[COLUMN_PSW] == $psw){
		return true;
	}
	return false;
}

function db_create_account($uuid, $psw){
	global $con;
	if(!db_connect()) return false;
    
	$sql = "INSERT INTO ".TABLE_USER." VALUES ('$uuid','$psw')";
    $result = mysqli_query($con, $sql);
    db_close();
    if($result) return true;
    return false;
}

function db_verify_account($uuid, $psw){
	$verify = _db_verify_account_common($uuid, $psw);
	db_close();
	return $verify;
}

function db_add_data($uuid,$psw,$time){
	global $con;
	$verify = _db_verify_account_common($uuid, $psw);
	if(!$verify) {
        db_close();
        return false;
    }
    
	$sql = "INSERT INTO ".TABLE_DATA." VALUES ('$uuid','$time')";
    $result = mysqli_query($con, $sql);
    db_close();
    if($result) return true;
    return false;
}

function db_query_data($uuid,$psw,$count,$before,$after){
	global $con;
	$verify = _db_verify_account_common($uuid, $psw);
    if(!$verify) { 
        db_close(); 
        return false;
    }

	$sql = "SELECT ".COLUMN_TIME." FROM ".TABLE_DATA." WHERE ".COLUMN_UUID." = '$uuid'";
	if(!is_null($before)){
		$sql = $sql." AND ".COLUMN_TIME." < $before";
	}
	if(!is_null($after)){
		$sql = $sql." AND ".COLUMN_TIME." > $after";
	}

	$sql = $sql." ORDER BY ".COLUMN_TIME." DESC";

	if(!is_null($count)){
		$sql = $sql." LIMIT $count";
	}

    $result = mysqli_query($con, $sql);
    if(!$result){
        db_close();
        return false;
    }

    $rs_arr = Array();
    while($row = mysqli_fetch_array($result)){
        $rs_arr[] = (int)$row[COLUMN_TIME];
    }

    db_close();
    return $rs_arr;
}

function db_clear_data($uuid, $psw){
    global $con;
    $verify = _db_verify_account_common($uuid, $psw);
    if(!$verify) {
        db_close();
        return false;
    }

    $sql = "DELETE FROM ".TABLE_DATA." WHERE ".COLUMN_UUID." = '$uuid'";
    $result = mysqli_query($con, $sql);
    db_close();
    if($result) return true;
    return false;
}


function db_set_alias($uuid, $psw, $alias){
	global $con;
	$verify = _db_verify_account_common($uuid, $psw);
	if(!$verify) {
		db_close();
		return false;
	}

	$sql = "INSERT INTO ".TABLE_PUSH." VALUES ('$uuid','$alias') ON DUPLICATE KEY UPDATE ".COLUMN_ALIAS." = '$alias'";
	$result = mysqli_query($con, $sql);
	db_close();
	if($result) return true;
	return false;
}

function db_get_alias($uuid){
    global $con;
    if(!db_connect()) return false;

    $sql = "SELECT ".COLUMN_ALIAS." FROM ".TABLE_PUSH." WHERE ".COLUMN_UUID."='$uuid'";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_array($result);
    db_close();
    if($row){
        return $row[COLUMN_ALIAS];
    }
    return false;
}

?>