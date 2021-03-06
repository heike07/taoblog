<?php

class TB_Post_Metas {
	public $error = '';
	public $allowed_types = ['post', 'page', 'tax'];

	public function has($tid, $type = 'post') {
		global $tbdb;

		if(!in_array($type, $this->allowed_types)) {
			$this->error = '未知的meta类型';
			return false;
		}

		$tid = (int)$tid;
		$sql = "SELECT id FROM post_metas WHERE type='$type' AND tid=$tid LIMIT 1";
		$r = $tbdb->query($sql);
		if(!$r || !is_a($r, 'mysqli_result')) {
			$this->error = $tbdb->error;
			return false;
		}

		return $r->num_rows > 0;
	}

	public function insert($tid, $type = 'post') {
		global $tbdb;

		if(!in_array($type, $this->allowed_types)) {
			$this->error = '未知的meta类型';
			return false;
		}

		if($this->has($tid, $type)) {
			$this->error = "类型为 $type 的 $tid 已经存在";
			return false;
		}

		$header = $_POST['postmetas_header'];
		$footer = $_POST['postmetas_footer'];
		$keywords = $_POST['postmetas_keywords'];

		if(!$header && !$footer && !$keywords) return false;

		$sql = "INSERT INTO post_metas (type,tid,header,footer,keywords) values (?,?,?,?,?)";
		if(($stmt = $tbdb->prepare($sql))
			&& $stmt->bind_param('sisss', $type, $tid, $header, $footer, $keywords)
			&& $stmt->execute()
			)
		{
			return true;
		}

		return false;
	}

	public function update($tid, $type = 'post', $insert_if_not_exists=true) {
		global $tbdb;

		if(!in_array($type, $this->allowed_types)) {
			$this->error = '未知的meta类型';
			return false;
		}

		if(!$this->has($tid, $type)) {
			$this->error = "类型为 $type 的 $tid 不存在";
			if(!$insert_if_not_exists) return false;
			else return $this->insert($tid, $type);
		}

		$header = $_POST['postmetas_header'];
		$footer = $_POST['postmetas_footer'];
		$keywords = $_POST['postmetas_keywords'];

		if(!$header && !$footer && !$keywords) {
			//TODO: 删除该meta
			return;
		}

		$sql = "UPDATE post_metas SET header=?,footer=?,keywords=? WHERE type='$type' AND tid=".(int)$tid;
		$sql .= " LIMIT 1";
		if(($stmt = $tbdb->prepare($sql))
			&& $stmt->bind_param('sss', $header, $footer, $keywords)
			&& $stmt->execute()
			)
		{
			return true;
		}

		return false;
	}

	public function get($tid, $type = 'post') {
		global $tbdb;

		if(!in_array($type, $this->allowed_types)) {
			$this->error = '未知的meta类型';
			return false;
		}

		if(!$this->has($tid, $type)) {
			$this->error = "类型为 $type 的 $tid 不存在";
			return false;
		}

		$tid = (int)$tid;

		$metas = new stdClass;

		$sql = "SELECT * FROM post_metas WHERE type='$type' AND tid=$tid LIMIT 1";
		if(($r = $tbdb->query($sql)) && is_a($r, 'mysqli_result') && $r->num_rows>0) {
			$metas = $r->fetch_object();
		}

		return $metas;
	}
}

function postmetas_head() {
	global $tbquery;
	global $tbtax;
	global $tbopt;

	if(!$tbquery->is_singular() || !$tbquery->count) {
		return false;
	}

	$tbpm = new TB_Post_Metas;

	global $the; // the post/page object

	$id = $the->id;
	$type = $the->type;
	$tax = $the->taxonomy;

	$ispost = $type === 'post';

	if($ispost) {
		$parents_ids = $tbtax->get_parents_ids($tax);
		$tax_metas = [];

		foreach($parents_ids as $pid) {
			$t = $tbpm->get($pid, 'tax');
			if($t) $tax_metas[] = $t;
		}
		$this_tax_meta = $tbpm->get($tax, 'tax');
		if($this_tax_meta) $tax_metas[] = $this_tax_meta;
	}

	$post_metas = $tbpm->get($id, $type);

	$metas = new stdClass;
	if($ispost)
		$metas->tax = &$tax_metas;
	$metas->post = $post_metas;

	$GLOBALS['postmetas'] = $metas;

	$keywords = '';

	if($ispost) {
		// TODO: 优化到post一起
		$names = array_reverse($tbtax->tree_from_id($tax)['name']);
		if($this_tax_meta && $this_tax_meta->keywords) unset($names[0]);

		$keywords = implode(',', $names);
		if($this_tax_meta && $this_tax_meta->keywords) $keywords = $this_tax_meta->keywords . $keywords;
	}

	// 输出关键字
	if($metas->post) {
		$keywords_post = $metas->post ? $metas->post->keywords : '';
		if($keywords_post) $keywords = $keywords_post . $keywords;
	}
	echo '	<meta name="keywords" content="',htmlspecialchars($keywords),',',htmlspecialchars($tbopt->get('blog_name')),'" />'."\n";

	if($ispost) {
		// 依次输出header
		foreach($tax_metas as $tm) {
			echo $tm->header;
		}
	}
	if($metas->post) echo $metas->post->header;
}

add_hook('tb_head', 'postmetas_head');

function postmetas_footer() {
	if(!isset($GLOBALS['postmetas'])) {
		// 如果head没被成功调用
		return;
	}
	$metas = $GLOBALS['postmetas'];

	if(isset($metas->tax)) {
		foreach($metas->tax as $tm)
			echo $tm->footer;
	}
	
	if($metas->post) echo $metas->post->footer;

	unset($GLOBALS['postmetas']);
}

add_hook('tb_footer', 'postmetas_footer');

function postmetas_post_posted($id, $pr) {
	$tbpm = new TB_Post_Metas;
	
	$tbpm->insert($id, $pr['type']);
}

add_hook('post_posted', 'postmetas_post_posted');

function postmetas_post_updated($id, $pr) {
	$tbpm = new TB_Post_Metas;
	
	$tbpm->update($id, $pr['type']);
}

add_hook('post_updated', 'postmetas_post_updated');

function postmetas_post_widget($p=null) {
	$tbpm = new TB_Post_Metas;

	$metas = $p ? $tbpm->get($p->id, $p->type) : null;

	$title = 'Metas';
	$position = 'left';
	$content = 
		 '<h4>Header</h4>'
		.'<textarea wrap="off" name="postmetas_header">'.($metas ? htmlspecialchars($metas->header) : '').'</textarea><br>'
		.'<h4>Footer</h4>'
		.'<textarea wrap="off" name="postmetas_footer">'.($metas ? htmlspecialchars($metas->footer) : '').'</textarea><br>'
		.'<h4>Keywords</h4>'
		.'<textarea wrap="off" name="postmetas_keywords">'.($metas ? htmlspecialchars($metas->keywords) : '').'</textarea>';

	return compact('title', 'position', 'content');

}

add_hook('post_widget', 'postmetas_post_widget');

