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
			<script>
				//app parameters vars
				var dateFrom;
				var dateTo;
				var accuracy;
				var datePrevFrom;
				var datePrevTo;
				var dateNextFrom;
				var dateNextTo;
				var trackerID;
				var trackerIDs = [];

				// map management vars
				var i;
				var map_drawn = false;
				var show_markers;
				var mymap;
				var tid_markers; // markers collected from json
				var my_marker;
				var my_markers = [];
				var my_latlngs = [];
				var polylines = [];
				var default_zoom;
				var default_center;
				var live_view = false;
				var live_view_timer;

				var marker_start_icons = [];
				var marker_finish_icons = [];
				var marker_icons = [];




///// INIT
				$( document ).ready(function() {
					initApp();
				});

				function initApp(){
					initUI();
					initMap();
					initCharts();
				}

				function initUI(){
					console.log("initUI : INIT");


					dateTo = moment('<?php echo $dateTo; ?>');
					dateFrom = moment('<?php echo $dateFrom; ?>');




					$('.input-daterange').datepicker({
						format: 'yyyy-mm-dd',
						language: 'fr',
						endDate: '0d',
					});

					$('.input-daterange').datepicker().on('hide', function(e) {
				        return gotoDate($('#dateFrom').val(), $('#dateTo').val());
				    });

					//accuracy event handlers
					accuracy = <?php echo $accuracy; ?>;
					$('#accuracy').change(function(){
						gotoAccuracy();
					});
					$('#accuracySubmit').click(function(){
						gotoAccuracy();
					});


					//trackerID event handlers
					trackerID = "<?php echo $trackerID; ?>";

					$('#trackerID_selector').change(function(){
						gotoTrackerID();
					});

					$('#configCollapse').on('show.bs.collapse', function (e) {
					    $('#configButton').removeClass( "btn-default" ).addClass( "btn-primary" ).addClass( "active" );
					})
					$('#configCollapse').on('hide.bs.collapse', function (e) {
					    $('#configButton').addClass( "btn-default" ).removeClass( "btn-primary" ).removeClass( "active" );
					})

					//setup history popupstate event handler
					window.onpopstate = handlePopState;
				}

				/**
				* initiate map config and fire getMarkers function
				* fired once on document.ready
				*/
				function initMap(){
					console.log("initMap : INIT");

					show_markers = Cookies.get('show_markers');
					console.log("initMap : INFO show_markers = " + show_markers);

					marker_start_icons[0] = L.AwesomeMarkers.icon({icon: 'play', markerColor: 'blue', iconColor: 'green' });
					marker_start_icons[1] = L.AwesomeMarkers.icon({icon: 'play', markerColor: 'red', iconColor: 'green' });
					marker_start_icons[2] = L.AwesomeMarkers.icon({icon: 'play', markerColor: 'orange', iconColor: 'green' });
					marker_start_icons[3] = L.AwesomeMarkers.icon({icon: 'play', markerColor: 'green', iconColor: 'darkgreen' });
					marker_start_icons[4] = L.AwesomeMarkers.icon({icon: 'play', markerColor: 'purple', iconColor: 'green' });
					marker_start_icons[5] = L.AwesomeMarkers.icon({icon: 'play', markerColor: 'cadetblue', iconColor: 'green' });
					marker_start_icons[6] = L.AwesomeMarkers.icon({icon: 'play', markerColor: 'darkred', iconColor: 'green' });
					marker_start_icons[7] = L.AwesomeMarkers.icon({icon: 'play', markerColor: 'darkgreen', iconColor: 'green' });
					marker_start_icons[8] = L.AwesomeMarkers.icon({icon: 'play', markerColor: 'darkpuple', iconColor: 'green' });

				    marker_finish_icons[0] = L.AwesomeMarkers.icon({icon: 'stop', markerColor: 'blue', iconColor: 'red' });
					marker_finish_icons[1] = L.AwesomeMarkers.icon({icon: 'stop', markerColor: 'red', iconColor: 'darkred' });
					marker_finish_icons[2] = L.AwesomeMarkers.icon({icon: 'stop', markerColor: 'orange', iconColor: 'red' });
					marker_finish_icons[3] = L.AwesomeMarkers.icon({icon: 'stop', markerColor: 'green', iconColor: 'red' });
					marker_finish_icons[4] = L.AwesomeMarkers.icon({icon: 'stop', markerColor: 'purple', iconColor: 'red' });
					marker_finish_icons[5] = L.AwesomeMarkers.icon({icon: 'stop', markerColor: 'cadetblue', iconColor: 'red' });
					marker_finish_icons[6] = L.AwesomeMarkers.icon({icon: 'stop', markerColor: 'darkred', iconColor: 'red' });
					marker_finish_icons[7] = L.AwesomeMarkers.icon({icon: 'stop', markerColor: 'darkgreen', iconColor: 'red' });
					marker_finish_icons[8] = L.AwesomeMarkers.icon({icon: 'stop', markerColor: 'darkpuple', iconColor: 'red' });

				    marker_icons[0] = L.AwesomeMarkers.icon({icon: 'user', markerColor: 'blue' });
					marker_icons[1] = L.AwesomeMarkers.icon({icon: 'user', markerColor: 'red' });
					marker_icons[2] = L.AwesomeMarkers.icon({icon: 'user', markerColor: 'orange' });
					marker_icons[3] = L.AwesomeMarkers.icon({icon: 'user', markerColor: 'green' });
					marker_icons[4] = L.AwesomeMarkers.icon({icon: 'user', markerColor: 'purple' });
					marker_icons[5] = L.AwesomeMarkers.icon({icon: 'user', markerColor: 'cadetblue' });
					marker_icons[6] = L.AwesomeMarkers.icon({icon: 'user', markerColor: 'darkred' });
					marker_icons[7] = L.AwesomeMarkers.icon({icon: 'user', markerColor: 'darkgreen' });
					marker_icons[8] = L.AwesomeMarkers.icon({icon: 'user', markerColor: 'darkpuple' });

				    //set checkbox
				    if(show_markers == '1'){
				    	//hideMarkers();
						//$('#show_markers').prop('checked',false);
						$('#show_markers').removeClass( "btn-default" ).addClass( "btn-primary" ).addClass( "active" );
				    }

					mymap = L.map('mapid').setView([48.866667, 2.333333], 11);

					L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
					    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
					    subdomains: ['a','b','c']
					}).addTo( mymap );

					getMarkers();

				}

				function initCharts(){

				}

