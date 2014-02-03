<?php

function hmDisplayPostDone(&$counter, &$output)
{
	global $user;
	$pattern_search_hide = '/\[hide\](.*?)\[\/hide\]/';
	if(allowed_to_see_hide($output)){
		$output['body'] = preg_replace('/\[hide\]/', '<center>Hidden Content:</center><br>', $output['body']);
		$output['body'] = preg_replace('/\[\/hide\]/', '', $output['body']);
	}else{
		$output['body'] = replace_bbcode($pattern_search_hide, $output['body'], "<center>Hidden Content<br>You need to like to see hidden content</center>");
	}
	//print_r($output);


}
function allowed_to_see_hide($output){
	if(allowed_to_see($output)){ return true;}
	$result = wesql::query('SELECT * FROM {db_prefix}likes WHERE id_content = {int:post_id} AND content_type = "post" AND id_member = {int:member_id}', 
			array(
				'member_id' => we::$user['mod_cache']['id'],
				'post_id' => $output['id'],
			)
		);
	//print_r($result);
	if(wesql::num_rows($result) > 0){
		return true;
	}
				

	return false;
}

function allowed_to_see_hide_reply($output){
	if(allowed_to_see($output)){ return true;}


	return false;
	
}

function allowed_to_see($output){
	global $users;
	if(we::$user['mod_cache']['id'] == $output['member']['id']){
		return true;
	}
	if(we::$is['admin']){
		return true;
	}
	return false;

}
function replace_bbcode($pattern, $body, $replace){
	preg_match_all($pattern, $body, $hit, PREG_PATTERN_ORDER);
	
	$new = $body;
	if(!empty($hit[0])){
		foreach($hit[0] as &$t){
			$new = str_replace($t, $replace, $new);
		}
	}
	return $new;


}

?>
