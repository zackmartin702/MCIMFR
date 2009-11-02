<?php
require_once("jsonServer.php");
require_once("api.php");
$functionList = new mashupAPI();
jsonRPCServer::handle($functionList) or jsonRPCServer::announce();
?>