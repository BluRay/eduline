<?php
class EmptyAction extends  Action{
	public function _empty(){
		$this->assign('isAdmin', 1);
		$this->assign('jumpUrl', SITE_URL);
		
		$this->error('你访问的页面不存在！');
	}
}