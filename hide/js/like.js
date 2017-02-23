/**
 * This is taken from wedge/core/javascript/topic.js
 * and slightly modified.
 * Copyright:
 * Wedge (http://wedge.org)
 * Copyright © 2010 René-Gilles Deberdt, wedge.org
 * Portions are © 2011 Simple Machines.
 * License: http://wedge.org/license/
 */

$(window).bind("load", function () {
    likePost = hideLikePost;
});

function hideLikePost(obj)
{
    var iMessageId = $(obj).closest('.msg').attr('id').slice(3);

    show_ajax();
    $.post(obj.href, function (response)
    {
        hide_ajax();
        response = JSON.parse(response);
        if(response.error !== false) {
            say(response.error)
        }
        if(response.body !== false) {
            $('#msg' + iMessageId + ' .inner').first().html(response.body);
        }
        $('#msg' + iMessageId + ' .post_like').first().replaceWith(response.like_html);
    });

    return false;
}
