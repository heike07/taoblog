<?php
	$blog_name = $tbopt->get('blog_name');

?><!DOCTYPE html> 
<html lang="zh-CN">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1" />
	<title><?php
		if($tbquery->is_home()) {
			echo '首页';
        } else if($tbquery->is_archive()) {
            echo '文章归档';
		} else if(!$tbquery->count) {
			echo '404';
		} else if($tbquery->is_singular()) {
			echo $the->title;
		} else if($tbquery->is_category()) {
			echo "第{$tbquery->pageno}页 - ";
			$names = $tbquery->category['name'];
			$names = array_reverse($names);
			echo implode(' - ', $names);
		} else if($tbquery->is_date()) {
			echo "第{$tbquery->pageno}页 - ";
			if($tbquery->date->yy >= 1970) echo $tbquery->date->yy,'年';
			if($tbquery->date->mm >= 1 && $tbquery->date->mm <= 12) echo $tbquery->date->mm,'月';
		} else if($tbquery->is_tag()) {
			echo "第{$tbquery->pageno}页 - ";
			echo $tbquery->tags;
		}

		echo ' - ',$blog_name;
	?></title>
	<?php if($tbquery->is_home()) {
		echo '<meta name="keywords" content="', $tbopt->get('keywords'), '" />', PHP_EOL;
} ?>
	<link rel="alternate" type="application/rss+xml" title="<?php echo htmlspecialchars($blog_name);?>" href="<?php echo '/rss';?>" />
	<link rel="stylesheet" type="text/css" href="/theme/style.css" />
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css" />
	<script type="text/javascript" src="//blog-10005538.file.myqcloud.com/jquery.min.js"></script>
	<script type="text/javascript" src="/theme/scripts/header.js"></script>
<?php if(!$tbquery->count) {

} else if($tbquery->is_singular()) {?>
    <link rel="canonical" href="<?php echo the_link($the);?>" />
    <base href="<?php echo the_id_link($the);?>" />
    <script type="text/javascript">var _post_id = <?php echo $the->id; ?>;var _comment_count = <?php echo $the->comments; ?>;</script>
<?php } 

	apply_hooks('tb_head'); ?>

</head>

<body>
<div id="wrapper">
    <!-- 头部 -->
	<header id="header">
        <div class="content">
            <h2 class="sitename"><a href="/"><?php echo $blog_name; ?></a></h2>
            <p class="motto">命运好幽默，让爱的人都沉默。</p>
            <div class="nav">
                <ol>
                    <li><a href="/">首页</a></li>
                    <li><a href="/friends">朋友们</a></li>
                    <li><a title="GitHub" href="https://github.com/movsb" rel="nofollow" target="_blank">同性交友</a></li>
                    <li><a href="/search" title="Google站内搜索">站内搜索</a></li>
                    <li><a href="/archives">文章归档</a></li>
                    <li><a href="/blog">博客程序</a></li>
                    <li><a href="/echo">建议反馈</a></li>
                    <li><a href="/rss">博客订阅</a></li>
                    <?php if($logged_in) {
                        echo '<li><a href="/admin/">管理后台</a></l>',PHP_EOL;
                    } ?>
                </ol>
            </div>
        </div>

	</header>

	<section id="main">
		<div id="content">

