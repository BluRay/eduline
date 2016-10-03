<?php
/**
 * 课程api
 * utime : 2016-03-06
 */
class VideoApi extends Api{
    private  $video; // 课程模型对象
    private $category; // 分类数据模型
    private $tableList = array(
        1=>'zy_question',2=>'zy_review',3=>'zy_note'
    );
    public function __construct(){
        parent::__construct();
        $this->video = M ( 'ZyVideo' );
        $this->category = model ( 'VideoCategory' );
    }

    /**
     * Eduline获取课程列表接口
     * 参数：
     * page 页数
     * count 每页条数
     * return   课程详情列表
     */
    public function videoList(){
        // 销量和评论排序
    	$orders = array (
    			'default'  => 'id DESC',
    			'saledesc' => 'video_order_count DESC',
    			'saleasc'  => 'video_order_count ASC',
    			'comtdesc' => 'video_comment_count DESC',
    			'comtasc'  => 'video_comment_count Asc',  
    	);
        if (isset ( $orders [$this->data['orderBy']] )) {
            $order = $orders [$this->data['orderBy']];
        } else {
            $order = $orders ['default'];
        }

        $time = time ();
        $where = "is_del=0 AND is_activity=1 AND uctime>$time AND listingtime<$time";
        $this->data ['cateId'] = intval ( $this->data ['cateId'] );
        if ($this->data ['cateId'] > 0) {
			$idlist = implode ( ',', $this->category->getVideoChildCategory ( intval ( $this->data ['cateId'] ), 1 ) );
            if ($idlist)
                $where .= " AND video_category IN($idlist)";
        }

        if ($this->data ['pType'] == 2 || $this->data ['pType'] == 1) {
            $oc = $this->data ['pType'] == 2 ? '>' : '=';
            if (vipUserType ( $this->mid ) > 0) {
                $vd = floatval ( getAppConfig ( 'vip_discount', 'basic', 10 ) );
                $mvd = floatval ( getAppConfig ( 'master_vip_discount', 'basic', 10 ) );
                $isVip = 1;
            } else {
                $isVip = 0;
            }
            // 查询价格 $oc 于0的数据，当在限时折扣的时候
            $ptWhere = "(is_tlimit=1 AND starttime<{$time} AND endtime>{$time} AND t_price{$oc}0)";
            // 如果是VIP，那么则查询价格 $oc 于0的数据，当不在限时折扣的时候
            if ($isVip) {
                $ptWhere .= " OR ((is_tlimit<>1 OR starttime>{$time} OR endtime<{$time}) AND (v_price*{$mvd}/10{$oc}0) OR (v_price*{$vd}/10{$oc}0))";
            }
            // 查询价格 $oc 于0的数据，当不在限时折扣并且当前用户不是VIP的时候
            $ptWhere .= " OR ((is_tlimit<>1 OR starttime>{$time} OR endtime<{$time}) AND (0={$isVip}) AND v_price{$oc}0)";
            $where .= " AND ({$ptWhere})";
        }
        $data = $this->video->where ( $where )->order ( $order )->limit ( $this->_limit() )->select();
        if( !$data ) {
        	$this->exitJson( array() );
        }
        foreach ( $data  as &$value ) {
             $value['price']    = getPrice ( $value, $this->mid );// 计算价格
             $value['imageurl'] = getCover($value['cover'] , 280 , 160);
        }
        $this->exitJson($data);
    }


