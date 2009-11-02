<?php
require_once("../bxm156/jsonClient.php");
if(isset($_POST['email']) and !empty($_POST['email']) and isset($_POST['password']) and !empty($_POST['password'])) {
	$email = $_POST['email'];
	$password = $_POST['password'];
} else {
	$email = "mashupsdemo@gmail.com";
	$password = "eecsdemo";
}
$client = new jsonRPCClient;
$events = $client->jsonRequest("getEventsFromCalendar",array($email,$password));
$events = json_decode($events,true);
$id = 0;
foreach ($events['response'] as $event) {
	$marker[$id]['title'] = $event['title'];
	$marker[$id]['where'] = $event['where'];
	if(!empty($event['where'])) {
		$coords = json_decode($client->jsonRequest("getLatLongFromAddress",array($event['where'])),true);
		if($coords['response']['status']) {
			$lat = $coords['response']['lat'];
			$long = $coords['response']['long'];
			$marker[$id]['lat'] = $coords['response']['lat'];
			$marker[$id]['long'] = $coords['response']['long'];
		}
	}
	$marker[$id]['startTime'] = $event['startTime'];
	$marker[$id]['endTime'] = $event['endTime'];
	$id++;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Google Calendar</title>
 
        <script type="text/javascript" src="http://www.google.com/jsapi?key=ABQIAAAAfuR-sgGlCTS-dM0Zf_jZJhRYExG3uEnNJ6jVzGskdzaq9B1vdRQL9kK2Ga2XKdspbKPP44OOKnW8fw"></script>
        <script type="text/javascript">

        google.load("maps", "2");
        google.load("elements", "1", {packages : ["localsearch"]});
        </script>
    <script type="text/javascript">
	
     function addMarker(map,latlng,title,where,startTime,endTime) {
     	var marker = new GMarker(latlng);
      	var html = "<b>" + title + "</b> <br/>" + "Where: " + where + "<br />Start Time: " + startTime + "<br />End Time: " + endTime;
      	GEvent.addListener(marker, 'click', function() {
        	marker.openInfoWindowHtml(html);
      	});
	  	map.addOverlay(marker);
    }

    function initialize() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map_canvas"));
        map.setCenter(new GLatLng(<?php echo $lat.",".$long; ?>), 13);
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());
		map.addControl(new google.elements.LocalSearch());

 
        // Add 10 markers to the map at random locations

 		<?php foreach($marker as $point) {
			if(!empty($point['lat']) and !empty($point['long'])) { ?>
		  addMarker(map,new GLatLng(<?php echo $point['lat'].",".$point['long']; ?>),"<?php echo $point['title']; ?>","<?php echo $point['where']; ?>","<?php echo date("M d, Y g:ia",strtotime($point['startTime'])); ?>","<?php echo date("M d, Y g:ia",strtotime($point['endTime'])); ?>");
		<?php } } ?>  
		  
        
      }
    }

    </script>

</head>

<body onload="initialize()" onunload="GUnload()">
<div style="width:700px;clear:both;"><form action="" method="post" enctype="multipart/form-data" name="gcal"><table border="0" align="center" cellpadding="3" cellspacing="3">
  <tr>
    <td>Email:</td>
    <td><input type="text" name="email" id="email" /></td>
  </tr>
  <tr>
    <td>Password:</td>
    <td><input type="password" name="password" id="password" /></td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input type="submit" name="submit" id="submit" value="Submit" /></td>
    </tr>
</table>
</form>
</div>
	<div style="width:100%;">
    <div style="width:700px; float:left;">
    <div id="map_canvas" style="width: 700px; height: 500px"></div>
    </div>
    <div style="width:400px;float:right;font-size:14px;"><?php echo print(nl2br(str_replace(' ','&nbsp;',print_r($events,true)))); ?></div>
  </div>
  </body>

</html>