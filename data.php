<?php

$leadId = $_POST['lead_id'];
$stmt = "SELECT count(*) FROM vicidial_list WHERE lead_id = $leadId";
print_r($stmt);
$data = mysql_query($stmt);

echo '<pre>';
print_r($data);
echo '</pre>';