    /**
     * Eduline获取课程详情接口
     * 参数：
     * id  课程id
     */
    public function videoInfo(){
        $id = intval ( $this->data ['id'] );
        $map ['id'] = array ( 'eq', $id );
        $data = D ( 'ZyVideo','classroom' )->where ( $map )->find ();
        if ( !$data) {
            $this->exitJson ( array() ,10006,'课程不存在' );
        }
        // 处理数据
        $data ['video_score'] = floor ( $data ['video_score'] / 20 ); // 四舍五入
        $data ['reviewCount'] = D ( 'ZyReview' ,'classroom')->getReviewCount ( 1, intval ( $data ['id'] ) );
        $data ['video_title'] = $data ['video_title'];
        $data ['video_intro'] = $data ['video_intro'];
        $data ['video_category_name'] = getCategoryName ( $data ['video_category'], true );
        $data ['cover'] = getCover($data['cover'] , 280 , 160);
        $data ['iscollect'] = D ( 'ZyCollection' ,'classroom')->isCollect ( $data ['id'], 'zy_video', intval ( $this->mid ) );
        $data ['mzprice'] = getPrice ( $data, $this->mid, true, true );
        $data ['isSufficient'] = D ( 'ZyLearnc','classroom' )->isSufficient ( $this->mid, $data ['mzprice'] ['price'] );
        $data ['isGetResource'] = isGetResource ( 1, $data ['id'], array (
            'video',
            'upload',
            'note',
            'question'
        ) );
        $data['isBuy']= D ( 'ZyOrder','classroom' )->isBuyVideo($this->mid , $id);
        $data['is_play_all'] = ($data['isBuy'] || intval( $data ['mzprice']['price'] )  <= 0 ) ? 1 : 0;
        //$data ['user'] = M ( 'User' )->getUserInfo ( $data ['uid'] );
        $this->exitJson($data);
    }

    /**
     * 获取图片资源接口
     */
    public function getAttrImage(){
	    $attid=intval($this->data['aid']);
	    $data['imgurl']=getAttachUrlByAttachId($attid);
	    $this->exitJson($data);
    }

    /**
     * 获取提问
     * @param integer kztype //数据分类 1:课程;2:专辑;
     * @param integer kzid //课程或者专辑ID
     * @param integer type //分类类型 1:提问,2:点评,3:笔记
     */
    public function render(){
        $kztype = intval($this->data['kztype']);//数据分类 1:课程;2:专辑;
        $kzid   = intval($this->data['kzid']);//课程或者专辑ID
        $type   = intval($this->data['type']);//分类类型 1:提问,2:点评,3:笔记

        $stable = parse_name($this->tableList[$type],1);
        //如果是课程的话就是=，专辑就是in
        $map['oid']        = $kzid;
        $map['parent_id']  = 0;

        //如果是专辑的话、、需要把下面的所有的
        if($kztype == 2){
            $vids = M('ZyAlbum')->where(array('id'=>array('eq',$kzid)))->getField('album_video');
            $vids = getCsvInt($vids);
            $vids = $vids?$vids:'0';
            $map['oid']        = array('in',(string)$kzid.','.$vids);
        }else{
            $map['type']       = $kztype;
        }

        if($type == 3){
            //复合查询--如果是他本人就连带私密的也查出来
            $where['uid']      = array('eq', $this->mid);
            $where['is_open']  = array('eq',1);
            $where['_logic']   = 'or';

            $map['_complex'] = $where;
        }
        $data = M($stable)->where($map)->order('`ctime` DESC')->limit($this->_limit())->select();
        $zyVoteMod = D('ZyVote','classroom');
        foreach($data as  &$value){
            $value['strtime']  = friendlyDate($value['ctime']);
            $value['uid']=$value['uid'];
            $value['username'] = getUserName($value['uid']);
            $value['userface'] = getUserFace($value['uid'],'s');
            $value['count']    = $this->getListCount($type,$value['id']);
            if($type == 2){
                $value['star']     = $value['star']/20;
                //判断时候已经投票了
                $value['isvote']   = $zyVoteMod->isVote($value['id'],'zy_review',$this->mid)?1:0;
                $value['username'] = intval($value['is_secret'])?'*****':$value['username'];
            }else if($type == 3){
                $value['note_description']  = msubstr($value['note_description'],0,44);
				if($value['type'] == 1){//是课程
					$video_title = M('zy_video')->where('id='.$value['oid'].' and is_del=0')->getField('video_title');
					$value['video_title'] = $video_title ? $video_title : $this->exitJson(array() , 0 ,'专辑找不到了');
				} else {
					$video_title = M('zy_album')->where('id='.$value['oid'].' and is_del=0')->getField('album_title');
					$value['video_title'] = $video_title ? $video_title : $this->exitJson(array() , 0 ,'专辑找不到了');
				}
            }
            $value['username'] = msubstr($value['username'],0,8);
        }
        $this->exitJson($data);
    }
    private function getListCount($type,$id){
        $stable = parse_name($this->tableList[$type],1);
        $map['parent_id'] = array('eq',$id);
        $count = M($stable)->where($map)->order('`ctime` DESC')->count();
        return $count;
    }

