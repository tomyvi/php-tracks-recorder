<?php
//http://owntracks.org/booklet/tech/http/
    # Obtain the JSON payload from an OwnTracks app POSTed via HTTP
    # and insert into database table.

    header("Content-type: application/javascript");
    require_once('config.inc.php');
    
	$response = array();

    $mysqli = new mysqli($_config['sql_host'], $_config['sql_user'], $_config['sql_pass'], $_config['sql_db']);
    
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && array_key_exists('action', $_POST)) {
	    
	    if($_POST['action'] === 'removeMarker'){
	    	
	    	if(!array_key_exists('epoch', $_POST)){
	    		$response['error'] = "No epoch provided for marker removal";
	    		$response['status'] = false;	
	    	}else{
	    		
	    		$stmt = $mysqli->prepare("DELETE FROM ".$_config['sql_prefix']."locations WHERE epoch = ?");
	    		
	    		if(!$stmt){
	    			$response['error'] = "Unable to prepare statement : " . $mysqli->error;
					$response['status'] = false;
	    		}else{
	    		
		    		$stmt->bind_param('i', $_POST['epoch']);
		    		//$stmt->bindParam(':epoc', $_POST['epoch'], PDO::PARAM_INT);
					
					if(!$stmt->execute()){
						$response['error'] = "Unable to delete marker from database : " . $stmt->error;
						$response['status'] = false;
					}
					
					$response['msg'] = "Marker deleted from database";
					$response['status'] = true;
					$stmt->close();
	    		}
	    	}
	    	
	    }else{
	    	$response['error'] = "No action to perform";
	    	$response['status'] = false;
	    }
	    
	    
	    
	    
	}else{
    	$response['error'] = "Invalid request type or no action";
    	$response['status'] = false;
    }
	
	echo json_encode($response);    
    
?>