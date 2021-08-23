<?php
require_once('../../BanYouBot/botconfig.php');
require_once('../../BanYouBot/apis/bmcacheLib.php');
header('access-control-allow-origin:*');
header('content-type:application/json');
$conn=new mysqli(DbAddress,DbUsername,DbPassword,DbName);
if (!isset($_GET['b'])) {
	die();
}
$bmid=(int)$_GET['b'];
if (!getbeatmapinfo("b={$bmid}","beatmap_id = {$bmid}",0,1,1,1,1)) {
	$beatmaps=[];
}
echo json_encode($beatmaps,JSON_NUMERIC_CHECK);
?>