///// EVENT HANDLERS
				/**
				* handles navigation to another date range
				*/
				function gotoDate(_dateFrom, _dateTo, pushState){
					console.log("gotoDate : INIT");

					var _dateFrom = (typeof _dateFrom !== 'undefined') ? moment(_dateFrom) : moment();
					var _dateTo = (typeof _dateTo !== 'undefined') ? moment(_dateTo) : moment();
					var pushState = (typeof pushState !== 'undefined') ? pushState : true;

					dateFrom = _dateFrom;
					dateTo = _dateTo;

					$('#dateFrom').val(moment(dateFrom).format('YYYY-MM-DD'));
					$('#dateTo').val(moment(dateTo).format('YYYY-MM-DD'));


					//push selected dates in window.history stack
					if(pushState) { window.history.pushState(
							{dateFrom: moment(dateFrom).format('YYYY-MM-DD'), dateTo: moment(dateTo).format('YYYY-MM-DD')},
							'',
							window.location.pathname + '?dateFrom=' + moment(dateFrom).format('YYYY-MM-DD') + '&dateTo=' + moment(dateTo).format('YYYY-MM-DD')
							);
					}

					getMarkers();
					return false;
				}

				/**
				* Adds two numbers
				* @return {Number} sum
				*/
				function gotoAccuracy(){
					console.log("gotoAccuracy : INIT");

					var _accuracy = parseInt($('#accuracy').val());

					if(_accuracy != accuracy){

						Cookies.set('accuracy', _accuracy);
						console.log("Accuracy cookie = " + Cookies.get('accuracy'));

						//location.href='./?dateFrom='+moment(dateFrom).format('YYYY-MM-DD') + '&dateTo=' + moment(dateTo).format('YYYY-MM-DD') + '&accuracy=' + _accuracy + '&trackerID=' + trackerID;

						accuracy = _accuracy;

						getMarkers();

					}else{
						$('#configCollapse').collapse('hide');
					}
					return false;
				}

				/**
				* reset map on tracker ID change
				*/
				function gotoTrackerID(){
					console.log("gotoTrackerID : INIT");

					var _trackerID = $('#trackerID_selector').val();

					if(_trackerID != trackerID){

						Cookies.set('trackerID', _trackerID);
						console.log("gotoTrackerID : INFO trackerID cookie = " + Cookies.get('trackerID'));

						trackerID = _trackerID;
						drawMap();

					}else{
						$('#configCollapse').collapse('hide');
					}
					return false;

				}

				function handlePopState(event){
					console.log("handlePopState : INIT");
					console.log(event);

					return gotoDate(event.state.dateFrom, event.state.dateTo, false);

				}

				/**
				* Sets up the live update of new recorded markers
				*/
				function setLiveMap(){
					console.log("setLiveMap : INIT");
					live_view = !live_view;

					if(live_view){
						live_view_timer = setTimeout(getMarkers(), 3000);
						$('#livemap_on').removeClass( "btn-default" ).addClass( "btn-primary" ).addClass( "active" );

					}else{
						clearTimeout(live_view_timer);
						$('#livemap_on').addClass( "btn-default" ).removeClass( "btn-primary" ).removeClass( "active" );
					}
				}



