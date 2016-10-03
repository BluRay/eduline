<?php
tsload(APPS_PATH.'/classroom/Lib/Action/UserAction.class.php');
class HomeAction extends UserAction{
    /**
    * 初始化
    * @return void
    */
    public function _initialize() {
        parent::_initialize();
    }
    /**
    * 内容管理--问答
    * @return void
    */
    public function wenda(){
        $this->display();
    }
    /**
    * 会员中心问题--列表处理
    * @return void
    */

	public function wenti(){
		$this->display();
	}
	/**
    * 会员中心笔记--列表处理
    * @return void
    */

	public function note(){
		$this->display();
	}
	/**
    * 会员中心笔记--列表处理
    * @return void
    */

	public function review(){
		$this->display();
	}
	
	/**
    * 会员中心专辑--列表处理
    * @return void
    */

	public function album(){
		$this->display();
	}
	
	/**
    * 会员中心课程--列表处理
    * @return void
    */

	public function video(){
		$this->assign('mid',$this->mid);
		$this->display();
	}
	/**
    * 会员中心课程--约课处理
    * @return void
    */
	public function course(){
		$this->assign('mid',$this->mid);
		$this->display();
	}
	/**
    * 会员中心课程--约课(教师)
    * @return void
    */
	public function teacher_course(){
		$this->assign('mid',$this->mid);
		$this->display();
	}
    /**
    * 问答中心--我的问题
    * @return void
    */
    public function getWenda(){
        $map = array();
        $data = array();
        $mid = $this->mid;
        $count = '';
        $page = intval($_POST['page']);
        $pageSize = 9;
        $startPage = empty($page) ? 0 : $page*$pageSize;
        $map['is_del'] = array('EQ',0);
        $map['uid'] = array('EQ',$mid);
        $list = M('zy_wenda')->where($map)->limit("$startPage,$pageSize")->order('ctime DESC')->select();
        $this->assign('list',$list);
        if($this->isAjax()){
            $data['html'] = $this->fetch('./wenda_list','回答中心回调函数','utf-8');
            $page == 0 && $data['count'] = M('zy_wenda')->where($map)->count();
            $data['page'] = $page+1;
            $this->ajaxReturn($data,'我的问题',1);
        }
	}
    /**
    * 问答中心--我的问题
    * @return void
    */
    public function getAnswer(){
        $map = array();
        $data = array();
        $mid = $this->mid;
        $count = '';
        $page = intval($_POST['page']);
        $pageSize = 9;
        $startPage = empty($page) ? 0 : $page*$pageSize;
        $map['d.is_del'] = array('EQ',0);
        $map['d.uid'] = array('EQ',$mid);
        $list = M("zy_wenda_comment d")->join("`".C('DB_PREFIX')."zy_wenda` w ON w.id = d.wid")->field('w.id,w.uid,w.wd_description,w.wd_comment_count,w.wd_help_count,d.uid as duid,d.description,d.ctime')->where($map)->limit("$startPage,$pageSize")->select();
        $this->assign('list',$list);
        $this->assign('wenda',"meque");
        if($this->isAjax()){
            $data['html'] = $this->fetch('./wenda_list','回答中心回调函数','utf-8');
            $page == 0 && $data['count'] = M('zy_wenda_comment')->where("`is_del`=0 AND `uid`=$mid")->count();
            $data['page'] = $page+1;
            $this->ajaxReturn($data,'',1);
        }
    }
	/**
    * 会员中心问题--异步处理
    * @return void
    */
	public function getwentilist(){
		$limit =9;
        $type  = t($_POST['type']);
		$zyQuestionMod   = D('ZyQuestion');
		$zyCollectionMod = D('ZyCollection');
		
		if($type == 'me'){
			$map['uid']       = intval($this->mid);
			$map['parent_id'] = 0;
			$order = 'ctime DESC';
			
			$data = $zyQuestionMod->where($map)->order($order)->findPage($limit);
			foreach($data['data'] as $key=>&$value){
				$value['qst_title'] = msubstr($value['qst_title'],0,15);
				$value['qst_description'] = msubstr($value['qst_description'],0,153);
				$value['strtime'] = friendlyDate($value['ctime']);
				$value['qcount']  = $zyQuestionMod->where(array('parent_id'=>array('eq',$value['id'])))->count();
			}
		}else if ($type == 'question'){
			$data = $zyQuestionMod->myAnswer($limit,intval($this->mid));
			
			foreach($data['data'] as $key=>&$value){
				$value['wenti']['qst_title'] = msubstr($value['wenti']['qst_title'],0,15);
				$value['wenti']['qst_description'] = msubstr($value['wenti']['qst_description'],0,149);
				$value['wenti']['strtime'] = friendlyDate($value['wenti']['ctime']);
				$value['wenti']['qcount']  = $zyQuestionMod->where(array('parent_id'=>array('eq',$value['wenti']['id'])))->count();
				
				$value['qst_title'] = msubstr($value['qst_title'],0,15);
				$value['qst_description'] = msubstr($value['qst_description'],0,31);
				$value['qcount']  = $zyQuestionMod->where(array('parent_id'=>array('eq',$value['id'])))->count();
			}
		}else if ($type == 'collect'){
			$data = $zyCollectionMod->myCollection('zy_question',$limit,intval($this->mid));
			foreach($data['data'] as $key=>&$value){
				$value['qst_title'] = msubstr($value['qst_title'],0,15);
				$value['qst_description'] = msubstr($value['qst_description'],0,153);
				$value['strtime'] = friendlyDate($value['ctime']);
				$value['qcount']  = $zyQuestionMod->where(array('parent_id'=>array('eq',$value['id'])))->count();
			}
		}
        $this->assign("data",$data['data']);
        $data['data'] = $this->fetch('wenti_list');
		echo json_encode($data);exit;
	}
	/**
    * 会员中心笔记--异步处理
    * @return void
    */
	public function getnotelist(){
		$limit = 9;
		$type  = t($_POST['type']);
		
		$zyNoteMod       = D('ZyNote');
		$zyCollectionMod = D('ZyCollection');
		
		if($type == 'me'){
			$map['uid']       = intval($this->mid);
			$map['parent_id'] = 0;
			$order = 'ctime DESC';
			
			$data = $zyNoteMod->where($map)->order($order)->findPage($limit);
			foreach($data['data'] as $key=>&$value){
				$value['note_title'] = msubstr($value['note_title'],0,15);
				$value['note_description'] = msubstr($value['note_description'],0,150);
				$value['strtime'] = friendlyDate($value['ctime']);
				$value['qcount']  = $zyNoteMod->where(array('parent_id'=>array('eq',$value['id'])))->count();
			}
		}else if ($type == 'collect'){
			$data = $zyCollectionMod->myCollection('zy_note',$limit,intval($this->mid));
			
			foreach($data['data'] as $key=>&$value){
				$value['note_title'] = msubstr($value['note_title'],0,15);
				$value['note_description'] = msubstr($value['note_description'],0,150);
				$value['strtime'] = friendlyDate($value['ctime']);
				$value['qcount']  = $zyNoteMod->where(array('parent_id'=>array('eq',$value['id'])))->count();
			}
		}
        $this->assign("data",$data['data']);
        $data['data'] = $this->fetch('note_list');
		echo json_encode($data);exit;
	}
	/**
    * 会员中心点评--异步处理
    * @return void
    */
	public function getreviewlist(){
		$limit =9;
		$type  = t($_POST['type']);
		
		$zyReviewMod       = D('ZyReview');
		
		if($type == 'me'){
			$map['uid']       = intval($this->mid);
			$map['parent_id'] = 0;
			$order = 'ctime DESC';
			
			$data = $zyReviewMod->where($map)->order($order)->findPage($limit);
			foreach($data['data'] as $key=>&$value){
				$value['star'] = $value['star']/20;
				$value['review_description'] = msubstr($value['review_description'],0,150);
				$value['strtime'] = friendlyDate($value['ctime']);
				$value['qcount']  = $zyReviewMod->where(array('parent_id'=>array('eq',$value['id'])))->count();
				
				$_map['id'] = array('eq',$value['oid']);
				//找到评论的内容
				if($value['type'] == 1){
					$value['title'] = M('ZyVideo')->where($_map)->getField('`video_title` as `title`');
					$value['_src']  = U('classroom/Video/view','id='.$value['oid']);
				}else{
					$value['title'] = M('ZyAlbum')->where($_map)->getField('`album_title` as `title`');	
					$value['_src']  = U('classroom/Album/view','id='.$value['oid']);
				}
				$value['title'] = msubstr($value['title'],0,18);
			}
		}
        $this->assign("data",$data['data']);
        $data['data'] = $this->fetch('review_list');
		echo json_encode($data);exit;
	}
	/**
    * 异步加载我购买的课程
    * @return void
    */
	public function getbuyvideoslist(){
		$limit      = intval($_POST['limit']);
		$uid        = intval($this->mid);
		$limit      = 9;
		//拼接两个表名
		$vtablename = C('DB_PREFIX').'zy_video';
		$otablename = C('DB_PREFIX').'zy_order';
		//拼接字段
		$fields     = ''; 
		$fields .= "{$otablename}.`learn_status`,{$otablename}.`uid`,{$otablename}.`id` as `oid`,";
		$fields .= "{$vtablename}.`video_title`,{$vtablename}.`video_category`,{$vtablename}.`id`,{$vtablename}.`video_intro`,";
		$fields .= "{$vtablename}.`cover`,{$vtablename}.video_order_count";
		//不是通过专辑购买的
	
		$where     = "{$otablename}.`is_del`=0 and {$otablename}.`uid`={$uid}";
		$data = M('ZyOrder')->join("{$vtablename} on {$otablename}.`video_id`={$vtablename}.`id`")->where($where)->field($fields)->findPage($limit);
		$vms = D('ZyVideoMerge')->getList(intval($this->mid), session_id());
        $this->assign('vms', getSubByKey($vms, 'video_id'));
	
		//把数据传入模板
		$this->assign('data',$data['data']);

		//取得数据
		$data['data'] = $this->fetch('_video_my_buy');
		echo json_encode($data);exit;
	}
	/**
    * 异步加载我收藏的课程
    * @return void
    */
	public function getcollectvideolist(){
		//获取购物车参数
        $vms = D('ZyVideoMerge')->getList($this->mid, session_id());
        $this->assign('vms', getSubByKey($vms, 'video_id'));
        //获取已购买课程id
        $buyVideos = D('zyOrder')->where("`uid`=".$this->mid." AND `is_del`=0")->field('video_id')->select();
            foreach($buyVideos as $key=>$val){
                $buyVideos[$key] = $val['video_id'];
            }
        $this->assign('buyVideos',$buyVideos);

		$limit =9;

		$uid        = intval($this->mid);
		//拼接两个表名
		$vtablename = C('DB_PREFIX').'zy_video';
		$ctablename = C('DB_PREFIX').'zy_collection';
		
		$fields     = '';
		$fields .= "{$ctablename}.`uid`,{$ctablename}.`collection_id` as `cid`,";
		
        $fields .="{$vtablename}.*";
		//拼接条件
		$where      = "{$ctablename}.`source_table_name`='zy_video' and {$ctablename}.`uid`={$uid}";
		//取数据
		$data = M('ZyCollection')->join("{$vtablename} on {$ctablename}.`source_id`={$vtablename}.`id`")->where($where)->field($fields)->findPage($limit);
        //循环计算课程价格
        foreach($data['data'] as &$val){
            $val['money']=getPrice($val,$this->mid);
        }
  
		$vms = D('ZyVideoMerge')->getList(intval($this->mid), session_id());
        $this->assign('vms', getSubByKey($vms, 'video_id'));
		//把数据传入模板
		$this->assign('data',$data['data']);
  
		
		//取得数据
		$data['data'] = $this->fetch('_video_my_collect');
		echo json_encode($data);exit;
	}
	/**
    * 异步加载我购买的专辑
    * @return void
    */
	public function getbuyalbumslist(){
		$limit      = 9;
		$uid        = intval($this->mid);
		//拼接两个表名
		$atablename = C('DB_PREFIX').'zy_album';
		$otablename = C('DB_PREFIX').'zy_order_album';
		//拼接字段
		$fields     = ''; 
		$fields .= "{$otablename}.`uid`,{$otablename}.`id` as `oid`,";
		$fields .= "{$atablename}.`id`,{$atablename}.`album_title`,{$atablename}.`album_category`,{$atablename}.`album_intro`,";
		$fields .= "{$atablename}.`cover`,{$atablename}.`album_order_count`";
		//不是通过专辑购买的
		$where     = "{$otablename}.`is_del`=0 and {$otablename}.`uid`={$uid}";
		
		$data = M('ZyOrderAlbum')->join("{$atablename} on {$otablename}.`album_id`={$atablename}.`id`")->where($where)->field($fields)->findPage($limit);
 
		//把数据传入模板
		$this->assign('data',$data['data']);

		//取得数据
		$data['data'] = $this->fetch('_album_my_buy');
		echo json_encode($data);exit;
	}
	/**
    * 异步加载我收藏的专辑
    * @return void
    */
	public function getcollectalbumslist(){
		$limit      = 9;
		$uid        = intval($this->mid);
		//拼接两个表名
		$atablename = C('DB_PREFIX').'zy_album';
		$ctablename = C('DB_PREFIX').'zy_collection';
		//拼接字段
		$fields     = ''; 
		$fields .= "{$ctablename}.`uid`,{$ctablename}.`collection_id` as `cid`,";
		$fields .= "{$atablename}.`id`,{$atablename}.`album_title`,{$atablename}.`album_category`,{$atablename}.`album_intro`,";
		$fields .= "{$atablename}.`cover`,{$atablename}.`album_order_count`";
		//拼接字段
		$where      = "{$ctablename}.`source_table_name` = 'zy_album' and {$ctablename}.`uid`={$uid}";
		
		$data = M('ZyCollection')->join("{$atablename} on {$ctablename}.`source_id`={$atablename}.`id`")->where($where)->field($fields)->findPage($limit);
		//把数据传入模板
		$this->assign('data',$data['data']);

		//取得数据
		$data['data'] = $this->fetch('_album_my_collect');
		echo json_encode($data);exit;
	}
	/**
    * 异步加载我的约课
    * @return void
    */
	public function getbuycourselist(){
		$limit      = 9;
		$uid        = intval($this->mid);
		//拼接字段
		$fields= 'o.`uid`,o.`teach_way`,o.`id`'; 
		$fields .= ",c.`course_id`,c.`course_name`,c.`course_price`,c.`course_teacher`,c.`course_inro`";
		//不是通过专辑购买的
		$where     = "o.`is_del`=".intval($_POST["is_del"])." and o.`uid`={$uid}";
		$data = M('zy_order_course o')->join("`".C('DB_PREFIX')."zy_teacher_course` c on c.`course_id`=o.`course_id`")->where($where)->field($fields)->findPage($limit);
 		foreach ($data["data"] as $key => $value) {
 			$data["data"][$key]["teacher_info"]=M("zy_teacher t")->join("`".C('DB_PREFIX')."user` u on u.`uid`=t.`uid`")->where("id=".$value["course_teacher"])->field("phone,name,reservation_count")->find();
 			$data["data"][$key]["course_info"]=M("zy_teacher_course")->where("course_id=".$value["course_id"])->find();
 		}
		//把数据传入模板
		$this->assign('data',$data['data']);
		//取得数据
		$data['data'] = $this->fetch('_course_my');
		echo json_encode($data);exit;
	}
	/**
    * 异步加载我的约课(教师)
    * @return void
    */
	public function getTeachercourselist(){
		$limit      = 9;
		$uid        = intval($this->mid);
		//拼接字段
		$fields= 'o.`uid`,o.`teach_way`,o.`id`'; 
		$fields .= ",c.`course_id`,c.`course_name`,c.`course_price`";
		$where     = "o.`is_del`=".intval($_POST["is_del"])." and o.`teacher_id`={$uid}";
		$data = M('zy_order_course o')->join("`".C('DB_PREFIX')."zy_teacher_course` c on c.`course_id`=o.`course_id`")->where($where)->field($fields)->findPage($limit);
 		foreach ($data["data"] as $key => $value) {
 			$data["data"][$key]["user_info"]=M("user")->where("uid=".$value["uid"])->field("phone,uname,sex")->find();
 			$data["data"][$key]["course_info"]=M("zy_teacher_course")->where("course_id=".$value["course_id"])->find();
 		}
		//把数据传入模板
		$this->assign('data',$data['data']);
		//取得数据
		$data['data'] = $this->fetch('_teacher_course');
		echo json_encode($data);exit;
	}	
	/**
    * 异步完成我的约课
    * @return void
    */
	public function delCourse(){
		$res=M("zy_order_course")->where("id=".intval($_POST["id"]))->data(array("is_del"=>1))->save();
		if($res){
			exit(json_encode(array('status'=>'1','info'=>'删除成功')));
		}else{
			exit(json_encode(array('status'=>'0','info'=>'删除失败')));
		}
	}	
}