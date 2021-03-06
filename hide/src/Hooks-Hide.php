<?php

/**
 * [hide_bbc_buttons description]
 * @hook bbc_buttons
 * @param        [type]                  $bbc [description]
 * @return       [type]                       [description]
 */
function hide_bbc_buttons(&$bbc) {
    global $context, $txt;
    loadPluginLanguage('CerealGuy:Hide', 'lang/Hide-Editor');

    array_splice(
        $bbc[0],
        5,
        0,
        [
            [
                'image' => $context['plugins_url']['CerealGuy:Hide'] . '/assets/bbc_hide.png',
            	'code' => 'hide',
            	'before' => '[hide]',
                'after' => '[/hide]',
            	'description' => $txt['hide_editor_hide_desc'],

            ],
            [
                'image' => $context['plugins_url']['CerealGuy:Hide'] . '/assets/bbc_hide_reply.png',
    			'code' => 'hide-reply',
    			'before' => '[hide-reply]',
                'after' => '[/hide-reply]',
    			'description' => $txt['hide_editor_hide_reply_desc'],
            ],
            [],
        ]
    );
}

function hide_display_main() {
    add_plugin_js_file('CerealGuy:Hide', 'js/like.js');
}

function hide_display_prepare_post($counter, $message) {
    global $context;
    loadPluginSource('CerealGuy:Hide', 'src/Subs-Hide');
    $context['hide_like'] = ['can_dislike' => allowedTo('hide_see_through') || !containsHide($message['body']), 'error' => false, 'body' => false]; // Hide
}
