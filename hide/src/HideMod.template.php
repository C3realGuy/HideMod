<?php

function template_hm_dislike_error($error, $id_msg = 0, $can_like = false){
	echo '<script type="text/javascript">say("'.$error.'");</script>';
	template_show_likes($id_msg, $can_like);

}

function template_hm_reload(){
	log_error("test");
	echo '<script type="text/javascript">window.location.reload();</script>';

}
