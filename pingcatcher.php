<?php 

// Get a random string from somewhere like https://www.random.org/strings/?num=1&len=20&digits=on&upperalpha=on&loweralpha=on&unique=on&format=html&rnd=new
// You'll use this to run this script.
define( 'EXEC_SECRET', '' );

// You can get this from https://github.com/settings/tokens/new
// The token needs "repo" and "notifications" access
define( 'GITHUB_TOKEN', '' );

// Your GitHub username (without the @)
define( 'GITHUB_USERNAME', '' );

// The Todoist list ID where you'd like GitHub todos to go.
// To get this, load the list you want to use in the todoist web interface.  At the end of the URL, you'll
// see something like "project%2F1282571104".  The numbers AFTER the "F" are the target list ID
define( 'GITHUB_TARGET_LIST',  );

// You're going to need to create an oAuth app and authorize it to get this token. Sorry!
define( 'WPC_TOKEN', '' );

// The Todoist list ID where you'd like WPCom todos to go.
define( 'WPC_TARGET_LIST',  );

// 1. Go to https://developer.todoist.com/appconsole.html
// 2. Create a new app.  Name it anything.
// 3. Scroll down and click "Create Test Token"
define( 'TODOIST_TOKEN', '' );


if( !isset( $_GET[ 'secret' ] ) || $_GET[ 'secret' ] != EXEC_SECRET ) {
	die( 'Adios.' );
}

/**
* Ping Catcher v1
*/
class PingCatcher
{
	public $history;
	
	function get_history()
	{
		if( $this->history ) {
			return $this->history;
		}
			
		$file   = 'history.txt';
		$handle = fopen( $file, 'r' );
		$data   = fread( $handle, filesize( $file ) );
		fclose($handle);
		
		if( $data ) {
			$data = json_decode( $data, true );
		}  else {
			$data = array();
		}
		
		$this->history = $data;
		
		return $data;
	}
	
	function log_task( $sig )
	{
		$this->get_history();
		
		$this->history[ $sig ] = time();
		
		return true;
	}
	
	function already_added( $sig )
	{
		$this->get_history();
		
		return isset( $this->history[ $sig ] );
	}
	
	function save_history()
	{
		$this->get_history();
		
		$this->clean_history();
		
		$my_file = 'history.txt';
		$handle = fopen( $my_file, 'w' ) or die( 'Cannot open file:  ' . $my_file );
		$data = json_encode( $this->history );
		fwrite($handle, $data);
		fclose($handle);
	}
	
	//clean out any tasks added more than 48 hours ago
	function clean_history()
	{
		$long_ago = time() - ( 86400 * 2 );
		
		foreach( $this->history as $sig => $time ) {
			if( $time < $long_ago ) {
				unset( $this->history[ $sig ] );
			}
		}
	}

	function add_task( $title, $link = null , $project = 'github', $note = null )
	{		
		$temp_id = md5( time() . rand() );
		$uuid = sha1( time() . rand() );
		
		$temp_id2 = md5( time() . rand() );
		$uuid2 = sha1( time() . rand() );
		
		$sig = md5( $title . $link . $project . $note );
		
		if( $this->already_added( $sig ) ) {
			echo 'Already added "' . $title . '" <br />';
			return;
		}
		
		echo '<strong>Adding: <a href="' . $link . '">' . $title . '</a></strong><br />';
		echo '<pre>' . $note . '</pre>';
		
		if( $project == 'wpcom' ) {
			$project_id = WPC_TARGET_LIST;
		} else {
			$project_id = GITHUB_TARGET_LIST;
		}
		$args = array();
		$args['type'] = 'item_add';
		$args['temp_id'] = $temp_id;
		$args['uuid'] = $uuid;
		$args['args'] = array();
		if( $link ) {
			$args['args']['content'] = $link . ' (' . $title . ')';
		} else {
			$args['args']['content'] = $title;
		}
		$args['args']['project_id'] = $project_id;
		
		if( $note ) {
			
			$note = str_ireplace( '<blockquote>', '========= Quote =========' . PHP_EOL, $note );
			$note = str_ireplace( '</blockquote>', PHP_EOL . '========= End Quote =========' . PHP_EOL, $note );
			
			$note = str_ireplace( '<em>', '_', $note );
			$note = str_ireplace( '</em>', '_', $note );
			
			$note = str_ireplace( '<strong>', '__', $note );
			$note = str_ireplace( '</strong>', '__', $note );
			
			$note = str_ireplace( '<code>', '` ', $note );
			$note = str_ireplace( '</code>', ' `', $note );
			
			$note = strip_tags( $note );
			
			// If we can add the task, then try to add The Body
			if( $this->todoist_request( $args ) )
			{
				$args = array();
				$args['type'] = 'note_add';
				$args['temp_id'] = $temp_id2;
				$args['uuid'] = $uuid2;
				$args['args'] = array();
				$args['args']['content'] = $note;
				$args['args']['item_id'] = $temp_id;
		
				// Add note
				$this->todoist_request( $args );
				
				$this->log_task( $sig );
			}
		
			
		}
		
		return true;
	}
	
	function todoist_request( $opts = array() )
	{		
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://todoist.com/api/v7/sync",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"token\"\r\n\r\n" . TODOIST_TOKEN ."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"commands\"\r\n\r\n" . '[' . json_encode( $opts ) . ']' . "\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
		  CURLOPT_HTTPHEADER => array(
		    "cache-control: no-cache",
		    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
		  ),
		));

		$ret = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  return false;
		}
		
		sleep( 10 );
		
		$o = json_decode( $ret, true );
		
		foreach( $o['sync_status'] as $is_ok ) {
			if( $is_ok == 'ok' ) {
				return true;
			} else {
				return false;
			}
		}
	}
}

if( WPC_TOKEN ) {
	include 'wpcom.php';
	$wpc = new WPCom;
	$wpc->get_mentions();
	$wpc->save_history();
}

if( GITHUB_TOKEN ) {
	include 'github.php';
	$github = new GitHub;
	$github->get_mentions();
	$github->save_history();
}


