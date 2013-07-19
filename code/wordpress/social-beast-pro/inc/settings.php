<?php
//Updates fields after data is submitted
if(isset($_POST['submit'])){
	if($_POST['sb_custom_keys'] != 0){
		update_option('sb_consumer_key',$_POST['sb_consumer_key']);
		update_option('sb_consumer_secret',$_POST['sb_consumer_secret']);
		update_option('sb_access_key',$_POST['sb_access_key']);
		update_option('sb_access_secret',$_POST['sb_access_secret']);
	}
	update_option('sb_custom_keys',$_POST['sb_custom_keys']);
	update_option('sb_post_types',$_POST['sb_post_types']);
	update_option('sb_default_tweet',$_POST['sb_default_tweet']);
}

//Calls classes
$twtOAuthUtil = new twtOAuthUtil;
$twtUtility = new twtUtility;

//Sends authorization request
if($_GET['oauth'] == 'authorize'){
	$twtOAuthUtil->twtUserAuth();

}

function checkPostType($value){
	$postChk = get_option('sb_post_types');
	if(in_array($value,$postChk)){
			return "checked";
	}
}

?>
<div class="wrap">

<?php screen_icon(); ?>
<h2><?php echo SB_NAME; ?></h2>
<div id="sbpSettings">
    <form method="post" action="">
        <div id="access_keys">
            <div class="app_options">
                <p>Please select a method to send your tweets. You can authorize your account with Social Beast for ease, or you can setup your own custom application. We recommend setting up your own application.</p>
                <ul>
                    <li><input class="app_select" type="button" id="select_sbp_app" name="Social Beast" value="Social Beast" /></li>
                    <li><input class="app_select" type="button" id="select_custom_app" name="Custom App" value="Custom App" /></li>
                </ul>
            </div>
                <div id="twitter">
                    <ul>
                        <li>
                            <label for="sb_consumer_key">Consumer Key:</label>
                            <input id="sb_consumer_key" name="sb_consumer_key" type="password" value="<?php echo get_option('sb_consumer_key'); ?>" />
                        </li>
                        <li>
                            <label for="sb_consumer_secret">Consumer Secret:</label>
                            <input id="sb_consumer_secret" name="sb_consumer_secret" type="password" value="<?php echo get_option('sb_consumer_secret'); ?>" />
                        </li>
                        <li>
                            <label for="sb_access_key">Access Token:</label>
                            <input id="sb_access_key" name="sb_access_key" type="password" value="<?php echo get_option('sb_access_key'); ?>" />
                        </li>
                        <li>
                            <label for="sb_access_secret">Access Secret:</label>
                            <input id="sb_access_secret" name="sb_access_secret" type="password" value="<?php echo get_option('sb_access_secret'); ?>" />
                        </li>
                        <input type="hidden" id="sb_custom_keys" name="sb_custom_keys" value="<?php echo get_option('sb_custom_keys'); ?>" />
                    </ul>
                </div>
            <div id="twt_accounts">
                <?php $twtUtility->verifiedAccount();	?>
            </div>
        </div>
        <div id="post_types">
            <p>Please select which post types you would like to use SBP from.</p>
            <div class="selection">
            	<ul>
                	<li><input type="checkbox" name="sb_post_types[]" value="post" <? echo checkPostType('post') ?> />Post</li>
                    <li><input type="checkbox" name="sb_post_types[]" value="page" <? echo checkPostType('page') ?> />Page</li>
					<?php
                    $posttype_args = array(
                        'public' => true,
                        '_builtin' => false
                    );
                    $posttypes = get_post_types($posttype_args);
                    
                    foreach($posttypes as $posttype){
                        echo "<li><input type=\"checkbox\" name=\"sb_post_types[]\" value=\"$posttype\" " . checkPostType($posttype) . " />" . ucfirst($posttype) . "</li>\n";
                    }
                    ?>
            	</ul>
            </div>
        </div>
        <div id="default_tweet">
        	<p>Use the legend below to compile your default tweet.</p>
        	<textarea name="sb_default_tweet"><?php echo get_option('sb_default_tweet') ?></textarea>
            <p>
            Title - %T<br />
            URL - %U
            </p>
        </div>
    <?php submit_button('Confirm Settings'); ?>
    </form>
</div>