    //取课程/专辑分类
    public function getVideoGroup(){
        $cateId = intval ( $this->data['cateId'] );
        $selCat = $this->category->getTreeById ( 0, $cateId );
        // 循环取出所有下级分类
        $datalist = array ();
        foreach ( $selCat ['list'] as &$val ) {
            $val ['childlist'] = $this->category->getChildCategory ( $val ['zy_video_category_id'], 1 );
            array_push ( $datalist, $val);
        }
        $this->exitJson($datalist);

    }
    //获取提问评论列表
    public function questionDetail(){
        $pid=intval($this->data['pid']);//获取笔记ID
        $qtype=intval($this->data['qtype']);//获取笔记类型
        $where=array(
        'parent_id'=>$pid,
        'type'=>$qtype
        );
        $data=M("zy_question")->where($where)->select();
        foreach($data as &$val){
            $val['userinfo']=model('User')->getUserInfo($val['uid']);
            $val['userface']=getUserFace($val['uid'],'m');
        }
        $this->exitJson($data);
    }
    //添加课程/专辑提问
    public function addQuestion(){
        $data['parent_id']           = intval($this->data['pid']);
        $data['qst_title']        = t($this->data['title']);
        $data['qst_description']  = t($this->data['content']);
        $data['type']		      = intval($this->data['kztype']);//提问类型【1:课程;2:专辑;】
        $data['uid'] 			  = intval($this->mid);
        $data['oid'] 			  = intval($this->data['kzid']);//对应的ID【专辑ID/课程ID】
        $data['qst_source'] 	  = '手机端';
        $data['ctime']			  = time();

        if(trim($data['qst_title'])){
            $data['qst_title'] = msubstr($data['qst_description'],0,14);
        }
        if($data['uid']){
            $this->exitJson( array() ,10015,'添加问题需要先登录');
        }
        if($data['qst_description']){
            $this->exitJson( array() ,10015,'请输入问题内容');
        }
        $i = M('ZyQuestion')->add($data);
        if($i){
            //更改专辑或课程的总提问数
            if(intval($_POST['kztype']) == 1){
                $_data['video_question_count'] = array('exp','`video_question_count` + 1');
                //课程
                M('ZyVideo')->where(array('id'=>array('eq',$data['oid'])))->save($_data);
            }else{
                $_data['album_question_count'] = array('exp','`album_question_count` + 1');
                //专辑
                M('ZyAlbum')->where(array('id'=>array('eq',$data['oid'])))->save($_data);
            }
            $_data['qst_comment_count'] = array('exp','`qst_comment_count` + 1');
            M('zy_question')->where(array('id'=>array('eq',$data['parent_id'])))->save($_data);
            $this->exitJson($i);
        }else{
            $this->exitJson($i);
        }
    }

