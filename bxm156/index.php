<?php
if(!function_exists('str_getcsv')) {
    function str_getcsv($input, $delimiter = ",", $enclosure = '"', $escape = "\\") {
        $fp = fopen("php://memory", 'r+');
        fputs($fp, $input);
        rewind($fp);
        $data = fgetcsv($fp, null, $delimiter, $enclosure); // $escape only got added in 5.3.0
        fclose($fp);
        return $data;
    }
}
if($_POST['action'] == "submit") {
	require("jsonClient.php");
	$client = new jsonRPCClient;
	$method = $_POST['method'];
	//$args = explode(",",$_POST['args']);
	$args = str_getcsv($_POST['args']);
	$response = $client->jsonRequest($method,$args,1);
	$array = json_decode($response,true);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>JSON Demo</title>
</head>

<body>
<div align="center"><h1>EECS 396 Demonstration of JSON-RPC Server</h1></div>
<?php if(!empty($response)) { ?>
<div align="center"><?php echo $response; ?><br /><br/><?php print_r($array); ?></div>
<?php } ?>
<form id="jsonform" name="jsonform" method="post" action="">
  <table width="0" border="0" align="center" cellpadding="3" cellspacing="3">
    <tr>
      <td>Method</td>
      <td><textarea name="method" cols="50" rows="5" id="method"></textarea></td>
    </tr>
    <tr>
      <td>ARGS</td>
      <td><textarea name="args" cols="50" rows="5" id="args"></textarea></td>
    </tr>
    <tr>
      <td colspan="2" align="center"><input type="submit" name="Submit JSON" id="Submit JSON" value="Submit JSON" />
      <input name="action" type="hidden" id="action" value="submit" /></td>
    </tr>
  </table>
</form>
</body>
</html>