<?php
require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
require('../../social-beast-pro.php');

global $twtUtility;
$twtUtility->deleteTweets($_GET['id']);
?>