<?php
loadPluginSource('CerealGuy:HideModv2', 'src/Subs-Hide');

// This function gets executed from Subs-BBC. It's kind of a hack because
// we misuse the validate feature of BBCodes in wedge. But it works and we
// don't have to use any stupid regexes like in v1.
function validate_hide_bbc(&$tag, &$data, &$disabled) {
  global $settings, $bbc_options, $bbc_type;

  // Maybe we already checked this post? (multiple hides)
  $hasLiked = (isset($bbc_options['has_liked']) && $bbc_options['has_liked'] == true) ? true : null;

  // If we don't know, check for it!
  if($hasLiked === null) {
    $postId = getPostId(); // get post id

    // if we can't get post id, log it
    if($postId == false) return log_error('Could\'nt get postId');

    // now check if current user liked this post
    $hasLiked = userHasLikedPost(MID, $postId);

    // and save it to bbc_options so we don't have to do this again
    $bbc_options['has_liked'] = $hasLiked;
  }

  if($hasLiked) {
    $tag['content']  = isset($settings['hidemod_hide_unlocked_before']) ? $settings['hidemod_hide_unlocked_before'] : 'DEFAULT HIDE UNLOCKED BEFORE:<br>';
    $tag['content'] .= parse_bbc($data, $bbc_type, $bbc_options);
    $tag['content'] .= isset($settings['hidemod_hide_unlocked_after']) ? $settings['hidemod_hide_unlocked_after'] : '';
  } else {
    $tag['content'] = $settings['hidemod_hide_locked'];
  }
}

// Same as with validate_hide_bbc
function validate_hide_reply_bbc(&$tag, &$data, &$disabled) {
  global $settings, $topic, $topicinfo;

  // Maybe we already checked this post? (multiple hides)
  $hasReplied = (isset($topicinfo['has_replied']) && $topicinfo['has_replied'] == true) ? true : null;

  if($hasReplied === null) {
    if(empty($topic)) return log_error('Couldn\'t get topic');

    $hasReplied = userHasRepliedToTopic(MID, $topic);

    $topicinfo['has_replied'] = $hasReplied;
  }



  if($hasReplied) {
    $tag['content'] = isset($settings['hidemod_hide_reply_unlocked_before']) ? $settings['hidemod_hide_reply_unlocked_before'] : 'DEFAULT HIDE-REPLY UNLOCKED BEFORE:<br>';
    $tag['content'] .= parse_bbc($data, $bbc_type, $bbc_options);
    $tag['content'] .= isset($settings['hidemod_hide_reply_unlocked_after']) ? $settings['hidemod_hide_reply_unlocked_after'] : '';
  } else {
    $tag['content'] = 'HAS NOT REPLIED';
  }

  $topicId = $topic;
}
