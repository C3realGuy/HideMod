<?php

/**
 * [hide_bbc_buttons description]
 * @hook bbc_buttons
 * @param        [type]                  $bbc [description]
 * @return       [type]                       [description]
 */
function hide_bbc_buttons(&$bbc) {
    global $txt;
    loadPluginLanguage('CerealGuy:Hide', 'lang/Hide-Editor');

    array_splice(
        $bbc[0],
        5,
        0,
        [
            [
                'image' => 'http://localhost:8080/plugins/hide2/assets/bbc_hide.png',
            	'code' => 'hide',
            	'before' => '[hide]',
                'after' => '[/hide]',
            	'description' => $txt['hide_editor_hide_desc'],

            ],
            [
                'image' => 'http://localhost:8080/plugins/hide2/assets/bbc_hide_reply.png',
    			'code' => 'hide-reply',
    			'before' => '[hide-reply]',
                'after' => '[/hide-reply]',
    			'description' => $txt['hide_editor_hide_reply_desc'],
            ],
            [],
        ]
    );
    file_put_contents('/tmp/test', var_export($bbc, true));

}

function hide_display_main() {
    add_plugin_js_file('CerealGuy:Hide', 'js/like.js');
}
