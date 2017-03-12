<?php
//http://owntracks.org/booklet/tech/http/
    # Obtain the JSON payload from an OwnTracks app POSTed via HTTP
    # and insert into database table.

    header("Content-type: application/json");
    require_once('config.inc.php');

    $payload = file_get_contents("php://input");
    $data =  @json_decode($payload, true);

    if ($data['_type'] == 'location') {

        # CREATE TABLE locations (dt TIMESTAMP, tid CHAR(2), lat DECIMAL(9,6), lon DECIMAL(9,6));
        $mysqli = new mysqli($_config['sql_host'], $_config['sql_user'], $_config['sql_pass'], $_config['sql_db']);

		//http://owntracks.org/booklet/tech/json/
        if (array_key_exists('acc', $data)) $accuracy = $data['acc'];
        if (array_key_exists('alt', $data)) $altitude = $data['alt'];
        if (array_key_exists('batt', $data)) $battery_level = $data['batt'];
		if (array_key_exists('cog', $data)) $heading = $data['cog'];
		if (array_key_exists('desc', $data)) $description = $data['desc'];
		if (array_key_exists('event', $data)) $event = $data['event'];
		if (array_key_exists('lat', $data)) $latitude = $data['lat'];
		if (array_key_exists('lon', $data)) $longitude = $data['lon'];
		if (array_key_exists('rad', $data)) $radius = $data['rad'];
		if (array_key_exists('t', $data)) $trig = $data['t'];
		if (array_key_exists('tid', $data)) $tracker_id = $data['tid'];
		if (array_key_exists('tst', $data)) $epoch = $data['tst'];
		if (array_key_exists('vac', $data)) $vertical_accuracy = $data['vac'];
		if (array_key_exists('vel', $data)) $velocity = $data['vel'];
		if (array_key_exists('p', $data)) $pressure = $data['p'];
		if (array_key_exists('conn', $data)) $connection = $data['conn'];

        $sql = "INSERT INTO log_locations (accuracy, altitude, battery_level, heading, description, event, latitude, longitude, radius, trig, tracker_id, epoch, vertical_accuracy, velocity, pressure, connection) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        # bind parameters (s = string, i = integer, d = double,  b = blob)
        $stmt->bind_param('iiiissddisssiids', $accuracy, $altitude, $battery_level, $heading, $description, $event, $latitude, $longitude, $radius, $trig, $tracker_id, $epoch, $vertical_accuracy, $velocity, $pressure, $connection);
        $stmt->execute();
        $stmt->close();
    }

    $response = array();
    # optionally add objects to return to the app (e.g.
    # friends or cards)
    print json_encode($response);
?>
