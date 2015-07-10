<?php
$ua = $_SERVER['HTTP_USER_AGENT'];
if(strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'],'iPod')
|| (strstr($_SERVER['HTTP_USER_AGENT'],'Android') && eregi('Mobile', $ua)))
 {
  header('Location: http://m.bevello.com/index.html');
  exit();
} else {
	header('Location: http://www.bevello.com');
	exit();
}
?>