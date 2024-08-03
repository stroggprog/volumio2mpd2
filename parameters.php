<?php

function help(){
	prln("Parameters:");
	prln("	-n		no action (output transformations only)");
	prln("	-a		output actions (playlist is cleared or new one created)");
	prln("	-h		print this help and quit");
	prln("");
	prln("If -n is used with -a (or -na) no changes are made on the server,");
	prln("but the actions that would have taken place are output");
}

class options {
	private $opts;

	public function __construct( $options ){
		$this->opts = getopt( $options );
	}

	public function is_opt( $opt ){
		return array_key_exists( $opt, $this->opts );
	}
}
?>
