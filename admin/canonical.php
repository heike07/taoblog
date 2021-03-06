<?php

function the_link(&$p, $home=true) {
    global $tbpost;
    global $tbmain;

	$home = $home ? $tbmain->home : '';
	$link = '';

	if($p->type === 'post') {
		$link = $home.'/'.$p->id.'/';
	} else if($p->type === 'page') {
		$link = $home.$tbpost->get_the_parents_string($p->id).'/'.$p->slug;
	} else {
		$link = '/';
	}

	return $link;
}

function the_id_link(&$p, $home=true) {
    global $tbpost;
    global $tbmain;

    $home = $home ? $tbmain->home : '';

	return $home . '/' . $p->id . '/';
}

function the_edit_link(&$p, $ret_anchor = true) {
	$link = '/admin/post.php?do=edit&id='.(int)$p->id;

	return $ret_anchor
		? '<a href="'.$link.'">编辑</a>'
		: $link;
}