    /**
     * 编辑提问
     * @return void
     */
    public function editQuestion(){
        $data['id']              = intval($this->data['id']);
        $data['qst_title']        = t($this->data['title']);
        $data['qst_description'] = t($this->data['content']);

        if($this->mid){
            $this->exitJson( array() ,10016,'编辑问题需要先登录');
        }
        if(trim($data['id'])){
            $this->exitJson( array() ,10016,'问题不存在');
        }
        if(trim($data['qst_description'])){
            $this->exitJson( array() ,10016,'问题内容不能为空');
        }

        $i = M('ZyQuestion')->save($data);
        if($i === false){
            $this->exitJson( array() ,10016,'修改失败');
        }
        $this->exitJson(true);
    }
    //获取笔记评论列表
    public function noteDetail(){
        $pid=intval($this->data['pid']);//获取笔记ID
        $ntype=intval($this->data['ntype']);//获取笔记类型
        $where=array(
        'parent_id'=>$pid,
        'type'=>$ntype
        );
        $data=M("zy_note")->where($where)->select();
        foreach($data as &$val){
            $val['userinfo']=model('User')->getUserInfo($val['uid']);
            $val['userface']=getUserFace($val['uid'],'m');
        }
        $this->exitJson($data);
    }
    //添加课程/专辑笔记
    public function addNote(){
        $data['parent_id']           = intval($this->data['pid']);
        $data['type']		         = intval($this->data['kztype']);//
        $data['uid'] 			     = intval($this->mid);
        $data['oid'] 			     = intval($this->data['kzid']);//对应的ID【专辑ID/课程ID】
        $data['is_open']             = intval($this->data['is_open']);
        $data['note_source'] 	     = '手机端';
        $data['note_title']          = t($this->data['title']);
        $data['note_description']    = t($this->data['content']);
        $data['ctime']			     = time();
        if(trim($data['note_title'])){
            $data['note_title'] = msubstr($data['note_description'],0,14);
        }
        if($data['uid']){
            $this->exitJson( array() ,10017,'添加笔记需要先登录');
        }
        if($data['oid']){
            $this->exitJson( array() ,10017,'请选择课程或专辑');
        }
        if($data['note_title']){
            $this->exitJson( array() ,10017,'请输入笔记标题');
        }
        if($data['note_description']){
            $this->exitJson( array() ,10017,'请输入笔记内容');
        }
        $i = M('ZyNote')->add($data);
        if($i){
            //更改专辑或课程的总提问数
            if(intval($_POST['kztype']) == 1){
                $_data['video_note_count'] = array('exp','`video_note_count` + 1');
                //课程
                M('ZyVideo')->where(array('id'=>array('eq',$data['oid'])))->save($_data);
            }else{
                $_data['album_note_count'] = array('exp','`album_note_count` + 1');
                //专辑
                M('ZyAlbum')->where(array('id'=>array('eq',$data['oid'])))->save($_data);
            }
            $_data['note_comment_count'] = array('exp','`note_comment_count` + 1');
            M('zy_note')->where(array('id'=>array('eq',$data['parent_id'])))->save($_data);
            //session('mzaddnote'.$data['oid'].$data['type'],time()+180);
            $this->exitJson($i);
        }else{
            $this->exitJson( array() ,10017,'添加失败');
        }
    }
    //编辑笔记接口
    public function editNote(){
        $data['id']               = intval($this->data['id']);
        $data['note_description'] = t($this->data['title']);
        $data['note_title'] =t($this->data['content']);
        if($this->mid){
            $this->exitJson( array() ,10018,'编辑笔记需要先登录');
        }

        if(trim($data['id'])){
            $this->exitJson( array() ,10018,'笔记不存在');
        }
        if(trim($data['note_description'])){
            $this->exitJson( array() ,10018,'笔记内容不能为空');
        }

        $i = M('ZyNote')->save($data);
        if($i === false){
            $this->exitJson( array() ,10018,'修改失败');
        }
        $this->exitJson(true);
    }

