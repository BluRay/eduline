<?php
/**
 * 问答api
 * utime : 2016-03-06
 */

class WendaApi extends Api{
	 protected $wenda= array() ;
	 protected $wenda_comment= array() ;
	/**
     * 初始化 
     */
    public function _initialize(){
        $this->wenda = D('ZyWenda','wenda');//问答模型
		 $this->wenda_comment=D('ZyWendaComment','wenda');//问答评论模型
    }
    
	/**
    * 问答中心--我的问答
    * @return void
    */
    public function getWenda(){
        $mid = $this->mid;
        $map['is_del'] = array('EQ',0);
        $map['uid']    = array('EQ',$mid);
        $list = M('zy_wenda')->where($map)->limit($this->_limit())->order('ctime DESC')->select();
        foreach($list as &$val){
        	$val['uname']    = getUserName($val['uid']);
			$val['userface'] = getUserFace($val['uid'],'m');
			$val['ctime']    = date('Y-m-d',$val['ctime']);
        }
		if($list){
			$this->exitJson($list);
		}else{
			$this->exitJson(array(),10016,'你还没有发表问题!');
		}
	}
	
	/**
	 * 问答中心--我的回答
	 * @return void
	 */
	public function getAnswer(){
		$mid = $this->mid;
		$map['d.is_del'] = array('EQ',0);
		$map['d.uid'] = array('EQ',$mid);
		$list = M("zy_wenda_comment d")->join("`".C('DB_PREFIX')."zy_wenda` w ON w.id = d.wid")->field('w.id,w.uid,wd_description,w.wd_comment_count,w.wd_browse_count,w.ctime')->where($map)->limit($this->_limit())->select();
		foreach($list as &$val){
			$val['uname']    = getUserName($val['uid']);
			$val['userface'] = getUserFace($val['uid'],'m');
			$val['ctime']    = date('Y-m-d',$val['ctime']);
		}
		if($list){
			$this->exitJson($list);
		}else{
			$this->exitJson(array(),10025,"没有相应的问题！");
		}
	}
	
	//删除问答
    public function delWenda(){
        $id = intval($this->data['id']);
        $data['is_del'] = 1;
		$where=array(
			'id'  => $id,
			'uid' => $this->mid
		);
        $res=M('ZyWenda')->where($where)->save($data);
        if($res){
			$this->exitJson(true);
        }else{
			$this->exitJson( array() ,10025,"对不起，删除失败！");
        }
    }
	
	//加载问答列表api
	public function getWendaList(){
		$wdtype = intval($this->data['wdtype']);//获取问答类型【1技术问答】【2技术分享】【3活动建议】
		$type   = intval($this->data['type']);//获取类别  1最新 2热门 3等待回复
		$where["is_del"]=0;
		switch($type){
			case 2:
			$order='wd_comment_count DESC';
			break;
			
			case 3:
			$where['wd_comment_count']=array('eq',0);
			break;
			
			default:
			$order='ctime DESC';
		}
		if($wdtype!=0){
			$where['type']=$wdtype;
		}else{
			$where['type']='1 = 1';
		}
		$wdlist = $this->wenda->where($where)->limit($this->_limit())->order($order)->select();
		//循环格式化数组
		foreach($wdlist as &$val){
			$val['uname']    = getUserName($val['uid']);
			$val['userface'] = getUserFace($val['uid'],'m');
			$val['tags']     = $this->wenda->getWendaTags($val['tag_id']);//取出问答的标签
			$val['wd_comment'] = $this->wenda_comment->getNowWenda($val['id'],1);//取最新的一条评论
			$val['wd_comment']['userface']=getUserFace($val['wd_comment']['uid'],'m');
			$val['wd_comment']['uname']=getUserName($val['wd_comment']['uid']);
		}
		if($wdlist){
			$this->exitJson($wdlist);
		}else{
			 $this->exitJson(array() , 10025 , "还没有回答");
		}
	}
	
	//获取课程问答
	public function getWendaByCourse(){
		$order = t($this->data['order']);
		$where = '`parent_id`=0 and `type`=1';
		switch($order){
			//最热
			case 1:
				$order = 'qst_comment_count DESC , ctime Desc';
				break;
			//待回复		
			case 2:
				$where .= ' and `qst_comment_count` = 0';
				break;
			default:
				$order = 'ctime DESC';
		}
		$wendaList = M('zy_question')->field('id,uid,qst_title,qst_description,qst_help_count,qst_comment_count,qst_collect_count,ctime')->where($where)->order($order)->limit($this->_limit())->select();
		if( !$wendaList ) {
			$this->exitJson( array() );
		}
		//循环格式化数组
		foreach($wendaList as &$val){
			$val['uname']    = getUserName( $val['uid'] );
			$val['userface'] = getUserFace( $val['uid'],'m' );
			$val['ctime']    = getDateDiffer( $val['ctime'] );//格式化时间数据
		}
		$this->exitJson($wendaList);
	}
	
