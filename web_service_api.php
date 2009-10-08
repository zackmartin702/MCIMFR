<?php    
/* web_service_api.php */   

// this file containes an example of how to write the functions we will be needing
   
/* Define an array to name the xmlrpc methods and      
  their corresponding PHP functions */    
$xmlrpc_methods = array();    
$xmlrpc_methods['news.getNewsList'] = news_getNewsList;    
$xmlrpc_methods['news.viewNewsItem'] = news_viewNewsItem;    
$xmlrpc_methods['method_not_found'] = XMLRPC_method_not_found;    
   
/* Now a useful function for converting MySQL datetime      
  to a UNIX timestamp which can then be used with      
  the XMLRPC_convert_timestamp_to_iso8601($timestamp) function.    
  This is not a method!    
  It comes from: http://www.zend.com/codex.php?id=176&single=1 */    
function mysql_datetime_to_timestamp($dt) {      
   $yr=strval(substr($dt,0,4));      
   $mo=strval(substr($dt,5,2));      
   $da=strval(substr($dt,8,2));      
   $hr=strval(substr($dt,11,2));      
   $mi=strval(substr($dt,14,2));      
   $se=strval(substr($dt,17,2));      
   return mktime($hr,$mi,$se,$mo,$da,$yr);      
}      
   
/* Function for listing news items, corresponding      
  to the news.getNewsList method Allows ordering by      
  column name and a result limit of up to 20 rows */    
function news_getNewsList ( $query_info=0 ) {    
   
   /* Define an array of column names we'll accept      
  to ORDER BY in our query */    
   $order_fields = array ( "author", "title" );    
   
   /* Now check to see if $query_info['order'] has      
   an acceptable value and assign the correct value      
   to the $order variable */    
   if ( ISSET ( $query_info['order'] ) &&    
           in_array ( $query_info['order'], $order_fields ) ) {    
       $order = "ORDER BY " . $query_info['order'] . ", date DESC ";    
   } else {    
       $order = "ORDER BY date DESC ";    
   }    
   
   /* Now check for $query_info['limit'] to specify      
   the number of news items we want returned,      
   and assign the correct value to $limit */    
   if ( ISSET ( $query_info['limit'] ) && $query_info['limit'] < 20 ) {    
       $limit = "LIMIT 0, " . $query_info['limit'] . " ";    
   } else {    
       $limit = "LIMIT 0, 5 ";    
   }    
   
   /* Now build the query */    
   $query = "SELECT * FROM kd_xmlrpc_news " . $order . $limit;    
   $sql = mysql_query ( $query );    
   if ( $sql ) {    
       $news_items = array();    
       while ( $result = mysql_fetch_array ( $sql ) ) {    
   
           /* Extract the variables we want from the row */    
           $news_item['news_id'] = $result['news_id'];    
           $news_item['date'] = XMLRPC_convert_timestamp_to_iso8601(    
               mysql_datetime_to_timestamp( $result['date'] )    
               );            
           $news_item['title'] = $result['title'];    
           $news_item['short_desc'] = $result['short_desc'];    
           $news_item['author'] = $result['author'];    
   
           /* Add to the $news_items array */    
           $news_items[] = $news_item;    
       }    
   
       /* Convert the $news_items array to a set      
       of XML-RPC parameters then respond with the XML. */    
       XMLRPC_response(XMLRPC_prepare($news_items),      
       KD_XMLRPC_USERAGENT);    
   } else {    
   
       /* If there was an error, respond with an      
       error message */    
       XMLRPC_error("1", "news_getNewsList() error: Unable      
       to read news:"    
           . mysql_error() . "\nQuery was: " . $query,      
KD_XMLRPC_USERAGENT);    
   }    
}    
   
/* Function for viewing a full news item corresponding      
  to the news.viewNewsItem method */    
function news_viewNewsItem ( $news_id ) {    
   
   /* Define the query to fetch the news item */    
   $query = "SELECT * FROM kd_xmlrpc_news WHERE news_id = '"      
   . $news_id . "'";    
   $sql = mysql_query ( $query );    
   if ( $result = mysql_fetch_array ( $sql ) ) {    
   
       /* Extract the variables for sending in      
       our server response */    
       $news_item['news_id'] = $result['news_id'];    
       $news_item['date'] = XMLRPC_convert_timestamp_to_iso8601(    
           mysql_datetime_to_timestamp( $result['date'] ) );          
       $news_item['title'] = $result['title'];    
       $news_item['full_desc'] = $result['full_desc'];    
       $news_item['author'] = $result['author'];    
   
       /* Respond to the client with the news item */    
       XMLRPC_response(XMLRPC_prepare($news_item),      
       KD_XMLRPC_USERAGENT);    
   } else {    
   
       /* If there was an error, respond with a      
       fault code instead */    
       XMLRPC_error("1", "news_viewNewsItem() error: Unable      
to read news:"    
           . mysql_error(), KD_XMLRPC_USERAGENT);    
   }    
}    
   
/* Function for when the request method name      
doesn't exist */    
function XMLRPC_method_not_found($methodName){    
   XMLRPC_error("2", "The method you requested, " . $methodName    
       . ", was not found.", KD_XMLRPC_USERAGENT);    
}    
?>