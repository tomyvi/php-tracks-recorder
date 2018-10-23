<?php

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



//http://owntracks.org/booklet/tech/http/
# Obtain the JSON payload from an OwnTracks app POSTed via HTTP
# and insert into database table.

header("Content-type: application/json");
require_once('./config.inc.php');

$payload = file_get_contents("php://input");

if(!$fpp) { $fpp = fopen('./log/payload_log.txt', 'a+'); }
fprintf($fpp, "%s\n", $payload);

_log("Payload = ".$payload);
$data =  @json_decode($payload, true);

$response_msg = null;

if ($data['_type'] == 'location') {

	if ($_config['sql_type'] == 'mysql') {
		require_once('lib/db/MySql.php');
		$sql = new MySql($_config['sql_db'], $_config['sql_host'], $_config['sql_user'], $_config['sql_pass'], $_config['sql_prefix']);
	} elseif ($_config['sql_type'] == 'sqlite') {
		require_once('lib/db/SQLite.php');
		$sql = new SQLite($_config['sql_db']);
	} else {
		die('Invalid database type: ' . $_config['sql_type']);
	}

	# CREATE TABLE locations (dt TIMESTAMP, tid CHAR(2), lat DECIMAL(9,6), lon DECIMAL(9,6));

	//http://owntracks.org/booklet/tech/json/
	//iiiissddissiiidsiis
    if (array_key_exists('acc', $data)) $accuracy = intval($data['acc']);
    if (array_key_exists('alt', $data)) $altitude = intval($data['alt']);
    if (array_key_exists('batt', $data)) $battery_level = intval($data['batt']);
	if (array_key_exists('cog', $data)) $heading = intval($data['cog']);
	if (array_key_exists('desc', $data)) $description = strval($data['desc']);
	if (array_key_exists('event', $data)) $event = strval($data['event']);
	if (array_key_exists('lat', $data)) $latitude = floatval($data['lat']);
	if (array_key_exists('lon', $data)) $longitude = floatval($data['lon']);
	if (array_key_exists('rad', $data)) $radius = intval($data['rad']);
	if (array_key_exists('t', $data)) $trig = strval($data['t']);
	if (array_key_exists('tid', $data)) $tracker_id = strval($data['tid']);
	if (array_key_exists('tst', $data)) $epoch = intval($data['tst']);
	if (array_key_exists('vac', $data)) $vertical_accuracy = intval($data['vac']);
	if (array_key_exists('vel', $data)) $velocity = intval($data['vel']);
	if (array_key_exists('p', $data)) $pressure = floatval($data['p']);
	if (array_key_exists('conn', $data)) $connection = strval($data['conn']);
	if (array_key_exists('topic', $data)) $topic = strval($data['topic']);


	//record only if same data found at same epoch / tracker_id
	if (!$sql->isEpochExisting($tracker_id, $epoch)) {

		$result = $sql->addLocation(
			$accuracy,
			$altitude,
			$battery_level,
			$heading,
			$description,
			$event,
			$latitude,
			$longitude,
			$radius,
			$trig,
			$tracker_id,
			$epoch,
			$vertical_accuracy,
			$velocity,
			$pressure,
			$connection,
			$topic,
			$place_id,
			$osm_id
		);

		if ($result) {
			http_response_code(200);
			_log("Insert OK");
		} else {
			http_response_code(500);
			$response_msg = 'Can\'t write to database';
			_log("Insert KO - Can't write to database.");
		}

	} else {
		_log("Duplicate location found for epoc $epoch / tid '$tracker_id' - no insert");
		$response_msg = 'Duplicate location found for epoch. Ignoring.';
	}

} else {
	http_response_code(200);
	_log("OK type is not location : " . $data['_type']);
}

//getting last known location for other tracker ids in database
$friends = $sql->getFriends($tracker_id);

$response = array();

if (!is_null($response_msg)) {
    // Add status message to return object (to be shown in app)
    $response[] = array(
        '_type' => 'cmd',
        'action' => 'action',
        'content' => $response_msg,
    );
}
if(count($friends) > 0) {
	//add friends data to response array
	$response = array_merge($response, $friends);
}

print json_encode($response);

fclose($fp);
