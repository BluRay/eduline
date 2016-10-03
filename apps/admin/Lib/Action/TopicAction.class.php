<?php
/**
 * 后台，系统配置控制器
 * @author jason <yangjs17@yeah.net> 
 * @version TS3.0
 */
// 加载后台控制器
tsload ( APPS_PATH . '/admin/Lib/Action/AdministratorAction.class.php' );
class TopicAction extends AdministratorAction {
	public function cate() {
		$this->_top ();
		$this->pageTitle ['cate'] = '资讯分类配置';
		$treeData = model ( 'CategoryTree' )->setTable ( 'zy_topic_category' )->getNetworkList ();
		$this->displayTree ( $treeData, 'zy_topic_category', 1 );
	}
	public function index() {
		$this->_top ();
		$this->pageTitle ['index'] = '资讯管理';
		$this->pageKeyList = array (
				'id',
				'title',
				'cate',
				'desc',
				'dateline',
				'action',
				're' 
		);
		$list = model ( 'Topics' )->getTopic ( 1, 0 );
		$this->pageButton [] = array (
				'title' => L ( 'PUBLIC_ADD' ),
				'onclick' => "admin.newZixun()" 
		);
		$this->pageButton [] = array (
				'title' => '删除',
				'onclick' => "admin.delZixun()" 
		);
		$cates = model ( 'Topics' )->getAdmincate ();
		foreach ( $list ['data'] as &$v ) {
			$v['id'] = $v ['id'];
			$v['re'] = ($v ['re'] == 1) ? '<a href="javascript:;" onClick="admin.Zixuntj(' . $v ['id'] . ',0,this)">取消推荐</a>' : '<a href="javascript:;" onClick="admin.Zixuntj(' . $v ['id'] . ',1,this)">设为推荐</a>';
			$v['cate'] = $cates [$v ['cate']];
			$v['desc'] = getShort($v['desc'] ,40);
			$v['dateline'] = date ( 'Y-m-d H:i', $v ['dateline'] );
			$v['action'] = '<a href="javascript:;" onClick="admin.editZixun(' . $v ['id'] . ')">编辑</a>';
		}
		$this->displayList ( $list );
	}
	public function ajaxtj() {
		$id = $_GET ['id'];
		if (! $id) {
			echo '参数错误，请刷新当前页面';
			exit ();
		} else {
			return model ( 'Topics' )->setTj ( $id );
		}
	}
	public function editor() {
		$this->pageTitle ['editor'] = '编辑资讯';
		$id = $_GET ['id'];
		if (! $id) {
			$this->assign ( 'jumpUrl', U ( 'admin/Topic/index' ) );
			$this->error ( '参数错误' );
		} else {
			$_REQUEST ['tabHash'] = 'editor';
			$this->_top ();
			$this->pageTitle ['newZixun'] = '编辑资讯';
			$this->pageTab [] = array (
					'title' => '编辑资讯',
					'tabHash' => 'editor',
					'url' => U ( 'admin/Topic/editor' ) 
			);
			
			$this->pageKeyList = array (
					'title',
					'desc',
					'text',
					'image',
					'cate',
					're',
					'readcount' 
			);
			$this->opt ['cate'] = model ( 'Topics' )->getAdmincate ();
			$data = model ( 'Topics' )->getOnedata ( $id );
			$this->opt ['re'] [1] = '是';
			$this->opt ['re'] [0] = '否';
			$this->savePostUrl = U ( 'admin/Topic/doeditor', array (
					'id' => $id 
			) );
			$this->displayConfig ( $data );
		}
	}
	public function doeditor() {
		$id = $_GET ['id'];
		if (! $id) {
			$this->assign ( 'jumpUrl', U ( 'admin/Topic/index' ) );
			$this->error ( '参数错误' );
		} else {
			$id = intval ( $_GET ['id'] );
			if (! $_POST ['title']) {
				$this->error ( '请输入标题' );
			} elseif (! $_POST ['desc']) {
				$this->error ( '请输入摘要' );
			} elseif (! $_POST ['text']) {
				$this->error ( '请输入内容' );
			} elseif (! $_POST ['image']) {
				$this->error ( '请上传封面' );
			} elseif (! $_POST ['cate']) {
				$this->error ( '请选择分类' );
			} else {
				$ary ['title'] = t ( $_POST ['title'] );
				$ary ['desc'] = t ( $_POST ['desc'] );
				$ary ['text'] = h ( $_POST ['text'] );
				$ary ['image'] = intval ( $_POST ['image'] );
				$ary ['cate'] = intval ( $_POST ['cate'] );
				$ary ['re'] = intval ( $_POST ['re'] );
				$ary ['dateline'] = time ();
				$ary ['recount'] = intval ( $_POST ['recount'] );
				if (model ( 'Topics' )->savedata ( $ary, $id )) {
					$this->assign ( 'jumpUrl', U ( 'admin/Topic/index' ) );
					$this->success ( '编辑成功' );
				} else {
					$this->error ( '未知错误' );
				}
			}
		}
	}
	public function newZixun() {
		$_REQUEST ['tabHash'] = 'newZixun';
		$this->_top ();
		$this->pageTitle ['newZixun'] = '添加资讯';
		$this->pageTab [] = array (
				'title' => '添加资讯',
				'tabHash' => 'newZixun',
				'url' => U ( 'admin/Topic/newZixun' ) 
		);
		
		$this->pageKeyList = array (
				'title',
				'desc',
				'text',
				'image',
				'cate',
				're',
				'recount' 
		);
		$this->opt ['cate'] = model ( 'Topics' )->getAdmincate ();
		$this->opt ['re'] [1] = '是';
		$this->opt ['re'] [0] = '否';
		$this->savePostUrl = U ( 'admin/Topic/donewZixun' );
		$this->displayConfig ();
	}
	public function donewZixun() {
		if (! $_POST ['title']) {
			$this->error ( '请输入标题' );
		} elseif (! $_POST ['desc']) {
			$this->error ( '请输入摘要' );
		} elseif (! $_POST ['text']) {
			$this->error ( '请输入内容' );
		} elseif (! $_POST ['cate']) {
			$this->error ( '请选择分类' );
		} else {
			$ary ['title'] = t ( $_POST ['title'] );
			$ary ['desc'] = t ( $_POST ['desc'] );
			$ary ['text'] = h ( $_POST ['text'] );
			$ary ['image'] = intval ( $_POST ['image'] );
			$ary ['cate'] = intval ( $_POST ['cate'] );
			$ary ['re'] = intval ( $_POST ['re'] );
			$ary ['dateline'] = time ();
			$ary ['readcount'] = intval ( $_POST ['readcount'] );
			if (model ( 'Topics' )->addNew ( $ary )) {
				$this->assign ( 'jumpUrl', U ( 'admin/Topic/index' ) );
				$this->success ( '添加成功' );
			} else {
				$this->error ( '未知错误' );
			}
		}
	}
	private function _top() {
		$this->pageTab [] = array (
				'title' => '资讯管理',
				'tabHash' => 'index',
				'url' => U ( 'admin/Topic/index' ) 
		);
		$this->pageTab [] = array (
				'title' => '资讯分类',
				'tabHash' => 'cate',
				'url' => U ( 'admin/Topic/cate' ) 
		);
	}
	// 删除资讯
	public function delTopics() {
		$data ['is_del'] = 1;
		$where = array (
				'id' => array (
						'in',
						$_POST ['id'] 
				) 
		);
		$res = M ( 'ZyTopic' )->where ( $where )->save ( $data );
		
		if ($res !== false) {
			$msg ['data'] = L ( 'PUBLIC_DELETE_SUCCESS' );
			$msg ['status'] = 1;
			echo json_encode ( $msg );
		} else {
			$msg ['data'] = "删除失败!";
			echo json_encode ( $msg );
		}
	}
}