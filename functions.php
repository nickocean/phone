<?php

function mel($mel, $time, $link, $stmt, $query_id, $VD_login, $server_ip, $session_name, $one_mysql_log) {
	if ($mel > 0) {mysql_error_logging($time,$link,$mel,$stmt,$query_id,$VD_login,$server_ip,$session_name,$one_mysql_log);}
}

function select($stmt, $link) {
	$rslt = mysqli_query($stmt, $link);
	return $rslt;
}

function insert($stmt, $link) {
	mysqli_query($stmt, $link);
}

function queryFetch($stmt, $link, $mel, $time, $link, $stmt, $query_id, $VD_login, $server_ip, $session_name, $one_mysql_log) {
	$rslt = select($stmt, $link);
	mel($mel, $time, $link, $stmt, $query_id, $VD_login, $server_ip, $session_name, $one_mysql_log);
	$row = mysqli_fetch_row($rslt);

	return $row;
}

function queryNum($stmt, $link, $mel, $time, $link, $stmt, $query_id, $VD_login, $server_ip, $session_name, $one_mysql_log) {
	$rslt = select($stmt, $link);
	mel($mel, $time, $link, $stmt, $query_id, $VD_login, $server_ip, $session_name, $one_mysql_log);
	$num = mysqli_num_rows($rslt);

	return $num;
}

//queryFetchRow
//queryNumRows