<?php 
class jsonRPCServer {
	public static function handle($object) {
	// checks if a JSON-RCP request has been received
		if (
			$_SERVER['REQUEST_METHOD'] != 'POST' || empty($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] != 'application/json') {
			// This is not a JSON-RPC request
			return false;
		}	
		$postRequest = file_get_contents('php://input'); //Read input of POST
		$request = json_decode($postRequest,true); //Convert JSON to associative array
		$output = array();
		if($result = @call_user_func_array(array($object,$request['method']),$request['params'])) {
			//Success
			$output['id'] = $request['id'];
			$output['response'] = $result;
		} else {
			//Somethings Wrong!
			$output['id'] = $request['id'];
			$output['response'] = "Error: Bad Input";
			$output['input'] = $request;
		}
		header('content-type: text/javascript');
		echo json_encode($output);
		return true;
	}
	public static function announce() {
		echo "GoogleMashups JSON Server ver 1";
		return true;
	}
	
}
?>