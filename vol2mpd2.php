#!/usr/bin/env php
<?php
define("__DEBUG__", false );

date_default_timezone_set('Europe/London');

$buck = "";
$in_path = __DIR__;
// these should be setup already
// the password might need to be setup in the calling script
// note: these values can be overridden in local.php
//
$host = getenv("MPD_HOST");
$port = getenv("MPD_PORT");
$pass = getenv("MPD_PASS");
if( !$host ) $host = "localhost";
if( !$port ) $port = "6600";
if( !$pass ) $pass = "";

include_once("debug.php");
include_once("local.php");
include_once("parameters.php");
include_once("mpd/simple-mpd.php");

$opts = new options("nah");

if( $opts->is_opt("h") ){
	help();
	exit;
}

$mpd = new mpd( $host, $port, $pass );

if( $mpd === false ){
	echo "Oops\n";
	exit;
}
prln( "MPD version: ".$mpd->version() );

$playlists = $mpd->list_playlists();

if( $opts->is_opt("n") ){
	prln("NO ACTIONS WILL BE TAKEN");
}

if( __DEBUG__ ){
	foreach( $playlists as $item ){
		prln( $item );
	}
}

$d = dir( $in_path );
$v_playlists = array();

while (false !== ($entry = $d->read())) {
	$v_playlists[] = $entry;
}
$d->close();

asort($v_playlists);

foreach( $v_playlists as $entry ){
	if( $entry != "." && $entry != ".." ){
		$clear = in_array( $entry, $playlists );
		createOrClear( $entry, $clear, $opts );
		Convert( $in_path, $entry );
	}
}

$mpd->close();

$dump = $mpd->disconnect();
prln("Disconnected");

function createOrClear( $name, $clear, $opts ){
	global $mpd;
	$res = false;
	$msg = "Create new playlist: $name";
	if( $clear ){
		$msg = "Deleting existing playlist: $name";
		if( !$opts->is_opt("n") ){
			$res = $mpd->remove_playlist( $name );
			if( !$res ){
				$msg = "  --> Failed to delete playlist: $name";
			}
		}
	}
	if( $opts->is_opt("a") ){
		prln($msg);
	}
	if( $mpd->error->err_no() ){
		prln("ERROR: [".$mpd->error->err_no()."] ".$mpd->error->err_str());
	}
	return $res;
}

function Convert( $path, $filename ){
	global $mpd, $opts, $preg_pattern_on, $preg_patterns;

	$playlist = "";

	$var = json_decode( file_get_contents($path."/".$filename), false );
	foreach( $var as $item ){
		$uri = "$item->uri";
		if( $preg_pattern_on ){
			foreach( $preg_patterns as $pattern => $replace ){
				$uri = preg_replace($pattern, $replace, $uri);
			}
		}
		if( $opts->is_opt("a") ){
			prln("Adding: $uri");
		}
		if( !$opts->is_opt("n") ){
			$ok = $mpd->add_to_playlist( "$filename", "$uri" );
			if( $mpd->error->err_no() ){
				prln("ERROR: [".$mpd->error->err_no()."] ".$mpd->error->err_str());
			}
		}
	}
}

?>
