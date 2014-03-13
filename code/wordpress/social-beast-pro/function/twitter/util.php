<?php
class twtAccessCtl {
	
	/*
	** Grants user access to send tweets with specific user accounts. Creates permissions 
	** array if one wasn't already created.
	*/
	public function grantUsrAccess($user,$account){
		global $wpdb;
		$query = "SELECT access FROM " . TWITTER_USER_TB . " WHERE user_id = " . $account;
		$result = $wpdb->get_row($query, ARRAY_N);
		$count = count($result);
		
		if($count == 1 && $result[0] != ''){
			$array = unserialize($result[0]);
			$match = 0;
			
			foreach($array['users'] as $id){
				if($user == $id){
					$match++;
				}
			}
			
			if($match == 0){
				array_push($array['users'],$user);
			}
		} else {
			$array = array(
				'users' => array($user),
				'groups' => array()
			);
		}
		
		$array = serialize($array);
		return $array;
	}
}

class twtUtility extends twtOAuthUtil{
	
	/*
	** Deletes specified status from twitter and DB.
	*/ 
	public function deleteTweet($id){
		$request = $this->destroyStatus($id);
		
		if(!empty($request['id_str']) && $request['id_str'] == $id){
			global $wpdb;
			$where = array('id' => $id);
			
			if(!$wpdb->delete(TWITTER_TWEETS_TB,$where)){
				echo "Unable to delete tweet from DB";
			} else {
				echo 1;
			}
		}
	}
	
	/*
	** Gather URLs relating to post for DB storage
	*/
	protected function extractURLs($post_id,$return){
		$short = array(
			'url' => wp_get_shortlink($post_id)
		);
		$long = array(
			'url' => get_permalink($post_id)
		);
		
		foreach($return['urls'] as $url){
			if($url['expanded_url'] == $short['url']){
				$short['wrap'] = $url['url'];
			}
			if($url['expanded_url'] == $long['url']){
				$long['wrap'] = $url['url'];
			}
		}
		
		$urls = array(
			'short_url' => $short,
			'long_url' => $long
		);
		
		return $urls;
	}
	
	/*
	** Collect hashtags used in the status for DB storage
	*/
	protected function extractHashtags($return){
		$count = count($return['hashtags']);
		$i = $count;
		if($count > 0){
			$hashtags = "";
			
			foreach($return['hashtags'] as $hashtag){
				$hashtags .= "#" . $hashtag['text'];
				
				if($i-- > 1){
					$hashtags .= " ";
				}
			}
			
			return $hashtags;
		}
	}
	
	/*
	** Collect mentions used in status for DB storage
	*/
	protected function extractMentions($return){
		$count = count($return['user_mentions']);
		$i = $count;
		if($count > 0){
			$mentions = "";
			
			foreach($return['user_mentions'] as $mention){
				$mentions .= "@" . $mention['screen_name'];
				
				if($i-- > 1){
					$mentions .= " ";
				}
			}
			
			return $mentions;
		}
	}
	
	/*
	** Updates twitter status and stores in DB
	*/
	public function tweet($tweet,$post){
		$response = $this->update($tweet);
		$return = json_decode($response['response'],true);
		
		if($response['code'] != 200){		
			return array('error',$return['errors'][0]['message']);
		}
		
		global $wpdb;
		$post_id = $post->ID;
		$timestamp = time();
		$query = "SELECT * FROM " . TWITTER_TWEETS_TB . " WHERE text=\"" . $return['text'] . "\" AND post_id=\"" . $post_id . "\"";
		$result = $wpdb->get_row($query, ARRAY_A);
		$count = count($result);
		
		if($count == 1){
			$tweetCount = $result['tweet_count'];
			$tweetCount++;
			$where = array('id' => $result['id_str']);
			$data = array(
				'tweet_count' => $tweetCount,
				'timestamp' => $timestamp
			);
			if(!$wpdb->update(TWITTER_TWEETS_TB,$data,$where)) return array('error',"The following error occured while trying to update tweet database: " . mysql_error());		
		} else {
			$url = $this->extractURLs($post_id,$return['entities']);
			$url = serialize($url);
			$mention = $this->extractMentions($return['entities']);
			$hashtag = $this->extractHashtags($return['entities']);
			$data = array(
				'id' => $return['id_str'],
				'post_id' => $post_id,
				'text' => $return['text'],
				'url' => $url,
				'mentions' => $mention,
				'hashtags' => $hashtag,
				'tweet_count' => 1,
				'timestamp' => $timestamp
			);
			if(!$wpdb->insert(TWITTER_TWEETS_TB,$data)) return array('error',"The following error occured while trying to update tweet database: " . mysql_error());
		}
		
		return array('status' => 1, 'id' => $return['id_str']);
	}
	
	/*
	** Lists previous tweets from post.
	*/
	public function postTweets($id){
		global $wpdb;
		$query = "SELECT * FROM " . TWITTER_TWEETS_TB . " WHERE post_id = " . $id . " ORDER BY timestamp DESC";
		$result = $wpdb->get_results($query,ARRAY_A); 
		$count = count($result);
		
		if($count > 0){
			foreach($result as $tweet){
				wp_cache_flush();
				$embed = $this->oEmbed($tweet['id']);
				$tweetcard = $embed['html'];
				$tweetid = $tweet['id'];
				$output = <<<OUT
<div id="sb_tweet_$tweetid">
	$tweetcard
	<div class="sb_tweet_postoptions">
		<input id="sb_tweet_delete" class="sb_tweet_delete" type="button" value="Delete" />
		<input type="hidden" value="$tweetid" />
	</div>
</div>
OUT;
				echo $output;

			}
		} else {
			echo "This post currently has no tweets.";
		}
	}
	
	/*
	** Replaces template tags with their appropriate values.
	*/
	public function twtReplace($post_id,$string){
		$post = get_post($post_id);
		$link = wp_get_shortlink($post_id) ? wp_get_shortlink($post_id) : get_permalink($post_id);
		$patterns = array('/%T/','/%U/');
		$replace = array($post->post_title,$link);
		$newStr = preg_replace($patterns,$replace,$string);
		
		return $newStr;
	}
	
	/*
	** Pulls twitter account information of specified account.
	*/
	public function verifiedAccount(){
		$user = $this->verifyKey();
		$settings = $this->userSettings();
		$pic = $user['profile_image_url'];
		$name = $user['name'];
		$followers = $user['followers_count'];
		$username = $settings['screen_name'];
		$output = <<<EOT
<div class="twtuser">
	<div class="twtpic"><img src="$pic" /></div>
	<div class="twt_names">
		<div class="twt_screenname">@$username</div>
		<div class="twt_name"><a href="http://twitter.com/$username" target="_blank" title="$name">$name</a></div>
	</div>
	<div class="twt_followers">$followers <br>Followers</div>
</div>
EOT;
		echo $output;
	}
}
?>