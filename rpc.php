<?php
//http://owntracks.org/booklet/tech/http/
    # Obtain the JSON payload from an OwnTracks app POSTed via HTTP
    # and insert into database table.

    header("Content-type: application/javascript");
    require_once('config.inc.php');
    
	$response = array();

    $mysqli = new mysqli($_config['sql_host'], $_config['sql_user'], $_config['sql_pass'], $_config['sql_db']);
    
    
    if (array_key_exists('action', $_REQUEST)) {
	    
	    if($_REQUEST['action'] === 'getMarkers'){
	    	
	    	if(!array_key_exists('dateFrom', $_GET)){
		        $_GET['dateFrom'] = date("Y-m-d");
		    }
		
		    if(!array_key_exists('dateTo', $_GET)){
		        $_GET['dateTo'] = date("Y-m-d");
		    }
		
		    if(array_key_exists('accuracy', $_GET) && $_GET['accuracy'] > 0){
		        $accuracy = intVal($_GET['accuracy']);
		    }else{
		        $accuracy = $_config['default_accuracy'];
		    }
		
		    $time_from = strptime($_GET['dateFrom'], '%Y-%m-%d');
		    $time_from = mktime(0, 0, 0, $time_from['tm_mon']+1, $time_from['tm_mday'], $time_from['tm_year']+1900);
		
		
		    $time_to = strptime($_GET['dateTo'], '%Y-%m-%d');
		    $time_to = mktime(23, 59, 59, $time_to['tm_mon']+1, $time_to['tm_mday'], $time_to['tm_year']+1900);
		    //$time_to = strtotime('+1 day', $time_to);
		
			$sql = "SELECT * FROM ".$_config['sql_prefix']."locations WHERE epoch >= $time_from AND epoch <= $time_to AND accuracy < ".$accuracy." AND altitude >=0 ORDER BY tracker_id, epoch ASC";
		    
		    $stmt = $mysqli->prepare($sql);
		
		    if(!$stmt){
		        $response['status'] = false;
		        $response['error'] = $mysqli->error;
		        http_response_code(500);
		    }
		
			$stmt->execute();
			$result = $stmt->get_result();
			$stmt->store_result();
		    
		    $tracker_id = "";
		    while($data = $result->fetch_assoc()){ 
		        //Loop through results here $data[] 
		        $markers[$data['tracker_id']][] = $data;
		    }
		    
		    $stmt->close();
		    $response['status'] = true;
		    $response['markers'] = json_encode($markers);
	    	
	    	
	    	
	    }else if($_REQUEST['action'] === 'deleteMarker'){
	    	
	    	if(!array_key_exists('epoch', $_REQUEST)){
	    		$response['error'] = "No epoch provided for marker removal";
	    		$response['status'] = false;
	    		http_response_code(204);
	    	}else{
	    		
	    		$stmt = $mysqli->prepare("DELETE FROM ".$_config['sql_prefix']."locations WHERE epoch = ?");
	    		
	    		if(!$stmt){
	    			$response['error'] = "Unable to prepare statement : " . $mysqli->error;
					$response['status'] = false;
					http_response_code(500);
	    		}else{
	    		
		    		$stmt->bind_param('i', $_REQUEST['epoch']);
		    		//$stmt->bindParam(':epoc', $_POST['epoch'], PDO::PARAM_INT);
					
					if(!$stmt->execute()){
						$response['error'] = "Unable to delete marker from database : " . $stmt->error;
						$response['status'] = false;
						http_response_code(500);
					}
					
					$response['msg'] = "Marker deleted from database";
					$response['status'] = true;
					$stmt->close();
	    		}
	    	}
	    	
	    }else if($_REQUEST['action'] === 'geoDecode'){
	    	
	    	if(!array_key_exists('epoch', $_REQUEST)){
	    		$response['error'] = "No epoch provided for marker removal";
	    		$response['status'] = false;
	    		http_response_code(204);
	    	}else{
	    		
	    		// GET MARKER'S LAT & LONG DATA
	    		$stmt = $mysqli->prepare("SELECT latitude, longitude FROM ".$_config['sql_prefix']."locations WHERE epoch = ?");
	    		
	    		if(!$stmt){
	    			$response['error'] = "Unable to prepare statement : " . $mysqli->error;
					$response['status'] = false;
					http_response_code(500);
	    		}else{
	    			
		    		$stmt->bind_param('i', $_REQUEST['epoch']);
		    		//$stmt->bindParam(':epoc', $_POST['epoch'], PDO::PARAM_INT);
					
					if(!$stmt->execute()){
						$response['error'] = "Unable to get marker from database : " . $stmt->error;
						$response['status'] = false;
					}
					
					$stmt->execute();
					$result = $stmt->get_result();
					$stmt->store_result();
					
				    while($data = $result->fetch_assoc()){ 
				        //Loop through results here $data[] 
				        $marker = $data;
				    }
				    
				    
				    $latitude = $marker['latitude'];
				    $longitude = $marker['longitude'];
				    
				    // GEO DECODE LAT & LONG
				    $geo_decode_url = $_config['geo_reverse_lookup_url'] . 'lat=' .$latitude. '&lon='.$longitude;
					$geo_decode_json = file_get_contents($geo_decode_url);		
					$geo_decode = @json_decode($geo_decode_json, true);
				
					$place_id = intval($geo_decode['place_id']);
					$osm_id = intval($geo_decode['osm_id']);
					$location = strval($geo_decode['display_name']);
					
					if($location == '') { $location = @json_encode($geo_decode); }
					
					//UPDATE MARKER WITH GEODECODED LOCATION
					$stmt = $mysqli->prepare("UPDATE ".$_config['sql_prefix']."locations SET display_name = ?, place_id = ?, osm_id = ? WHERE epoch = ? AND latitude = ? AND longitude = ?");
					
					if(!$stmt){
		    			$response['error'] = "Unable to prepare statement : " . $mysqli->error;
						$response['status'] = false;
						http_response_code(500);
		    		}else{
		    			
			    		$stmt->bind_param('siiidd', $location, $place_id, $osm_id, $_REQUEST['epoch'], $latitude, $longitude);
						
						if(!$stmt->execute()){
							$response['error'] = "Unable to update marker in database : " . $stmt->error;
							$response['status'] = false;
							http_response_code(500);
						}else{
							//SEND BACK DATA
							$response['msg'] = "Marker's location fetched and saved to database";
							$response['location'] = $location;
							$response['status'] = true;
						}
		    		}
					
					$stmt->close();
	    		}
	    		
	    	}
	    	
	    }else{
	    	$response['error'] = "No action to perform";
	    	$response['status'] = false;
	    	http_response_code(404);
	    }
	    
	    
	    
	    
	}else{
    	$response['error'] = "Invalid request type or no action";
    	$response['status'] = false;
    	http_response_code(404);
    }
	
	echo json_encode($response);    
    
?>