<?php

function mel($mel, $time, $link, $stmt, $query_id, $VD_login, $server_ip, $session_name, $one_mysql_log) {
	if ($mel > 0) {mysql_error_logging($time,$link,$mel,$stmt,$query_id,$VD_login,$server_ip,$session_name,$one_mysql_log);}
}

function select($stmt, $link) {
	$rslt = mysql_query($stmt, $link);
	return $rslt;
}

function insert($stmt, $link) {
	mysql_query($stmt, $link);
}

function queryFetch($stmt, $link, $mel, $time, $link, $stmt, $query_id, $VD_login, $server_ip, $session_name, $one_mysql_log) {
	$rslt = select($stmt, $link);
	mel($mel, $time, $link, $stmt, $query_id, $VD_login, $server_ip, $session_name, $one_mysql_log);
	$row = mysql_fetch_row($rslt);

	return $row;
}

function queryNum($stmt, $link, $mel, $time, $link, $stmt, $query_id, $VD_login, $server_ip, $session_name, $one_mysql_log) {
	$rslt = select($stmt, $link);
	mel($mel, $time, $link, $stmt, $query_id, $VD_login, $server_ip, $session_name, $one_mysql_log);
	$num = mysql_num_rows($rslt);

	return $num;
}

//queryFetchRow
//queryNumRows