<?php
global $twtUtility;
global $post;

if(isset($_SESSION['status_type'])){
	sbpStatusMessage($_SESSION['status_type'],$_SESSION['status_message']);
	$_SESSION['status_type'] = '';
	$_SESSION['status_message'] = '';
}
?>

<div id="sb_meta_box">
    <div id="sb_twitter">
        <div id="sb_tweet">
            <textarea id="sb_tweetbox" name="sb_tweetbox"><?php echo get_post_meta($post->ID,'_sb_savedtweet',true); ?></textarea>
            <input type="hidden" name="sb_origtweet" value="<?php echo get_post_meta($post->ID,'_sb_savedtweet',true); ?>" />
            <div id="sb_tweet_char"></div>
        </div>
        <div id="sb_prev_tweets">
        	<?php $twtUtility->postTweets($post->ID); ?>
        </div>
    </div>
</div>