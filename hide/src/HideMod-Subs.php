<?php
if (!defined('WEDGE'))
	die('Hacking attempt...');

global $pattern_search_bbc, $pattern_search_hide, $pattern_search_hide_reply;
$pattern_search_bbc = '/\[{}\]([\s\S]*)\[\/{}\]/'; //regex for bbcode
$pattern_search_hide = str_replace("{}", "hide", $pattern_search_bbc);
$pattern_search_hide_reply = str_replace("{}", "hide-reply", $pattern_search_bbc); //regex for hide-reply

function hmLike()
//Thats the "normal" like code from wedge, but a bit modified. Not a nice way but easiest.
{
	global $topic, $context, $settings;
	global $pattern_search_bbc, $pattern_search_hide; //HM: Some globals we need
	loadSource('Like'); //HM: We only overwrite Like but we also need DisplayLike
	$dont_allowed_to_dislike_hide = true; //tweaking for debug, should normally be true
	$contains_hide = false;

	if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'view')
		return DisplayLike();

	if (!MID || empty($settings['likes_enabled']))
		fatal_lang_error('no_access', false);

	// We might be doing a topic.
	if (empty($_REQUEST['msg']) || (int) $_REQUEST['msg'] == 0)
	{
		// If it isn't a topic, check the external handler, just in case. They'll have to be checking $_REQUEST themselves, and performing their own session check.
		$result = call_hook('like_handler', array(&$changes));
		if (empty($result))
			fatal_lang_error('not_a_topic', false);

		foreach ($result as $func => $response)
			list ($id_content, $content_type) = $response;
	}
	else
	{
		checkSession('get');

		$id_content = (int) $_REQUEST['msg'];
		if (isset($_GET['thought']))
		{
			$content_type = 'think';

			$request = wesql::query('
				SELECT
					h.id_member, h.thought
				FROM {db_prefix}thoughts AS h
				WHERE h.id_thought = {int:tid}
					AND {query_see_thought}',
				array(
					'tid' => $id_content,
				)
			);

			$valid = false;
			if (wesql::num_rows($request) != 0)
			{
				list ($id_author, $subject) = wesql::fetch_row($request);
				$valid = true;
			}
			wesql::free_result($request);
			if (!$valid || (empty($settings['likes_own_posts']) && $id_author == MID))
				fatal_lang_error('no_access', false);

			$context['redirect_from_like'] = '#thought' . $id_content;
		}
		else
		{
			$content_type = 'post';

			// Validate this message is in this topic.
			$request = wesql::query('
				SELECT id_topic, id_member, subject
				FROM {db_prefix}messages
				WHERE id_msg = {int:msg}',
				array(
					'msg' => $id_content,
				)
			);
			$in_topic = false;
			if (wesql::num_rows($request) != 0)
			{
				list ($id_topic, $id_author, $subject) = wesql::fetch_row($request);
				$in_topic = $id_topic == $topic;
			}
			wesql::free_result($request);
			if (!$in_topic || (empty($settings['likes_own_posts']) && $id_author == MID))
				fatal_lang_error('not_a_topic', false);

			$context['redirect_from_like'] = 'topic=' . $topic . '.msg' . $_REQUEST['msg'] . '#msg' . $_REQUEST['msg'];
		}
	}

	if (empty($id_content) || empty($content_type))
		fatal_lang_error('no_access', false);
	$contains_hide = match_post_regex($id_content, $pattern_search_hide);
	// Does the current user already like said content?
	$request = wesql::query('
		SELECT like_time
		FROM {db_prefix}likes
		WHERE id_content = {int:id_content}
			AND content_type = {string:content_type}
			AND id_member = {int:user}',
		array(
			'id_content' => $id_content,
			'content_type' => $content_type,
			'user' => MID,
		)
	);

	$like_time = time();

	if ($row = wesql::fetch_row($request))
	{
		//HM: Disable unlike if post contains [hide] bbc.
		$new_like = false;

		if($content_type == 'post' and match_post_regex($id_content, $pattern_search_hide) and $dont_allowed_to_dislike_hide == true){
			// TODO Rewrite this... double code nix good code
			$now_liked = false;
			
			

		}else{
			// We had a row. Kill it.
			wesql::query('
				DELETE FROM {db_prefix}likes
				WHERE id_content = {int:id_content}
					AND content_type = {string:content_type}
					AND id_member = {int:user}',
				array(
					'id_content' => $id_content,
					'content_type' => $content_type,
					'user' => MID,
				)
			);
			$now_liked = false;
		}
		
	}
	else
	{
		// No we didn't, insert it.
		wesql::insert('',
			'{db_prefix}likes',
			array('id_content' => 'int', 'content_type' => 'string-6', 'id_member' => 'int', 'like_time' => 'int'),
			array($id_content, $content_type, MID, $like_time)
		);
		$now_liked = true;

		// Send notifications.
		if (!empty($id_author) && !empty($subject))
		{
			if ($content_type == 'think')
				Notification::issue('likes_thought', $id_author, $id_content, array(
					'subject' => $subject,
					'member' => array(
						'id' => MID,
						'name' => we::$user['name'],
					),
				));
			else
				Notification::issue('likes', $id_author, $_REQUEST['msg'], array(
					'topic' => $topic,
					'subject' => $subject,
					'member' => array(
						'id' => MID,
						'name' => we::$user['name'],
					),
				));
		}
	}

	wesql::free_result($request);

	call_hook('liked_content', array(&$content_type, &$id_content, &$now_liked, &$like_time));


	if (AJAX)
	{
		if ($content_type == 'think')
			return return_thoughts();

		// OK, we're going to send some details back to the user through the magic of AJAX. We need to get those details, first of all.
		$context['liked_posts'] = array();

		$request = wesql::query('
			SELECT id_content, id_member
			FROM {db_prefix}likes
			WHERE id_content = {int:id_content}
				AND content_type = {string:content_type}
			ORDER BY like_time',
			array(
				'id_content' => $id_content,
				'content_type' => $content_type,
			)
		);

		while ($row = wesql::fetch_assoc($request))
		{
			// If it's us, log it as being us.
			if ($row['id_member'] == MID)
				$context['liked_posts'][$row['id_content']]['you'] = true;
			elseif (empty($context['liked_posts'][$row['id_content']]['others']))
				$context['liked_posts'][$row['id_content']]['others'] = 1;
			else
				$context['liked_posts'][$row['id_content']]['others']++;
		}
		wesql::free_result($request);



		// Now the AJAXish data. We must be able to like it, otherwise we wouldn't be here!
		loadTemplate('Msg');
		loadPluginTemplate('CerealGuy:HideMod', 'src/HideMod');
		
		if(isset($contains_hide) and $contains_hide == true and $dont_allowed_to_dislike_hide == true and $now_liked == false){


			return_callback('template_hm_dislike_error', array("Error: Du kannst diesen Beitrag nicht disliken.",$id_content, true));
		}elseif(isset($contains_hide) and $contains_hide == true){
			
			return_callback('template_hm_reload', array($id_content, true));
		}else{
			return_callback('template_show_likes', array($id_content, true));
		}
	}
	else
		redirectexit($context['redirect_from_like']);
}

