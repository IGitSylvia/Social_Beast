tweetBox = document.getElementById("sb_tweetbox");
charCount = document.getElementById("sb_tweet_char");
prevTweets = document.getElementById("sb_prev_tweets");
twtapbutton = document.getElementById('sb_ap_button');
twtstatus = document.getElementById('sb_auto_publish');

charCount.onload = tweetCharCount();
tweetBox.onkeyup = tweetCharCount;

twtstatus.onload = tweetAPInit();
twtapbutton.onclick = tweetAPToggle;

prevTweets.onload = tweetDeleteButton();

function tweetCharCount(){
	charCount.innerHTML = 140 - tweetBox.value.length;	
}
function tweetDelete(){
	var tweetid = jQuery(this).next().attr("value");
	var url = "inc/ajax/del_tweets.php";
    var success = jQuery.get(url,{id: tweetid});
	
    if(success == 1){
        var htmlid = "sb_tweet_" + tweetid;
        var tweet = document.getElementById(htmlid);
        jQuery(tweet).slideUp(500,function(){ jQuery(tweet).remove();});
    }
}
function tweetDeleteButton(){
	var button = prevTweets.getElementsByTagName('input');
	
	for(var i = 0; i < button.length; i++){
		if(button[i].className == 'sb_tweet_delete'){
			button[i].onclick = tweetDelete;
		}
	}
}
function enableTweetAutoPublish(){	
	twtapbutton.className = 'enabled';
	twtapbutton.disabled = ''
	twtapbutton.value = 'Auto-Publish'
	twtstatus.value = '1';
}
function disableTweetAutoPublish(){
	twtapbutton.className = 'disabled';
	twtapbutton.disabled = '';
	twtapbutton.value = 'No Publish'
	twtstatus.value = '0';
}
function tweetAPInit(){
	if(twtstatus.value == 1){
		enableTweetAutoPublish();
	} else if(twtstatus.value == 0) {
		disableTweetAutoPublish();
	}
}
function tweetAPToggle(){
	if(twtstatus.value == 1){
		disableTweetAutoPublish();
	} else if(twtstatus.value == 0) {
		enableTweetAutoPublish();
	}
}