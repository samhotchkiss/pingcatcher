<?php 

require( 'secrets.php' );

if( !isset( $_GET[ 'secret' ] ) || $_GET[ 'secret' ] != EXEC_SECRET ) {
	die( 'Adios.' );
}

/**
* Ping Catcher v1
*/
class PingCatcher
{
	public $history;
	
	function log_task( $sig, $note )
	{
		$my_file = 'history/' . $sig . '.txt';
		$handle = fopen( $my_file, 'w' ) or die( 'Cannot open file:  ' . $my_file );
		$data = date('r') . PHP_EOL . PHP_EOL . $note;
		fwrite($handle, $data);
		fclose($handle);
		
		return true;
	}
	
	function already_added( $sig )
	{		
		return file_exists( 'history/' . $sig . '.txt' );
	}
	
	//clean out any tasks added more than 72 hours ago
	function clean_history()
	{
		return;
	}

	function add_task( $title, $link = null , $project = 'github', $note = null )
	{		
		$temp_id = md5( time() . rand() );
		$uuid = sha1( time() . rand() );
		
		$temp_id2 = md5( time() . rand() );
		$uuid2 = sha1( time() . rand() );
		
		$sig = md5( $title . $link . $project . $note );
		
		if( $this->already_added( $sig ) ) {
			echo 'Already added "' . $title . '" (' . $link . ')<br />';
			return;
		}
		
		echo '<strong>Adding: <a href="' . $link . '">' . $title . '</a></strong><br />';
		echo '<pre>' . $note . '</pre>';
		
		if( $project == 'wpcom' ) {
			$project_id = WPC_TARGET_LIST;
		} elseif( $project == 'wpcom-p2' ) {
			$project_id = WPC_P2_TARGET_LIST;
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
				$this->log_task( $sig, $args['args']['content'] . PHP_EOL . $note );
				
				$args = array();
				$args['type'] = 'note_add';
				$args['temp_id'] = $temp_id2;
				$args['uuid'] = $uuid2;
				$args['args'] = array();
				$args['args']['content'] = $note;
				$args['args']['item_id'] = $temp_id;
		
				// Add note
				$this->todoist_request( $args );
				
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
		
		sleep( 5 );
		
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
	echo 'Checking wpcom';
	include 'wpcom.php';
	$wpc = new WPCom;
	$wpc->get_mentions();
}

if( GITHUB_TOKEN ) {
	echo 'Checking github';
	include 'github.php';
	$github = new GitHub;
	$github->get_mentions();
}


