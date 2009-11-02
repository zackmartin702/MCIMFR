<?php
class jsonRPCClient {
	private $url;
	public function __construct($url= "http://googlemashups.case.edu/bxm156/server.php") {
		$this->url = $url;
	}
	public function __destroy() {
		unset($this->url);
		
	}
	public function __call($method,$args) {
		jasonRequest($method,$args,1);
	}
	public function jsonRequest($method, $args, $id = 1) {
		$request = array();
		$request['id'] = $id;
		if (!is_scalar($method)) {
			die('Method name has no scalar value');
		}
		if (is_array($args)) {
			$args = array_values($args);
		} else {
			die('Params must be given as array');
		}
		
		$request['method'] = $method;
		$request['params'] = $args;
		$jsonRequest = json_encode($request);
		// performs the HTTP POST
		$opts = array ('http' => array (
							'method'  => 'POST',
							'header'  => 'Content-type: application/json',
							'content' => $jsonRequest
							));
		$context  = stream_context_create($opts);
		if ($fp = fopen($this->url, 'r', false, $context)) {
			$response = '';
			while($row = fgets($fp)) {
				$response.= trim($row)."\n";
			}
			//$response = json_decode($response,true); // Gets associative array!
		} else {
			die("unable to connect");
		}
		return $response;
	}
}
?>