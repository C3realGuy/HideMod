<?php

loadPluginSource('CerealGuy:Hide', 'src/Subs-Hide');
loadPluginLanguage('CerealGuy:Hide', 'lang/Hide-BBC');
add_plugin_css_file('CerealGuy:Hide', 'css/hide', true);


/**
 * This function gets executed from Subs-BBC. It's kind of a hack because
 * we misuse the validate feature of BBCodes in wedge. But it works and we
 * don't have to use any stupid regexes like in v1.
 * @param                array                                    $tag            [description]
 * @param                [type]                                    $content         [description]
 * @param                [type]                                    $disabled [description]
 * @return             [type]                                                        [description]
 */
function bbc_validate_hide_bbc(&$tag, &$content, &$disabled, &$params) {
    global $settings, $bbc_options, $bbc_type;

    // Maybe we already checked this post? (multiple hides)
    $showHide = (isset($bbc_options['show_hide_like']) && $bbc_options['show_hide_like'] == true) ? true : null;

    if (allowedTo('hide_see_through')) {
        $showHide = true;
    }

    // If we don't know, check for it!
    if($showHide === null && we::$is_guest === false) {
        $postId = getPostId(); // get post id

        // if we can't get post id, we better don't show stuff
        if($postId == false) {
            $showHide = false;
        }else {
            // now check if user owns post or current user liked this post
            $showHide = userOwnsPost(MID, $postId) || userHasLikedPost(MID, $postId);
        }
    }

    // and save it to bbc_options so we don't have to do this again
    $bbc_options['show_hide_like'] = $showHide;

    if($showHide) {
        if($bbc_type != 'quote') {

            $tag['content'] = isset($settings['hidemod_hide_unlocked_before']) ? translate_lang_strings($settings['hidemod_hide_unlocked_before']) : 'DEFAULT HIDE UNLOCKED BEFORE:<br>';
            $tag['content'] .= parse_bbc($content, $bbc_type, $bbc_options);
            $tag['content'] .= isset($settings['hidemod_hide_unlocked_after']) ? translate_lang_strings($settings['hidemod_hide_unlocked_after']) : '';
        } else {
            $tag['content'] = '[hide]' . parse_bbc_quote($content) .'[/hide]';
        }
    } else {
        if($bbc_type != 'quote') {
            $tag['content'] = translate_lang_strings($settings['hidemod_hide_locked']);
        } else {
            $tag['content'] = '[hide]';
            $tag['content'] .= isset($settings['hidemod_hide_locked_quote']) ? translate_lang_strings($settings['hidemod_hide_locked_quote']) : '*** DEFAULT HIDDEN CONTENT: LIKE TO SEE ***';
            $tag['content'] .= '[/hide]';
        }
    }
}

// Same as with validate_hide_bbc
function bbc_validate_hide_reply_bbc(&$tag, &$content, &$disabled, &$params) {
    global $settings, $topic, $topicinfo, $bbc_options, $bbc_type;
    // Maybe we already checked this post? (multiple hides)
    $showHide = (isset($topicinfo['show_hide_reply']) && $topicinfo['show_hide_reply'] == true) ? true : null;

    if(allowedTo('hide_see_through')) {
        $showHide = true;
    }

    if($showHide === null && we::$is_guest === false) {
        $topic = getTopicId();
        $postId = getPostId(); // get post id
        if($topic == false || $postId == false) {
            // We couldn't get topic id, so we better hide stuff
            $showHide = false;
        }else {
            $showHide = userOwnsPost(MID, $postId) || userHasRepliedToTopic(MID, $topic);
        }
    }

    $bbc_options['show_hide_reply'] = $showHide;

    if($showHide) {
        if($bbc_type != 'quote') {
            $tag['content'] = isset($settings['hidemod_hide_reply_unlocked_before']) ? translate_lang_strings($settings['hidemod_hide_reply_unlocked_before']) : 'DEFAULT HIDE-REPLY UNLOCKED BEFORE:<br>';
            $tag['content'] .= parse_bbc($content, $bbc_type, $bbc_options);
            $tag['content'] .= isset($settings['hidemod_hide_reply_unlocked_after']) ? translate_lang_strings($settings['hidemod_hide_reply_unlocked_after']) : '';

        } else{
            $tag['content'] = '[hide-reply]' . parse_bbc_quote($content) . '[/hide-reply]';
        }
    } else {
        if($bbc_type != 'quote') {
            $tag['content'] = translate_lang_strings($settings['hidemod_hide_reply_locked']);
        } else {
            $tag['content'] = '[hide-reply]';
            $tag['content'] .= isset($settings['hidemod_hide_reply_locked_quote']) ? translate_lang_strings($settings['hidemod_hide_reply_locked_quote']) : '*** DEFAULT HIDDEN CONTENT: REPLY TO SEE ***';
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
    $message = preg_replace('/\<div class="bbc_code"\>.+<code>/is', '[code]',    $message);
    $message = preg_replace('/\<\/code>\<\/div\>/is', '[/code]', $message);
    $message = preg_replace('/\<br\>/is', "\n", $message);
    return $message;
}
