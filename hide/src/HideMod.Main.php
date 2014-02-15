<?php
global $pattern_search_bbc, $pattern_search_hide, $pattern_search_hide_reply;
$pattern_search_bbc = '/\[{}\](.*?)\[\/{}\]/'; //regex for bbcode
$pattern_search_hide = str_replace("{}", "hide", $pattern_search_bbc);
$pattern_search_hide_reply = str_replace("{}", "hide-reply", $pattern_search_bbc); //regex for hide-reply


function hmDisplayPostDone(&$counter, &$output)
{

	global $user, $settings;
	global $pattern_search_bbc, $pattern_search_hide, $pattern_search_hide_reply;

	if(strpos($output['body'],'hide') == false) return; //check if post contains hide, if not skip this shit here
	
	if(allowed_to_see_hide($output)){ //check if user is allowed to see [hide]
		//hes allowed to see it, therefore clean up bbcode and do before/after stuff
		$output['body'] = preg_replace('/\[hide\]/', $settings['hidemod_sa2'], $output['body']);

		$output['body'] = preg_replace('/\[\/hide\]/', !empty($settings['hidemod_sa3']) ? $settings['hidemod_sa3'] : '', $output['body']);
	}else{
		//hes not allowed to see it, therefore replace [hide....]blubb[/hide] with a message
		$output['body'] = replace_bbcode($pattern_search_hide, $output['body'], $settings['hidemod_sa1']);
	}
	//print_r($output);
	if(allowed_to_see_hide_reply($output)){ //same like line 11 but checks for hide-reply
		$output['body'] = preg_replace('/\[hide-reply\]/', $settings['hidemod_sb2'], $output['body']);
		$output['body'] = preg_replace('/\[\/hide-reply\]/', !empty($settings['hidemod_sb3']) ? $settings['hidemod_sb3'] : '', $output['body']);
	}else{
		$output['body'] = replace_bbcode($pattern_search_hide_reply, $output['body'], $settings['hidemod_sb1']);
	}

}
function hmQuoteFastDone(&$xml, &$post_id, &$row){
	global $pattern_search_hide, $pattern_search_hide_reply, $settings;
	if(we::$is['admin'] or we::$user['mod_cache']['id'] == $row['id_member']){
		//User is admin or 
		log_error("HM ".$user['mod_cache']['id']." ".$row['id_member']);
		return;
	}
	if(!user_has_replied_to_topic($row['id_topic'])){
		$xml = preg_replace($pattern_search_hide_reply, "*** HIDDEN CONTENT REPLY TO SEE IT ***", $xml);
        }
	if(!user_has_liked_post($post_id)){
		$xml = preg_replace($pattern_search_hide, "*** HIDDEN CONTENT LIKE TO SEE IT ***", $xml);
        }

}

function init_settings(){
	//Some workaround for empty setting variables
	global $settings;
	$vars = array("sa2", "sa3", "sb2", "sb3");
	foreach($vars as $v){
		
		if(empty($settings["hidemode_".$v])){
			$settings["hidemode_".$v] = "s";
		}	
	}

}
function allowed_to_see_hide($output){
	//checks if user is allowed to see hide
	//checks if user has liked or is admin/poster
	if(allowed_to_see($output)){ return true;} //check if user is owner/admin
	return(user_has_liked_post($output['id']));
}

function allowed_to_see_hide_reply($output){
	//check if user is allowed to see hide-reply
	//checks if user has replied or is admin/poster
	if(allowed_to_see($output)){ return true;} //check if user is owner/admin
	$topicid = topic_id_from_post_id($output['id']);
	
	return user_has_replied_to_topic($topicid);
	
}

function allowed_to_see($output){
	//checks if user is allowed to see this hide
	// returns true if user is admin or user is poster
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
