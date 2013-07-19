tweetBox = document.getElementById("sb_tweetbox");
charCount = document.getElementById("sb_tweet_char");

charCount.onload = tweetCharCount();
tweetBox.onkeyup = tweetCharCount;

function tweetCharCount(){
	charCount.innerHTML = 140 - tweetBox.value.length;	
}