<?php header("Content-type: text/javascript"); ?>

selectSBP = document.getElementById("select_sbp_app");
selectCustom = document.getElementById("select_custom_app");

selectSBP.OnClick = disableAppFields;

function disableAppFields(){
	var consumerKey = document.getElementById("sb_consumer_key");
    consumerKey.disabled = true;
}