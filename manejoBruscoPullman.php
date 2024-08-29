<?php 

date_default_timezone_set("America/Santiago");

$dia=$_GET['dia'] ?? 1 ;

$hoy = date("Y-m-d");

//$ayer=date('Y-m-d',strtotime("-$dia days"));
$ayer = date('Y-m-d', strtotime("-$dia days"));

$user="Pullman";

$pasw="123";

include __DIR__."/manejoBrusco.php";

manejo($user,$pasw,$ayer);




?>