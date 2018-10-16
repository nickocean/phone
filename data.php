<?php

phpinfo();

require( "dbconnect.php" );
$leadId = $_POST['lead_id'];
$stmt = "SELECT * FROM vicidial_list WHERE lead_id = $leadId";
$res = mysql_query($stmt);
$data = mysql_fetch_array($res);

echo '<pre>';
print_r($data);
echo '</pre>';

$userName = 'dev';
$userApiKey = '3dc80aa0c30f554de82af4ab3924d37316a998cc';
$url="http://oro.demo";