<?php 
/**
 * http://ipiwatcher.applinzi.com/api.php?uuid=1234-5678&psw=MD5&action=create
 * http://ipiwatcher.applinzi.com/api.php?uuid=1234-5678&psw=MD5&action=verify
 * http://ipiwatcher.applinzi.com/api.php?uuid=1234-5678&psw=MD5&action=add&time=1465715023
 * http://ipiwatcher.applinzi.com/api.php?uuid=1234-5678&psw=MD5&action=query&count=10&before=1465715023&after=0
 * http://ipiwatcher.applinzi.com/api.php?uuid=1234-5678&psw=MD5&action=clear
 * http://ipiwatcher.applinzi.com/api.php?uuid=1234-5678&psw=MD5&action=alias&alias=12345678
 */
header("Content-type: application/json");

require_once('database.php');
require_once('utils.php');
require_once('push.php');

if(!parameter_check()){
	return;
}

$action = $_GET['action'];
$uuid = $_GET['uuid'];
$psw = $_GET['psw'];
$time = isset($_GET['time']) ? (int) $_GET['time'] : null;
$count = isset($_GET['count']) ? (int) $_GET['count'] : null;
$before = isset($_GET['before']) ? (int) $_GET['before'] : null;
$after = isset($_GET['after']) ? (int) $_GET['after'] : null;
$alias = isset($_GET['alias']) ? $_GET['alias'] : null;

route($action);

function create_account(){
	global $uuid,$psw;
	$result = db_create_account($uuid, $psw);
	$arr = array('status'=>bool_to_ok($result)); 
	echo json_encode($arr);
}

function verify_account(){
    global $uuid,$psw;
    $result = db_verify_account($uuid, $psw);
    $arr = array('status'=>bool_to_ok($result)); 
	echo json_encode($arr);
}

function add_data(){
    global $uuid,$psw,$time;
    $result = db_add_data($uuid,$psw,$time);
    $push_status = 'not';
    if($result){
        $alias = db_get_alias($uuid);
        if($alias){
            $push_result = push_to_client($alias, $time);
            $push_status = $push_result ? 'ok' : 'error';
        }
    }
    $arr = array('status'=>bool_to_ok($result), 'push'=>$push_status);
    echo json_encode($arr);
}

function query_data(){
    global $uuid,$psw,$count,$before,$after;
    $result = db_query_data($uuid,$psw,$count,$before,$after);
    if( gettype($result) == "boolean"){
        $arr = array('status'=>'error');
    }else{
        $arr = array('status'=>'ok', 'data'=>$result);
    }
    
	echo json_encode($arr);
}

function clear_data(){
    global $uuid,$psw;
    $result = db_clear_data($uuid, $psw);
    $arr = array('status'=>bool_to_ok($result));
    echo json_encode($arr);
}

function set_alias(){
    global $uuid,$psw,$alias;
    $result = db_set_alias($uuid, $psw, $alias);
    $arr = array('status'=>bool_to_ok($result));
    echo json_encode($arr);
}

function parameter_check(){
    if(!isset($_GET['action'])){
        echo "action is needed\r\n";
        return false;
    }
    if(!in_array($_GET['action'],array('create', 'verify', 'add','query', 'clear', 'alias'))){
        echo "action: create/verify/add/query/clear/alias\r\n";
        return false;
    }
    if(!isset($_GET['uuid'])){
        echo "uuid is needed\r\n";
        return false;
    }

    if(!isset($_GET['psw'])){
        echo "psw is needed\r\n";
        return false;
    }
    
    if($_GET['action']=='add' && !isset($_GET['time'])){
        echo "time is needed\r\n";
        return false;
    }

    if($_GET['action']=='alias' && !isset($_GET['alias'])){
        echo "alias is needed\r\n";
        return false;
    }

    return true;
}

function route($action){
    switch($action){
        case 'create':
            create_account();
            break;
        case 'verify':
            verify_account();
            break;
        case 'add':
            add_data();
            break;
        case 'query':
            query_data();
            break;
        case 'clear':
            clear_data();
            break;
        case 'alias':
            set_alias();
            break;
        default:
            echo "action is invalid\r\n";
    }
}

?>