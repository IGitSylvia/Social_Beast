<?php 
require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
header("Content-type: text/javascript");

global $option;
?>

selectSBP = document.getElementById("select_sbp_app");
selectCustom = document.getElementById("select_custom_app");

selectSBP.onclick = disableAppFields;
selectCustom.onclick = enableAppFields;
window.onload = checkCustomApp;

function checkCustomApp(){
	var setting = <?php echo $option['custom_keys']; ?>;
    
    if(setting == 0){
    	disableAppFields();
    } else {
    	enableAppFields();
    }
}

function disableAppFields(){
	var consumerKey = document.getElementById("sb_consumer_key");
    var consumerSecret = document.getElementById("sb_consumer_secret");
    var accessKey = document.getElementById("sb_access_key");
    var accessSecret = document.getElementById("sb_access_secret");
    var classStr = document.getElementById("select_custom_app").className.replace(/ *selectedApp/g, '');
    
    consumerKey.disabled = true;
    consumerKey.className = 'disabledInput';
    consumerSecret.disabled = true;
    consumerSecret.className = 'disabledInput';
    accessKey.disabled = true;
    accessKey.className = 'disabledInput';
    accessSecret.disabled = true;
    accessSecret.className = 'disabledInput';
    
    document.getElementById("sb_custom_keys").value = 0;
    document.getElementById("select_custom_app").className = classStr;
    document.getElementById("select_sbp_app").className += " selectedApp";

}

function enableAppFields(){
	var consumerKey = document.getElementById("sb_consumer_key");
    var consumerSecret = document.getElementById("sb_consumer_secret");
    var accessKey = document.getElementById("sb_access_key");
    var accessSecret = document.getElementById("sb_access_secret");
    var classStr = document.getElementById("select_sbp_app").className.replace(/ *selectedApp/g, '');
    
    consumerKey.disabled = false;
    consumerKey.className = '';
    consumerSecret.disabled = false;
    consumerSecret.className = '';
    accessKey.disabled = false;
    accessKey.className = '';
    accessSecret.disabled = false;
    accessSecret.className = '';
    
    document.getElementById("sb_custom_keys").value = 1;
    document.getElementById("select_sbp_app").className = classStr;
    document.getElementById("select_custom_app").className += " selectedApp";
}
