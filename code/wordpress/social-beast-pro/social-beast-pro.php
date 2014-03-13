<?php
/*
Plugin Name: Social Beast Pro
Plugin URI: http://www.jarretredding.com
Description: Create custom updates and post to various social networks.  Includes support for Facebook Pages.  Be the beast!
Author: Reactor 5
Author URI: http://jarretredding.com
Version: 0.2a
*/

define('VERSION',"0.2a");

ini_set("log_errors", "On");
ini_set("error_log", "error_log.txt");
ini_set("display_errors", 1);

//Installer
register_activation_hook(__FILE__,'sbpSetup');

//Defines name of version
define('SB_NAME',"Social Beast Pro");

//Defines Directory and URL of the plugin
define('SB_DIR', plugin_dir_path(__FILE__));
define('SB_URL', plugin_dir_url(__FILE__));

//Defines global variables
$option = get_option('sb_options');
$twtAppKey = get_option('sb_custom_app');


//Defines database tables
define('TWITTER_USER_TB',$wpdb->prefix . "sbp_twitterusers");
define('TWITTER_TWEETS_TB',$wpdb->prefix . "sbp_tweets");

//Load classes and function for each social network
require(SB_DIR . "function/twitter.php");

//Calls classes
$twtOAuthUtil = new twtOAuthUtil;
$twtUtility = new twtUtility;

add_action('admin_menu','sbp_init');
add_action('admin_menu','sb_meta_boxes');
add_action('admin_enqueue_scripts','sbpJS');

//Metaboxes for specified post types
function sb_meta_boxes(){
	global $option;
	
	foreach($option['post_types'] as $type){
		add_meta_box('sbp_post_box','Social Beast Pro','sbp_meta_box',$type,'normal','high');
	}
}
function sbp_meta_box(){
	require( SB_DIR . '/inc/metabox.php');
}


//Determines when certain scripts should be enqueued.
function sbpJS(){
	$screen = get_current_screen();
	global $option;
	
	if($screen->base == 'settings_page_sbp-settings'){
		wp_register_style('sbp-settings', SB_URL . 'css/sbp-options.css');
		wp_enqueue_style('sbp-settings');
		wp_enqueue_script('sbp-settings-js', SB_URL . 'js/sbp-options-js.php','','',true);
	}
	
	foreach($option['post_types'] as $type){
		if($screen->post_type == $type){
			wp_register_style('sbp-meta', SB_URL . 'css/sbp-meta.css');
			wp_enqueue_style('sbp-meta');
			wp_enqueue_script('sbp-meta-js', SB_URL . 'js/sbp-meta.js','','',true);
			wp_localize_script('sbp-meta-js','sbpAjax',array('ajaxurl' => admin_url( 'admin-ajax.php' )));
			return;
		}
	}
}

//Adds options for settings page
function sbpSetup(){	
	
	$options = array(
		'custom_keys' => 1,
		'default_tweet' => "%T - %U",
		'post_types' => array('post')
	);
	
	$access = array(
		'twitter' => array(
			'users' => array(),
			'groups' => array("administrator")
		),
	);

	add_option('sb_custom_app');
	add_option('sb_options',$options);
	add_option('sb_perms', $access);
	add_option('sb_version', VERSION);
	
	sbpDBSetup();
	if(get_option('sb_post_types')) sbpOptionUpgrade();
}

//Initializes settings pages and meta boxes
function sbp_init(){
	add_options_page("Social Beast Pro Settings","Social Beast Pro Settings",'manage_options','sbp-settings','sbpSettingsPage');
}

//Calls settings page under "Settings"
function sbpSettingsPage(){
	require( SB_DIR . '/inc/settings.php');
}

function sbpDBSetup() {
   global $wpdb;
   
   $twitter_users = $wpdb->prefix . "sbp_twitterusers";
   $tweets_db = $wpdb->prefix . "sbp_tweets";
      
   $users = "CREATE TABLE $twitter_users (
  user_id bigint(20) NOT NULL,
  oauth_token varchar(60) NOT NULL,
  oauth_token_secret varchar(60) NOT NULL,
  screen_name varchar(45) NOT NULL,
  access longtext,
  PRIMARY KEY (user_id),
  UNIQUE KEY user_id_UNIQUE (user_id),
  UNIQUE KEY oauth_token_UNIQUE (oauth_token),
  UNIQUE KEY oauth_token_secret_UNIQUE (oauth_token_secret)
);";

	$tweets = "CREATE TABLE $tweets_db (
  id bigint(20) NOT NULL,
  post_id bigint(20) NOT NULL,
  text varchar(140) NOT NULL,
  tweet_count int(11) NOT NULL,
  timestamp int(11) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY id_UNIQUE (id)
);";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
   dbDelta($users);
   dbDelta($tweets);
}

function sbpOptionUpgrade(){
	
	$options = array(
		'custom_keys' => get_option('sb_custom_keys'),
		'default_tweet' => get_option('sb_default_tweet'),
		'post_types' => get_option('sb_post_types')
	);
	
	$access = array(
		'twitter' => array(
			'users' => array(),
			'groups' => array("administrator")
		),
	);
	
	update_option('sb_options',$options);
	update_option('sb_perms',$access);
	
	if(get_option('sb_custom_keys') == 1){
	
		$customKeys = array(
			'consumerKey' => get_option('sb_consumer_key'),
			'consumerSecret' => get_option('sb_consumer_secret'),
			'accessKey' => get_option('sb_access_key'),
			'accessSecret' => get_option('sb_access_secret'),
			'permissions' => $access
		);
		
		update_option('sb_custom_app',$customKeys);
	}
	
	delete_option('sb_consumer_key');
	delete_option('sb_consumer_secret');
	delete_option('sb_access_key');
	delete_option('sb_access_secret');
	delete_option('sb_custom_keys');
	delete_option('sb_default_tweet');
	delete_option('sb_post_types');
	delete_option('sb_user_access');

}

//Function to create admin notices.
function sbpNotices($type,$message){
	echo "<div class=\"" . $type . "\"><p>" . $message . "</p></div>";
}

//Sets up and runs functions to produce a message in the Wordpress Admin Notice box. Types are "updated" or "error".
function sbpStatusMessage($type,$message){
	add_action('admin_notices', 'sbpNotices', 10, 2);
	do_action('admin_notices',$type,$message);
}

//Eliminates spaces in string
function sbpDropSpace($value){
	$value = str_replace(" ","",$value);
	$pattern = '/\s+/';
	$value = preg_replace($pattern,'',$value);
	return $value;
}
?>