///// UI HANDLERS
				/**
				* updates config bar UI based on user params
				*/
				function updateNavbarUI(_dateFrom, _dateTo){
					console.log("updateNavbarUI : INIT");


					if(typeof _dateFrom == "undefined") { _dateFrom = dateFrom; }
					if(typeof _dateTo == "undefined") { _dateTo = dateTo; }


					diff = _dateTo.diff(_dateFrom, 'days');
					//if(dateTo.isSame(dateFrom)){ diff = diff+1; }

					datePrevTo = moment(_dateFrom).subtract(1, 'days');
					datePrevFrom = moment(datePrevTo).subtract(diff, 'days');

					dateNextFrom = moment(_dateTo).add(1, 'days');
					dateNextTo = moment(dateNextFrom).add(diff, 'days');

					//disable Next button
					if(dateNextFrom.isAfter(moment())){
						$('#nextButton').addClass('disabled');
					}else{
						$('#nextButton').removeClass('disabled');
					}

					//disable today button
					if(dateNextFrom.isSame(moment())){
						$('#todayButton').addClass('disabled');
						$('#livemap_on').removeClass('disabled');
					}else{
						$('#todayButton').removeClass('disabled');
						$('#livemap_on').addClass('disabled');
					}
				}

				/*
				* UI update
				* updates tracker ID selection dropdown list from config
				*
				*/

				function updateTrackerIDs(_tid_markers){
					console.log("updateTrackerIDs : INIT");

					try{
						$("#trackerID_selector option[value!='<?php echo $_config['default_trackerID']; ?>']").each(function() {
						    $(this).remove();
						});

						if(typeof _tid_markers != "undefined" && _tid_markers != null) {
							trackerIDs = Object.keys(_tid_markers);

							$.each(trackerIDs, function( index, value ) {
								$('#trackerID_selector').append($('<option>', {
								    value: value,
								    text: value
								}));
							});

							$("#trackerID_selector").val(trackerID);


						}else{
							console.log("updateTrackerIDs : INFO no trackerID found in markers json");
					    	return ;
						}


					}catch(err) {
					    console.log("updateTrackerIDs : ERROR " + err.message);
					    alert( err.message );
					}


				}

				/**
				* Draws a set of location tracks per tid in _tid_markers array
				* @param {Array} _tid_markers
				*/
				function drawMap(_tid_markers){
					console.log("drawMap : INIT");

					try{
						if((typeof _tid_markers == "undefined" || _tid_markers == null) && typeof tid_markers != "undefined" && tid_markers != null) {
							_tid_markers = tid_markers;
							console.log("drawMap : INFO null param given but global markers available !");
						}else if(typeof _tid_markers != "undefined" && _tid_markers != null) {
							tid_markers = _tid_markers;
							console.log("drawMap : INFO non null param given !");
						}else{
							console.log("drawMap : ERROR null param given and global markers not available !");
							alert('No location markers collected for selected dates and accuracy !');
							return;
						}

						console.log("drawMap : INFO tid_markers = ");
						console.log(tid_markers);

						//vars for map bounding
						max_lat = -1000;
						min_lat = 1000;
						max_lon = -1000;
						min_lon = 1000;

						if(map_drawn){ eraseMap(); }

						nb_markers=0; // global markers counter
						trackerIDs = Object.keys(_tid_markers);

						tid_markers = []; // markers collected from json
						my_markers = [];
						my_latlngs = [];
						polylines = [];


						if(trackerIDs.length > 0){


							for ( j=0; j < trackerIDs.length; ++j ){


								tid = trackerIDs[j];
								markers = _tid_markers[tid];
								my_latlngs[tid] = [];
								my_markers[tid] = [];

								if(trackerID == "<?php echo $_config['default_trackerID']; ?>" || trackerID == tid){

									var trackerIDString = '<br/>TrackerID : ' + tid;

									if(markers.length > 0){
										for ( i=0; i < markers.length; ++i ) {

										   	nb_markers = nb_markers+1;
										   	dateString = markers[i].dt;
											if(markers[i].epoch != 0){
												var newDate = new Date();
												newDate.setTime(markers[i].epoch * 1000);
												dateString = newDate.toLocaleString();
											}

											var accuracyString = '<br/>Accuracy : ' + markers[i].accuracy + ' m';
											var headingString = "";
											var velocityString = "";
											var locationString = "";
											if(markers[i].heading != null) headingString = '<br/>Heading : ' + markers[i].heading + ' °';
											if(markers[i].velocity != null) velocityString = '<br/>Velocity : ' + markers[i].velocity + ' km/h';
											if(markers[i].display_name != null){
												locationString = "<br/>Location : <a role='button' onclick='showBoundingBox("+ i +");' title='Show location bounding box' >" + markers[i].display_name + '</a>';
											}else{
												locationString = "<br/>Location : <span id='loc_"+ i +"'><a role='button' onclick='geodecodeMarker("+ i +");' title='Get location (geodecode)'>Get location</a></span>";
											}

											removeString = "<br/><br/><a href=\"#\" onclick=\"deleteMarker('"+ tid +"', "+ i +");\">Delete marker</a>";

											//prepare popup HTML code for marker
											popupString = dateString + trackerIDString + accuracyString + headingString + velocityString + locationString + removeString;

										   	//create leaflet market object with custom icon based on tid index in array
									   		//first marker
									   		if(i == 0){
									   			my_marker = L.marker( [markers[i].latitude, markers[i].longitude], {icon: marker_start_icons[j]} ).bindPopup(popupString);
									   		//last marker
									   		}else if(i == markers.length-1){
									   			my_marker = L.marker( [markers[i].latitude, markers[i].longitude], {icon: marker_finish_icons[j]} ).bindPopup(popupString);
									   		//all other markers
									   		}else{
									   			my_marker = L.marker( [markers[i].latitude, markers[i].longitude], {icon: marker_icons[j]} ).bindPopup(popupString);
									   		}

									   		if(max_lat < markers[i].latitude) { max_lat = markers[i].latitude; }
									   		if(min_lat > markers[i].latitude) { min_lat = markers[i].latitude; }
									   		if(max_lon < markers[i].longitude) { max_lon = markers[i].longitude; }
									   		if(min_lon > markers[i].longitude) { min_lon = markers[i].longitude; }

									   		//add marker to map only if cookie 'show_markers' says to or if 1st or last marker
									   		if(show_markers != '0' || i == 0 || i == markers.length-1){
									   			my_marker.addTo( mymap );
									   		}
									   		/*
									   		//default show popup for last marker of track
									   		if(i == markers.length-1){
									   			my_marker.addTo( mymap ).openPopup();;
									   		}
									   		*/

									   		//collect all markers location to prepare drawing track, per trackerID
									   		my_latlngs[tid][i] = [markers[i].latitude, markers[i].longitude, i];


									   		//todo : onmouseover marker, display accuracy radius
									   		//if(markers[i].acc > 0){
										   /*
										   if(i+1 == markers.length && markers[i].acc > 0){
										   		var circle = L.circle(my_latlngs[i], {
												    opacity: 0.2,
												    radius: markers[i].acc
												}).addTo(mymap);
										   }
										   */

										   //array of all markers for display / hide markers + initial auto zoom scale
										   my_markers[tid][i] = my_marker;

										}

										//var polylines[tid] = L.polyline(my_latlngs[tid]).addTo(mymap);
										polylines[tid] = L.hotline(my_latlngs[tid], {
												min: 0,
												max: markers.length,
												palette: {
													0.0: 'green',
													0.5: 'yellow',
													1.0: 'red'
												},
												weight: 4,
												outlineColor: '#000000',
												outlineWidth: 0.5
										}).addTo(mymap);

									}else{
										console.log("drawMap : ERROR No location data for trackerID '" + trackerID + "' found !");
										alert('No location data for trackerID \'' + trackerID + '\' found !');
									}


								}


							}





						}else{
							console.log("drawMap : ERROR No location data found for any trackerID !");
							alert('No location data found for any trackerID !');
						}

						//save default zoom scale
						setDefaultZoom();

						//auto zoom scale based on all markers location
						mymap.fitBounds([
						    [min_lat, min_lon],
						    [max_lat, max_lon]
						]);

						//set map drawn flag
						map_drawn = true;

						return true;

					}catch(err) {
					    console.log("drawMap : ERROR " + err.message);
						alert( err.message );
						map_drawn = false;
						return false;
					}


				}

				function setDefaultZoom(){
					console.log("setDefaultZoom : INIT");

					setTimeout(function() {
						default_zoom = mymap.getZoom();
						default_center = mymap.getCenter();
					}, 2000);

				}

				/**
				* Clears all markers on current map
				*/
				function eraseMap(){
					console.log("eraseMap : INIT");

					$.each(trackerIDs, function(_index, _tid){
						if(_tid in polylines) { polylines[_tid].removeFrom(mymap); }
				    });

					$.each(trackerIDs, function(_index, _tid){

						//if(trackerID == _tid || trackerID == "<?php echo $_config['default_trackerID']; ?>"){

							$.each(my_markers[_tid], function(_index2, _marker){

								_marker.remove();

							});
						//}

					});

					return true;
				}

				/**
				* Displays Icons for each marker
				*/
				function showMarkers(){
					console.log("showMarkers : INIT");

					$.each(trackerIDs, function(_index, _tid){

						if(trackerID == _tid || trackerID == "<?php echo $_config['default_trackerID']; ?>"){

							$.each(my_markers[_tid], function(_index2, _marker){

								//add marker to map except first & last (never removed)
								if(_index2 != 0 || _index2 !=my_markers[_tid].length){
									_marker.addTo( mymap );
								}
							});
						}

					});
					return true;
				}

				/**
				* Hide icons for each markers except 1st & last
				*/
				function hideMarkers(){
					console.log("hideMarkers : INIT");

					$.each(trackerIDs, function(_index, _tid){

						if(trackerID == _tid || trackerID == "<?php echo $_config['default_trackerID']; ?>"){

							$.each(my_markers[_tid], function(_index2, _marker){
								//remove marker except first & last
								if(_index2 > 0 && _index2 < my_markers[_tid].length-1){
									_marker.remove();
								}
							});
						}

					});

					return true;
				}

				/**
				* Toggle to display or hide icons for markers
				*/
				function showHideMarkers(){
					console.log("showHideMarkers : INIT");
					//$('#show_markers').change(function() {
					if($('#show_markers').hasClass( "btn-default" )){
						showMarkers();
						Cookies.set('show_markers', 1, { expires: 365 });
						show_markers = 1;
						$('#show_markers').removeClass( "btn-default" ).addClass( "btn-primary" ).addClass( "active" );
						return true;
					}else{
						hideMarkers();
						Cookies.set('show_markers', 0, { expires: 365 });
						show_markers = 0;
						$('#show_markers').removeClass("btn-primary").removeClass("active").addClass("btn-default");
						return true;
					}
				}

				/**
				* reset zoom to stored level at initial load/display
				*/
				function resetZoom(){
					console.log("resetZoom : INIT");
					mymap.setView(default_center, default_zoom);
					return false;
				}

				function drawAltitudeChart(){

				}

				function drawVelocityChart(){

				}


