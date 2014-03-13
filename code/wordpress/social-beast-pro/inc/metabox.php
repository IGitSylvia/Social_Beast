<?php
global $twtUtility;
global $post;
global $option;

if(isset($_SESSION['status_type'])){
	sbpStatusMessage($_SESSION['status_type'],$_SESSION['status_message']);
	$_SESSION['status_type'] = '';
	$_SESSION['status_message'] = '';
}

function tweetAutoPublish($post){
	global $option;
	//$posttype = $post->post_type;
	$ap = count(get_post_meta($post->ID,'_sb_autopublish'));

	if($post->post_status != 'publish'){
		$ap = ($ap > 0) ? get_post_meta($post->ID,'_sb_autopublish',true) : $option['auto_publish'];
	} else {
		$ap = 2;
	}
	
	switch($ap){
		case NULL:
		case '0':
			echo "0";
			break;
		case 1:
		case "all":
			echo "1";
			break;
		case 2:
			echo "2";
			break;
		default:
			echo "ERROR!";
			break;			
	}	
}
?>

<div id="sb_meta_box">
    <div id="sb_twitter">
        <div id="sb_tweet">
            <textarea id="sb_tweetbox" name="sb_tweetbox"><?php echo get_post_meta($post->ID,'_sb_savedtweet',true); ?></textarea>
            <input type="hidden" name="sb_origtweet" value="<?php echo get_post_meta($post->ID,'_sb_savedtweet',true); ?>" />
            <div id="sb_tweet_autopublish">
            	<input id="sb_ap_button" type="button" value="AP Disabled" name="sb_ap_button" disabled="disabled" />
                <div id="sb_tweet_status"></div>
                <input id="sb_auto_publish" type="hidden" name="sb_auto_publish" value="<?php tweetAutoPublish($post); ?>" />
            </div>
            <div id="sb_tweet_char"></div>
        </div>
        <div id="sb_prev_tweets"><?php $twtUtility->postTweets($post->ID); ?></div>
    </div>
</div>
<input type="hidden" name="pass" value="1" />