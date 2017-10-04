<?php
if ($_SERVER['HTTPS'] == 'on') {
	header('HTTP/1.1 301 Moved Permanently');
	header('Location:http://www.osupink.org/get_bp.php');
	die();
}
?>
<!DOCTYPE HTML>
<html>
<head>
<link rel="icon" href="//assets.osupink.org/favicon.ico">
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
body {
background-size:cover;
background-image:url('//assets.osupink.org/bg.jpg');
background-attachment:fixed;
}
.right {
float:right;
}
</style>
</head>
<body>
<h2>批量下osu!bp</h2>
<span class="right">推广:<a href="http://shang.qq.com/wpa/qunwpa?idkey=b8d427735c7eb9260bda9a9b2dfdb504bd4ded78dad89937fa8207839b7cf391">osu!俄亥俄州立大学</a></span>
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
error_reporting(0);
set_time_limit(300);
ignore_user_abort(1);
require_once('config.php');
$key='whatsthis?';
$cache=1;
$cachetime=604800;
function get_bp($user,$mode,$limit) {
	global $key,$cache,$memcache,$cachetime,$cacheerror;
	if ($cache && !$cacheerror && $json=$memcache->get("$user.bp.$mode.$limit")) {
		$obj=json_decode($json);
	} else {
		$json=file_get_contents(apiurl."get_user_best?k=$key&u=$user&m=$mode&limit=$limit",0,stream_context_create(array('http' => array('method' => 'GET','timeout' => 30))));
		if ($cache && !$cacheerror) {
			$memcache->set("$user.bp.$mode.$limit",$json,0,$cachetime);
		}
		$obj=json_decode($json);
	}
	if (count($obj) != 0) {
		$array=array();
		for ($i=0;$i<count($obj);$i++) {
			array_push($array,array('beatmap_id' => $obj[$i]->beatmap_id,'score' => $obj[$i]->score,'maxcombo' => $obj[$i]->maxcombo,'date' => $obj[$i]->date,'pp' => $obj[$i]->pp,'rank' => $obj[$i]->rank,'count50' => $obj[$i]->count50,'count100' => $obj[$i]->count100,'count300' => $obj[$i]->count300,'countmiss' => $obj[$i]->countmiss,'countkatu' => $obj[$i]->countkatu,'countgeki' => $obj[$i]->countgeki,'perfect' => $obj[$i]->perfect));
		}
		return $array;
	} else {
		return 0;
	}
}
function get_bm($id) {
	global $key,$cache,$memcache,$cachetime,$cacheerror;
	if ($cache && !$cacheerror && $json=$memcache->get("$id.bm")) {
		$obj=json_decode($json);
	} else {
		$json=file_get_contents(apiurl."get_beatmaps?k=$key&b=$id",0,stream_context_create(array('http' => array('method' => 'GET','timeout' => 30))));
		if ($cache && !$cacheerror) {
			$memcache->set("$id.bm",$json,0,$cachetime);
		}
		$obj=json_decode($json);
	}
	return array('bmid' => $id,'bmdid' => $obj[0]->beatmapset_id,'title' => $obj[0]->title,'artist' => $obj[0]->artist,'version' => $obj[0]->version);
}
if (!empty($_GET['user'])) {
	$cacheerror=0;
	if ($cache) {
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
	/*
	112,114,115:这里的修改是为了减轻单个玩家的不同数量bp被重复缓存，因得到全部100张bp的性能损失较小，且可以提高缓存命中率及获取速度，故将get_bp的$limit固定为100，$i<count($bp)改为$i<$bplimit.
	*/
	if ($bp=get_bp($user,$mode,100)) {
		echo "<script>\nfunction alldownload(name) {\nname=document.getElementsByName(name);\nfor (var i=0;i<name.length;i++) {\nvar iframe=document.createElement('iframe');\niframe.src=name[i].href;\niframe.style.display=\"none\";\ndocument.getElementsByTagName('p')[i].appendChild(iframe);\n}\n}\n</script>\n<p><button onclick=\"alldownload('osu');\">批量下载</button> <button onclick=\"alldownload('bloodcat');\">批量下载(bloodcat)</button></p>\n";
		$bplimit=(count($bp)<$limit) ? count($bp) : $limit;
		for ($i=0;$i<$bplimit;$i++) {
			$now=$i+1;
			$bm=get_bm($bp[$i]['beatmap_id']);
			$pp=$bp[$i]['pp'];
			$date=$bp[$i]['date'];
			$rank=$bp[$i]['rank'];
			$bmid=$bm['bmid'];
			$bmdid=$bm['bmdid'];
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
			echo "<p>".$now."、<img src=\"https://s.ppy.sh/images/$rank".'_small.png'."\"> $artist - $title [$version]".$perfect."PP:$pp Combo:$maxcombo Score:$score countmiss:$countmiss count50:$count50 count100:$count100 count300:$count300 countkatu:$countkatu countgeki:$countgeki Date:$date <a href=\"https://osu.ppy.sh/b/$bmid\">谱面地址</a> <a name=\"osu\" href=\"https://osu.ppy.sh/d/$bmdid\">下载地址</a> <a name=\"bloodcat\" href=\"http://bloodcat.osupink.org/d/$bmdid\">下载地址(bloodcat)</a> <a name=\"mengsky\" href=\"http://mengsky.osupink.org/d/$bmdid\">下载地址(mengsky)</a></p>\n";
		}
	} else {
		echo "<p>找不到该账号.</p>\n";
	}
	if ($cache && !$cacheerror) {
		$memcache->close();
	}
} elseif (isset($_GET['user'])) {
	header('HTTP/1.1 301 Moved Permanently');
	header("Location:http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}");
}
?>
</body>
</html>