	//加载7天内热门问题
	public function sevendayHot(){
		$senhotwd=$this->wenda->query("select * from ".C('DB_PREFIX')."zy_wenda  where DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= ctime and is_del=0 ORDER BY wd_comment_count DESC limit 5");
		//格式化数据
		foreach($senhotwd as &$val){
			$val['tags']=$this->wenda->getWendaTags($val['tag_id']);//取出问答的标签
			$val['wd_comment']=$this->wenda_comment->getNowWenda($val['id'],1);//取最新的一条评论
			$val['wd_comment']['userface']=getUserFace($val['wd_comment']['uid'],'m');
			$val['wd_comment']['uname']=getUserName($val['wd_comment']['uid']);
            $val['uname']=getUserName($val['uid']);
            $val['userface']=getUserFace($val['uid'],'m');
		}
		$this->exitJson($senhotwd);
	}
	
	//根据标签搜索问答
	public function tagSearch(){
		$tagid = intval($this->data['tagid']);//获取标签id
		$where['tag_id']=array('like','%'.$tagid.'%');
		$wendalist=$this->wenda->where($where)->order('ctime ASC')->limit($this->_limit())->select();
		//格式化数据
		foreach($wendalist as &$val){
			$val['tags']       = $this->wenda->getWendaTags($val['tag_id']);//取出问答的标签
			$val['wd_comment'] = $this->wenda_comment->getNowWenda($val['id'],1);//取最新的一条评论
			$val['wd_comment']['userface'] = getUserFace($val['wd_comment']['uid'],'m');
			$val['wd_comment']['uname']    = getUserName($val['wd_comment']['uid']);
			$val['uname']    = getUserName($val['uid']);
			$val['userface'] = getUserFace($val['uid'],'m');
		}
		$this->exitJson($wendalist);
	}
	
	//根据内容搜索问答
	public function strSearch(){
		$str=t($this->data['str']);//获取要搜索的内容
		$wendalist=$this->wenda->where("( `wd_title` LIKE '%$str%' ) OR  ( `wd_description` LIKE '%$str%' )")->order('ctime ASC')->limit($this->_limit())->select();
		//格式化数据
		foreach($wendalist as &$val){
			$val['tags']=$this->wenda->getWendaTags($val['tag_id']);//取出问答的标签
			$val['wd_comment']=$this->wenda_comment->getNowWenda($val['id'],1);//取最新的一条评论
			$val['wd_comment']['userface']=getUserFace($val['wd_comment']['uid'],'m');
			$val['wd_comment']['uname']=getUserName($val['wd_comment']['uid']);
			$val['uname']=getUserName($val['uid']);
			$val['userface']=getUserFace($val['uid'],'m');
		}
		$this->exitJson($wendalist);
	}
	
	//发布/修改问答
	public function postWenda(){
		$type=intval($this->data['typeid']);
        //$title=strip_tags($this->data['title'], '<a><br><span><b><i><strong><img>');
        $content = strip_tags($this->data['content'], '<a><br><span><b><i><strong><img>');
        $tags=t($this->data['tags']);
		$wid=intval($this->data['wid']);//获取编辑类型  1添加 2修改
        if($type<=0 ||$type>3)$this->exitJson( array() ,10032,"对不起，发布类型错误！");
        //if(strlen($title)<2 || strlen($title)>120)$this->exitJson( array() ,10032,"对不起，标题长度在2-20个字符之间");
        if(strlen($content)<3 )$this->exitJson( array() ,10032,"对不起，内容至少为3个字符");
        $data=array(
            'type'=>$type,
            'uid'=>$this->mid,
            //'wd_title'=>$title,
            'wd_description'=>$content,
            'tag_id'=>$tags,
            'ctime'=>time()
        );
		if($wid){
			$res=M('ZyWenda')->where(array('id'=>$wid))->save($data);
		}else{
			$res=M('ZyWenda')->add($data);
		} 
		if($res){
			$this->exitJson($res);
		}
		$this->exitJson( array() ,10032,"对不起，操作失败");
	}
	
	//评论问答
	public function doWendaComment(){
        $wid  = intval($this->data['wid']);//获取问答id
        $cont = strip_tags($this->data['count'], '<a><br><span><b><i><strong><img>');//获取评论内容
        if( empty($wid) || empty($cont)) $this->exitJson( array() ,10033,"对不起，操作失败");
        $data=array(
            'uid'=>$this->mid,
            'wid'=>$wid,
            'description'=>$cont,
            'ctime'=>time()
        );
        $res = $this->wenda_comment->add($data);
        if($res){//评论成功
            //设置问答评论数量+1
            $this->wenda->addCommentCount($wid);
            //查询应用的作者
            $wdinfo=$this->wenda->where(array('id'=>$wid))->find();
            //添加消息记录
            model('Message')->doCommentmsg($this->mid,$wdinfo['uid'],$wid,$wdinfo['uid'],'wenda',$res,0,limitNumber($wdinfo['wd_description'],500),$cont);
			$this->exitJson(true);
        }else{
			$this->exitJson( array() ,10033,"对不起，操作失败");
        }
    }
    
