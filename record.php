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
_log("Payload = ".$payload);
$data =  @json_decode($payload, true);

if ($data['_type'] == 'location') {

    # CREATE TABLE locations (dt TIMESTAMP, tid CHAR(2), lat DECIMAL(9,6), lon DECIMAL(9,6));
    $mysqli = new mysqli($_config['sql_host'], $_config['sql_user'], $_config['sql_pass'], $_config['sql_db']);
	
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
	
	
	$sql = "SELECT epoch FROM ".$_config['sql_prefix']."locations WHERE tracker_id = '$tracker_id' AND epoch = $epoch";
	
	_log("Duplicate SQL = ".$sql);
	
	if ($stmt = $mysqli->prepare($sql)){
    	
    	$stmt->execute();
		$stmt->store_result();
		
		_log("Duplicate SQL : Rows found =  ".$stmt->num_rows);

	    //record only if same data found at same epoch / tracker_id
	    if($stmt->num_rows == 0) {

			$sql = "INSERT INTO ".$_config['sql_prefix']."locations (accuracy, altitude, battery_level, heading, description, event, latitude, longitude, radius, trig, tracker_id, epoch, vertical_accuracy, velocity, pressure, connection, place_id, osm_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		    $stmt = $mysqli->prepare($sql);
		    $stmt->bind_param('iiiissddissiiidsii', $accuracy, $altitude, $battery_level, $heading, $description, $event, $latitude, $longitude, $radius, $trig, $tracker_id, $epoch, $vertical_accuracy, $velocity, $pressure, $connection, $place_id, $osm_id);
			    
		    if ($stmt->execute()){
		    	
		    	# bind parameters (s = string, i = integer, d = double,  b = blob)
			    http_response_code(200);
				$response['msg'] = "OK record saved";
				_log("Insert OK");
			
		    }else{
				http_response_code(500);
				die("Can't write to database : ".$stmt->error);
				$response['msg'] = "Can't write to database";
				_log("Insert KO - Can't write to database : ".$stmt->error);
			}

	    }else{
	    	_log("Duplicate location found for epoc $epoch / tid '$tracker_id' - no insert");
	    }
	    $stmt->close();
	
    }else{
		http_response_code(500);
		die("Can't read from database");
		$response['msg'] = "Can't read from database";
		_log("Can't read from database");
	}


    

    

}else{
	http_response_code(204);
	$response['msg'] = "OK type is not location";
	_log("OK type is not location : " . $data['_type']);
}

$response = array();

print json_encode($response);

fclose($fp);
?>
