tweetBox = document.getElementById("sb_tweetbox");
charCount = document.getElementById("sb_tweet_char");
postID = document.getElementById('post_ID');
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
	this.className = "wait";
	this.disabled = 'disabled';
	this.value = 'Wait';
	
    jQuery.post(sbpAjax.ajaxurl,{action: 'sbp-deletetweet',id: tweetid},function(data){
		if(data == 1){
			var htmlid = "sb_tweet_" + tweetid;
			var tweet = document.getElementById(htmlid);
			jQuery(tweet).slideUp(500,function(){ jQuery(tweet).remove();});
			
			this.className = "";
			this.disabled = '';
			this.value = 'Delete';
    	}
	});
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
function liveTweetButton(){
	twtapbutton.className = 'live_tweet';
	twtapbutton.disabled = '';
	twtapbutton.value = 'Tweet'
}
function tweetAPInit(){
	switch(twtstatus.value){
		case "1":
			enableTweetAutoPublish();
			break;
		case "0":
			disableTweetAutoPublish();
			break;
		case "2":
			liveTweetButton();
			break;
		default:
			break;
	}
}
function tweetAPToggle(){
	switch(twtstatus.value){
		case "1":
			disableTweetAutoPublish();
			break;
		case "0":
			enableTweetAutoPublish();
			break;
		case "2":
			this.className = "wait";
			this.disabled = 'disabled';
			this.value = 'Wait';
			
			jQuery.post(sbpAjax.ajaxurl,{action: 'sbp-livetweet',status: tweetBox.value,postID: postID.value},function(data){
				if(data.status == 1){
					tweetBox.value = '';
					document.getElementById('sb_tweet_status').innerHTML = "Success!";
				} else {
					document.getElementById('sb_tweet_status').innerHTML = "Failed: " + data[1];
				}
				
				jQuery('#sb_tweet_status').fadeIn(500).delay(5000).fadeOut(500);
				liveTweetButton();
			}, "json");
			break;
		default:
			break;
	}
}