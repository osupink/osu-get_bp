<?php
require_once('../config.php');
require_once('../../BanYouBot/botconfig.php');
header('access-control-allow-origin:*');
header('content-type:application/json');
if (!isset($_GET['user'])) {
	die();
}
$cache=1;
$bp=[];
if ($cache) {
	$memcache=new Memcache;
	if (!$memcache->connect(memadd,memport)) {
		die(json_encode($bp,JSON_NUMERIC_CHECK));
	}
}
$user=$_GET['user'];
$limit=(isset($_GET['limit']) && is_numeric($_GET['limit']) && 1 <= $_GET['limit'] && $_GET['limit'] <= 100) ? $_GET['limit'] : 10;
$mode=(isset($_GET['mode']) && is_numeric($_GET['mode']) && 0 <= $_GET['mode'] && $_GET['mode'] <= 3) ? $_GET['mode'] :0;
if (!$bp=get_bp($user,$mode,$limit)) {
	$bp=[];
}
echo json_encode($bp,JSON_NUMERIC_CHECK);
?>
