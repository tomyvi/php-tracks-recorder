<?php
//http://owntracks.org/booklet/tech/http/
    # Obtain the JSON payload from an OwnTracks app POSTed via HTTP
    # and insert into database table.

    header("Content-type: application/javascript");
    require_once('config.inc.php');
    
	$response = array();

	if ($_config['sql_type'] == 'mysql') {
		require_once('lib/db/MySql.php');
		/** @var MySql $sql */
		$sql = new MySql($_config['sql_db'], $_config['sql_host'], $_config['sql_user'], $_config['sql_pass'], $_config['sql_prefix']);
	} elseif ($_config['sql_type'] == 'sqlite') {
		require_once('lib/db/SQLite.php');
		/** @var SQLite $sql */
		$sql = new SQLite($_config['sql_db']);
	} else {
		die('Invalid database type: ' . $_config['sql_type']);
	}

    if (array_key_exists('action', $_REQUEST)) {

	    if ($_REQUEST['action'] === 'getMarkers') {

	    	if (!array_key_exists('dateFrom', $_GET)) {
		        $_GET['dateFrom'] = date("Y-m-d");
		    }

		    if (!array_key_exists('dateTo', $_GET)) {
		        $_GET['dateTo'] = date("Y-m-d");
		    }

		    if (array_key_exists('accuracy', $_GET) && $_GET['accuracy'] > 0) {
		        $accuracy = intVal($_GET['accuracy']);
		    } else {
		        $accuracy = $_config['default_accuracy'];
		    }

		    $time_from = strptime($_GET['dateFrom'], '%Y-%m-%d');
		    $time_from = mktime(0, 0, 0, $time_from['tm_mon']+1, $time_from['tm_mday'], $time_from['tm_year']+1900);


		    $time_to = strptime($_GET['dateTo'], '%Y-%m-%d');
		    $time_to = mktime(23, 59, 59, $time_to['tm_mon']+1, $time_to['tm_mday'], $time_to['tm_year']+1900);
		    //$time_to = strtotime('+1 day', $time_to);

			$markers = $sql->getMarkers($time_from, $time_to, $accuracy);

			if ($markers === false) {
				$response['status'] = false;
				$response['error'] = 'Database query error';
				http_response_code(500);
			} else {
				$response['status'] = true;
				$response['markers'] = json_encode($markers);
			}


	    } elseif ($_REQUEST['action'] === 'deleteMarker') {

	    	if (!array_key_exists('epoch', $_REQUEST)) {
	    		$response['error'] = "No epoch provided for marker removal";
	    		$response['status'] = false;
	    		http_response_code(204);
	    	}else{
	    		$result = $sql->deleteMarker($_REQUEST['epoch']);
				if ($result === false) {
					$response['error'] = 'Unable to delete marker from database.';
					$response['status'] = false;
					http_response_code(500);
				} else {
					$response['msg'] = "Marker deleted from database";
					$response['status'] = true;
				}
			}

	    } elseif ($_REQUEST['action'] === 'geoDecode') {

	    	if (!array_key_exists('epoch', $_REQUEST)) {
	    		$response['error'] = "No epoch provided for marker removal";
	    		$response['status'] = false;
	    		http_response_code(204);
	    	} else {

				// GET MARKER'S LAT & LONG DATA
				$marker = $sql->getMarkerLatLon($_REQUEST['epoch']);

				if ($marker === false) {
					$response['error'] = 'Unable to get marker from database.';
					$response['status'] = false;
				} else {
				    $latitude = $marker['latitude'];
				    $longitude = $marker['longitude'];

				    // GEO DECODE LAT & LONG
				    $geo_decode_url = $_config['geo_reverse_lookup_url'] . 'lat=' .$latitude. '&lon='.$longitude;
					$geo_decode_json = file_get_contents($geo_decode_url);
					$geo_decode = @json_decode($geo_decode_json, true);

					$place_id = intval($geo_decode['place_id']);
					$osm_id = intval($geo_decode['osm_id']);
					$location = strval($geo_decode['display_name']);

					if ($location == '') { $location = @json_encode($geo_decode); }

					//UPDATE MARKER WITH GEODECODED LOCATION
					$result = $sql->updateLocationData((int)$_REQUEST['epoch'], (float)$latitude, (float)$longitude, $location, $place_id, $osm_id);

					if ($result === false) {
						$response['error'] = 'Unable to update marker in database.';
						$response['status'] = false;
						http_response_code(500);
		    		} else {
						$response['msg'] = 'Marker\'s location fetched and saved to database';
						$response['location'] = $location;
						$response['status'] = true;
		    		}
	    		}

	    	}

	    } else {
	    	$response['error'] = "No action to perform";
	    	$response['status'] = false;
	    	http_response_code(404);
	    }

	} else {
    	$response['error'] = "Invalid request type or no action";
    	$response['status'] = false;
    	http_response_code(404);
    }

	echo json_encode($response);
