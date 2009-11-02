<?php
class mashupAPI {
	public function name($name) {
		$string = "Hello ".$name;
		return $string;
	}
	public function getCalendarList($user,$pass) {
		require_once('Zend/Loader.php');
		Zend_Loader::loadClass('Zend_Gdata');
		Zend_Loader::loadClass('Zend_Gdata_AuthSub');
		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
		Zend_Loader::loadClass('Zend_Gdata_Calendar');
		$service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME; // predefined service name for calendar
		$client = Zend_Gdata_ClientLogin::getHttpClient($user,$pass,$service);
		if(!$client) {
			return "Bad Credentials";
		}
		$gdataCal = new Zend_Gdata_Calendar($client);
  		$calFeed = $gdataCal->getCalendarListFeed();
		$id=0;
  		foreach ($calFeed as $calendar) {
 			$result[$id]['title'] = $calendar->title->text;
			$result[$id]['url'] = $calendar->link[0]->href;
			$id++;
		}
		return $result;
	}
	public function getEventsFromCalendar($user,$pass) {
		require_once('Zend/Loader.php');
		Zend_Loader::loadClass('Zend_Gdata');
		Zend_Loader::loadClass('Zend_Gdata_AuthSub');
		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
		Zend_Loader::loadClass('Zend_Gdata_Calendar');
		$service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME; // predefined service name for calendar
		$client = Zend_Gdata_ClientLogin::getHttpClient($user,$pass,$service);
  		$gdataCal = new Zend_Gdata_Calendar($client);
  		//Below: Specific dates only grabbed from calednar
		$query = $gdataCal->newEventQuery();
  		$query->setUser('default');
  		$query->setVisibility('private');
  		$query->setProjection('full');
  		$query->setOrderby('starttime');
  		$query->setStartMin(date("Y-m-01"));
  		$eventFeed = $gdataCal->getCalendarEventFeed($query);

		//$eventFeed = $gdataCal->getCalendarEventFeed();
		$id = 0;
  		foreach ($eventFeed as $event) {
			//$id = $event->id->text
			
			$title = $event->title->text;
			$result[$id]['title']=$title;
    		foreach ($event->when as $when) {
				$result[$id]['startTime'] = $when->startTime;
				$result[$id]['endTime'] = $when->endTime;
			}
			$result[$id]['where'] = $event->where[0]->valueString;
			$id++;
    	}
		return $result;
	}
	public function getLatLongFromAddress($address) {
		$array['key'] = "ABQIAAAAfuR-sgGlCTS-dM0Zf_jZJhRYExG3uEnNJ6jVzGskdzaq9B1vdRQL9kK2Ga2XKdspbKPP44OOKnW8fw";
		$array['sensor'] = false;
		$array['output'] = "json";
		$address ="http://maps.google.com/maps/geo?q=".urlencode($address)."&sensor=false&key=".$array['key'];
		$page = file_get_contents($address);
		$array = json_decode($page,true);
		if($array['Status']['code'] == "200") {
			$output['status'] = true;
			$output['lat'] = $array['Placemark'][0]['Point']['coordinates'][1];
			$output['long'] = $array['Placemark'][0]['Point']['coordinates'][0];
		} else {
			$output['status'] = false;
			$output['Error'] = "Unable to find address: ".$address;
		}
		return $output;
	}
	public function generateMapFromEvents($email,$password) {
		require("jsonClient.php");;
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
		$output['header']['content'] = 
		"<script type=\"text/javascript\" src=\"http://www.google.com/jsapi?key=ABQIAAAAfuR-sgGlCTS-dM0Zf_jZJhRYExG3uEnNJ6jVzGskdzaq9B1vdRQL9kK2Ga2XKdspbKPP44OOKnW8fw\"></script>
        <script type=\"text/javascript\">
        google.load(\"maps\", \"2\");
        google.load(\"elements\", \"1\", {packages : [\"localsearch\"]});
        </script>
    <script type=\"text/javascript\">
	
     function addMarker(map,latlng,title,where,startTime,endTime) {
     	var marker = new GMarker(latlng);
      	var html = \"<b>\" + title + \"</b> <br/>\" + \"Where: \" + where + \"<br />Start Time: \" + startTime + \"<br />End Time: \" + endTime;
      	GEvent.addListener(marker, 'click', function() {
        	marker.openInfoWindowHtml(html);
      	});
	  	map.addOverlay(marker);
    }

    function initialize() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById(\"map_canvas\"));
        map.setCenter(new GLatLng(".$lat.",".$long."), 13);
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());
		map.addControl(new google.elements.LocalSearch());\n";

 		foreach($marker as $point) {
			if(!empty($point['lat']) and !empty($point['long'])) {
		  		$output['header']['content'] .= "addMarker(map,new GLatLng(".$point['lat'].",".$point['long']."),"."\"".$point['title']."\",\"".$point['where']."\",\"".date("M d, Y g:ia",strtotime($point['startTime']))."\",\"".date("M d, Y g:ia",strtotime($point['endTime']))."\");\n";
		  
			}
		}
      

   		$output['header']['content'] .= "} } </script>";
		$output['body']['tag'] = "onload=\"initialize()\" onunload=\"GUnload()\"";
		reset($marker);
		$output['events'] = $marker;
		return $output;
	}
}	
?>