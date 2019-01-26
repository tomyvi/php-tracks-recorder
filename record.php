<?php

header("Content-type: application/json");

$_config = [];
require_once('./config.inc.php');

$fp = NULL; //fopen('./log/record_log.txt', 'a+'); do not open file if not needed
function _log($msg){
    global $fp;
    global $_config;

    if($_config['log_enable'] === True){
        if(!$fp) { $fp = fopen('./log/record_log.txt', 'a+'); }

        return fprintf($fp, date('Y-m-d H:i:s') . " - ".$_SERVER['REMOTE_ADDR']." - %s\n", $msg);
    } else {
        return True;
    }
}

if ($_config['sql_type'] == 'mysql') {
    require_once('lib/db/MySql.php');
    $sql = new MySql($_config['sql_db'], $_config['sql_host'], $_config['sql_user'], $_config['sql_pass'], $_config['sql_prefix']);
} elseif ($_config['sql_type'] == 'mysqlpdo') {
    require_once('lib/db/MySqlPdo.php.php');
    $sql = new MySqlPdo($_config['sql_db']);
} elseif ($_config['sql_type'] == 'sqlite') {
    require_once('lib/db/SQLite.php');
    $sql = new SQLite($_config['sql_db']);
} else {
    die('Invalid database type: ' . $_config['sql_type']);
}

if ($_config['recorder'] == 'owntracks') {
    require_once('lib/recorder/OwntracksRecorder.php');
    $recorder = new OwntracksRecorder();
} elseif ($_config['recorder'] == 'overland') {
    require_once('lib/recorder/OverlandRecorder.php');
    $recorder = new OverlandRecorder();
} else {
    die('Invalid recorder type: ' . $_config['recorder']);
}

$payload = file_get_contents("php://input");

_log("Payload = ".$payload);

try{
    $records = $recorder->parsePayload($payload);

    foreach($records as $rec){
        try {
            $recorder->saveRecord($rec, $sql);
            $http_response_code = 200;
            $response_msg = 'Record saved to database';
            _log("Insert OK");

        } catch (\Exception $e) {
            $http_response_code = $e->getCode();
            $response_msg = "Can't write to database : " . $e->getMessage();
            _log("Insert KO - " . $response_msg);

        }

    }
}
catch(Exception $e){
    $http_response_code = $e->getCode();
    $response_msg = $e->getMessage();
    _log($e->getMessage());
}



//getting last known location for other tracker ids in database
$friends = array();
if(count($records)>0) $friends = $recorder->getFriendsLocation($records[0]);

$response = $recorder->buildResponseArray($response_msg, $http_response_code);

if(count($friends) > 0) {
    //add friends data to response array
    $response = array_merge($response, $friends);
}

http_response_code($http_response_code);
print json_encode($response);

fclose($fp);
