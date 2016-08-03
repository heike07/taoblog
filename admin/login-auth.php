<?php

require_once(dirname(__FILE__).'/../setup/config.php');
require_once('utils/die.php');
require_once('models/base.php');

// 用于登录页面的验证
function login_auth_passwd($arg = []) {
	require_once('models/options.php');
	$opt = new TB_Options;

    $saved_login = explode(',', $opt->get('login'));
    if($saved_login === false || count($saved_login) != 2)
        return false;

    $saved_user     = $saved_login[0];
    $saved_passwd   = $saved_login[1];

	$user           = $arg['user'] ?? '';
	$passwd         = $arg['passwd'] ?? '';

	if($user === $saved_user && sha1($passwd) === $saved_passwd) {
		return true;
	}
    else {
        return false;
    }
}

// 用于通过cookie认证客户端
function login_auth($redirect=false) {
	require_once('models/options.php');

	$opt = new TB_Options;

    $is_ssl = $_SERVER['SERVER_PORT'] == 443;

	$ip = $_SERVER['REMOTE_ADDR'];
	$cookie_login = $_COOKIE['login'] ?? '';

	$loggedin = $is_ssl && $cookie_login && $cookie_login === sha1($ip.$opt->get('login'));

	if(!$loggedin) {
		if($redirect) {
			$home = ($is_ssl ? 'https://' : 'http://') . $opt->get('home');
			$url = $home.'/admin/login.php?url='.urlencode($_SERVER['REQUEST_URI']);

			header('HTTP/1.1 302 Login Required');
			header('Location: '.$url);
			die(0);
		}

		return false;
	} else {
		return true;
	}
}

// 用于生成认证 cookie，独立出来的原因是 api 部分会用到
function login_gen_cookie() {
	$opt = new TB_Options;
    return sha1($_SERVER['REMOTE_ADDR'].$opt->get('login'));
}

// 用于在登录成功之后设置客户端认证的cookie
// 保存的是 sha1(ip + login)
function login_auth_set_cookie() {
	$opt = new TB_Options;
	setcookie('login', login_gen_cookie(), 0, '/', '', true, true);
}

