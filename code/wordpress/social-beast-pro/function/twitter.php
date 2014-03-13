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

add_action( 'wp_ajax_sbp-deletetweet', 'ajaxDeleteTweet' );
add_action( 'wp_ajax_sbp-livetweet', 'ajaxLiveTweet' );

function sb_twtPublishPost($post){
	global $twtUtility;
	global $option;
	$tweeted = get_post_meta($post->ID,'_sb_tweeted',true);
	$publish = get_post_meta($post->ID,'_sb_autopublish',true);
	$posttype = $post->post_type; 
	
	foreach($option['post_types'] as $type){
		if($type == $posttype){
			if($tweeted != 1 && $publish == 1){
				$custom = isset($_POST['sb_tweetbox']) ? $_POST['sb_tweetbox'] : get_post_meta($post->ID,'_sb_savedtweet',true);
				$tweet = ($custom != '') ? $twtUtility->twtReplace($post->ID,$custom) : $twtUtility->twtReplace($post->ID,get_option('sb_default_tweet'));
				$update = $twtUtility->tweet($tweet,$post);
			
				if($update['status'] == 1){
					$id = $post->ID;
					delete_post_meta($id,'_sb_savedtweet');
					update_post_meta($id,'_sb_tweeted','1');
				} else {
					$_SESSION['status_type'] = $update[0];
					$_SESSION['status_message'] = "The following error occured while trying to update status: " . $update[1];
				}
			}
		}
	}
}

function sb_twtSavePost(){
	global $post;
	global $option;
	$posttype = isset($post->post_type) ? $post->post_type : null;
	if(isset($post->ID)) $tweeted = get_post_meta($post->ID,'_sb_tweeted',true);
	
	foreach($option['post_types'] as $type){
		if($type == $posttype){
			if($_POST['sb_tweetbox'] != $_POST['sb_origtweet']){
				$id = $post->ID;
				update_post_meta($id,'_sb_savedtweet',$_POST['sb_tweetbox']);
			}
			
			if(isset($_POST['sb_auto_publish'])) update_post_meta($post->ID,'_sb_autopublish',$_POST['sb_auto_publish']);
		}
	}
}

function ajaxDeleteTweet(){
	global $twtUtility;
	$twtUtility->deleteTweet($_POST['id']);
	exit;
}
function ajaxLiveTweet(){
	global $twtUtility;
	$post = get_post($_POST['postID']);
	$tweet = $twtUtility->twtReplace($post->ID,$_POST['status']);
	$update = $twtUtility->tweet($tweet,$post);
	$json = json_encode($update);
	
	if(!empty($update['status']) && $update['status'] == 1){
		$id = $post->ID;
		delete_post_meta($id,'_sb_savedtweet');
		wp_die($json);
		echo $json;
	} else {
		echo $json;
	}
	exit;
}
?>