# Volumio2Mpd2
Written in PHP, reads Volumio style playlists and uploads them to MPD.

  - [About](#about)
  - [Parameters](#parameters)
  - [local.php](#local-php)
  - [simple-mpd-php](#simple-mpd-php)


## About
Volumio playlists are json files that contain additional information such as where to find the album art etc. This script will extract the uri information, apply any regex transformations (you can provide multiple transformations) and then upload them to MPD.

If the playlist already exists on MPD, it will be deleted first.

## Parameters
If run without any parameters, the script will attempt to connect to MPD and report the API version:
```
$ ./vol2mpd2.php
MPD version: 0.23.5
Disconnected
```

Run with flag `-h` for help screen:
```
$ ./vol2mpd2.php -h
Parameters:
        -n              no action (output transformations only)
        -a              output actions (playlist is cleared or new one created)
        -h              print this help and quit

If -n is used with -a (or -na) no changes are made on the server,
but the actions that would have taken place are output
```

Flags can be combined and in any order, so `-anh` is the same as `-h -a -n`. If the `-h` flag is used, the help  screen is shown and the script terminates without any further processing.

## local.php
All user-configurable options are in local.php.

`$in_path` is where you define a path to where your volumio playlists live. Although these are not changed in any way, it's probably best to use a copy.

The environment variable `MPD_HOST` is required. `MPD_PORT` and `MPD_PASS` will default to `6600` and an empty string respectively. You can set these in any of the usual ways, or you can set them in `local.php` by using the `putenv()` function:
```
putenv("MPD_HOST=localhost");
putenv("MPD_PORT=6600");
putenv("MPD_PASS=G0shVVutAbadPusswud");
```

You can also setup a list of transformations which will change file uri's. These are setup in an associative array, with the keys being the regular expression to match, and the value being the replacement. Note that each element of the array will be expressed against the file uri in the order they are defined, so care should be taken that one transformation isn't trumped by a later one.
```
$preg_pattern_on = true;
$preg_patterns = array( "!^Aardvaark/!" => "Extended-Disk/Aardvaark/",
						"!^NAS/!" 		=> ""
					);
```
The `$preg_pattern_on` variable is used to enable or disable transformations, so you can turn them off without actually deleting the `$preg_patterns` array.

## simple-mpd.php
This class is a simplified form a class that was written for PHP 4. It has been re-written, updated for PHP 8, cut back to just the elements needed, and given a proper error class.
