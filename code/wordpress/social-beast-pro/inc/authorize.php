<?php
require("/../function/oauth.php");
$twtOAuthUtil = new twtOAuthUtil;
$twtOAuthUtil->twtAuth();

echo "test output"
?>