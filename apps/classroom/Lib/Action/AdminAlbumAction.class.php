<?php
/**
 * 云课堂播(专辑)后台配置
 * 1.专辑管理 - 目前支持1级分类
 * @version CY1.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class AdminAlbumAction extends AdministratorAction
{
	/**
	 * 初始化，配置内容标题
	 * @return void
	 */
	public function _initialize()
	{
		parent::_initialize();
	}

	//专辑列表
	public function index(){
		$this->_initClassroomListAdminMenu();
		$this->_initClassroomListAdminTitle();
		$this->pageKeyList 	 = array('id','album_title','cover','user_title','ctime','DOACTION');
		$this->pageButton[]  =  array('title'=>'搜索专辑','onclick'=>"admin.fold('search_form')");
		$this->searchKey 	 = array('id','album_title');
		$this->searchPostUrl = U('classroom/AdminAlbum/index');
		$listData = $this->_getData(20 , 0);
		$this->displayList($listData);
	}


	//编辑、添加专辑
	public function addAlbum(){
		$this->_initClassroomListAdminMenu();
		$this->_initClassroomListAdminTitle();
		$this->pageKeyList = array('album_category','album_title','album_intro','album_html','cover','album_tag','listingtime','uctime','is_best');

		$this->opt['is_best'] = array( '1'=>'设置为精选' );
		$this->notEmpty = array('album_title','album_intro','album_category','cover');
		$this->savePostUrl = U('classroom/AdminAlbum/doAddAlbum');
		ob_start();
		echo W('VideoLevel',array('type'=>2));
		$output = ob_get_contents();
        $html=$this->fetch("album");
		ob_end_clean();
		$this->displayConfig(array('album_category'=>$output,'album_html'=>$html));
	}

	//添加专辑操作
	public function doAddAlbum(){
		$post = $_POST;
		$data['album_title'] 		= t($post['album_title']); //专辑名称
		$data['album_intro'] 		= t($post['album_intro']); //专辑简介
		$myAdminLevelhidden 		= getCsvInt(t($post['myAdminLevelhidden']),0,true,true,',');  //处理分类全路径
		$fullcategorypath 			= explode(',',$post['myAdminLevelhidden']);
		$category 					= array_pop($fullcategorypath);
		$category					= $category == '0' ? array_pop($fullcategorypath) : $category; //过滤路径最后为0的情况
		$data['fullcategorypath'] 	= $myAdminLevelhidden; //分类全路径
		$data['album_category']		= $category; //树尖分类id
		$data['cover'] 				= intval($post['cover']); //封面id
		$data['listingtime'] 		= $post['listingtime'] ? strtotime($post['listingtime']) : 0; //上架时间
		$data['uctime'] 			= $post['uctime'] ? strtotime($post['uctime']) : 0; //下架时间
		$data['is_best'] 			= isset($post['is_best']) ? intval($post['is_best']) : 0; //编辑精选
		$album_tag 					= explode(',' , $post['album_tag']);
		$video_ids 					= explode(',' , trim($post['video_ids'] ,',' ) );
		
		if(!$data['album_title']) $this->error('专辑标题不能为空');
		if(!$data['album_intro']) $this->error('专辑简介不能为空');
		if(!$data['album_category']) $this->error('请选择专辑分类');
		if(!$data['cover']) $this->error('还没有上传封面');
		if($data['uctime'] < $data['listingtime']) $this->error('下架时间不能小于上架时间');
		
		if($post['id']){
		    $data['utime']              = time();
			$result = M('zy_album')->where('id = '.$post['id'])->data($data)->save();
		} else {
		    $data['ctime']              = time();
		    $data['utime']              = time();
		    $data['uid']                = $this->mid;
			$result = M('zy_album')->data($data)->add();
		}
		if($result){
			unset($data);
			//处理标签和课程
			if($post['id']){
				//删除旧标签
				model('Tag')->setAppName('classroom')->setAppTable('zy_album')->deleteSourceTag($post['id']);
				$tag_reslut = model('Tag')->setAppName('classroom')->setAppTable('zy_album')->addAppTags($post['id'],$album_tag);
				//删除旧课程
				M('zy_album_video_link')->where('album_id='.$post['id'])->delete();
				//添加专辑课程关联
				$sql = 'insert into '.C('DB_PREFIX').'zy_album_video_link (`album_id`,`video_id`) values';
				foreach($video_ids as $val){
					$sql .= '('.$post['id'] .','.$val .'),';
				}
				M()->query( trim($sql , ','));
			} else {
				//添加标签
				$tag_reslut = model('Tag')->setAppName('classroom')->setAppTable('zy_album')->addAppTags($result,$album_tag);
				//添加专辑课程关联
				$sql = 'insert into '.C('DB_PREFIX').'zy_album_video_link (`album_id`,`video_id`) values';
				foreach($video_ids as $val){
					$sql .= '('.$result .','.$val .'),';
				}
				M()->query( trim($sql , ','));
			}
			$data['str_tag'] = implode(',' ,getSubByKey($tag_reslut,'name'));
			$data['tag_ids'] = ','.implode(',',getSubByKey($tag_reslut,'tag_id')).',';
			$map['id'] = isset($post['id']) ? $post['id'] : $result;
			M('zy_album')->where($map)->data($data)->save();
			if($post['id']){
				$this->success('编辑成功');
			} else {
				$this->success('添加成功');
			}
		} else {
			$this->error("系统错误，请稍后再试");
		}
	}

	/**
	 * 编辑专辑
	 */
	public function editAlbum(){
		$this->_initClassroomListAdminMenu();
		$this->pageTitle['editAlbum'] = '编辑专辑';
		$this->pageTab[]   = array('title'=>'编辑专辑','tabHash'=>'editAlbum','url'=>U('classroom/AdminAlbum/editAlbum'));
		$this->pageKeyList = array('id','myAdminLevelhidden','album_title','album_intro','album_html','cover','album_tag','listingtime','uctime','is_best');

		$this->opt['is_best'] = array( '1'=>'设置为精选' );
		$this->notEmpty       = array('album_title','album_intro','album_category','cover');
		$this->savePostUrl    = U('classroom/AdminAlbum/doAddAlbum');
		
		$data = D("ZyAlbum","classroom")->getAlbumById($_GET['id']);
		$data['album_tag'] = $data['str_tag'];
		$data['fullcategorypath'] = trim($data['fullcategorypath'] , ',');
		ob_start();
		echo W('VideoLevel',array('type'=>2,'default'=>$data['fullcategorypath']));
		$output = ob_get_contents();
		ob_end_clean();
		$data['myAdminLevelhidden'] = $output;
		
		//查询专辑包含的课程
		$video_data = M('zy_album_video_link')->where('album_id='.$_GET['id'])->field('video_id')->findAll();
		$video_data = getSubByKey($video_data , 'video_id');
		$video_ids  = implode(',', $video_data);

        $this->assign("data" , $video_data);
        $this->assign("album_video",$video_ids.',');
        $html = $this->fetch("album");
        $data['album_html'] = $html;
		$this->displayConfig($data);
	}
	
	//删除专辑(隐藏)
	public function delAlbum(){
		if(!$_POST['id']){
			exit(json_encode(array('status'=>0,'info'=>'请选择要删除的对象!')));
		} 
		$map['id'] = intval($_POST['id']);
		$data['is_del'] = $_POST['is_del'] ? 0 : 1; //传入参数并设置相反的状态
		if(M('zy_album')->where($map)->data($data)->save()){
			exit(json_encode(array('status'=>1,'info'=>'操作成功')));
		} else {
			exit(json_encode(array('status'=>1,'info'=>'操作失败')));
		}
	}

	//专辑回收站(被隐藏的专辑)
	public function recycle(){
		$this->_initClassroomListAdminMenu();
		$this->_initClassroomListAdminTitle();
		$this->pageKeyList = array('id','album_title','user_title','ctime','DOACTION');
		$listData = $this->_getData(20,1);
		$this->displayList($listData);
	}
	

	//获取专辑数据
	private function _getData($limit = 20, $is_del){
		if(isset($_POST)){
			$_POST['id'] && $map['id'] = intval($_POST['id']);
			$_POST['album_title'] && $map['album_title'] = array('like', '%'.t($_POST['album_title']).'%');
		}
		$map['is_del'] = $is_del; //搜索非隐藏内容
		$list = M('zy_album')->where($map)->order('ctime desc,id desc')->findPage($limit);
		foreach ($list['data'] as &$value){
			$value['album_title'] = msubstr($value['album_title'],0,20);
			$value['user_title'] = getUserSpace($value['uid']);
			$value['ctime'] = friendlyDate($value['ctime']);
			$value['cover'] = "<img src=".getCover($value['cover'] , 60 , 60)." width='60px' height='60px'>";
			$value['DOACTION'] = '<a href="'.U('classroom/AdminAlbum/askAlbum',array('tabHash'=>'askAlbum','id'=>$value['id'])).'">提问</a> | ';
			$value['DOACTION'] .= '<a href="'.U('classroom/AdminAlbum/noteAlbum',array('tabHash'=>'noteAlbum','id'=>$value['id'])).'">笔记</a> | ';
			$value['DOACTION'] .= '<a href="'.U('classroom/AdminAlbum/reviewAlbum',array('tabHash'=>'reviewAlbum','id'=>$value['id'])).'">评价</a> | ';
			$value['DOACTION'] .= $value['is_del'] ? '<a href="'.U('classroom/AdminAlbum/editAlbum',array('id'=>$value['id'],'tabHash'=>'editVideo')).'">编辑</a> | 
					<a onclick="admin.delObject('.$value['id'].',\'Album\','.$value['is_del'].');" href="javascript:void(0)">恢复</a>' : '<a href="'.U('classroom/AdminAlbum/editAlbum',array('id'=>$value['id'],'tabHash'=>'editVideo')).'">编辑</a> | 
							<a onclick="admin.delObject('.$value['id'].',\'Album\','.$value['is_del'].');" href="javascript:void(0)">删除</a>';;
		}
		return $list;
	}
	
	
	
	
	/**
	 * 专辑对应的提问
	 */
	public function askAlbum(){
		$this->_initClassroomListAdminTitle();
		$this->_initClassroomListAdminMenu();
		$this->pageTab[] = array('title'=>'专辑提问列表','tabHash'=>'askAlbum','url'=>U('classroom/AdmimAlbum/askAlbum'));
		$this->pageTitle['askAlbum'] = '专辑问题列表';
		if(!$_GET['id']) $this->error('请选择要查看的专辑');
		$field = 'id,uid,oid,qst_title,qst_comment_count';
		$this->pageKeyList = array('id','qst_title','uid','oid','qst_comment_count','DOACTION');
		$map['oid'] = intval($_GET['id']);
		$map['parent_id'] = 0; //父类id为0
		$map['type'] = 2;
		$data = D('ZyQuestion','classroom')->getListForId($map,20,$field);
		foreach ($data['data'] as $key => $vo){
			$data['data'][$key]['oid'] = D('ZyAlbum','classroom')->getAlbumTitleById($vo['oid']);
			$data['data'][$key]['uid'] = getUserName($vo['uid']);
			$data['data'][$key]['DOACTION'] = '<a href="'.U('classroom/AdminAlbum/answerAlbum',array('oid'=>$vo['oid'],'id'=>$vo['id'],'tabHash'=>'answerAlbum')).'">查看回答</a> | <a href="javascript:void();" onclick="admin.delContent('.$vo['id'].',\'Album\',\'ask\')">删除(连带删除回答及回答的评论)</a>';
		}
		$this->displayList($data);
	}
	
	/**
	 * 提问对应的回答
	 */
	public function answerAlbum(){
		if(!$_GET['id']) $this->error('请选择要查看的问题');
		$this->_initClassroomListAdminTitle();
		$this->_initClassroomListAdminMenu();
		$this->pageTab[] = array('title'=>'回答列表','tabHash'=>'answerAlbum','url'=>U('classroom/AdminAlbum/answerAlbum'));
		$this->pageTitle['answerAlbum'] = '回答列表';
		$field = 'id,uid,oid,qst_title,qst_comment_count';
		$this->pageKeyList = array('id','qst_title','uid','oid','qst_comment_count','DOACTION');
		$map['parent_id'] = intval($_GET['id']); //父类id为问题id
		$map['oid'] = intval($_GET['oid']);
		$map['type'] = 2;
		$data = D('ZyQuestion','classroom')->getListForId($map,20,$field);
		foreach ($data['data'] as $key => $vo){
			$data['data'][$key]['oid'] = D('ZyAlbum','classroom')->getAlbumTitleById($vo['oid']);
			$data['data'][$key]['uid'] = getUserName($vo['uid']);
			$data['data'][$key]['DOACTION'] = '<a href="'.U('classroom/AdminAlbum/commentAlbum',array('oid'=>$vo['oid'],'id'=>$vo['id'],'tabHash'=>'commentAlbum')).'">查看评论</a> | <a href="javascript:void();" onclick="admin.delContent('.$vo['id'].',\'Album\',\'ask\')">删除(连带删除评论)</a>';
		}
		$this->displayList($data);
	}
    //检索专辑列表
    public function seachVideo(){
        $key   = t($_POST['key']);
        $time  = time();
        $where = "is_del=0 AND is_activity=1 AND uctime>$time AND listingtime<$time AND video_title like  '%$key%'";
        $videolist = D('ZyVideo')->where($where)->select();
        $this->assign("list",$videolist);

        $html = $this->fetch("seachVideo");
        echo json_encode($html);exit;
    }
	/**
	 * 对回答的评论
	 */
	public function commentAlbum(){
		if(!$_GET['id']) $this->error('请选择要查看的回答');
		$this->_initClassroomListAdminTitle();
		$this->_initClassroomListAdminMenu();
		$field = 'id,uid,oid,qst_title';
		$this->pageTab[] = array('title'=>'评论列表','tabHash'=>'commentAlbum','url'=>U('classroom/AdminAlbum/commentAlbum'));
		$this->pageTitle['commentAlbum'] = '评论列表';
		$this->pageKeyList = array('id','qst_title','uid','oid','DOACTION');
		$map['parent_id'] = intval($_GET['id']); //父类id为问题id
		$map['oid'] = intval($_GET['oid']);
		$map['type'] = 2;
		$data = D('ZyQuestion','classroom')->getListForId($map,20,$field);
		foreach ($data['data'] as $key => $vo){
			$data['data'][$key]['oid'] = D('ZyAlbum','classroom')->getAlbumTitleById($vo['oid']);
			$data['data'][$key]['uid'] = getUserName($vo['uid']);
			$data['data'][$key]['DOACTION'] = '<a href="javascript:void();" onclick="admin.delContent('.$vo['id'].',\'Album\',\'ask\')">删除</a>';
		}
		$this->displayList($data);
	}
	
	/******************************************提问结束，笔记开始 ************/
	
	/**
	 * 专辑对应的笔记
	 */
	public function noteAlbum(){
		$this->_initClassroomListAdminTitle();
		$this->_initClassroomListAdminMenu();
		$this->pageTab[] = array('title'=>'专辑笔记列表','tabHash'=>'noteAlbum','url'=>U('classroom/AdminAlbum/noteAlbum'));
		$this->pageTitle['noteAlbum'] = '专辑笔记列表';
		if(!$_GET['id']) $this->error('请选择要查看的专辑');
		$field = 'id,uid,oid,note_title,note_comment_count';
		$this->pageKeyList = array('id','note_title','uid','oid','note_comment_count','DOACTION');
		$map['oid'] = intval($_GET['id']);
		$map['parent_id'] = 0; //父类id为0
		$map['type'] = 2;
		$data = D('ZyNote','classroom')->getListForId($map,20,$field);
		foreach ($data['data'] as $key => $vo){
			$data['data'][$key]['oid'] = D('ZyAlbum','classroom')->getAlbumTitleById($vo['oid']);
			$data['data'][$key]['uid'] = getUserName($vo['uid']);
			$data['data'][$key]['DOACTION'] = '<a href="'.U('classroom/AdminAlbum/noteCommentAlbum',array('oid'=>$vo['oid'],'id'=>$vo['id'],'tabHash'=>'noteCommentAlbum')).'">查看评论</a> | <a href="javascript:void();" onclick="admin.delContent('.$vo['id'].',\'Alubm\',\'note\')">删除(连带删除回答及回答的评论)</a>';
		}
		$this->displayList($data);
	}
	
	/**
	 * 笔记对应的评论
	 */
	public function noteCommentAlbum(){
		if(!$_GET['id']) $this->error('请选择要查看的评论');
		$this->_initClassroomListAdminTitle();
		$this->_initClassroomListAdminMenu();
		$this->pageTab[] = array('title'=>'评论列表','tabHash'=>'noteCommentAlbum','url'=>U('classroom/AdminAlbum/noteCommentAlbum'));
		$this->pageTitle['noteCommentAlbum'] = '评论列表';
		$field = 'id,uid,oid,note_title,note_comment_count';
		$this->pageKeyList = array('id','note_title','uid','oid','note_comment_count','DOACTION');
		$map['parent_id'] = intval($_GET['id']); //父类id为问题id
		$map['oid'] = intval($_GET['oid']);
		$map['type'] = 2;
		$data = D('ZyNote','classroom')->getListForId($map,20,$field);
		foreach ($data['data'] as $key => $vo){
			$data['data'][$key]['oid'] = D('ZyAlbum','classroom')->getAlbumTitleById($vo['oid']);
			$data['data'][$key]['uid'] = getUserName($vo['uid']);
			$data['data'][$key]['DOACTION'] = '<a href="'.U('classroom/AdminAlbum/noteReplayAlbum',array('oid'=>$vo['oid'],'id'=>$vo['id'],'tabHash'=>'noteReplayAlbum')).'">查看回复</a> | <a href="javascript:void();" onclick="admin.delContent('.$vo['id'].',\'Album\',\'note\')">删除(连带删除评论)</a>';
		}
		$this->displayList($data);
	}
	
	/**
	 * 对笔记评论的回复
	 */
	public function noteReplayAlbum(){
		if(!$_GET['id']) $this->error('请选择要查看的评论');
		$this->_initClassroomListAdminTitle();
		$this->_initClassroomListAdminMenu();
		$field = 'id,uid,oid,note_title';
		$this->pageTab[] = array('title'=>'回复列表','tabHash'=>'noteReplayAlbum','url'=>U('classroom/AdminAlbum/noteReplayAlbum'));
		$this->pageTitle['noteReplayAlbum'] = '回复列表';
		$this->pageKeyList = array('id','note_title','uid','oid','DOACTION');
		$map['parent_id'] = intval($_GET['id']); //父类id为问题id
		$map['oid'] = intval($_GET['oid']);
		$map['type'] = 2;
		$data = D('ZyNote','classroom')->getListForId($map,20,$field);
		foreach ($data['data'] as $key => $vo){
			$data['data'][$key]['oid'] = D('ZyAlbum','classroom')->getAlbumTitleById($vo['oid']);
			$data['data'][$key]['uid'] = getUserName($vo['uid']);
			$data['data'][$key]['DOACTION'] = '<a href="javascript:void();" onclick="admin.delContent('.$vo['id'].',\'Album\',\'note\')">删除</a>';
		}
		$this->displayList($data);
	}
	
	/*******************************************笔记操作结束,评论开始******************/
	/**
	 * 专辑对应的评价
	 */
	public function reviewAlbum(){
		$this->_initClassroomListAdminTitle();
		$this->_initClassroomListAdminMenu();
		$this->pageTab[] = array('title'=>'专辑评价列表','tabHash'=>'reviewAlbum','url'=>U('classroom/AdminAlbum/reviewAlbum'));
		$this->pageTitle['reviewAlbum'] = '专辑评价列表';
		if(!$_GET['id']) $this->error('请选择要查看的评价');
		$field = 'id,uid,oid,review_description,star,review_comment_count';
		$this->pageKeyList = array('id','review_description','uid','oid','star','review_comment_count','DOACTION');
		$map['oid'] = intval($_GET['id']);
		$map['parent_id'] = 0; //父类id为0
		$map['type'] = 2;
		$data = D('ZyReview','classroom')->getListForId($map,20,$field);
		foreach ($data['data'] as $key => $vo){
			$data['data'][$key]['oid'] = D('ZyAlbum','classroom')->getAlbumTitleById($vo['oid']);
			$data['data'][$key]['uid'] = getUserName($vo['uid']);
			$data['data'][$key]['DOACTION'] = '<a href="'.U('classroom/AdminAlbum/reviewCommentAlbum',array('oid'=>$vo['oid'],'id'=>$vo['id'],'tabHash'=>'reviewCommentAlbum')).'">查看评论</a> | <a href="javascript:void();" onclick="admin.delContent('.$vo['id'].',\'Album\',\'review\')">删除(连带删除回复)</a>';
			$data['data'][$key]['start'] = $vo['start']/ 20;
		}
		$this->displayList($data);
	}
	
	/**
	 * 评价对应的回复
	 */
	public function reviewCommentAlbum(){
		if(!$_GET['id']) $this->error('请选择要查看的评论');
		$this->_initClassroomListAdminTitle();
		$this->_initClassroomListAdminMenu();
		$this->pageTab[] = array('title'=>'评论列表','tabHash'=>'reviewCommentAlbum','url'=>U('classroom/AdminAlbum/reviewCommentAlbum'));
		$this->pageTitle['reviewCommentAlbum'] = '评论列表';
		$field = 'id,uid,oid,review_description';
		$this->pageKeyList = array('id','review_description','uid','oid','DOACTION');
		$map['parent_id'] = intval($_GET['id']); //父类id为问题id
		$map['oid'] = intval($_GET['oid']);
		$map['type'] = 2;
		$data = D('ZyReview','classroom')->getListForId($map,20,$field);
		foreach ($data['data'] as $key => $vo){
			$data['data'][$key]['oid'] = D('ZyAlbum','classroom')->getAlbumTitleById($vo['oid']);
			$data['data'][$key]['uid'] = getUserName($vo['uid']);
			$data['data'][$key]['DOACTION'] = '<a href="javascript:void();" onclick="admin.delContent('.$vo['id'].',\'Album\'\'review\')">删除</a>';
		}
		$this->displayList($data);
	}
	
	//****************************评论结束***********************//
	/**
	 * 删除提问、回答、评论
	 * 
	 */
	public function delProperty(){
		if(!$_POST['id']) exit(json_encode(array('status'=>0,'info'=>'错误的参数')));
		if(!$_POST['property'] || !in_array($_POST['property'], array('ask','note','review'))) exit(json_encode(array('status'=>0,'info'=>'参数错误')));
		if($_POST['property'] == 'ask'){
			$result = D('ZyQuestion','classroom')->doDeleteQuestion(intval($_POST['id']));
		}  else if($_POST['property'] == 'note'){
			$result = D('ZyNote','classroom')->doDeleteNote(intval($_POST['id']));
		} else if($_POST['property']){
			$result = D('ZyReview','classroom')->doDeleteReview(intval($_POST['id']));
		}
		if($result['status'] == 1){
			exit(json_encode(array('status'=>1,'info'=>'删除成功')));
		} else {
			exit(json_encode(array('status'=>0,'info'=>'删除失败，请稍后再试')));
		}
	}

	/**
	 * 专辑后台管理菜单
	 * @return void
	 */
	private function _initClassroomListAdminMenu(){
		$this->pageTab[] = array('title'=>'专辑列表','tabHash'=>'index','url'=>U('classroom/AdminAlbum/index'));
		$this->pageTab[] = array('title'=>'添加专辑','tabHash'=>'addAlbum','url'=>U('classroom/AdminAlbum/addAlbum'));
		$this->pageTab[] = array('title'=>'专辑回收站','tabHash'=>'recycle','url'=>U('classroom/AdminAlbum/recycle'));
	}

	/**
	 * 专辑后台的标题
	 */
	private function _initClassroomListAdminTitle(){
		$this->pageTitle['index'] = '专辑列表';
		$this->pageTitle['addAlbum'] = '添加专辑';
		$this->pageTitle['recycle'] = '专辑回收站';
	}

}