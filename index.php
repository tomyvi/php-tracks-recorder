<?php
setlocale(LC_TIME, "fr_FR");
require_once('config.inc.php');
$dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : date('Y-m-d');
$dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : date('Y-m-d');

if(isset($_GET['accuracy'])){
	$accuracy = intval($_GET['accuracy']);
}else if(isset($_COOKIE['accuracy'])){
	$accuracy = intval($_COOKIE['accuracy']);
}else{
	$accuracy = $_config['default_accuracy'];
}

?>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<link rel="icon" href="./img/favicon.ico" />

		<!-- JQUERY !-->
		<script src="./js/jquery-3.1.1.min.js"></script>

		<!-- MOMENTS.JS !-->
		<script src="./js/moment-with-locales.js"></script>

		<!-- BOOTSTRAP !-->
		<script src="./js/bootstrap.min.js" ></script>

		<!-- BOOTSTRAP DATETIMEPICKER !-->
		<script src="./js/bootstrap-datepicker.js"></script>
		<script src="./js/bootstrap-datepicker.fr.min.js"></script>

		<!-- LEAFLET.JS !-->
		<script src="./js/leaflet.js"></script>
		<script src="./js/leaflet.hotline.js"></script>

		<script src="./js/js.cookie.js"></script>

		<script src="./map_points.php?dateFrom=<?php echo $dateFrom; ?>&dateTo=<?php echo $dateTo; ?>&accuracy=<?php echo $accuracy; ?>"></script>

		<!-- BOOTSTRAP !-->
		<link rel="stylesheet" href="./css/bootstrap.min.css" />
		<link rel="stylesheet" href="./css/bootstrap-theme.min.css" />

		<!-- BOOTSTRAP DATETIMEPICKER !-->
		<link rel="stylesheet" href="./css/bootstrap-datepicker.css" />

		<!-- LEAFLET.JS !-->
		<link rel="stylesheet" href="./css/leaflet.css" />

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
					<a href="javascript:gotoDate(datePrevFrom, datePrevTo);" class="btn btn-primary" role="button">
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

						<button onclick="resetZoom();" class="btn btn-default">
							<span class="hidden-xs">Reset view</span>
							<span class="visible-xs"><span class="glyphicon glyphicon-screenshot"></span></span>
						</button>
						<a href="javascript:gotoDate();" class="btn btn-default" style="display: inline-block;" id="todayButton">
							<span class="hidden-xs">Today</span>
							<span class="visible-xs"><span class="glyphicon glyphicon-arrow-up"></span></span>
						</a>
						<a href="javascript:gotoDate(dateNextFrom, dateNextTo);" class="btn btn-primary" style="display: inline-block;" id="nextButton">
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
					  	<button onclick="showHideMarkers();" class="btn btn-default" id="markers_on">
							<span class="hidden-xs">Show markers</span>
							<span class="visible-xs"><span class="glyphicon glyphicon-map-marker"></span></span>
						</button>
					</div>
					<div class="col-xs-10 text-right">
						<form class="form-inline"><span class="hidden-xs">Accuracy : </span>
						    <div class="input-group">
						      <input type="number" size='4' class="form-control" id="accuracy" value="<?echo $accuracy; ?>" />
							  <span class="input-group-addon"><span class="hidden-xs">meters</span><span class="visible-xs">m</span></span>
							  <span class="input-group-btn"><button type="button" class="btn btn-default" id="accuracySubmit">OK</button></span>
							</div>
						</form>
					</div>
				</div>
			  </div>
			</div>
			<script>


				//dates manipulation
				function gotoDate(_dateFrom, _dateTo){
					var _dateFrom = (typeof _dateFrom !== 'undefined') ? moment(_dateFrom) : moment();
					var _dateTo = (typeof _dateTo !== 'undefined') ? moment(_dateTo) : moment();

					location.href='./?dateFrom='+_dateFrom.format('YYYY-MM-DD') + '&dateTo=' + _dateTo.format('YYYY-MM-DD');
					return false;
				}

				function gotoAccuracy(){
					var _accuracy = parseInt($('#accuracy').val());

					if(_accuracy != accuracy){

						Cookies.set('accuracy', _accuracy);
						console.log("Accuracy cookie = " + Cookies.get('accuracy'));

						location.href='./?dateFrom='+moment(dateFrom).format('YYYY-MM-DD') + '&dateTo=' + moment(dateTo).format('YYYY-MM-DD') + '&accuracy=' + _accuracy;
					}else{
						$('#configCollapse').collapse('hide');
					}
					return false;
				}


				//datetimepicker setup
				var dateFrom;
				var dateTo;
				var accuracy;
				var datePrevFrom;
				var datePrevTo;
				var dateNextFrom;
				var dateNextTo;

				$(function(){

					dateTo = moment('<?php echo $dateTo; ?>');
					dateFrom = moment('<?php echo $dateFrom; ?>');
					accuracy = <?php echo $accuracy; ?>;
					console.log("dateFrom = "+dateFrom.format('YYYY-MM-DD')+ '/' +dateFrom.format('X'));
					console.log("dateTo = "+dateTo.format('YYYY-MM-DD')+ '/' +dateTo.format('X'));
					console.log('accuracy = ' + accuracy);

					diff = dateTo.diff(dateFrom, 'days');
					//if(dateTo.isSame(dateFrom)){ diff = diff+1; }
					console.log("Diff = "+diff);

					datePrevTo = moment(dateFrom).subtract(1, 'days');;
					datePrevFrom = moment(datePrevTo).subtract(diff, 'days');
					console.log("datePrevFrom = "+datePrevFrom.format('YYYY-MM-DD'));
					console.log("datePrevTo = "+datePrevTo.format('YYYY-MM-DD'));

					dateNextFrom = moment(dateTo).add(1, 'days');
					dateNextTo = moment(dateNextFrom).add(diff, 'days');
					console.log("dateNextFrom = "+dateNextFrom.format('YYYY-MM-DD'));
					console.log("dateNextTo = "+dateNextTo.format('YYYY-MM-DD'));

					//disable Next button
					if(dateNextFrom.isAfter(moment())){
						$('#nextButton').addClass('disabled');
					}

					//disable today button
					if(dateNextFrom.isSame(moment())){
						$('#todayButton').addClass('disabled');
					}

					$('.input-daterange').datepicker({
						format: 'yyyy-mm-dd',
						language: 'fr',
						endDate: '0d',
					});

					$('.input-daterange').datepicker().on('hide', function(e) {

				        return gotoDate($('#dateFrom').val(), $('#dateTo').val());

				    });
				});

				$('#accuracy').change(function(){

					gotoAccuracy();
				});
				$('#accuracySubmit').click(function(){
					gotoAccuracy();
				});

				$('#configCollapse').on('show.bs.collapse', function (e) {
				    $('#configButton').removeClass( "btn-default" ).addClass( "btn-primary" ).addClass( "active" );
				})
				$('#configCollapse').on('hide.bs.collapse', function (e) {
				    $('#configButton').addClass( "btn-default" ).removeClass( "btn-primary" ).removeClass( "active" );
				})

			</script>
		</div>
		<div class="container">

			<div id="mapid"></div>
			<script>

				var i;
				var markers_set;
				var mymap;
				var my_marker;
				var my_markers = [];
				var my_latlngs = [];
				var polyline;
				var default_zoom;
				var default_center;

				$( document ).ready(function() {

					markers_set = Cookies.get('markers_on');

				    //set checkbox
				    if(markers_set == '1'){
				    	//hideMarkers();
						//$('#markers_on').prop('checked',false);
						$('#markers_on').removeClass( "btn-default" ).addClass( "btn-primary" ).addClass( "active" );
				    }

					mymap = L.map('mapid').setView([48.866667, 2.333333], 11);

					L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
					    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
					    subdomains: ['a','b','c']
					}).addTo( mymap );

					//
					drawMap(markers);

					//wait 1 second before reading current zoom and location
					setTimeout(function() {
						default_zoom = mymap.getZoom();
						default_center = mymap.getCenter();
					}, 1000);
				});

				function drawMap(markers){

					if(markers.length > 0){
						for ( i=0; i < markers.length; ++i )
						{

						   	dateString = markers[i].dt;
							if(markers[i].epoch != 0){
								var newDate = new Date();
								newDate.setTime(markers[i].epoch * 1000);
								dateString = newDate.toLocaleString();
							}

							var accuracyString = '<br/>Accuracy : ' + markers[i].accuracy + ' m';
							var headingString = "";
							var velocityString = "";
							if(markers[i].heading != null) headingString = '<br/>Heading : ' + markers[i].heading + ' °';
							if(markers[i].velocity != null) velocityString = '<br/>Velocity : ' + markers[i].velocity + ' km/h';

							removeString = "<br/><br/><a href='javascript:removeMarker("+ i +");'>Delete</a>";
							 addressString = "<br/><a href='http:\/\/nominatim.openstreetmap.org/reverse?format=xml&lat=" + markers[i].latitude + "&lon=" + markers[i].longitude + "'>Address</a>";
							popupString = dateString + accuracyString + addressString + headingString + velocityString + removeString;

					   		my_marker = L.marker( [markers[i].latitude, markers[i].longitude] ).bindPopup(popupString);

					   		//display marker only if cookie says to
					   		if(markers_set != '0' || i == 0 || i == markers.length-1){
					   			my_marker.addTo( mymap );
					   		}
					   		my_latlngs[i] = [markers[i].latitude, markers[i].longitude, i];


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
						   my_markers[i] = my_marker;

						}

						//var polyline = L.polyline(my_latlngs).addTo(mymap);
						polyline = L.hotline(my_latlngs, {
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

						var group = new L.featureGroup(my_markers);
			 			mymap.fitBounds(group.getBounds());

					}

					return true;
				}

				function eraseMap(){
					polyline.removeFrom(mymap);
					hideMarkers();
					return true;
				}

				//map manipulation
				function showMarkers(){
					for ( var i=1; i < markers.length-1; ++i )
					{
					   my_markers[i].addTo( mymap );
					}
					return true;
				}
				function hideMarkers(){
					for ( var i=1; i < markers.length-1; ++i )
					{
					   my_markers[i].remove();
					}
					return true;
				}
				function showHideMarkers(){
					//$('#markers_on').change(function() {
					if($('#markers_on').hasClass( "btn-default" )){
						showMarkers();
						Cookies.set('markers_on', 1, { expires: 365 });
						$('#markers_on').removeClass( "btn-default" ).addClass( "btn-primary" ).addClass( "active" );
						return true;
					}else{
						hideMarkers();
						Cookies.set('markers_on', 0, { expires: 365 });
						$('#markers_on').removeClass("btn-primary").removeClass("active").addClass("btn-default");
						return true;
					}
				}

				function removeMarker(i){


					if(confirm('Do you really want to remove marker ?')){
						console.log("Removing marker #" + i);

						//ajax call to remove marker from backend
						$.ajax({
					        url: 'rpc.php',
					        data: {
					        	'epoch': markers[i].epoch,
					        	'action': 'removeMarker'
					        },
					        type: 'post',
	        				dataType: 'json',
					        success: function(data, status)
					        {
					            console.log(data);

					        	if(data.status){
							        //removing element from JS array
									markers.splice(i, 1);

									//redraw map from scratch
									eraseMap();
									drawMap(markers);
					        	}else{
					        		console.log("Status : " + status);
					        		console.log("Data : " + data);
					        	}
					        },
					        error: function(xhr, desc, err) {
						        console.log(xhr);
						        console.log("Details: " + desc + "\nError:" + err);
					        }
					    });
					}
				}

				//zoom manipulation
				function resetZoom(){
					mymap.setView(default_center, default_zoom);
					return false;
				}

			</script>
		</div>
	</body>
</html>
