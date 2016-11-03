<?php
/**
 * 云课堂后台配置
 * 1.课程管理 - 目前支持1级分类
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
require_once './api/qiniu/rs.php';

class AdminVideoAction extends AdministratorAction
{
	/**
	 * 初始化，配置内容标题
	 * @return void
	 */
	public function _initialize(){
		parent::_initialize();
	}

	//通过审核课程列表
	public function index(){
		$this->_initClassroomListAdminMenu();
		$this->_initClassroomListAdminTitle();
		$this->pageKeyList = array('id','video_title','cover','user_title','activity','ctime','DOACTION');
		$this->pageButton[] =  array('title'=>'搜索课程','onclick'=>"admin.fold('search_form')");
		$this->searchKey = array('id','video_title','uid');
		$this->searchPostUrl = U('classroom/AdminVideo/index');
		$listData = $this->_getData(20,0,1);
		$this->displayList($listData);
	}

	//未通过审核课程列表
	public function unauditList(){
		$this->_initClassroomListAdminMenu();
		$this->_initClassroomListAdminTitle();
		$this->pageButton[] = array("title"=>"批量审核","onclick"=>"admin.crossVideos('','crossVideos','批量审核','课程')");
		$this->pageKeyList = array('id','video_title','user_title','activity','ctime','DOACTION');
		$listData = $this->_getData(20,0,0);
		$this->displayList($listData);
	}

	//前台投稿待审核课程列表
	public function forwordUnauditList(){
		$this->_initClassroomListAdminMenu();
		$this->_initClassroomListAdminTitle();
		$this->pageButton[] = array("title"=>"批量审核","onclick"=>"admin.crossVideos('','crossVideos','批量审核','课程')");
		$this->pageKeyList = array('id','video_title','user_title','activity','ctime','DOACTION');
		$listData = $this->_getData(20,0,0);
		$this->displayList($listData);
	}
	
	//课程回收站(被隐藏的课程)
	public function recycle(){
		$this->_initClassroomListAdminMenu();
		$this->_initClassroomListAdminTitle();
		$this->pageButton[] = array("title"=>"批量审核","onclick"=>"admin.crossVideos('','crossVideos','批量审核','课程')");
		$this->pageKeyList = array('id','video_title','user_title','activity','ctime','DOACTION');
		$listData = $this->_getData(20,1);
		$this->displayList($listData);
	}
	
	//编辑、添加课程
	public function addVideo(){
		$this->_initClassroomListAdminMenu();
		$this->_initClassroomListAdminTitle();
		//获取专辑
		$album_list = M('zy_album')->where('is_del=0')->getField('id,album_title');
		if($_GET['id']){
			$data = D('ZyVideo','classroom')->getVideoById(intval($_GET['id']));
			//获取课程所属专辑
			$album_ids = M('zy_album_video_link')->where('video_id='.$_GET['id'])->findAll();
			$this->assign('album_ids' , getSubByKey($album_ids , 'album_id'));
			$this->assign($data);
		}
		
		//如果上传到七牛服务器
		if(getAppConfig('upload_room','basic') == 1 ) {
			//生成上传凭证
			$bucket = getAppConfig('qiniu_Bucket','qiniuyun');
			Qiniu_SetKeys(getAppConfig('qiniu_AccessKey','qiniuyun'), getAppConfig('qiniu_SecretKey','qiniuyun'));
			$putPolicy = new Qiniu_RS_PutPolicy($bucket);
			$filename="eduline".rand(5,8).time();
	        $str = "{$bucket}:{$filename}";
	        $entryCode = Qiniu_Encode($str);
	        $putPolicy->PersistentOps= "avthumb/mp4/ab/160k/ar/44100/acodec/libfaac/r/30/vb/5400k/vcodec/libx264/s/1920x1080/autoscale/1/strpmeta/0|saveas/".$entryCode;
	    	$upToken=$putPolicy->Token(null);
	    	
	    	$this->assign("filename" , $filename);
	    	$this->assign("uptoken" , $upToken);
		}
		
		//查询讲师列表
		$trlist = $this->teacherList();
		//获取配置上传空间   0本地 1七牛 2阿里云 3又拍云
		$upload_room = getAppConfig('upload_room','basic');
		$this->assign('upload_room' , $upload_room);
		$this->assign('trlist' , $trlist);
		$this->assign('album_list' , $album_list);
		$this->display();
	}
	
	//添加课程操作
	public function doAddVideo(){
		$post = $_POST;
		if(empty($post['video_title'])) exit(json_encode(array('status'=>'0','info'=>"课程标题不能为空")));
		if(empty($post['video_intro'])) exit(json_encode(array('status'=>'0','info'=>"课程简介不能为空")));
		if(empty($post['v_price'])) exit(json_encode(array('status'=>'0','info'=>"课程价格不能为空")));
		if(empty($post['video_tag'])) exit(json_encode(array('status'=>'0','info'=>"课程标签不能为空")));
		if(empty($post['cover_ids'])) exit(json_encode(array('status'=>'0','info'=>"课程封面不能为空")));
		//如果上传到七牛服务器
		if(getAppConfig('upload_room','basic') == 1 ) {
			if(empty($post['videokey'])) exit(json_encode(array('status'=>'0','info'=>"请上传课程视频")));
		}
		if($post['limit_discount'] > 1 || $post['limit_discount'] < 0){
			exit(json_encode(array('status'=>'0','info'=>'请输入0-1的数字')));
		}
		$myAdminLevelhidden 		= getCsvInt(t($post['myAdminLevelhidden']),0,true,true,',');  //处理分类全路径
		$fullcategorypath 			= explode(',',$post['myAdminLevelhidden']);
		$category 					= array_pop($fullcategorypath);
		$category					= $category == '0' ? array_pop($fullcategorypath) : $category; //过滤路径最后为0的情况
		$data['fullcategorypath'] 	= $myAdminLevelhidden; //分类全路径
		$data['video_category']		 = $category == '0' ? array_pop($fullcategorypath) : $category;
		if(empty($category)) exit(json_encode(array('status'=>'0','info'=>'您还没选择课程分类')));

		
		$data['listingtime'] 		 = $post['listingtime'] ? strtotime($post['listingtime']) : 0; //上架时间
		$data['uctime'] 			 = $post['uctime'] ? strtotime($post['uctime']) : 0; //下架时间
		if($data['endtime'] < $data['starttime'] || $data['uctime'] < $data['listingtime']){
			exit(json_encode(array('status'=>'0','info'=>'结束时间不能小于开始时间')));
		}
		//格式化七牛数据
		$videokey=t($_POST['videokey']);
		//获取上传空间 0本地 1七牛 2阿里云 3又拍云
		if(getAppConfig('upload_room','basic') == 0 ) {
			$video_address = getAttachUrlByAttachId( $post['attach'][0] );
		} else {
			$video_address="http://".getAppConfig('qiniu_Domain','qiniuyun')."/".$videokey;
		}
		//echo "---->video_address = ".$video_address;
		$data['qiniu_key']=$videokey;
		
		$data['is_activity'] 	 	 = 1;
		$data['video_title'] 		 = t($post['video_title']); //课程名称
		$data['video_intro'] 		 = t($post['video_intro']); //课程简介
		$data['v_price'] 			 = $post['v_price']; //市场价格
		$data['discount'] 	 		 = isset($post['discount']) ? $post['discount'] : 1; //会员价格(前台用户上传的课程才有用)；
		$data['is_tlimit']           = isset($post['is_tlimit']) ? intval($post['is_tlimit']) : 0; //限时打折
		$data['starttime'] 			 = $post['starttime'] ? strtotime($post['starttime']) : 0; //限时开始时间
		$data['endtime'] 			 = $post['endtime'] ? strtotime($post['endtime']) : 0; //限时结束时间
		$data['limit_discount'] 	 = isset($post['is_tlimit']) && ($post['limit_discount'] <= 1 && $post['limit_discount'] >= 0) ? $post['limit_discount'] : 1; //限时折扣
		$data['teacher_id']          = intval($_POST['trid']);//获取讲师
		if($video_address!="")$data['video_address']       = $video_address;//正确的视频地址
		$data['cover'] 			 	 = intval($post['cover_ids']); //封面
		$data['videofile_ids'] 		 = isset($post['videofile_ids']) ? intval($post['videofile_ids']) : 0; //课件id
		$data['is_best']      		 = isset($post['is_best']) ? intval($post['best_recommend']) : 0; //编辑精选
		$data['t_price'] 			 = $data['v_price'] * $data['limit_discount'];
		$data['is_part_album'] 		 = intval($_POST['is_part_album']);
		$video_tag					 = t($post['video_tag']);//课程标签
		$album_ids 					 = $post['album_id'];//课程所属专辑
		if($post['id']){
			$data['utime'] = time();
			$result = M('zy_video')->where('id = '.$post['id'])->data($data)->save();
			//echo "---->result = ".$result;
			//print_r($data);
		} else {
			$data['ctime'] = time();
			$data['utime'] = time();
			$data['uid']   = $this->mid;
			$result = M('zy_video')->data($data)->add();
		}
		if($result){
			unset($data);
			if($post['id']){
				//添加标签
				model('Tag')->setAppName('classroom')->setAppTable('zy_video')->deleteSourceTag($post['id']);
				$tag_reslut = model('Tag')->setAppName('classroom')->setAppTable('zy_video')->addAppTags($post['id'],$video_tag);
				//删除旧专辑
				M('zy_album_video_link')->where('video_id='.$post['id'])->delete();
				//添加专辑课程关联
				$sql = 'insert into '.C('DB_PREFIX').'zy_album_video_link (`album_id`,`video_id`) values';
				foreach($album_ids as $val){
					$sql .= '('.$val .','.$post['id'] .'),';
				}
				M()->query( trim($sql , ','));
			} else {
				$tag_reslut = model('Tag')->setAppName('classroom')->setAppTable('zy_video')->addAppTags($result,$video_tag);
				//添加专辑课程关联
				$sql = 'insert into '.C('DB_PREFIX').'zy_album_video_link (`album_id`,`video_id`) values';
				foreach($album_ids as $val){
					$sql .= '('.$val .','.$result .'),';
				}
				M()->query( trim($sql , ','));
			}
			$data['str_tag'] = implode(',' ,getSubByKey($tag_reslut,'name'));
			$data['tag_id']  = ','.implode(',',getSubByKey($tag_reslut,'tag_id')).',';
			$map['id'] = $post['id'] ? $post['id'] : $result;
			M('zy_video')->where($map)->data($data)->save();
			if($post['id']){
				exit(json_encode(array('status'=>'1','info'=>'编辑成功')));
			} else {
				exit(json_encode(array('status'=>'1','info'=>'添加成功')));
			}
		} else {
			exit(json_encode(array('status'=>'0','info'=>'系统繁忙，请稍后再试')));
		}
	}

	//批量审核课程
	public function crossVideos(){
		$map['id'] = is_array($_POST['id']) ? array('IN',$_POST['id']) : intval($_POST['id']);
		$table = M('zy_video');
		$data['is_activity']  = 1;
		$result = $table->where($map)->data($data)->save();
		if($result){
			$this->ajaxReturn('审核成功');
		} else {
			$this->ajaxReturn('系统繁忙，稍后再试');
		}
	}

	//删除(隐藏)课程
	public function delVideo(){
		if(!$_POST['id']){
			exit(json_encode(array('status'=>0,'info'=>'请选择要删除的对象!')));
		}
		$map['id'] = intval($_POST['id']);
		$data['is_del'] = $_POST['is_del'] ? 0 : 1; //传入参数并设置相反的状态
		if(M('zy_video')->where($map)->data($data)->save()){
			exit(json_encode(array('status'=>1,'info'=>'操作成功')));
		} else {
			exit(json_encode(array('status'=>1,'info'=>'操作失败')));
		}
	}
	
	/**
	 * 删除视频(删除存储空间的视频)
	 */
	public function deletevideo(){
		$videokey=t($_POST['videokey']);//获取视频key
	
		$bucket =  getAppConfig('qiniu_Bucket','qiniuyun');
		Qiniu_SetKeys(getAppConfig('qiniu_AccessKey','qiniuyun'),  getAppConfig('qiniu_SecretKey','qiniuyun'));
		$client = new Qiniu_MacHttpClient(null);
		$err = Qiniu_RS_Delete($client, $bucket, $videokey);
	
		if ($err !== null) {
			exit(json_encode(array('status'=>'0','info'=>"删除失败或视频已不存在！")));
		}else{
			$data['qiniu_key']="";
			D('ZyVideo')->where(array("qiniu_key"=>$videokey))->save($data);
			exit(json_encode(array('status'=>'1','info'=>'删除成功，请添加新视频！')));
		}
	}
	
	//讲师列表
	private function teacherList(){
		$map = array(
				'is_del'=>0
		);
		$teacherlist=D('ZyTeacher')->where($map)->order("ctime DESC")->select();
		return $teacherlist;
	}
	
	//获取课程数据
	private function _getData($limit = 20, $is_del = 0, $is_activity = 1){
		if(isset($_POST)){
			$_POST['id'] && $map['id'] = intval($_POST['id']);
			$_POST['video_title'] && $map['video_title'] = array('like', '%'.t($_POST['video_title']).'%');
			$_POST['uid'] && $map['uid'] = intval($_POST['uid']);
		}
		$map['is_del'] = $is_del; //搜索非隐藏内容
		if(isset($is_activity)){
			$map['is_activity'] = $is_activity;
		}
		$list = M('zy_video')->where($map)->order('ctime desc,id desc')->findPage($limit);
		foreach ($list['data'] as &$value){
			$value['video_title'] = msubstr($value['video_title'],0,20);
			$value['user_title']  = getUserSpace($value['uid']);
			$value['activity']    = $value['is_activity'] == '1' ? '<span style="color:green">已审核</span>' : '<span style="color:red">未审核</span>';
			$value['ctime'] = friendlyDate($value['ctime']);
			$value['cover'] = "<img src=".getCover($value['cover'] , 60 ,60)." width='60px' height='60px'>";
			$value['DOACTION'] = '<a target="_blank" href=" '.U('classroom/Album/watch',array('aid'=>$value['id'],'type'=>1)).' ">查看</a> | ';
			$value['DOACTION'] .= '<a href="'.U('classroom/AdminVideo/askVideo',array('tabHash'=>'askVideo','id'=>$value['id'])).'">提问</a> | ';
			$value['DOACTION'] .= '<a href="'.U('classroom/AdminVideo/noteVideo',array('tabHash'=>'noteVideo','id'=>$value['id'])).'">笔记</a> | ';
			$value['DOACTION'] .= '<a href="'.U('classroom/AdminVideo/reviewVideo',array('tabHash'=>'reviewVideo','id'=>$value['id'])).'">评价</a> | ';
			if( $value['is_del'] == 0 && $value['is_activity'] == '0'){
				$value['DOACTION'] .= '<a href="javascript:void();" onclick="admin.crossVideo('.$value['id'].',true)">通过审核</a> | ';
			}
			$value['DOACTION'] .= $value['is_del'] ? '<a href="'.U('classroom/AdminVideo/addVideo',array('id'=>$value['id'],'tabHash'=>'editVideo')).'">编辑</a> | 
					<a onclick="admin.delObject('.$value['id'].',\'Video\','.$value['is_del'].');" href="javascript:void(0)">恢复</a>' : '<a href="'.U('classroom/AdminVideo/addVideo',array('id'=>$value['id'],'tabHash'=>'editVideo')).'">编辑</a> | 
							<a onclick="admin.delObject('.$value['id'].',\'Video\','.$value['is_del'].');" href="javascript:void(0)">删除(隐藏)</a> ';
		}
		return $list;
	}
	
	
	
	
	
	/**
	 * 课程对应的提问
	 */
	public function askVideo(){
		$this->_initClassroomListAdminTitle();
		$this->_initClassroomListAdminMenu();
		$this->pageTab[] = array('title'=>'课程提问列表','tabHash'=>'askVideo','url'=>U('classroom/AdminVideo/askVideo'));
		$this->pageTitle['askVideo'] = '课程问题列表';
		if(!$_GET['id']) $this->error('请选择要查看的课程');
		$field = 'id,uid,oid,qst_title,qst_comment_count';
		$this->pageKeyList = array('id','qst_title','uid','oid','qst_comment_count','DOACTION');
		$map['oid'] = intval($_GET['id']);
		$map['parent_id'] = 0; //父类id为0
		$map['type'] = 1;
		$data = D('ZyQuestion','classroom')->getListForId($map,20,$field);
		foreach ($data['data'] as $key => $vo){
			$data['data'][$key]['oid'] = D('ZyVideo','classroom')->getVideoTitleById($vo['oid']);
			$data['data'][$key]['uid'] = getUserName($vo['uid']);
			$data['data'][$key]['DOACTION'] = '<a href="'.U('classroom/AdminVideo/answerVideo',array('oid'=>$vo['oid'],'id'=>$vo['id'],'tabHash'=>'answerVideo')).'">查看回答</a> | <a href="javascript:void();" onclick="admin.delContent('.$vo['id'].',\'Video\',\'ask\')">删除(连带删除回答及回答的评论)</a>';
		}
		$this->displayList($data);
	}

	/**
	 * 提问对应的回答
	 */
	public function answerVideo(){
		if(!$_GET['id']) $this->error('请选择要查看的问题');
		$this->_initClassroomListAdminTitle();
		$this->_initClassroomListAdminMenu();
		$this->pageTab[] = array('title'=>'回答列表','tabHash'=>'answerVideo','url'=>U('classroom/AdminVideo/answerVideo'));
		$this->pageTitle['answerVideo'] = '回答列表';
		$field = 'id,uid,oid,qst_title,qst_comment_count';
		$this->pageKeyList = array('id','qst_title','uid','oid','qst_comment_count','DOACTION');
		$map['parent_id'] = intval($_GET['id']); //父类id为问题id
		$map['oid'] = intval($_GET['oid']);
		$map['type'] = 1;
		$data = D('ZyQuestion','classroom')->getListForId($map,20,$field);
		foreach ($data['data'] as $key => $vo){
			$data['data'][$key]['oid'] = D('ZyVideo','classroom')->getVideoTitleById($vo['oid']);
			$data['data'][$key]['uid'] = getUserName($vo['uid']);
			$data['data'][$key]['DOACTION'] = '<a href="'.U('classroom/AdminVideo/commentVideo',array('oid'=>$vo['oid'],'id'=>$vo['id'],'tabHash'=>'commentVideo')).'">查看评论</a> | <a href="javascript:void();" onclick="admin.delContent('.$vo['id'].',\'Video\',\'ask\')">删除(连带删除评论)</a>';
		}
		$this->displayList($data);
	}

	/**
	 * 对回答的评论
	 */
	public function commentVideo(){
		if(!$_GET['id']) $this->error('请选择要查看的回答');
		$this->_initClassroomListAdminTitle();
		$this->_initClassroomListAdminMenu();
		$field = 'id,uid,oid,qst_title';
		$this->pageTab[] = array('title'=>'评论列表','tabHash'=>'commentVideo','url'=>U('classroom/AdminVideo/commentVideo'));
		$this->pageTitle['commentVideo'] = '评论列表';
		$this->pageKeyList = array('id','qst_title','uid','oid','DOACTION');
		$map['parent_id'] = intval($_GET['id']); //父类id为问题id
		$map['oid'] = intval($_GET['oid']);
		$map['type'] = 1;
		$data = D('ZyQuestion','classroom')->getListForId($map,20,$field);
		foreach ($data['data'] as $key => $vo){
			$data['data'][$key]['oid'] = D('ZyVideo','classroom')->getVideoTitleById($vo['oid']);
			$data['data'][$key]['uid'] = getUserName($vo['uid']);
			$data['data'][$key]['DOACTION'] = '<a href="javascript:void();" onclick="admin.delContent('.$vo['id'].',\'Video\',\'ask\')">删除</a>';
		}
		$this->displayList($data);
	}

	/******************************************提问结束，笔记开始 ************/

	/**
	 * 课程对应的笔记
	 */
	public function noteVideo(){
		$this->_initClassroomListAdminTitle();
		$this->_initClassroomListAdminMenu();
		$this->pageTab[] = array('title'=>'课程笔记列表','tabHash'=>'noteVideo','url'=>U('classroom/AdminVideo/askVideo'));
		$this->pageTitle['askVideo'] = '课程笔记列表';
		if(!$_GET['id']) $this->error('请选择要查看的课程');
		$field = 'id,uid,oid,note_title,note_comment_count';
		$this->pageKeyList = array('id','note_title','uid','oid','note_comment_count','DOACTION');
		$map['oid'] = intval($_GET['id']);
		$map['parent_id'] = 0; //父类id为0
		$map['type'] = 1;
		$data = D('ZyNote','classroom')->getListForId($map,20,$field);
		foreach ($data['data'] as $key => $vo){
			$data['data'][$key]['oid'] = D('ZyVideo','classroom')->getVideoTitleById($vo['oid']);
			$data['data'][$key]['uid'] = getUserName($vo['uid']);
			$data['data'][$key]['DOACTION'] = '<a href="'.U('classroom/AdminVideo/noteCommentVideo',array('oid'=>$vo['oid'],'id'=>$vo['id'],'tabHash'=>'noteCommentVideo')).'">查看评论</a> | <a href="javascript:void();" onclick="admin.delContent('.$vo['id'].',\'Video\',\'note\')">删除(连带删除回答及回答的评论)</a>';
		}
		$this->displayList($data);
	}

	/**
	 * 笔记对应的评论
	 */
	public function noteCommentVideo(){
		if(!$_GET['id']) $this->error('请选择要查看的评论');
		$this->_initClassroomListAdminTitle();
		$this->_initClassroomListAdminMenu();
		$this->pageTab[] = array('title'=>'评论列表','tabHash'=>'noteCommentVideo','url'=>U('classroom/AdminVideo/answerVideo'));
		$this->pageTitle['answerVideo'] = '评论列表';
		$field = 'id,uid,oid,note_title,note_comment_count';
		$this->pageKeyList = array('id','note_title','uid','oid','note_comment_count','DOACTION');
		$map['parent_id'] = intval($_GET['id']); //父类id为问题id
		$map['oid'] = intval($_GET['oid']);
		$map['type'] = 1;
		$data = D('ZyNote','classroom')->getListForId($map,20,$field);
		foreach ($data['data'] as $key => $vo){
			$data['data'][$key]['oid'] = D('ZyVideo','classroom')->getVideoTitleById($vo['oid']);
			$data['data'][$key]['uid'] = getUserName($vo['uid']);
			$data['data'][$key]['DOACTION'] = '<a href="'.U('classroom/AdminVideo/noteReplayVideo',array('oid'=>$vo['oid'],'id'=>$vo['id'],'tabHash'=>'noteReplayVideo')).'">查看回复</a> | <a href="javascript:void();" onclick="admin.delContent('.$vo['id'].',\'Video\',\'note\')">删除(连带删除评论)</a>';
		}
		$this->displayList($data);
	}

	/**
	 * 对笔记评论的回复
	 */
	public function noteReplayVideo(){
		if(!$_GET['id']) $this->error('请选择要查看的评论');
		$this->_initClassroomListAdminTitle();
		$this->_initClassroomListAdminMenu();
		$field = 'id,uid,oid,note_title';
		$this->pageTab[] = array('title'=>'回复列表','tabHash'=>'noteReplayVideo','url'=>U('classroom/AdminVideo/commentVideo'));
		$this->pageTitle['commentVideo'] = '回复列表';
		$this->pageKeyList = array('id','note_title','uid','oid','DOACTION');
		$map['parent_id'] = intval($_GET['id']); //父类id为问题id
		$map['oid'] = intval($_GET['oid']);
		$map['type'] = 1;
		$data = D('ZyNote','classroom')->getListForId($map,20,$field);
		foreach ($data['data'] as $key => $vo){
			$data['data'][$key]['oid'] = D('ZyVideo','classroom')->getVideoTitleById($vo['oid']);
			$data['data'][$key]['uid'] = getUserName($vo['uid']);
			$data['data'][$key]['DOACTION'] = '<a href="javascript:void();" onclick="admin.delContent('.$vo['id'].',\'Video\',\'note\')">删除</a>';
		}
		$this->displayList($data);
	}

	/*******************************************笔记操作结束,评论开始******************/
	/**
	 * 课程对应的评价
	 */
	public function reviewVideo(){
		$this->_initClassroomListAdminTitle();
		$this->_initClassroomListAdminMenu();
		$this->pageTab[] = array('title'=>'课程评价列表','tabHash'=>'reviewVideo','url'=>U('classroom/AdminVideo/reviewVideo'));
		$this->pageTitle['reviewVideo'] = '课程评价列表';
		if(!$_GET['id']) $this->error('请选择要查看的评价');
		$field = 'id,uid,oid,review_description,star,review_comment_count';
		$this->pageKeyList = array('id','review_description','uid','oid','star','review_comment_count','DOACTION');
		$map['oid'] = intval($_GET['id']);
		$map['parent_id'] = 0; //父类id为0
		$map['type'] = 1;
		$data = D('ZyReview','classroom')->getListForId($map,20,$field);
		foreach ($data['data'] as $key => $vo){
			$data['data'][$key]['oid'] = D('ZyVideo','classroom')->getVideoTitleById($vo['oid']);
			$data['data'][$key]['uid'] = getUserName($vo['uid']);
			$data['data'][$key]['DOACTION'] = '<a href="'.U('classroom/AdminVideo/reviewCommentVideo',array('oid'=>$vo['oid'],'id'=>$vo['id'],'tabHash'=>'reviewCommentVideo')).'">查看评论</a> | <a href="javascript:void();" onclick="admin.delContent('.$vo['id'].',\'Video\',\'review\')">删除(连带删除回复)</a>';
			$data['data'][$key]['start'] = $vo['start']/ 20;
		}
		$this->displayList($data);
	}

	/**
	 * 评价对应的回复
	 */
	public function reviewCommentVideo(){
		if(!$_GET['id']) $this->error('请选择要查看的评论');
		$this->_initClassroomListAdminTitle();
		$this->_initClassroomListAdminMenu();
		$this->pageTab[] = array('title'=>'评论列表','tabHash'=>'reviewCommentVideo','url'=>U('classroom/AdminVideo/reviewCommentVideo'));
		$this->pageTitle['reviewCommentVideo'] = '评论列表';
		$field = 'id,uid,oid,review_description';
		$this->pageKeyList = array('id','review_description','uid','oid','DOACTION');
		$map['parent_id'] = intval($_GET['id']); //父类id为问题id
		$map['oid'] = intval($_GET['oid']);
		$map['type'] = 1;
		$data = D('ZyReview','classroom')->getListForId($map,20,$field);
		foreach ($data['data'] as $key => $vo){
			$data['data'][$key]['oid'] = D('ZyVideo','classroom')->getVideoTitleById($vo['oid']);
			$data['data'][$key]['uid'] = getUserName($vo['uid']);
			$data['data'][$key]['DOACTION'] = '<a href="javascript:void();" onclick="admin.delContent('.$vo['id'].',\'Video\'\'review\')">删除</a>';
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
	 * 审核课程
	 */
	public function crossVideo(){
		if(!$_POST['id']) exit(json_encode(array('status'=>0,'info'=>'错误的参数')));
		$map['id'] = intval($_POST['id']);
		$data['is_activity'] = $_POST['cross'] == 'true' ? 1 : 0; //0为未通过状态
		if(M('zy_video')->where($map)->data($data)->save()){
			exit(json_encode(array('status'=>1,'info'=>'操作成功')));
		} else {
			exit(json_encode(array('status'=>0,'info'=>'操作失败')));
		}
	}


	/**
	 * 课程后台管理菜单
	 * @return void
	 */
	private function _initClassroomListAdminMenu(){
		$this->pageTab[] = array('title'=>'通过审核课程列表','tabHash'=>'index','url'=>U('classroom/AdminVideo/index'));
		$this->pageTab[] = array('title'=>'未通过审核课程列表','tabHash'=>'unauditList','url'=>U('classroom/AdminVideo/unauditList'));
		//$this->pageTab[] = array('title'=>'前台投稿待审课程列表','tabHash'=>'forwordUnauditList','url'=>U('classroom/AdminVideo/forwordUnauditList'));
		$this->pageTab[] = array('title'=>'课程回收站','tabHash'=>'recycle','url'=>U('classroom/AdminVideo/recycle'));
		$this->pageTab[] = array('title'=>'添加课程','tabHash'=>'addVideo','url'=>U('classroom/AdminVideo/addVideo'));
		
	}

	/**
	 * 课程后台的标题
	 */
	private function _initClassroomListAdminTitle(){
		$this->pageTitle['index'] = '通过审核课程';
		$this->pageTitle['forwordUnauditList'] = '前台投稿待审课程列表';
		$this->pageTitle['unauditList'] = '未通过审核课程';
		$this->pageTitle['recycle'] 	= '课程回收站';
		$this->pageTitle['addVideo']    = '添加课程';
	}

}