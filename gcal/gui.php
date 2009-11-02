<?php
if($_POST['action'] == "submit") { 
	require_once("../bxm156/jsonClient.php");
	$client = new jsonRPCClient;
	$result = $client->jsonRequest("generateMapFromEvents",array($_POST['email'],$_POST['password']));
	$result = json_decode($result,true);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>gCal</title>
<link href="/gcal/style.css" rel="stylesheet" type="text/css" />
<?php echo $result['response']['header']['content']; ?>
</head>

<body <?php echo $result['response']['body']['tag']; ?>>
<div align="center"><h1>
Google Calendar</h1><?php echo $result['response']['error']; ?></div>
<div id="map_canvas" class="container">
	<div class="loginForm">
    <form action="" method="post" enctype="multipart/form-data" name="gui">
          <table width="100%" border="0" cellspacing="3" cellpadding="3">
            <tr>
              <td>Email:</td>
              <td><input type="text" name="email" id="email" /></td>
            </tr>
            <tr>
              <td>Password:</td>
              <td><input type="password" name="password" id="password" /></td>
            </tr>
            <tr>
              <td colspan="2" align="center">
              <input type="submit" name="submit" id="submit" value="Login" />
              <input name="action" type="hidden" id="action" value="submit" /></td>
            </tr>
          </table>
          </form>
	</div>
</div>
</body>
</html>