    //添加点评api
    public function addReview(){
        //查看此人是否已经购买此课程//专辑
        if(intval($this->data['kztype']) == 1){
            //课程
            $isbuy = D('ZyService','classroom')->checkVideoAccess($this->mid,intval($this->data['kzid']));
            if($isbuy){
                $this->exitJson( array() ,10019,'需要购买之后才能点评');
            }
        }else{
            //专辑
            $isbuy = isBuyAlbum($this->mid,intval($this->data['kzid']));
            if($isbuy){
                $this->exitJson( array() ,10019,'需要购买之后才能点评');
            }
        }

        //每个人只能点评一次
        $count = M('ZyReview')->where(array('oid'=>intval($this->data['kzid']),'parent_id'=>0,'uid'=>$this->mid,'type'=>array('eq',intval($this->data['kztype']))))->count();
        if($count){
            $this->exitJson( array() ,10019,'已经点评了');
        }

        $data['parent_id']           = 0;
        $data['star']		         = intval($this->data['score'])*20;//分数
        $data['type']		         = intval($this->data['kztype']);//
        $data['uid'] 			     = intval($this->mid);
        $data['is_secret'] 			 = intval($this->data['is_secret']);
        $data['oid'] 			     = intval($this->data['kzid']);//对应的ID【专辑ID/课程ID】
        $data['review_source'] 	     = 'web网页';
        $data['review_description']  = t($this->data['content']);
        $data['ctime']			     = time();
        if($data['uid']){
            $this->exitJson( array() ,10019,'评价需要先登录');
        }
        if($data['star']){
            $this->exitJson( array() ,10019,'请给课程打分');
        }
        if($data['review_description']){
            $this->exitJson( array() ,10019,'请输入评价内容');
        }

        $i = M('ZyReview')->add($data);
        if($i){
            //点评之后 要计算此专辑的总评分
            $star = M('ZyReview')->where(array('oid'=>intval($this->data['kzid']),'parent_id'=>0,'type'=>array('eq',intval($this->data['kztype']))))->Avg('star');
            if(intval($_POST['kztype']) == 1){
                $_data['video_score'] = intval($star);
                $_data['video_comment_count'] = array('exp','`video_comment_count` + 1');
                //课程
                M('ZyVideo')->where(array('id'=>array('eq',$data['oid'])))->save($_data);
            }else{
                $_data['album_score'] = intval($star);
                $_data['album_comment_count'] = array('exp','`album_comment_count` + 1');
                //专辑
                M('ZyAlbum')->where(array('id'=>array('eq',$data['oid'])))->save($_data);
            }
            //session('mzaddreview',time()+180);
            $this->exitJson($i);
        }else{
            $this->exitJson( array() ,10019,'评价失败');
        }
    }

	   //加载我购买点的课程
	   public function getBuyVideosList(){
			$uid        = intval($this->data['uid'])?intval($this->data['uid']):$this->mid;
			//拼接两个表名
			$vtablename = C('DB_PREFIX').'zy_video';
			$otablename = C('DB_PREFIX').'zy_order';
			//拼接字段
			$fields     = ''; 
			$fields .= "{$otablename}.`learn_status`,{$otablename}.`uid`,{$otablename}.`id` as `oid`,";
			$fields .= "{$vtablename}.`video_title`,{$vtablename}.`video_category`,{$vtablename}.`id`,{$vtablename}.`video_intro`,";
			$fields .= "{$vtablename}.`cover`,{$vtablename}.`video_address`,{$vtablename}.video_order_count";
			//不是通过专辑购买的
			$where     = "{$otablename}.`is_del`=0 and {$otablename}.`uid`={$uid}";
			$data = M('ZyOrder')->join("{$vtablename} on {$otablename}.`video_id`={$vtablename}.`id`")->where($where)->field($fields)->limit($this->_limit())->select();
            foreach($data as &$val){
            	$val['price'] = getPrice ( $val, $this->mid);// 计算价格
                $val['cover'] = getCover($val['cover']  , 280 , 160);
            }
		    $this->exitJson($data);
	   }
	   //我收藏的课程
	   public function getCollectVideoList(){
			//获取购物车参数
	        $vms = D('ZyVideoMerge','classroom')->getList($this->mid, session_id());
	        //获取已购买课程id
	        $buyVideos = D('zyOrder','classroom')->where("`uid`=".$this->mid." AND `is_del`=0")->field('video_id')->select();
	            foreach($buyVideos as $key=>$val){
	                $buyVideos[$key] = $val['video_id'];
	            }
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
			$data = M('ZyCollection')->join("{$vtablename} on {$ctablename}.`source_id`={$vtablename}.`id`")->where($where)->field($fields)->limit($this->_limit())->select();
	        //循环计算课程价格
	        foreach($data as &$val){
	            $val['money'] = getPrice($val,$this->mid);
				$val['cover'] = getCover($val['cover']  , 280 , 160);
	        }
	        $this->exitJson($data);
		}




	  /**
	     * 删除操作
	     * 1:购买的;2:收藏的;3：上传的---审核中;4:上传的---已发布
	     * @param int $return 
	     * @return void|array
	     */
	    public function delalbum() {
	        $id = intval($this->data['id']);
	        $type = intval($this->data['type']);
	        $rtype = intval($this->data['rtype']);
	
	        if ($rtype == 1) {
	            $this->delbuyalandvi($id, $type);
	        } else if ($rtype == 2) {
	            $this->delcollectalandvi($id, $type);
	        } else if ($rtype == 3) {
	            $this->delalbumorvideo($id, $type);
	        } else if ($rtype == 4) {
	            $this->delalbumorvideo($id, $type);
	        }
	    }
	
