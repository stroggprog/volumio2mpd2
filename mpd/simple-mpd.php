<?php
/*
	This is a very simple class to provide the basic functionality
	required to create playlists
*/

define("RES_OK", "OK");
define("RES_ERR", "ACK");
define("MPD_LISTPLAYLISTS"	, "listplaylists");
define("MPD_PLAYLISTCLEAR"	, "playlistclear");
define("MPD_PLAYLISTADD"	, "playlistadd");
define("MPD_PLAYLISTDELETE"	, "rm");


class mpd_error {
	private $error_no;
	private $error_str;

	public function __construct( $err_no = 0, $err_str = "" ){
		$this->error_no = $err_no;
		$this->error_str = $err_str;
	}

	public function err_no(){
		return $this->error_no;
	}
	public function err_str(){
		return $this->error_str;
	}
}

class mpd {
	private $conn;
	private $host;
	private $port;
	private $pass;
	private $conn_timeout;
	private $version;

	private $sock;
	private $is_connected;

	private $deferror;
	public $error;

	public function __construct( $host = "localhost", $port = 6600, $pass = "", $timeout = 10 ) {
		$this->host = $host;
		$this->port = $port;
		$this->pass = $pass;
		$this->conn_timeout = $timeout;
		$this->is_connected = false;
		$this->version = array();

		$this->deferror = new mpd_error();

		$conn = $this->connect();
		if( $conn === false ){
			return false;
		}
		if( $pass != '' ){
			if( $this->cmd( CMD_PWD, array($pass) ) === false ){
				$fclose( $this->sock );
				$this->connected = false;
				$this->error = new mpd_error( 1, "Invalid password" );
				return false;
			}
		}
		return $this;
	}

	private function connect(){
		$this->error = $this->deferror;
		$this->sock = fsockopen( $this->host, $this->port, $err_no, $err_str, $this->conn_timeout );
		if( !$this->sock ){
			$this->error = new mpd_error( $err_no, $err_str );
			return false;
		}
		while( !feof( $this->sock ) ){
			$res = fgets( $this->sock, 1024 );
			if( strncmp( RES_OK, $res, strlen(RES_OK) ) == 0 ){
				list ($this->version) = sscanf($res, RES_OK . " MPD %s\n");
				$this->is_connected = true;
				return true;
			}
			else {
				$this->error = new mpd_error( -1, $res );
				return false;
			}
		}
		$this->error = new mpd_error( -1, "Unknown" );
		return false;
	}

	// disconnect returns true even if there is no connection to disconnect
	//
	function disconnect(){
		if( $this->is_connected ){
			fclose( $this->sock );
			$this->is_connected = false;
		}
		return !$this->is_connected;
	}

	// alias of disconnect
	//
	function close(){
		return $this->disconnect();
	}

	function cmd($cmd, $args = array()) {
		$this->error = $this->deferror;
		if (!$this->is_connected) {
			$this->error = new mpd_error( 2, "Not Connected" );
			return false;
			
		} else if (!is_array($args)) {
			$this->error = new mpd_error( 3, "Command arguments not presented as an array" );
			return false;
			
		} else {
			$response_str = '';
			
			$cmd_str = '';
			foreach($args as $arg) {
				$cmd_str .= ' "'.$arg.'"';
			}
			$cmd_str = $cmd.$cmd_str;
			
			fputs($this->sock, "$cmd_str\n");
			while( !feof($this->sock) ){
				$res = fgets($this->sock, 1024);
				
				// ignoring OK signal at end of transmission
				if (strncmp(RES_OK, $res, strlen(RES_OK)) == 0) {
					break;
				}
				
				// catch the message at the end of transmission
				if (strncmp(RES_ERR, $res, strlen(RES_ERR)) == 0) {
					list ($tmp, $err) = explode(RES_ERR . ' ', $res);
					$this->error = new mpd_error( 4, strtok($err, "\n") );
				}
				
				if( $this->error->err_no() > 0 ){
					return false;
				}
				
				$response_str .= $res;
			}
			return $response_str;
		}
	}

	function version(){
		return $this->version;
	}

	function add_to_playlist( $playlist, $track ){
		$res = $this->cmd(MPD_PLAYLISTADD, array($playlist,$track));
		if( $res !== false ) return true;
		return false;
	}

	function clear_playlist( $name ){
		$res = $this->cmd(MPD_PLAYLISTCLEAR, array($name) );
		if( $res !== false ){
			return true;
		}
		return false;
	}

	function remove_playlist( $name ){
		$res = $this->cmd(MPD_PLAYLISTDELETE, array($name) );
		if( $res !== false ){
			return true;
		}
		return false;
	}

	function list_playlists(){
		$lists = $this->cmd(MPD_LISTPLAYLISTS);
		if( $lists !== false ){
			$lists = $this->parse_playlists( $lists );
			return $lists;
		}
		return $lists;
	}

	private function parse_playlists( $string ){
		$ret = array();
		$list = explode( "\n", $string );
		foreach( $list as $line ){
			if( substr( $line, 0, 10 ) == "playlist: " ){
				$ret[] = substr( $line, 10 );
			}
		}
		return $ret;
	}


}
?>
