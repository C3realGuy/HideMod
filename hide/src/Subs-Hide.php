<?php

function userOwnsPost($user_id, $post_id) {
  $query = wesql::query('SELECT 1 FROM {db_prefix}messages WHERE id_msg={int:id_post} AND id_member={int:id_user} LIMIT 1',
                        array('id_post' => $post_id,
                              'id_user' => $user_id));
  return wesql::fetch_row($query)[0] == '1';
}

function userHasLikedPost($user_id, $post_id) {
  $query = wesql::query('SELECT 1 FROM {db_prefix}likes
                         WHERE id_content = {int:id_post}
                         AND content_type = "post"
                         AND id_member = {int:id_user} LIMIT 1',
                        array('id_post' => $post_id,
                              'id_user' => $user_id));
  return wesql::fetch_row($query)[0] == '1';
}

function userHasRepliedToTopic($user_id, $topic_id) {
  $query = wesql::query('SELECT 1 FROM {db_prefix}messages
                         WHERE id_topic = {int:id_topic}
                         AND id_member = {int:id_user} LIMIT 1',
                        array('id_user' => $user_id,
                              'id_topic' => $topic_id));
  return wesql::fetch_row($query)[0] == '1';
}

function getPostId() {
  global $bbc_options, $bbc_type;
  log_error(print_r([$bbc_options, $bbc_type], true));
  // If we're lucky, bbc_options['cache'] tells us the post id
  if(isset($bbc_options['cache'])) return $bbc_options['cache'];
  if($bbc_type == 'quote') return (int) $_REQUEST['quote'];
  log_error('Couldn\'t get Post Id!' . print_r([$bbc_options, $bbc_type], true));
  return false;
}

function getTopicId() {
  global $bbc_options, $bbc_type, $topic;
  if(!empty($topic)) return $topic;
  if($bbc_type == 'quote') {
    $postId = getPostId();
    $query = wesql::query('SELECT id_topic FROM {db_prefix}messages WHERE id_msg = {int:id_post}', array('id_post' => $postId));
    $row = wesql::fetch_row($query)[0];
    return (int) $row;
  }
  log_error('Couldn\'t get Topic Id!' . print_r([$bbc_options, $bbc_type, $topic], true));
  return false;
}

function translate_lang_strings($str) {
    return preg_replace_callback('~{{(\w+)}}~', 'parse_lang_strings', $str);
}

// Check if a message contains a hide tag which is not between code tags
function containsHide($message, $hide_bbc = '[hide]') {
    // First check if we even have a [hide] in our message, if not
    // we can directly return false.
    if(strpos($message, $hide_bbc) === false)
        return false;

    // if we have no code tag in our message, directly return true
    if(strpos($message, '[code') === false)
        return true;

    // okay we have a hide and maybe a code, strip out anything between [code=?.*]...[/code]
    return strpos(preg_replace('/(\[code(=.*?)?\](.*?)((\[\/code\])|$))/s', '', $message), $hide_bbc) !== false;
}
