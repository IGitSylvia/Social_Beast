<?php
/*
** Include or require all needed files here for twitter functionality.
*/

require_once(SB_DIR . "function/twitter/oauth.php");
require_once(SB_DIR . "function/twitter/util.php");

add_action('save_post','sb_twtSavePost');
add_action('new_to_publish','sb_twtPublishPost');
add_action('draft_to_publish','sb_twtPublishPost');
add_action('pending_to_publish','sb_twtPublishPost');
add_action('future_to_publish','sb_twtPublishPost');

function sb_twtPublishPost($post){
	global $twtUtility;
	global $posttypes;
	$tweeted = get_post_meta($post->ID,'_sb_tweeted',true);
	$posttype = $post->post_type; 
	$custom = isset($_POST['sb_tweetbox']) ? $_POST['sb_tweetbox'] : get_post_meta($post->ID,'_sb_savedtweet',true);
	$tweet = ($custom != '') ? $twtUtility->twtReplace($post->ID,$custom) : $twtUtility->twtReplace($post->ID,get_option('sb_default_tweet'));
	
	foreach($posttypes as $type){
		if($type == $posttype){
			if($tweeted != 1){
				$update = $twtUtility->tweet($tweet,$post);
			
				if($update == 1){
					$id = $post->ID;
					delete_post_meta($id,'_sb_savedtweet');
					update_post_meta($id,'_sb_tweeted','1');
				} else {
					$_SESSION['status_type'] = $update[0];
					$_SESSION['status_message'] = $update[1];
				}
			}
		}
	}
}

function sb_twtSavePost(){
	global $post;
	global $posttypes;
	$tweeted = get_post_meta($post->ID,'_sb_tweeted',true);
	
	$posttype = $post->post_type;
	foreach($posttypes as $type){
		if($type == $posttype){
			if($_POST['sb_tweetbox'] != $_POST['sb_origtweet']){
				$id = $post->ID;
				update_post_meta($id,'_sb_savedtweet',$_POST['sb_tweetbox']);
			}
		}
	}
}
?>