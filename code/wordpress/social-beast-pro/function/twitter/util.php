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
	
	public function tweet($tweet,$post){
		$response = $this->update($tweet);
		$return = json_decode($response['response'],true);
		
		if($response['code'] != 200){		
			return array('error',"The following error occured while trying to update status: " . $return['errors'][0]['message']);
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
			$where = array('id' => $result['id']);
			$data = array(
				'tweet_count' => $tweetCount,
				'timestamp' => $timestamp
			);
			if(!$wpdb->update(TWITTER_TWEETS_TB,$data,$where)) return array('error',"The following error occured while trying to update tweet database: " . mysql_error());		
		} else {
			$data = array(
				'id' => $return['id'],
				'post_id' => $post_id,
				'text' => $return['text'],
				'tweet_count' => 1,
				'timestamp' => $timestamp
			);
			if(!$wpdb->insert(TWITTER_TWEETS_TB,$data)) return array('error',"The following error occured while trying to update tweet database: " . mysql_error());
		}
		
		return 1;
	}
	
	public function postTweets($id){
		global $wpdb;
		$query = "SELECT * FROM " . TWITTER_TWEETS_TB . " WHERE post_id = " . $id;
		$result = $wpdb->get_results($query,ARRAY_A); 
		$count = count($result);
		
		if($count > 0){
			foreach($result as $tweet){
				wp_cache_flush();
				$embed = $this->oEmbed($tweet['id']);
				echo $embed['html'];
			}
		} else {
			echo "This post currently has no tweets.";
		}
	}
	
	public function twtReplace($post_id,$string){
		$post = get_post($post_id);
		$link = wp_get_shortlink() ? wp_get_shortlink($post_ID) : get_permalink($post_id);
		$patterns = array('/%T/','/%U/');
		$replace = array($post->post_title,$link);
		$newStr = preg_replace($patterns,$replace,$string);
		
		return $newStr;
	}
	
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