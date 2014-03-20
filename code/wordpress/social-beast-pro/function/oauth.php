<?php

/*
** Contains methods to successfully complete OAuth requests.
*/
class twtOAuth {
	
	private $config;	
	protected $consumerKey;
	protected $consumerSecret;
	protected $oauthKey;
	protected $oauthSecret;
	protected $params;
	
	function __construct(){
		$this->params = array();
		$this->config = array(
			"host" => 'api.twitter.com',
			"signature_method" => 'HMAC-SHA1',
			"base_url" => 'https://api.twitter.com',
			"oauth_version" => '1.0',
			"sb_consumer_key" => '',
			"sb_consumer_secret" => '',
			"sb_access_key" => '',
			"sb_access_secret" => ''	
		);
		
		//Hard coded to 1 for testing.
		$this->oauthKeys(1);
	}
	
	/*
	** Sets auth keys, allowing users to use their own application.
	*/
	private function oauthKeys($custom){
		$this->consumerKey = ($custom == 1) ? get_option('sb_consumer_key') : $this->config['sb_consumer_key'];
		$this->consumerSecret = ($custom == 1) ? get_option('sb_consumer_secret') : $this->config['sb_consumer_secret'];
		$this->oauthKey = ($custom == 1) ? get_option('sb_access_key') : $this->config['sb_access_key'];
		$this->oauthSecret = ($custom == 1) ? get_option('sb_access_secret') : $this->config['sb_access_secret'];
	}
	
	/*
	**  Add parameters to the REST call. You can put in unlimted parameters.
	*/
	public function addParameter(){
		$numParams = func_num_args();
		if ($numParams == 0){ return; }
		
		$params = func_get_args();
		error_log('args: ' . $params);
		foreach($params as $param){
			$info = explode("=", $param);
			$this->params[$info[0]] = $info[1];
		}
	}
	
