<?php
//http://owntracks.org/booklet/tech/http/
    # Obtain the JSON payload from an OwnTracks app POSTed via HTTP
    # and insert into database table.

    header("Content-type: application/javascript");
    require_once('config.inc.php');

    $mysqli = new mysqli($_config['sql_host'], $_config['sql_user'], $_config['sql_pass'], $_config['sql_db']);

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

	$sql = "SELECT * FROM ".$_config['sql_prefix']."locations WHERE epoch >= $time_from AND epoch <= $time_to AND accuracy < ".$accuracy." AND altitude >=0 ORDER BY epoch ASC";
    echo "//$sql\n\n";

    $stmt = $mysqli->prepare($sql);

    if(!$stmt){
        echo $mysqli->error;
    }

	$stmt->execute();
	$result = $stmt->get_result();
	$stmt->store_result();
    
    while($data = $result->fetch_assoc()){ 
        //Loop through results here $data[] 
        $markers[] = $data;
    }
    
    $stmt->close();
    print "markers = ".json_encode($markers).";";
?>