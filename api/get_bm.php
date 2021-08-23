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
} elseif (count($beatmaps) > 0) {
	$beatmaps[0]['bmdid']=$beatmaps[0]['beatmapset_id'];
}
echo json_encode($beatmaps,JSON_NUMERIC_CHECK);
?>
