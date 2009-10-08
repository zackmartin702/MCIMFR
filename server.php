<?php    
/* server.php */    

require_once("config.php");   
   
/* Include Keith's xml-rpc library */    
include("xmlrpc.php");    
   
/* Include a file that defines all the xml-rpc "methods" */    
include("web_service_api.php");    
   
/* Now use the XMLRPC_parse function to take POST    
  data from what xml-rpc client connects and turn    
  it into normal PHP variables */    
$xmlrpc_request = XMLRPC_parse($GLOBALS['HTTP_RAW_POST_DATA']);    
   
/* From the PHP variables generated, let's get the    
  method name ie. server asks "What would you like    
  me to do for you?" */    
$methodName = XMLRPC_getMethodName($xmlrpc_request);    
   
/* Get the parameters associated with that method    
  e.g "So you want to view a news item. Tell me    
  which one you want. What's the id#?" */    
$params = XMLRPC_getParams($xmlrpc_request);    
   
/* Error check - if a method was used that doesn't    
  exist, return the error response to the client */    
if(!isset($xmlrpc_methods[$methodName])){    
   $xmlrpc_methods['method_not_found']($methodName);    
   
/* Otherwise, let's run the PHP function corresponding    
  to that method - note the functions themselves    
  return the correct formatted xml-rpc response    
  to the client */    
}else{    
   
   /* Call the method - notice $params[0] not just $params as the    
   documentation states. */    
   $xmlrpc_methods[$methodName]($params[0]);    
}    
?>