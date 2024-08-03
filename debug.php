<?php
function debug( $text ){
	if( __DEBUG__ ){
		prln( $text );
	}
}

function prln( $text ){
	echo "$text\n";
}
?>
