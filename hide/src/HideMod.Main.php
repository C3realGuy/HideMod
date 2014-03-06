<?php
global $pattern_search_bbc, $pattern_search_hide, $pattern_search_hide_reply;
$pattern_search_bbc = '/\[{}\](.*?)\[\/{}\]/'; //regex for bbcode
$pattern_search_hide = str_replace("{}", "hide", $pattern_search_bbc);
$pattern_search_hide_reply = str_replace("{}", "hide-reply", $pattern_search_bbc); //regex for hide-reply


function hmQuoteFastDone(&$xml, &$post_id, &$row){
	global $pattern_search_hide, $pattern_search_hide_reply, $settings;
	if(allowed_to_see($row['id_member'])){

		return;
	}
	if(!user_has_replied_to_topic($row['id_topic'])){
		$xml = preg_replace($pattern_search_hide_reply, "*** HIDDEN CONTENT REPLY TO SEE IT ***", $xml);
        }
	if(!user_has_liked_post($post_id)){
		$xml = preg_replace($pattern_search_hide, "*** HIDDEN CONTENT LIKE TO SEE IT ***", $xml);
        }

}

function hmPostBBCParse(&$message, &$bbc_options){
	global $context, $settings, $pattern_search_bbc, $pattern_search_hide, $pattern_search_hide_reply, $topicinfo, $topic;
	if(isset($bbc_options['cache'])){
		$god = allowed_to_see($topicinfo['id_member_started']);
		if($god or user_has_liked_post($bbc_options['cache'])){
			$message = preg_replace('/\[hide\]/', $settings['hidemod_sa2'], $message);

			$message = preg_replace('/\[\/hide\]/', !empty($settings['hidemod_sa3']) ? $settings['hidemod_sa3'] : '', $message);
		}else{
			// not allowed hide
			$message = preg_replace($pattern_search_hide, $settings['hidemod_sa1'], $message);
		}

		if($god or user_has_replied_to_topic($topic)){
			$message = preg_replace('/\[hide-reply\]/', $settings['hidemod_sb2'], $message);
			$message = preg_replace('/\[\/hide-reply\]/', !empty($settings['hidemod_sb3']) ? $settings['hidemod_sb3'] : '', $message);
		}else{
			$message = preg_replace($pattern_search_hide_reply, $settings['hidemod_sb1'],$message);
		}
	}
	

}




function allowed_to_see($id_member_started){
	//checks if user is allowed to see this hide
	// returns true if user is admin or user is poster

	global $topicinfo;
	if(we::$user['mod_cache']['id'] == $id_member_started){
		return true;
	}
	if(we::$is['admin']){
		return true;
	}
	return false;

}

	

function user_has_replied_to_topic($topicid){
	$query = wesql::query("SELECT * 
				FROM {db_prefix}messages 
				WHERE id_topic = {int:id_topic} 
				and id_member = {int:id_user} LIMIT 1",	array(
					'id_topic' => $topicid,
					'id_user' => we::$user['mod_cache']['id']));
	if(wesql::num_rows($query) > 0){
		return true;
	}
	return false;
					

}

function user_has_liked_post($postid){
	$result = wesql::query('SELECT * FROM {db_prefix}likes WHERE id_content = {int:post_id} AND content_type = "post" AND id_member = {int:member_id}', 
			array(
				'member_id' => we::$user['mod_cache']['id'],
				'post_id' => $postid,
			)
		);
	//print_r($result);
	if(wesql::num_rows($result) > 0){
		return true;
	}
	return false;
}
?>
