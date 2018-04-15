<?php 

/**
* WP.com PingCatcher
*/
class WPCom extends PingCatcher
{
	
	function get( $url, $opts = array(), $token = null )
	{
		$token = WPC_TOKEN;

		$url_base = 'https://public-api.wordpress.com/rest/v1/';

		if( stripos( $url, $url_base ) !== false ){
			$url_base = '';
		}

		$url = $url_base . $url;

		if( is_array( $opts ) && $opts ) {
			$url .= '?' . http_build_query( $opts );
		}

		$curl = curl_init( $url );

		curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $token, 'Accept: application/json', 'User-Agent: PingCatcher' ) );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );

		$ret = curl_exec( $curl );

		if ( !$ret ) {
			die( 'Oh no! WordPress.com went away!' );
		}

		$o = json_decode( $ret );

		if ( $ret && $ret != '[]' && !$o ) {
			die( 'Oh no! ' . $ret );
		}

		return $o;
	}
	
	function get_mentions( $number = 100 )
	{

		$o = array();

		$last_imported = time() - 86400;

		$notifications = $this->get( 'notifications', array( 'number' => $number ) );

		if ( $notifications && isset( $notifications->notes ) ) {
			foreach ( $notifications->notes as $n ) {
				
				// More than a day old?  Move along.
				if ( $n->timestamp <= $last_imported ) {
					break;
				}
				
				// no text, get out
				if( !isset( $n->subject->text ) ) {
					continue;
				}

				// Not a direct mention?  Carry on.
				if( stripos( $n->subject->text, 'mentioned you' ) )
				{
					$header = '**Mention**: ';
					$delim = 'mentioned you';
				} elseif ( stripos( $n->subject->text, 'replied to your comment' ) ) {
					$header = '**Reply**: ';
					$delim = 'replied to your comment';
				} elseif ( stripos( $n->subject->text, 'commented on your post' ) ) {
					$header = '**Comment**: ';
					$delim = 'commented on your post';
				} else {
					continue;
				}

				
				$item_count = count( $n->body->items );
				$itemcount = $item_count - 1;
				$url = null;
				while( $itemcount >= 0 ) {
					if( isset( $n->body->items[$itemcount]->header_link ) ) {
						$url = $n->body->items[$itemcount]->header_link;
						$body = $n->body->items[$itemcount]->html;
						$itemcount = -100;
					}
					$itemcount = $itemcount - 1;
				}
				if( ! $url ) {
					$url = $n->body->header_link;
				}

				$subject_parts = explode( $delim, $n->subject->text );
				$name = trim( $subject_parts[0] );
				
				$title = $header . $n->body->header_text . ' by _' . $name . '_';
				
				// echo '<h3>' . $title . '</h3>';
				// echo 'URL: ' . $url . '<br />';
				// echo 'Body: ' . $body . '<br /><br />';
				
				$this->add_task( $title, $url, $project = 'wpcom', $body );
				

			}
		}


		return true;

	}
	
		
}


