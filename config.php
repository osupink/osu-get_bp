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
	$obj=json_decode($json, true);
	if (count($obj) != 0) {
		if (!$cached && memcache && !$cacheerror) {
			$memcache->set("$user.bp.$mode.100",$json,0,memcachetime);
		}
		array_splice($obj, $limit);
		return $obj;
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
