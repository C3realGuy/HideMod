<?php

function hmDisplayPostDone(&$counter, &$output)
{
	global $user, $settings;
	$pattern_search_hide = '/\[hide\](.*?)\[\/hide\]/'; //regex for hide
	$pattern_search_hide_reply = '/\[hide-reply\](.*?)\[\/hide-reply\]/'; //regex for hide-reply

	if(strpos($output['body'],'hide') == false) return; //check if post contains hide, if not skip this shit here
	
	if(allowed_to_see_hide($output)){ //check if user is allowed to see [hide]
		//hes allowed to see it, therefore clean up bbcode and do before/after stuff
		$output['body'] = preg_replace('/\[hide\]/', $settings['hidemod_sa2'], $output['body']); 
		$output['body'] = preg_replace('/\[\/hide\]/', $settings['hidemod_sa3'], $output['body']);
	}else{
		//hes not allowed to see it, therefore replace [hide....]blubb[/hide] with a message
		$output['body'] = replace_bbcode($pattern_search_hide, $output['body'], $settings['hidemod_sa1']);
	}
	//print_r($output);
	if(allowed_to_see_hide_reply($output)){ //same like line 11 but checks for hide-reply
		$output['body'] = preg_replace('/\[hide-reply\]/', $settings['hidemod_sb2'], $output['body']);
		$output['body'] = preg_replace('/\[\/hide-reply\]/', $settings['hidemod_sb3'], $output['body']);
	}else{
		$output['body'] = replace_bbcode($pattern_search_hide_reply, $output['body'], $settings['hidemod_sb1']);
	}

}
function allowed_to_see_hide($output){
	if(allowed_to_see($output)){ return true;} //check if user is owner/admin
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
	if(allowed_to_see($output)){ return true;} //check if user is owner/admin
	$topicid = topic_id_from_post_id($output['id']);
	
	return user_has_replied_to_topic($topicid, we::$user['mod_cache']['id']);
	
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

function topic_id_from_post_id($postid){
	$query = wesql::query("SELECT id_topic
				FROM {db_prefix}messages
				WHERE id_msg = {int:post_id}
				LIMIT 1 ", array('post_id' => $postid));

	if(wesql::num_rows($query) > 0){
		$row = wesql::fetch_assoc($query);
		return $row['id_topic'];
	}
}	

function user_has_replied_to_topic($topicid, $userid){
	$query = wesql::query("SELECT * 
				FROM {db_prefix}messages 
				WHERE id_topic = {int:id_topic} 
				and id_member = {int:id_user} LIMIT 1",	array(
					'id_topic' => $topicid,
					'id_user' => $userid));
	if(wesql::num_rows($query) > 0){
		return true;
	}
	return false;
					

}
?>
