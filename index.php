<?php

require_once('./config.inc.php');

$dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : date('Y-m-d');
$dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : date('Y-m-d');


if(isset($_GET['accuracy']) && $_GET['accuracy'] != '' && intval($_GET['accuracy']) > 0){
	$accuracy = intval($_GET['accuracy']);
}else if(isset($_COOKIE['accuracy']) && $_COOKIE['accuracy'] != '' && intval($_COOKIE['accuracy']) > 0){
	$accuracy = intval($_COOKIE['accuracy']);
}else{
	$accuracy = $_config['default_accuracy'];
}

if(isset($_GET['trackerID']) && $_GET['trackerID'] != '' && strlen($_GET['trackerID']) == 2){
	$trackerID = $_GET['trackerID'];
}else if(isset($_COOKIE['trackerID']) && $_COOKIE['trackerID'] != '' && strlen($_COOKIE['trackerID']) == 2){
	$trackerID = $_COOKIE['trackerID'];
}else{
	$trackerID = $_config['default_trackerID'];
}

?>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<link rel="icon" href="./img/favicon.ico" />

		<!-- JQUERY !-->
		<script src="//code.jquery.com/jquery-3.3.1.min.js"></script>

		<!-- MOMENTS.JS !-->
		<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment-with-locales.min.js"></script>

		<!-- HIGHCHARTS.JS !-->
		<script src="//code.highcharts.com/highcharts.src.js"></script>

		<!-- BOOTSTRAP !-->
		<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.min.js" ></script>

		<!-- BOOTSTRAP DATETIMEPICKER !-->
		<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/locales/bootstrap-datepicker.fr.min.js"></script>

		<!-- LEAFLET.JS !-->
		<script src="//cdnjs.cloudflare.com/ajax/libs/leaflet/1.3.4/leaflet.js"></script>
		<script src="//cdn.jsdelivr.net/npm/leaflet-hotline@0.4.0/src/leaflet.hotline.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/Leaflet.awesome-markers/2.0.2/leaflet.awesome-markers.min.js"></script>

		<script src="//cdnjs.cloudflare.com/ajax/libs/js-cookie/2.2.0/js.cookie.min.js"></script>

		<!-- BOOTSTRAP !-->
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
		<link rel="stylesheet" href="//getbootstrap.com/docs/3.3/dist/css/bootstrap.min.css" />

		<!-- BOOTSTRAP DATETIMEPICKER !-->
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.css" />

		<!-- LEAFLET.JS !-->
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/leaflet/1.3.4/leaflet.css" />
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/Leaflet.awesome-markers/2.0.2/leaflet.awesome-markers.css" />

		<style>
			#mapid { height: 85%; }

			.disabled {
		        pointer-events: none;
		        cursor: default;
		        opacity: 0.5;
		    }
		</style>
		<title>Your Own Tracks</title>
	</head>
	<body>
		<div class="container">
			<div class="row page-header">
				<div class="col-xs-1 text-left">
					<a onclick="gotoDate(datePrevFrom, datePrevTo);" class="btn btn-primary" role="button">
						<span class="hidden-xs">Previous</span>
						<span class="visible-xs"><span class="glyphicon glyphicon-arrow-left"></span></span>
					</a>
				</div>
				<div class="col-xs-5 text-center">
					<div class="input-group input-daterange ">
					    <input type="text" class="form-control" value="<?php echo $dateFrom; ?>" id="dateFrom">
					    <span class="input-group-addon">to</span>
					    <input type="text" class="form-control" value="<?php echo $dateTo; ?>" id="dateTo">
					</div>
				</div>
				<div class="col-xs-6 text-right">
					<div class="btn-group" role="group">
						<a role="button" data-toggle="collapse" href="#configCollapse" class="btn btn-default"  id="configButton">
							<span class="hidden-xs">Config</span>
							<span class="visible-xs"><span class="glyphicon glyphicon-cog"></span></span>
						</a>

						<a role="button" onclick="resetZoom();" class="btn btn-default">
							<span class="hidden-xs">Reset view</span>
							<span class="visible-xs"><span class="glyphicon glyphicon-screenshot"></span></span>
						</a>
						<a role="button" onclick="gotoDate();" class="btn btn-default" style="display: inline-block;" id="todayButton">
							<span class="hidden-xs">Today</span>
							<span class="visible-xs"><span class="glyphicon glyphicon-arrow-up"></span></span>
						</a>
						<a role="button" onclick="gotoDate(dateNextFrom, dateNextTo);" class="btn btn-primary" style="display: inline-block;" id="nextButton">
							<span class="hidden-xs">Next</span>
							<span class="visible-xs"><span class="glyphicon glyphicon-arrow-right"></span></span>
						</a>
					</div>
				</div>
			</div>
			<div class="collapse" id="configCollapse"><br/>
			  <div class="well">
			  	<div class="row">
			  		<div class="col-xs-2 text-left">
					  	<a role="button" onclick="showHideMarkers();" class="btn btn-default" id="show_markers">
							<span class="hidden-xs">Show markers</span>
							<span class="visible-xs"><span class="glyphicon glyphicon-map-marker"></span></span>
						</a>
					</div>
					<div class="col-xs-2 text-left">
					  	<a role="button" onclick="setLiveMap();" class="btn btn-default" id="livemap_on">
							<span class="hidden-xs">Live map</span>
							<span class="visible-xs"><span class="glyphicon glyphicon-play-circle"></span></span>
						</a>
					</div>
					<div class="col-xs-8 text-right">
						<form class="form-inline"><span class="hidden-xs">Accuracy : </span>
						    <div class="input-group">
						      <input type="number" size='4' class="form-control" id="accuracy" value="<?echo $accuracy; ?>" />
							  <span class="input-group-addon"><span class="hidden-xs">meters</span><span class="visible-xs">m</span></span>
							  <span class="input-group-btn"><button type="button" class="btn btn-default" id="accuracySubmit">OK</button></span>
							</div>
						</form>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 text-left">
						<div class="input-group">
							<div class="input-group-addon">
								<span class="hidden-xs">Tracker ID</span>
								<span class="visible-xs"><span class="glyphicon glyphicon-user"></span></span>
							</div>
							<select class="form-control" name="tracker_id" id="trackerID_selector" style="">
						    	<option value="all"><?php echo $_config['default_trackerID']; ?></option>

							</select>
						</div>
					</div>
				</div>
			  </div>
			</div>
			<script type="text/javascript">
			window.config = {
				dateTo:'<?php echo $dateTo; ?>',
				dateFrom:'<?php echo $dateFrom; ?>',
				accuracy: <?php echo $accuracy; ?>,
				trackerID:"<?php echo $trackerID; ?>",
				default_trackerID: "<?php echo $_config['default_trackerID']; ?>"
			};
			</script>
			<script type="text/javascript" src="js/app.js"></script>
		</div>
		<div class="container">

			<div id="mapid"></div>
			<div id="velocityChart"></div>
			<div id="altitudeChart"></div>
		</div>
	</body>
</html>
