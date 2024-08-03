<?php

// the path where to fetch the volumio playlists from
// probably a good idea to use a local copy
//
$in_path = __DIR__."/lists";

// uncomment these lines if these variables don't already exist in your environment
// Don't forget to change the values to something sane for your system
//
//putenv("MPD_HOST=localhost");
//putenv("MPD_PORT=6600");
//putenv("MPD_PASS=G0shVVutAbadPusswud");

// enable and configure to adjust a volumio uri to an mpd uri
// the format is:
//				 $preg_patterns["search"] = "replace"
//
$preg_pattern_on = true;
$preg_patterns = array( "!^Aardvaark/!" => "Extended-Disk/Aardvaark/",
						"!^NAS/!" 		=> ""
					);

?>