	/*
	** Obtains request token.
	*/
	protected function getRequestToken(){
		$response = $this->request('POST','/oauth/request_token',null,array('oauth_callback' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']),1);
		
		if($response['code'] == 200){
			$data = array();
			$responses = explode("&",$response['response']);
			
			foreach($responses as $item){
				$break = explode("=",$item);
				$data[$break[0]] = $break[1];
				error_log('Request Response: ' . $break[0] . ' => ' .$break[1]);
			}
			
			return $data;
		}
		
		error_log("Token Request Error: HTTP Error Code " . $response['code'] . "\n" . $response['response']);
		exit;	
	}
	
	/*
	** Collects header information to use in various methods and puts them in  
	** a keyed array.
	*/
	private function headInfo($method,$resource){
		$url = $this->config['base_url'] . $resource;
		
		$info = array(
			'method' => $method,
			'url' => $url	
		);
		
		return $info;
	}
	
	/*
	** Forms OAuth Authorization String.
	*/
	private function oauthAuthorization($oauth){
		$auth = "Authorization: OAuth ";
		$i = count($oauth);
		foreach($oauth as $key => $value){
			$auth .= rawurlencode($key) . "=\"" . rawurlencode($value) . "\"";
			
			if($i-- > 1){
				$auth .= ", ";
			}
		}
		
		return $auth;		
	}
	
	/*
	**  Constructs paramaters for OAuth request. Extras should be in an array with 
	**  key values. $excludeToken should have a value of 0 or 1.
	*/
	private function oauthConstruct($header,$extra,$excludeToken){
		$oauth = array();
		
		if(is_array($extra)){
			foreach($extra as $key => $value){
				$oauth[$key] = $value;
			}
		}
		
		if($excludeToken == 0){
			$oauthKey = $this->oauthKey;
			$oauthSecret = isset($oauth['oauth_token_secret']) ? $oauth['oauth_token_secret'] : $this->oauthSecret;
		}
		
		$oauth['oauth_consumer_key'] = $this->consumerKey;
		if(!isset($oauth['oauth_token']) && $excludeToken == 0){ $oauth['oauth_token'] = $oauthKey; }
		$oauth['oauth_timestamp'] = time();
		$oauth['oauth_signature_method'] = $this->config['signature_method'];
		$oauth['oauth_version'] = $this->config['oauth_version'];
		$oauth['oauth_nonce'] = md5($this->consumerKey . time() . rand(1, 10000));
		$oauth['oauth_signature'] = $this->oauthSignature($header,$this->params,$oauth,$this->consumerSecret,$oauthSecret);

		ksort($oauth);
		return $oauth;
	}
	
	/*
	** Creates signature as specified by twitter documentation.  Both $header, $params, and
	** $oauth values should contain arrays.
	*/
	private function oauthSignature($header,$params,$oauth,$consumerSecret,$accessSecret){
		
		//Set up parameter string. Start with joining arrays, sort by key, then convert to 
		//url encoded string.
		$parameters = array();
		
		if(is_array($params)){
			foreach($params as $key => $value){
				$parameters[$key] = $value;
			}
		}
		foreach($oauth as $key => $value){
			$parameters[$key] = $value;
		}
		ksort($parameters);
		$paramString = '';
		
		$i = count($parameters);
		foreach($parameters as $key => $value){
			$key = rawurlencode($key);
			$value = rawurlencode($value);
			
			$paramString .= $key . "=" . $value;
			
			if($i-- > 1){
				$paramString .= "&";
			}
		}
		$paramString = rawurlencode($paramString);
		
		//Add uppercase method and url encoded url to create Signature Base String.
		$method = strtoupper($header['method']);
		$url = rawurlencode($header['url']);
		$signatureBase = $method . "&" . $url . "&" . $paramString;
		
		//Create signing key by combining both secret keys (if applicable).
		$consumerSecret = rawurlencode($consumerSecret);
		$signingKey = $consumerSecret . "&";
		
		if($accessSecret != ''){
			$accessSecret = rawurlencode($accessSecret);
			$signingKey .= $accessSecret;		
		}
		
		//Final steps are to hash the signature base and then convert to base64.
		$signature = hash_hmac('sha1',$signatureBase,$signingKey,true);
		$signature = base64_encode($signature);
		return $signature;					
	}
	
	public function request($method,$resource,$params,$oauthExtra,$excludeToken=0){
		$headInfo = $this->headInfo($method,$resource);
		$oauth = $this->oauthConstruct($headInfo,$oauthExtra,$excludeToken);
		$authorization = $this->oauthAuthorization($oauth);
		
		$header = array($authorization);
		$options = array(
			CURLOPT_URL => $headInfo['url'],
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => 0,
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $params
		);
		
		$request = curl_init();
		curl_setopt_array($request,$options);
		$response = curl_exec($request);
		$code = curl_getinfo($request,CURLINFO_HTTP_CODE);
		if(!$response){ error_log("cURL Error: " . curl_error($request)); }
		curl_close($request);

		return array(
			'code' => $code,
			'response' => $response
		);	
	}
	
	/*
	** Constructs URL for use in various methods. Settig API to 0 will exlude the base URL.
	** API = 0 should be used when needing to put in full URL, i.e. Callback. Params must
	** be an array.
	*/
	protected function url($resource,$params,$api = 1){
		$url = ($api == 1) ? $this->config['base_url'] . $resource : $resource;
		
		if(is_array($params)){
			$url .= "?";
			$i = count($params);
			foreach($params as $key => $value){
				$url .= $key . "=" . $value;
				
				if($i-- > 1){
					$url .= "&";
				}
			}
		}
		return $url;		
	}
}

/*
** twtUtil class methods are designed to execute specific twitter functions such
** a authorizing a user, sending a tweet, seeing retweets and replies, etc.
*/
class twtOAuthUtil extends twtOAuth {
	
	/*
	** Authorizes Twitter accounts for use. Excludes account with custom application.
	*/
	public function twtUserAuth(){
		
		if(!isset($_GET['oauth_verifier'])){
			$token = $this->getRequestToken();
			if($token['oauth_callback_confirmed'] != true){
				error_log("Token Request Callback Status: " . $token['oauth_callback_confirmed']);
				return;
			}
			
			$_SESSION['authtoken'] = $token['oauth_token'];
			$_SESSION['authsecret'] = $token['oauth_token_secret'];
			$params = array('oauth_token' => $token['oauth_token']);
			
			$redirect = $this->url('/oauth/authorize',$params);
			echo '<META HTTP-EQUIV="Refresh" Content="0; URL=' . $redirect .'">';
			exit;
		}
		
		if($_SESSION['authtoken'] == $_GET['oauth_token']){
			global $wpdb;
			$this->addParameter('oauth_verifier=' . $_GET['oauth_verifier']);
			$RequestToken = array('oauth_token' => $_GET['oauth_token']);
			$request = $this->request('POST','/oauth/access_token',$this->params,$RequestToken);
			
			if($request['code'] == 200){
				$data = array();
				$responses = explode("&",$request['response']);

				foreach($responses as $response){
					$item = explode('=',$response);
					$data[$item[0]] = $item[1];
				}
				
				$insert = $this->updateTwitterUsr($data);
				
				if($insert === true){
					sbpStatusMessage('updated',$data['screen_name'] . " has been successfully authenticated.");
				} else {
					sbpStatusMessage('error',"Twitter user authentication failed with the following message: " . $insert);
				}
				
			} else {
				sbpStatusMessage('error',"Unable to obtain Access Token. Error Code" . $request['code']  . " - " . $request['response']);
				error_log('Access Token Response: HTTP Code ' . $request['code'] . ' - ' . $request['response']);
			}
		} else {
			sbpStatusMessage('error','Request and Access tokens do not match.');
			error_log('Request and Access tokens do not match');
		}
	}
	
	/*
	** Updates Twitter user table.  $data should be an associative array with 
	** column name as the keys.
	*/
	private function updateTwitterUsr($data){
		global $wpdb;
		$query = "SELECT user_id FROM " . TWITTER_USER_TB . " WHERE user_id = " . $data['user_id'];
		$result = $wpdb->get_row($query, ARRAY_N);
		$count = count($result);
		
		if($count == 1){
			$where = array('user_id' => $data['user_id']);
			$wpdb->update(TWITTER_USER_TB,$data,$where);
			sbpStatusMessage('update',"Update function used!");
		} else {
			$wpdb->insert(TWITTER_USER_TB,$data);
		}
		
		$error = mysql_error();
		
		if($error){
			return $error;
		} else {
			return true;
		}
		
	}

}
?>