	    /**
	     * 删除购买的专辑和课程 <--type   1:课程;2:专辑;-->
	     * @param int $return 
	     * @return void|array
	     */
	    private function delbuyalandvi($id, $type) {
	        $map['id'] = array('eq', $id);
	        $data['is_del'] = 1;
	        if ($type == 1) {
	            $i = M('ZyOrder')->where($map)->save($data);
	        } else {
	            $i = M('ZyOrderAlbum')->where($map)->save($data);
	        }
	        if ($i === false) {
	            $this->exitJson( array() ,10016,'对不起，删除失败！');
	        } else {
	            $this->exitJson(true);
	        }
	    }
		
		/**
	 * 添加笔记、提问的评论
	 * type 1:提问,2:点评,3:笔记
	 * @return void
	 */
	public function tongwen(){
		$types = array(
			1 => 'ZyQuestion',
			2 => 'ZyNote',
			3 => 'ZyNote',
		);
		$rid      = intval($this->data['rid']);
		$type     = intval($this->data['type']);
		$stable = $types[$type];
		if($type == 1){
			$data['qst_help_count']  = array('exp','qst_help_count+1');
		}else{
			$data['note_help_count'] = array('exp','note_help_count+1');
		}
		$i = M($stable)->where(array('id'=>array('eq',$rid)))->save($data);
		if($i){
            //查出被评论人的uid和内容
            $finfo=M($stable)->where(array('id'=>array('eq',$rid)))->find();
            if(empty($reply_id)){
                $fid=$finfo['uid'];
            }else{
                $fid=$reply_id;
            }
            model('Message')->doCommentmsg($this->mid,$fid,$finfo['id'],0,'zy_question',0,limitNumber($finfo['qst_description'],30),$content);
			$this->exitJson(true);
		}else{
			$this->exitJson( array() ,10017,"对不起，操作失败！");		
		}
	}
	
	/**
	 * 处理投票
	 * @return bool
	 */
	public function doreviewvote(){
		$kztype = 5;
		$kzid   = intval($this->data['kzid']);
		$type   = intval($this->data['type']);
		$uid    = intval($this->mid);
		if($kztype <= 0){
			$this->exitJson( array() ,10018,'投票资源错误');
		}

		if($uid){
			$this->exitJson( array() ,10018,'投票需要登录');
		}
		$zyVoteMod = D('ZyVote','classroom');
		$stable    = $zyVoteMod->_collType[$kztype];
		if($type>0){
			//取消投票
			$i = $zyVoteMod->delvote($kzid,$stable,$uid);
            if($i){
                $this->exitJson( array() ,0,"已取消投票");
            }else{
               $this->exitJson( array() ,10018,'取消投票失败！'); 
            }
        }else{
			//投票
			$i = $zyVoteMod->addvote(array(
				'uid'               => $uid,
				'source_id'         => $kzid,
				'source_table_name' => $stable,
				'ctime'             => time(),
			));
            if($i){
                $this->exitJson( array() ,0,"投票成功");
            }else{
               $this->exitJson( array() ,10018,'投票失败'); 
            }
		}
	}
	
	/**
	 * classroom/Public/collect
	 * 收藏功能
	 * 专辑收藏/课程收藏/提问收藏/笔记收藏/点评收藏
	 *  1=>'zy_album',//专辑收藏
	 *	2=>'zy_video',//课程收藏
	 * @param int $type 0:取消收藏;1:收藏;
	 * @return bool
	 */
	public function collect(){
		$zyCollectionMod = D('ZyCollection','classroom');
		$type   = intval($this->data['type']);//0:取消收藏;1:收藏;
		$sctype = intval($this->data['sctype']);//专辑收藏/课程收藏/提问收藏/笔记收藏/点评收藏
		$source_id = intval($this->data['source_id']);//资源ID
		if($sctype <= 0){
			$this->exitJson( array() ,10023,'收藏资源错误');
		}
		$data['uid'] = intval($this->mid);
		$data['source_id'] = intval($source_id);
		$data['source_table_name'] = $zyCollectionMod->_collType[$sctype];
		$data['ctime'] = time();
		if($type){
			$i = $zyCollectionMod->delcollection($data['source_id'],$data['source_table_name'],$data['uid']);
			if($i === false){
				$this->exitJson( array() ,10023,$zyCollectionMod->getError());
			}else{
				$this->exitJson(true);
			}
		}else{
			$i = $zyCollectionMod->addcollection($data);
			if($i === false){
				$this->exitJson( array() ,10023,$zyCollectionMod->getError());
			}else{
				$this->exitJson(true);
			}
		}
	}




