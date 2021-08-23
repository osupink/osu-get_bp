<?php
function get_bp($user,$mode,$limit) {
	global $memcache, $cacheerror;
	$cached=1;
	if (memcache && !$cacheerror) {
		if ($memcache->get("$user.$mode.notfound")) {
			return 0;
		}
		$json=$memcache->get("$user.bp.$mode.100");
	}
	if (!$json) {
		$cached=0;
		$json=file_get_contents(apiurl."get_user_best?k=".osuAPIKey."&u=$user&m=$mode&limit=100",0,stream_context_create(array('http' => array('method' => 'GET','timeout' => 30))));
	}
	$obj=json_decode($json);
	if (count($obj) != 0) {
		if (!$cached && memcache && !$cacheerror) {
			$memcache->set("$user.bp.$mode.100",$json,0,memcachetime);
		}
		$array=array();
		for ($i=0;$i<min($limit,count($obj));$i++) {
			array_push($array,array('beatmap_id' => $obj[$i]->beatmap_id,'score' => $obj[$i]->score,'maxcombo' => $obj[$i]->maxcombo,'date' => $obj[$i]->date,'pp' => $obj[$i]->pp,'rank' => $obj[$i]->rank,'count50' => $obj[$i]->count50,'count100' => $obj[$i]->count100,'count300' => $obj[$i]->count300,'countmiss' => $obj[$i]->countmiss,'countkatu' => $obj[$i]->countkatu,'countgeki' => $obj[$i]->countgeki,'perfect' => $obj[$i]->perfect));
		}
		return $array;
	} elseif (memcache && !$cacheerror) {
		$memcache->set("$user.$mode.notfound",1,0,86400);
	}
	return 0;
}
define('apiurl','https://osu.ppy.sh/api/');
define('memcache',1);
define('memcachetime',604800);
define('memadd','127.0.0.1');
define('memport',11211);
?>
