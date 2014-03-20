<?php
class twtAccessCtl {
	
	/*
	** Grants user access to send tweets with specific user accounts. Creates permissions array
	** if one wasn't already created.
	*/
	public static function grantUsrAccess($user,$account){
		global $wpdb;
		$query = "SELECT access FROM " . TWITTER_USER_TB . " WHERE user_id = " . $account;
		$result = $wpdb->get_row($query, ARRAY_N);
		$count = count($result);
		
		if($count == 1 && $result[0] != ''){
			$array = unserialize($result[0]);
			$match = 0;
			
			foreach($array['users'] as $id){
				if($user == $id){
					$match++;
				}
			}
			
			if($match == 0){
				array_push($array['users'],$user);
			}
		} else {
			$array = array(
				'users' => array($user),
				'groups' => array()
			);
		}
		
		$array = serialize($array);
		return $array;
	}
}
?>