    // 添加一个课程到购物车
    public function addVideoMerge() {
        if ( $this->mid) {
            $this->exitJson( array() ,10040,'请登录' );
        }
        $id = intval ( $this->data ['id'] );
        if (D ( 'zyOrder','classroom')->where ( "`video_id`=$id AND `is_del`=0 AND `uid`=" . $this->mid )->count () > 0) {
            $this->exitJson( array() ,10040, '你已经购买过了！' );
        }
        if ($this->video->where ( "id={$id}" )->count () > 0) {
            if (D ( 'ZyVideoMerge','classroom')->addVideo ( $id, $this->mid, session_id () )) {
                $this->exitJson(true);
            }
        }
        $this->exitJson( array() ,10040,'对不起，课程已在购物车中' );
    }

    // 删除购物车中的课程
    public function delVideoMerges() {
        if ( $this->mid)
            $this->exitJson(null,10041,'需要先登录' );
        $map = array ();
        $videoIds=trim($this->data['videoIds'],',');
        $map ['video_id'] = array (
            'IN',
            $videoIds
        );
        $map ['uid'] = array (
            'eq',
            $this->mid
        );
  //       if (session_id ()){
  //           $map ['tmp_id'] = session_id ();
		// }
        $rst = D( 'ZyVideoMerge','classroom')->where ( $map )->delete ();
        if ($rst == false) {
            $this->exitJson(true);
        }
        $this->exitJson( array() ,10041,'对不起，删除失败' );
    }

    /**
     * 购物车批量付款
     */
    public function buyVideos() {
        $post = $this->data;
        $vids = $post ['vids']; // 课程id
        $vids=explode(",",$vids);
        $uid = $this->mid;
        if (empty ( $vids )) $this->exitJson(null,10041,'请勾选要提交的课程' );
        $total_price = 0;
        $vidsnum = "";
        foreach ( $vids as $key => $val ) {
            $avideos [$val] = D ( "ZyVideo",'classroom' )->getVideoById ( $val );
            $avideos [$val] ['price'] = getPrice ( $avideos [$val], $uid, true, true );
            $videodata = $videodata . D ( 'ZyVideo','classroom' )->getVideoTitleById ( $val ) . ",";
            $vidsnum = $vidsnum . $val . ",";
            // 价格为0的/限时免费的 不加入购物记录
            if ($avideos [$val] ['price'] ['price'] == 0) {
                unset ( $avideos [$val] );
                continue;
            }

            // 当购买过之后，或者课程的创建者是当前购买者的话，价格为0
            $avideos [$val] ['is_buy'] = D ( "ZyOrder",'classroom' )->isBuyVideo ( $uid, $val );
            $total_price += ($avideos [$val] ['is_buy'] || $avideos [$val] ['uid'] == $uid) ? 0 : round ( $avideos [$val] ['price'] ['price'], 2 );
        }
        // 获取$uid的学币数量
        if ( D ( 'ZyLearnc','classroom' )->isSufficient ( $uid, $total_price, 'balance' )) {
            $this->exitJson( array(),10041, '可支配的学币不足' );
        }
        if ( D ( "ZyLearnc",'classroom' )->consume ( $uid, $total_price )) {
            $this->exitJson( array(),10041, '合并付款失败，请稍后再试' );
        }
        // 添加消费记录
        D ( 'ZyLearnc','classroom' )->addFlows ( $this->mid, 0, $total_price, $avideos, 'zy_order_video' );
        // 添加每个课程的订单数量
        $vidsnum = trim ( $vidsnum, "," );
        $sql = "update `".C('DB_PREFIX')."zy_video`  set video_order_count=video_order_count+1 where `id` in($vidsnum)";
        M ()->query ( $sql );
        // 添加课程购买记录
        $time = time ();
        foreach ( $avideos as $key => $val ) {
            $insert_value .= "('" . $this->mid . "','" . $val ['uid'] . "','" . $val ['id'] . "','" . $val ['v_price'] . "','" . ($val ['price'] ['discount'] / 10) . "','" . $val ['price'] ['dis_type'] . "','" . $val ['price'] ['price'] . "','0'," . $time . ",0),";
        }
        $query = "INSERT INTO " . C ( "DB_PREFIX" ) . "zy_order (`uid`,`muid`,`video_id`,`old_price`,`discount`,`discount_type`,`price`,`learn_status`,`ctime`,`is_del`) VALUE " . trim ( $insert_value, ',' );

        $rst = M ()->query ( $query );
        $map ['video_id'] = array (
            'IN',
            $vids
        );
        $map ['uid'] = array (
            'eq',
            $uid
        );
        $rst = M ( 'zyVideoMerge','classroom' )->where ( $map )->delete ();
        if ($rst) {
            $s ['uid'] = $this->mid;
            $s ['is_read'] = 0;
            $s ['title'] = "恭喜您购买课程成功";
            $s ['body'] = "恭喜您成功购买如下课程：" . trim ( $videodata, "," );
            $s ['ctime'] = time ();
            model ( 'Notify' )->sendMessage ( $s );
            $this->exitJson(true);
        } else {
            $this->exitJson( array() ,10041, '购买失败' );
        }
    }

