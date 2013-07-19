Social Beast
============

Social Beast aims to give users of blogging applications such as Wordpress, Joomla, etc., the ability to publish their content to various social networks like Twitter, Facebook, and Tumblr using powerful features from their API's that I don't see in use very often. Target features can be found in the wiki.

Changelog
============

/* Version 0.1 (Alpha) */

-Base Version
-Custom Twitter Apps Usable
-Can specify while post types to enable plugin
-Twitter functionality enabled
-Can set default Twitter Message
-Keys for URL and Title enabled
-Enabled ShortURL support
-Embedded Tweet enabled

/* Version 0.1.1 (Alpha) */

-Fixed issue with initial sb_post_types setup where data would double serialze.
-Fixed issue with initial sb_user_access setup where data would double serialze.
-Fixed request method problem with "GET" query variables that caused verifyAccount() method not to work.
-Disabled oAuth user Authorization redirect.

Forming Tweets
============

As of version 0.1.1 there are only 2 variables that represent post values.  Due to the wrapping of t.co links, it appears that you need to have a certain amount of characters available for a tweet to go through.  This may result in a "tweet to long" error.  If you experience this error please send an e-mail to c0nc3pt.SF at gmail.com with the tweet you were trying to send so I can work on a solution.

%T = Title  
%U = URL