	//获取问答详情
	public function detail(){
		$wid = intval($this->data['wid']);
		$uid = intval($this->data['uid']);
		if(empty($wid)){
			 $this->exitJson( array() ,10034,"对不起，您查看的问答不存在！");
		}
		$where=array(
			'id'     => $wid,
			'is_del' => 0
		);
		$wdata=$this->wenda->where($where)->find();
		if($wdata){
			//判断是否关注对方
			$r= model('Follow')->getFollowState($this->mid,$uid);
			$wdata['follow_state']=$r["following"];
			$wdata['userinfo']=model('User')->getUserInfo($wdata['uid']);
			$this->exitJson($wdata);
		}else{
			$this->exitJson( array() ,10034,"对不起，您查看的问答不存在或已被删除！");
		}
	}
    //获取问答评论列表
	public function wendaComment(){
		$wid=intval($this->data['wid']);//获取问答id
		$where['parent_id']=0;
		$where['wid']=$wid;
		$where['is_del']=0;
		$wdlist= $this->wenda_comment->where($where)->order('ctime DESC')->limit($this->_limit())->select();
		if($wdlist){
			foreach($wdlist as &$val){
            	$val['userinfo']=model('User')->getUserInfo($val['uid']);
            	$val['userface']=getUserFace($val['uid'],'m');
        	}
      		$this->exitJson($wdlist);
		}else{
      		$this->exitJson( array() ,10034,"对不起，没有获取到相应的评论！");
		}
    }
    
    //加载牛人排行榜
    public function wendaCommentDesc(){
        $nblist=$this->wenda_comment->query("SELECT uid,COUNT(id) as count FROM ".C('DB_PREFIX')."zy_wenda_comment WHERE is_del=0 GROUP BY uid   ORDER BY count DESC LIMIT 6");
        foreach($nblist as &$val){
            $val['userinfo']=model('User')->getUserInfo($val['uid']);
        }
        $this->exitJson($nblist);
    }
    
	//加载二级回复
	public function getSonComment(){
        $id=intval($this->data['id']);
        $map=array(
            'parent_id'=>$id,
            'is_del'=>0
        );
        $data=$this->wenda_comment->where($map)->order("ctime DESC")->limit($this->_limit())->select();
        //循环取时间差
        foreach($data as &$val){
            $val['ctime']=getDateDiffer($val['ctime']);
            $val['userinfo']=model('User')->getUserInfo($val['uid']);
        }
		$this->exitJson($data);
    }

    /**
     * 添加子回复
     */
    public function doSonComment(){
        $id=intval($this->data['id']);//获取父级评论id
        $count=t($this->data['txt']);//获取回复内容
        $wid=intval($this->data['wid']);//获取问答id
        if(strlen($count)<6){
            $this->exitJson( array() ,10034,"对不起，评论内容最少3字符");
        }
        $map=array(
            'parent_id'=>$id,
            'wid'=>$wid,
            'description'=>$count,
            'ctime'=>time(),
            'uid'=>$this->mid
        );
        $res=$this->wenda_comment->add($map);
        if($res){
            //设置问答评论数量+1
            $this->wenda->addCommentCount($wid);
            //设置子评论数量+1
            $this->wenda_comment->addCommentCount($id);
            //查询应用的作者
            $wuid=$this->wenda->where(array('id'=>$wid))->getField('uid');
            //查询评论内容
            $cominfo=$this->wenda_comment->where(array('id'=>$id))->find();
            //添加消息记录
            model('Message')->doCommentmsg($this->mid,$cominfo['uid'],$wid,$wuid,'wenda',$res,$id,limitNumber($cominfo['description'],500),$count);
            $this->exitJson(true);
        }else{
            $this->exitJson( array() ,10034,"对不起，评论失败");
        }
    }
    
	/**
	 * 添加问答点赞
	 * @return void
	 */
	public function dopraise(){
		$comment_id  = intval($this->data['comment_id']);
		$uid = $this->mid;
		$res = M("zy_wenda_praise")->where(array('uid'=>$uid,'comment_id'=>$comment_id))->find();
		if($res)$this->exitJson( array() ,10035,"你已经点赞过了");
		$i= M("zy_wenda_praise")->data(array('uid'=>$uid,'comment_id'=>$comment_id))->add();
		if($i){
			$parise=M("zy_wenda_comment")->where("id=".$comment_id)->find();
			$data["help_count"]=$parise["help_count"]+1;
			M("zy_wenda_comment")->where("id=".$comment_id)->save($data);
			$this->exitJson(true);
		}else{
			$this->exitJson( array() ,10035,"对不起，操作失败！");
		}
	}
	
	
		
		
		
}
	