///// DATA HANDLERS
				/**
				* get the markers data from RPC and fires drawMap function if success
				*/
				function getMarkers(){
					console.log("getMarkers : INIT");

					//ajax call to get list of markers
					$.ajax({
				        url: 'rpc.php',
				        data: {
				        	'dateFrom': dateFrom.format('YYYY-MM-DD'),
				        	'dateTo': dateTo.format('YYYY-MM-DD'),
				        	'accuracy': accuracy,
				        	//'trackerID' : trackerID,
				        	//'epoc': time(),
				        	'action': 'getMarkers'
				        },
				        type: 'GET',
        				dataType: 'json',
        				beforeSend: function(xhr)
        				{
        					$('#mapid').css('filter','blur(5px)');
        				},
				        success: function(data, status)
				        {
				            if(data.status){

								jsonMarkers = JSON.parse(data.markers);

								if(handleMarkersData(jsonMarkers)){ $('#mapid').css('filter','blur(0px)'); }

				        	}else{
				        		console.log("getMarkers : ERROR Status : " + status);
				        		console.log("getMarkers : ERROR Data : ");
				        		console.log(data);
				        	}
				        },
				        error: function(xhr, desc, err) {
					        console.log(xhr);
					        console.log("getMarkers : ERROR Details: " + desc + "\nError:" + err);
				        }
				    });

				}

				/* Call-back function following ajax call to get markers (json decoded)
				*/
				function handleMarkersData(_tid_markers){

					drawMap(_tid_markers);

					drawVelocityChart(_tid_markers);
					drawAltitudeChart(_tid_markers);

					updateTrackerIDs(_tid_markers);
					updateNavbarUI();

					return true;

				}

				/**
				* get human readable location information for a specific location marker
				*/
				function geodecodeMarker(i){
					console.log("geodecodeMarker : INIT");

					console.log("geodecodeMarker : INFO Geodecoding marker #" + i);

					//ajax call to remove marker from backend
					$.ajax({
				        url: 'rpc.php',
				        data: {
				        	'epoch': markers[i].epoch,
				        	'action': 'geoDecode'
				        },
				        type: 'get',
        				dataType: 'json',
				        success: function(data, status)
				        {
				            if(data.status){

				            	console.log("geodecodeMarker : INFO Status : " + status);
				        		console.log("geodecodeMarker : INFO Data : " + data);

						        //update marker data
						        $('#loc_'+i).html("<a role='button' onclick='showBoundingBox("+ i +");' title='Show location bounding box' >" + data.location + "</a>");

				        	}else{
				        		console.log("geodecodeMarker : ERROR Status : " + status);
				        		console.log("geodecodeMarker : ERROR Data : " + data);
				        	}
				        },
				        error: function(xhr, desc, err) {
					        console.log(xhr);
					        console.log("geodecodeMarker : ERROR Details: " + desc + "\nError:" + err);
				        }
				    });

				}

				/**
				* removes marker from map & deletes from DB
				*/
				function deleteMarker(tid, i){
					console.log("deleteMarker : INIT tid = "+tid+" i = "+i);


					if(confirm('Do you really want to permanently delete marker ?')){
						console.log("deleteMarker : INFO Removing marker #" + i);

						//ajax call to remove marker from backend
						$.ajax({
					        url: 'rpc.php',
					        data: {
					        	'epoch': tid_markers[tid][i].epoch,
					        	'action': 'deleteMarker'
					        },
					        type: 'get',
	        				dataType: 'json',
					        success: function(data, status)
					        {
					            if(data.status){
							        //removing element from JS array
									tid_markers[tid].splice(i, 1);


									drawMap();
					        	}else{
					        		console.log("deleteMarker : ERROR Status : " + status);
					        		console.log("deleteMarker : ERROR Data : " + data);
					        	}
					        },
					        error: function(xhr, desc, err) {
						        console.log(xhr);
						        console.log("deleteMarker : ERROR Details: " + desc + "\nError:" + err);
					        }
					    });
					}
				}

				/**
				* displays the bounding box around a location data
				*/
				function showBoundingBox(i){
					console.log("showBoundingBox : INIT i = "+i);



				}

			</script>
		</div>
		<div class="container">

			<div id="mapid"></div>
			<div id="velocityChart"></div>
			<div id="altitudeChart"></div>
		</div>
	</body>
</html>
