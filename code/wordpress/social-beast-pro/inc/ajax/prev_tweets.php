<?php
require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

$twtUtility->postTweets($post->ID);
?>