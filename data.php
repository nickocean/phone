<?php

$leadId = $_POST['lead_id'];
$stmt = "SELECT list_id FROM vicidial_list WHERE lead_id = '$leadId'";
$data = mysql_query($stmt);

//echo '<pre>';
print_r($data);
//echo '</pre>';