function allowed_to_see($id_member_started){
	//checks if user is allowed to see this hide
	// returns true if user is admin or user is poster
	global $topicinfo;
	if(MID == $id_member_started or we::$is['admin']){
		return true;
	}
	return false;

}

function get_post_author($id){
	$req = wesql::query('
		SELECT
			id_msg, poster_time, id_member, body, smileys_enabled, poster_name, m.approved, m.data
		FROM {db_prefix}messages AS m
		INNER JOIN {db_prefix}topics AS t ON t.id_topic = m.id_topic AND {query_see_topic}
		WHERE id_msg = {int:id_msg}',
		array('id_msg' => $id)
	);
	$row = wesql::fetch_assoc($req);
	wesql::free_result($req);

	if (empty($row['id_msg']))
		return false;
	return $row['id_member'];
}	


function user_has_replied_to_topic($topicid){
	// returns bool if currently logged in user has replied to $topicid
	$query = wesql::query("SELECT * 
				FROM {db_prefix}messages 
				WHERE id_topic = {int:id_topic} 
				and id_member = {int:id_user} LIMIT 1",	array(
					'id_topic' => $topicid,
					'id_user' => MID));
	if(wesql::num_rows($query) > 0){
		return true;
	}
	return false;
					

}

function user_has_liked_post($postid){
	// returns bool if currently logged in user has liked post
	$query = wesql::query('SELECT * FROM {db_prefix}likes WHERE id_content = {int:post_id} AND content_type = "post" AND id_member = {int:member_id}', 
			array(
				'member_id' => MID,
				'post_id' => $postid,
			)
		);
	//print_r($result);
	if(wesql::num_rows($query) > 0){
		return true;
	}
	return false;
}

function match_post_regex($postid, $regex){
	// Check if regex matches post
	global $pattern_search_hide;
	$query = wesql::query("SELECT * 
				FROM {db_prefix}messages 
				WHERE id_msg = {int:id_post} LIMIT 1",	array(
					'id_post' => $postid));
	$result = wesql::fetch_assoc($query);
	return preg_match($regex, $result['body']);

}
