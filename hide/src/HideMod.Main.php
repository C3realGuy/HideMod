<?php
// CerealGuy:HideMod
if (!defined('WEDGE'))
	die('Hacking attempt...');

function hmQuoteFastDone(&$xml, $row){
	$post_id = $_REQUEST['quote'];
	loadPluginSource('CerealGuy:HideMod', 'src/HideMod-Subs');
	global $pattern_search_hide, $pattern_search_hide_reply, $settings;
	print $row['id_member'];
	if(we::$is['admin'] or MID == $row['id_member']){
		//User is admin or 
		return;
	}
	if(!user_has_replied_to_topic($row['id_topic'])){
		$xml = preg_replace($pattern_search_hide_reply, $settings['hidemod_sc2'], $xml);
        }
	if(!user_has_liked_post($post_id)){
		$xml = preg_replace($pattern_search_hide, $settings['hidemod_sc1'], $xml);
        }

}

function hmPostBBCParse(&$message, &$bbc_options, &$type){
	loadPluginSource('CerealGuy:HideMod', 'src/HideMod-Subs'); 
	global $context, $settings, $pattern_search_bbc, $pattern_search_hide, $pattern_search_hide_reply, $topicinfo, $topic, $board;
	$allowed_types = array('post', 'post-preview'); //perhaps interesting for tweaking at some day
	$disabled_boards = !empty($settings['hidemod_disabled_boards']) ? unserialize($settings['hidemod_disabled_boards']) : array(); // nice line, modified from TopicSolved, prepares board settings from acp

	if(isset($bbc_options['cache']) and isset($topic) and in_array($type, $allowed_types)){

		if($topicinfo['id_first_msg'] == $bbc_options['cache']){
			// It's the first post of a topic, therefore we already have the id_member_started info
			$god = allowed_to_see($topicinfo['id_member_started']);
		}else{
			// It's a post, but not the first, therefore we have to get that info
			$god = allowed_to_see(get_post_author($bbc_options['cache']));
			
		}
		if(in_array($board, $disabled_boards)){ // check if board is in disabled_boards (settings in acp)
			$god = true;
		}
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

function hmActionList(){
	//Overwrite like function
	global $action_list;
	$action_list['like'] = array("src/HideMod-Subs", "hmLike", "CerealGuy:HideMod");

}

