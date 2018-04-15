<?php 

/**
* GitHub PingCatcher
*/
class GitHub extends PingCatcher
{
	
	function get( $url, $opts = array(), $token = null )
	{
		$token = GITHUB_TOKEN;

		$url_base = 'https://api.github.com/';

		if( stripos( $url, $url_base ) !== false ){
			$url_base = '';
		}

		$url = $url_base . $url;

		if( is_array( $opts ) && $opts ) {
			$url .= '?' . http_build_query( $opts );
		}

		$curl = curl_init( $url );

		curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: token ' . $token, 'Accept: application/json', 'User-Agent: PingCatcher' ) );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );

		$ret = curl_exec( $curl );

		if ( !$ret ) {
			die( 'Oh no! GitHub went away!' );
		}

		$o = json_decode( $ret );

		if ( $ret && $ret != '[]' && !$o ) {
			die( 'Oh no! ' . $ret );
		}

		return $o;
	}
	
	function get_mentions( $new_mentions_only = false )
	{
		$o = array();
		
		$query_last_checked = date( 'c', strtotime( '7 days ago' ) );

		$notifications = $this->get( 'notifications', array( 'all' => 'true', 'participating' => 'true', 'since' => $query_last_checked ) );
		
		if ( $notifications ) {
			foreach ( $notifications as $n ) {
				
				// Check the original message
				$nbody = $this->get( $n->subject->url );
				
				// is it a review request?  Look to see if it was requested of me specifically, or just my team.
				if( $n->reason == 'review_requested' ) {
					if( $nbody->requested_reviewers ) {
						foreach( $nbody->requested_reviewers as $reviewers ) {
							if( $reviewers->login == GITHUB_USERNAME ) {
								$this->add_task( '**Review Requested**: ' . $n->subject->title . ' (Repo: _' . $n->repository->name . '_)', $nbody->html_url, $project = 'github', $nbody->body );
							}
						}
					}
				}
				
				
				// Look for @mention in original message
				if( stripos( $nbody->body, '@' . GITHUB_USERNAME ) !== false ) {
					$this->add_task( '**Mention**: ' . $n->subject->title .  ' by _' . $nbody->user->login . '_', $nbody->html_url, $project = 'github', $nbody->body );
				}

				$comment_url = str_replace( '/pulls/', '/issues/', $n->subject->url ) . '/comments';

				$ncomms = $this->get( $comment_url );
				
				// Look for @mentions in each comment
				if ( $ncomms ) {
					foreach ( $ncomms as $nc ) {
						if( stripos( $nc->body, '@samhotchkiss' ) !== false ) {
							$this->add_task( '**Mention**: ' . $n->subject->title . ' by _' . $nc->user->login . '_', $nc->html_url, $project = 'github', $nc->body );
						}
					}
				}
			}
		}
		
		return true;

	}
	
		
}


