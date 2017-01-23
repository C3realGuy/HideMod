<?php

loadPluginSource('CerealGuy:Hide', 'src/Subs-Hide');

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

    // if we can't get post id, we better don't show stuff
    if($postId == false) {
      $hasliked = false;
    }else {
      // now check if current user liked this post
      $hasLiked = userHasLikedPost(MID, $postId);

      // and save it to bbc_options so we don't have to do this again
      $bbc_options['has_liked'] = $hasLiked;
    }
  }
  $showHide = $hasLiked;

  if($showHide) {
    if($bbc_type != 'quote') {
      $tag['content']  = isset($settings['hidemod_hide_unlocked_before']) ? $settings['hidemod_hide_unlocked_before'] : 'DEFAULT HIDE UNLOCKED BEFORE:<br>';
      $tag['content'] .= parse_bbc($data, $bbc_type, $bbc_options);
      $tag['content'] .= isset($settings['hidemod_hide_unlocked_after']) ? $settings['hidemod_hide_unlocked_after'] : '';
    } else {
      $tag['content'] = '[hide]'.parse_bbc_quote($data).'[/hide]';
    }
  } else {
    if($bbc_type != 'quote') {
      $tag['content'] = $settings['hidemod_hide_locked'];
    } else {
      $tag['content'] = '[hide]';
      $tag['content'] .=  isset($settings['hidemod_hide_locked_quote']) ? $settings['hidemod_hide_locked_quote'] : '*** DEFAULT HIDDEN CONTENT: LIKE TO SEE ***';
      $tag['content'] .= '[/hide]';
    }
  }
}

// Same as with validate_hide_bbc
function validate_hide_reply_bbc(&$tag, &$data, &$disabled) {
  global $settings, $topic, $topicinfo, $bbc_options, $bbc_type;
  log_error($bbc_type);
  // Maybe we already checked this post? (multiple hides)
  $hasReplied = (isset($topicinfo['has_replied']) && $topicinfo['has_replied'] == true) ? true : null;

  if($hasReplied === null) {
    $topic = getTopicId();
    if($topic == false) {
      // We couldn't get topic id, so we better hide stuff
      $hasReplied = false;
    }else {
      $hasReplied = userHasRepliedToTopic(MID, $topic);
      $topicinfo['has_replied'] = $hasReplied;
    }
  }

  $showHide = $hasReplied;

  if($showHide) {
    if($bbc_type != 'quote') {
      $tag['content'] = isset($settings['hidemod_hide_reply_unlocked_before']) ? $settings['hidemod_hide_reply_unlocked_before'] : 'DEFAULT HIDE-REPLY UNLOCKED BEFORE:<br>';
      $tag['content'] .= parse_bbc($data, $bbc_type, $bbc_options);
      $tag['content'] .= isset($settings['hidemod_hide_reply_unlocked_after']) ? $settings['hidemod_hide_reply_unlocked_after'] : '';
    } else{
      $tag['content'] = '[hide-reply]'.parse_bbc_quote($data).'[/hide-reply]';
    }
  } else {
    if($bbc_type != 'quote') {
      $tag['content'] = $settings['hidemod_hide_reply_locked'];
    } else {
      $tag['content'] = '[hide-reply]';
      $tag['content'] .= isset($settings['hidemod_hide_reply_locked_quote']) ? $settings['hidemod_hide_reply_locked_quote'] : '*** DEFAULT HIDDEN CONTENT: REPLY TO SEE ***';
      $tag['content'] .= '[/hide-reply]';
    }
  }
}

function hide_quote(&$row) {
  $row['body'] = parse_bbc_quote($row['body']);
}

function parse_bbc_quote($message) {
  loadSource('Subs-BBC');
  $message = parse_bbc($message, 'quote', array('tags' => array('hide', 'hide-reply', 'code')));

  // Now revert the [code] replacement
  $message = preg_replace('/\<div class="bbc_code"\>.+<code>/is', '[code]',  $message);
  $message = preg_replace('/\<\/code>\<\/div\>/is', '[/code]', $message);
  $message = preg_replace('/\<br\>/is', "\n", $message);
  return $message;
}