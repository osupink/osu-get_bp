<!DOCTYPE HTML>
<html>
<head>
<link rel="icon" href="//static.b.osu.pink/favicon.ico">
<meta http-equiv="content-type" content="text/html;charset=utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?php if (isset($_GET['user'])) { echo $_GET['user'].' - '; } ?>批量下osu!bp</title>
<style>
a {
color:#0989f6;
text-decoration:none;
}
h2 {
display:inline;
}
.right {
float:right;
}
</style>
</head>
<body>
<h2>批量下osu!bp</h2>
<span class="right">推广: <a href="https://jq.qq.com/?_wv=1027&k=bOW1smaP">BanYou 玩家群 (osu! 私服)</a></span>
<hr>
<form method="get">
<p>账号(不得为空)：<input type="text" name="user" autocomplete="off"></p>
<p>数量(1-100,默认为10)：<input type="text" name="limit" autocomplete="off"></p>
<p>模式(默认为osu!std)：<br>
osu!std：<input type="radio" name="mode" checked="checked" value="0">
<br>
osu!Taiko：<input type="radio" name="mode" value="1">
<br>
osu!CatchTheBeat：<input type="radio" name="mode" value="2">
<br>
osu!mania：<input type="radio" name="mode" value="3">
</p>
<input type="submit" value="查询">
</form>
<?php
error_reporting(1);
set_time_limit(300);
ignore_user_abort(1);
require_once('config.php');
require_once('../BanYouBot/botconfig.php');
require_once('../BanYouBot/apis/bmcacheLib.php');
$conn=new mysqli(DbAddress,DbUsername,DbPassword,DbName);
if (!empty($_GET['user'])) {
	$cacheerror=0;
	if (cache) {
		$memcache=new Memcache;
		if (!$memcache->connect(memadd,memport)) {
			$cacheerror=1;
			echo "<p>缓存连接失败.</p>\n";
		}
	}
	$user=$_GET['user'];
	$limit=(!empty($_GET['limit']) && is_numeric($_GET['limit']) && 1<=$_GET['limit'] && $_GET['limit']<=100) ? $_GET['limit'] : 10;
	$mode=(!empty($_GET['mode']) && is_numeric($_GET['mode']) && 0<=$_GET['mode'] && $_GET['mode']<=3) ? $_GET['mode'] :0;
	if (empty($_GET['limit']) || !isset($_GET['mode']) || $_GET['mode'] === '') {
		header('HTTP/1.1 301 Moved Permanently');
		header("Location:http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?user=$user&limit=$limit&mode=$mode");
	}
	if ($bp=get_bp($user,$mode,$limit)) {
		echo "<script>\nfunction alldownload(name) {\nname=document.getElementsByName(name);\nfor (var i=0;i<name.length;i++) {\nvar iframe=document.createElement('iframe');\niframe.src=name[i].href;\niframe.style.display=\"none\";\ndocument.getElementsByTagName('p')[i].appendChild(iframe);\n}\n}\n</script>\n<p><button onclick=\"alldownload('osu');\">批量下载</button> <button onclick=\"alldownload('sayobot');\">批量下载 (Sayobot)</button></p>\n";
		for ($i=0;$i<count($bp);$i++) {
			$now=$i+1;
			$bmid=$bp[$i]['beatmap_id'];
			$bms=getbeatmapinfo("b={$bmid}","beatmap_id = {$bmid}",0,1,1,1,1);
			if ($bms === 0) {
				continue;
			}
			$bm=$beatmaps[0];
			$pp=$bp[$i]['pp'];
			$date=$bp[$i]['date'];
			$rank=$bp[$i]['rank'];
			$bmid=$bm['beatmap_id'];
			$bmdid=$bm['beatmapset_id'];
			$title=htmlspecialchars($bm['title']);
			$score=$bp[$i]['score'];
			$artist=htmlspecialchars($bm['artist']);
			$version=htmlspecialchars($bm['version']);
			$perfect=$bp[$i]['perfect'] ? ' (Perfect) ' : ' ';
			$maxcombo=$bp[$i]['maxcombo'];
			$count50=$bp[$i]['count50'];
			$count100=$bp[$i]['count100'];
			$count300=$bp[$i]['count300'];
			$countmiss=$bp[$i]['countmiss'];
			$countkatu=$bp[$i]['countkatu'];
			$countgeki=$bp[$i]['countgeki'];
			echo "<p>".$now."、<img src=\"https://s.ppy.sh/images/$rank".'_small.png'."\"> $artist - $title [$version]".$perfect."PP:$pp Combo:$maxcombo Score:$score countmiss:$countmiss count50:$count50 count100:$count100 count300:$count300 countkatu:$countkatu countgeki:$countgeki Date:$date <a href=\"https://osu.ppy.sh/b/$bmid\">谱面地址</a> <a name=\"osu\" href=\"https://osu.ppy.sh/d/$bmdid\">下载地址</a> <a name=\"sayobot\" href=\"https://txy1.sayobot.cn/beatmaps/download/$bmdid\">下载地址 (Sayobot)</a></p>\n";
		}
	} else {
		echo "<p>找不到该账号.</p>\n";
	}
	if (cache && !$cacheerror) {
		$memcache->close();
	}
} elseif (isset($_GET['user'])) {
	header('HTTP/1.1 301 Moved Permanently');
	header("Location:http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}");
}
?>
</body>
</html>
