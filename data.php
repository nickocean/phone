<?php

$data = mysql_query("SELECT * FROM vicidial_list WHERE lead_id = '{$_POST['lead_id']}'");

echo '<pre>';
print_r($data);
echo '</pre>';