    //根据内容搜索课程
    public function strSearch(){
        $str=t($this->data['str']);//获取要搜索的内容
        $videolist=$this->video->where("( `video_title` LIKE '%$str%' ) OR  ( `video_intro` LIKE '%$str%' ) and `is_del` =0 ")->order('ctime DESC')->limit($this->_limit())->select();
        // 计算价格
        foreach ( $videolist  as &$value ) {
            $value ['price']   = getPrice ( $value, $this->mid, true, false );
            $value['imageurl'] = getCover($value['cover'] , 280 , 160);
        }
        $this->exitJson($videolist);
    }
    //根据标签搜索课程
    public function tagSearch(){
        $tagid = intval($this->data['tagid']);//获取标签id
        $where['video_tag_id'] = array('like','%'.$tagid.'%');
        $where['is_del']       = 0;
        $videolist=$this->video->where($where)->order('ctime DESC')->limit($this->_limit())->select();
        foreach ( $videolist  as &$value ) {
            $value ['price']   = getPrice ( $value, $this->mid, true, false );
            $value['imageurl'] = getCover($value['cover'] , 280 , 160);
        }
        $this->exitJson($videolist);
    }

    // 购物车列表
    public function merge() {
        if ( $this->mid) {
            $this->exitJson(null,10043, "请登录先，客官" );
        }
        $merge_video_list  = D ( "ZyVideoMerge",'classroom' )->getList ( $this->mid);
        //$merge_video_list ['total_price'] = 0;
        foreach ( $merge_video_list as &$value ) {
            $value['tlimit_state'] = 0; // 判断是否限时
            $value['video_info'] = D ( "ZyVideo",'classroom' )->getVideoById ( $value ['video_id'] );
            $value['is_buy'] = D ( "ZyOrder",'classroom' )->isBuyVideo ( $this->mid, $value ['video_id'] );
            $value['price'] = getPrice ( $value['video_info'], $this->mid );
            //$merge_video_list ['total_price'] += $value['is_buy'] ? 0 : round ( $value['price'], 2 );
            $value['legal'] = $value['video_info'] ['uctime'] > time () ? 1 : 0;
            if ($value['video_info'] ['is_tlimit'] == 1 && $value['video_info'] ['starttime'] <= time () && $value['video_info'] ['endtime'] >= time ()) {
                $value['tlimit_state'] = 1;
            }
        }
        $this->exitJson($merge_video_list);
    }


}









?> 