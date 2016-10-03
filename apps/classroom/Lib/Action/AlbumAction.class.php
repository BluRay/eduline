<?php
/**
 * 云课堂点播(专辑)控制器
 * @version CY1.0
 */
tsload(APPS_PATH . '/classroom/Lib/Action/CommonAction.class.php');
class AlbumAction extends CommonAction {
    protected $album = null; //课程模型对象
    protected $category = null; //分类数据模型
    protected $albumdata=null;
    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $type=intval($_GET['type']);//获取播放类型
        if($type==2){
	        $aid=intval($_GET['aid']);
	        $sVideos = D('ZyAlbum')->getVideoId($aid);
	        $sVideos = getCsvInt($sVideos);
	        $data = M("ZyVideo")->where(array('id' => array('in', (string) $sVideos),'is_del'=>0))->select();
	        foreach ($data as &$value) {
	            $value['mzprice'] = getPrice($value, $this->mid, true, true);
	            $value['isBuyVideo'] = isBuyVideo($this->mid, $value['id']) ? 1 : 0;
	            $is_colle=D('ZyCollection')->where(array('uid'=>$this->mid,'source_id'=>$value['id'],'source_table_name'=>'zy_video'))->find();
	            if($is_colle){
	                $value['is_colle']=1;
	            }else{
	                $value['is_colle']=0;
	            }
	        }
        }
        $this->albumdata = $data;
        $this->album = D('ZyAlbum');
        $this->category = model('VideoCategory');
    }

    public function index() {

        $cateId = intval($_GET['cateId']);
        $selCat = $this->category->getTreeById($cateId, 2);
        $datalist=array();
        foreach($selCat['list'] as &$val){
            $val['childlist']=$this->category->getChildCategory($val['zy_video_category_id'],2);
            array_push($datalist,$val);
        }
        $this->assign('selCate', $datalist);
        $tagSize = intval(getAppConfig('album_list_tag_num', 'page', 30));
        $tags = model('Tag')->setAppName('classroom')
                ->setAppTable('zy_album')
                ->getHotTags($tagSize, 1200);

        if(!empty($tags)){
            $this->assign('tags', $tags);

        }
        $this->display();
    }

    /**
     * 取得课程分类
     */
    public function getCategroy() {
        $id = intval($_GET['id']);
        if ($id > 0) {
            $data = $this->category->getChildCategory($id, 2);
        }
        if (empty($data))
            $data = null;
        $this->ajaxReturn($data);
    }

    /**
     * 取得课程列表
     * @param boolean $return 是否返回数据，如果不是返回，则会直接输出Ajax JSON数据
     * @return void|array
     */
    public function getList($return = false) {
        //排序
        $order = 'id DESC';

        $time = time();
        $where = "is_del=0 AND uctime>$time AND listingtime<$time";
        $_GET['cateId'] = intval($_GET['cateId']);
        $_GET['tagId'] = intval($_GET['tagId']);
        if ($_GET['cateId'] > 0) {
            $idlist = implode(',', $this->category
                            ->getVideoChildCategory($_GET['cateId'], 2));
            if ($idlist)
                $where .= " AND album_category IN($idlist)";
        }

        if ($_GET['tagId'] > 0) {
            $inSql = "SELECT row_id FROM ".C('DB_PREFIX')."app_tag WHERE app='classroom' AND `table`='zy_album' AND `is_del`=0 AND tag_id={$_GET['tagId']}";
            $where .= " AND id IN($inSql)";
        }
        $size = 16;
        $data = $this->album->where($where)->order($order)->findPage($size);
        //计算总课程
        foreach($data['data'] as &$val){
            $video_ids = D('ZyAlbum')->getVideoId($val['id']);
            $vids = D('zyVideo')->where(array('id'=>array('IN',$video_ids),'is_del'=>0))->field('id')->select();
            $val['video_cont'] = count($vids);
            //获取专辑价格
            $val['mzprice'] = $this->album->getAlbumMoeny( $video_ids );
            //格式化专辑评分
            $val['score']    = round($val['album_score']/20);
        }

        if ($data['data']) {
            $this->assign('listData', $data['data']);
            $this->assign('tagId',$_GET['tagId']);//定义排序
            $this->assign('cateId',$_GET['cateId']);//定义分类
            $html = $this->fetch('index_list');
        } else {
            $html = $this->fetch('index_list');
        }
        $data['data'] = $html;

        if ($return) {
            return $data;
        } else {
            echo json_encode($data);
            exit();
        }
    }

    public function view() {
        $id = intval($_GET['id']);
        $map['id'] = array('eq', $id);
        $data = D('ZyAlbum')->where($map)->find();
        if (!$data) {
            $this->assign('isAdmin', 1);
            $this->error('专辑不存在!');
        }
        //处理数据
        $data['album_score'] = floor($data['album_score'] / 20); //四舍五入
        //获取评论数
        $data['reviewCount'] = D('ZyReview')->getReviewCount(2, intval($data['id']));
        $data['album_title'] = msubstr($data['album_title'], 0, 24);
        $data['album_intro'] = msubstr($data['album_intro'], 0, 100);
        //专辑分类
        $data['album_category_name'] = getCategoryName($data['album_category'], true);
        $data['str_tag'] = array_chunk(explode(',', $data['str_tag']), 3, false);

        //是否收藏
        $data['iscollect'] = D('ZyCollection')->isCollect($data['id'], 'zy_album', intval($this->mid));
        
        //检查一个用户的余额/冻结的数量是否够支配
        $data['isSufficient'] = D('ZyLearnc')->isSufficient($this->mid, $data['mzprice']['price']);
        //查询资源是否存在
        $data['isGetResource'] = isGetResource(2, $data['id'], array('video', 'upload', 'note', 'question'));

        $album_video = D('ZyAlbum')->getVideoId($id);
        //获取专辑价格
        $data['mzprice'] = $this->album->getAlbumMoeny( $album_video );
        //查询所有讲师的id
        $tids = D('zyVideo')->where(array('id'=>array('IN',trim( $album_video ,',')),'teacher_id'=>array('NEQ','0')))->field('teacher_id')->select();
        foreach($tids as $key=>$val){
            $tids[$key] = $val['teacher_id'];
        }

        $tids=array_flip(array_flip($tids));//去掉重复讲师id
        $tidr=implode(",",$tids);
        $tdata=D('ZyTeacher')->where(array('id'=>array('IN',trim($tidr,","))))->select();
        //获取当前用户可支配的余额
        $data['balance'] = D("zyLearnc")->getUser($this->mid);
        $data['is_buy'] = D("ZyOrder")->isBuyAlbum($this->mid, $id);
        $this->assign('trlist',$tdata);
        $this->assign('data', $data);
        $this->assign('id', $id);
        $this->assign('uid',$this->mid);
        $this->display();
    }

    /**
     * 取得专辑目录----课程标题
     * @param int $return 
     * @return void|array
     */
    public function getcatalog() {
        $limit = intval($_POST['limit']);
        $id = intval($_POST['id']);

        $sVideos = D('ZyAlbum')->getVideoId($id);
        $sVideos = getCsvInt($sVideos);
        $data = M("ZyVideo")->where(array('id' => array('in', (string) $sVideos),'is_del'=>0))->select();
        
        $this->assign('data', $data);
        $result = $this->fetch('_MuLu');
        exit( json_encode($result) );
    }

    /**
     * 加载课程/专辑笔记
     */
    public function getnotelist(){
    	$type=intval($_REQUEST['type']);//获取笔记类型 【1:课程;2:专辑;】
    	$oid=intval($_REQUEST['oid']);//获取对应的栏目id
    	$map=array(
    		'type'=>$type,
    		'oid'=>$oid,
    		'parent_id'=>0,	
    	    'uid'=>$this->mid	
    	);
        $data=D('ZyNote')->where($map)->order("ctime DESC")->findPage(6);
        //格式化昵称
        foreach($data['data'] as &$val){
        	$val['uname']=getUserName($val['uid']);
        }
        
        $this->assign("data",$data['data']);
        $this->assign('oid',$oid);     
        $data['data']=$this->fetch("note");
        echo json_encode($data);exit;
    }
    /**
     * 加载课程/专辑提问
     */
    public function getquestionlist(){
    	$type=intval($_REQUEST['type']);//获取笔记类型 【1:课程;2:专辑;】
    	$oid=intval($_REQUEST['oid']);//获取对应的栏目id
    	$map=array(
    			'type'=>$type,
    			'oid'=>$oid,
    			'parent_id'=>0,
    			'uid'=>$this->mid
    	);
    	$data=D('ZyQuestion')->where($map)->order("ctime DESC")->findPage(6);
    	//echo M()->getlastsql();
    	$this->assign("data",$data['data']);
    	$this->assign('oid',$oid);
    	$data['data']=$this->fetch("question");
    	echo json_encode($data);exit;
    }
    
    

    /**
     * 专辑观看页面
     */
    public function watch() {
        include SITE_PATH . '/api/cc/spark_config.php';
        $this->assign('sp_config', $spark_config);
        $aid = intval($_GET['aid']);
        $type = intval($_GET['type']); //数据分类 1:课程;2:专辑;
      
        if ($type == 1) { //课程
            $data = M("ZyVideo")->where(array('id' => array('eq', $aid)))->select();
            $data[0]['mzprice'] = getPrice($data[0], $this->mid, true, true);
            $data[0]['isBuyVideo'] = isBuyVideo($this->mid, $data[0]['id']) ? 1 : 0;
            $is_colle=D('ZyCollection')->where(array('uid'=>$this->mid,'source_id'=>$data[0]['id'],'source_table_name'=>'zy_video'))->find();
            if($is_colle){
            	$data[0]['is_colle']=1;
            }else{
            	$data[0]['is_colle']=0;
            }
            if (!isset($data[0]) && !$data[0]) {
                $this->assign('isAdmin', 1);
                $this->error('课程不存在!');
            }
          
        } else {
        	$data=$this->albumdata;
            if (!isset($data[0]) && !$data[0]) {
                $this->assign('isAdmin', 1);
                $this->error('没有要播放的视频!');
            }
        }
        //判断是否是限时免费
        $is_free=0;
        if($data[0]['is_tlimit']==1 && $data[0]['starttime'] < time() && $data[0]['endtime'] > time() && $data[0]['limit_discount']==0.00){
            $is_free=1;
        }
        if( floatval( $data[0]['v_price'] ) <= 0 ) {
        	$is_free = 1;
        }
        $is_buy = D("ZyOrder")->isBuyAlbum($this->mid, $aid);
        //查询登录
        $isadmin=D('UserGroupLink')->where(array('user_group_id'=>1,'uid'=>$this->mid))->find();
        //限时免费/已购买/上传者是自己/管理员  不用购买课程
        if($data[0]['isBuyVideo']==1 || model('UserGroup')->isAdmin($uid) ||$isadmin){
            $is_free = 1;
        }
        $test_time=getAppConfig("video_free_time");

        $balance = D("zyLearnc")->getUser($this->mid);
        $this->assign("video_address",$jmstr);
        $this->assign("test_time",$test_time);
        $this->assign('balance', $balance);
        $this->assign('is_free',$is_free);
        $this->assign('vid', $data[0]['id']);
        $this->assign('video_id', $data[0]['video_id']);
        $this->assign('video_title', $data[0]['video_title']);
        $this->assign('video_order_count', $data[0]['video_order_count']);
        $this->assign('price', $data[0]['mzprice']['oriPrice']);
        $this->assign('is_colle',$data[0]['is_colle']);
        $this->assign('isBuyVideo', $data[0]['isBuyVideo']);
        $this->assign('utime', $data[0]['utime']);
        $this->assign('listingtime',$data[0]['listingtime']);
        $this->assign('cover',$data[0]['cover']);
        $this->assign("score",$data[0]['video_score']/20);
        $this->assign('data', $data);
        $this->assign('is_buy', $is_buy);
        $this->assign('aid', $aid);
        $this->assign('type', $type);
        $this->assign('isphone', isMobile() ? 1 : 0);
        $this->assign('mzbugvideoid', session('mzbugvideoid'));
        $this->assign('mid',$this->mid);
        $this->display();
    }
    /**
     * 同步播放视频
     */
    public function synvideo(){
    	$vid = intval($_GET['vid']);
    	$aid=intval($_GET['aid']);
    	$type=intval($_GET['type']);
    	$data = M("ZyVideo")->where(array('id' => array('eq', $vid)))->select();
    	$data[0]['mzprice'] = getPrice($data[0], $this->mid, true, true);
    	$is_colle=D('ZyCollection')->where(array('uid'=>$this->mid,'source_id'=>$data[0]['id'],'source_table_name'=>'zy_video'))->find();
    	if($is_colle){
    		$data[0]['is_colle']=1;
    	}else{
    		$data[0]['is_colle']=0;
    	}
    	if (!isset($data[0]) && !$data[0]) {
    		$this->assign('isAdmin', 1);
    		$this->error('课程不存在!');
    	}
    	//判断是否是限时免费
    	$is_free=0;
    	if($data[0]['is_tlimit']==1 && $data[0]['starttime'] < $nowtime && $data[0]['endtime'] > $nowtime && $data[0]['limit_discount']==0.00){
    		$is_free=1;
    	}
        $data[0]['isBuyVideo']= isBuyVideo($this->mid, $data[0]['id']) ? 1 : 0;
    	$test_time=getAppConfig("video_free_time");
    	$this->assign("video_address",$jmstr);
    	$this->assign("is_free",$is_free);
    	$this->assign("test_time",$test_time);
    	$this->assign('vid', $data[0]['id']);
    	$this->assign('video_id', $data[0]['video_id']);
    	$this->assign('video_title', $data[0]['video_title']);
    	$this->assign('video_order_count', $data[0]['video_order_count']);
    	$this->assign('price', $data[0]['mzprice']['oriPrice']);
    	$this->assign('is_colle',$data[0]['is_colle']);
    	$this->assign('isBuyVideo', $data[0]['isBuyVideo']);
    	$this->assign('utime', $data[0]['utime']);
    	$this->assign('listingtime',$data[0]['listingtime']);
    	$this->assign('cover',$data[0]['cover']);
    	$this->assign("score",$data[0]['video_score']/20);
    	$this->assign('data', $this->albumdata);
        $this->assign("aid",$aid);
    	$this->assign('aid', $aid);
    	$this->assign('type', $type);
    	$this->assign('album_address', $data[0]['video_address']);
    	$this->display("watch");
    }
    

    /**
     * 获取解密后的url
     */
    public function getvideo(){
        $info=t($_REQUEST['video']);//获取加密的视频
        if(empty($info)){
            echo "非法请求1！";
            exit;
        }
        $keyinfo=explode("|",sunjiemi($info));//解密分割数组
        $nowtime=time();
        if($keyinfo[0]<$nowtime){
            echo "非法请求！";
            exit;
        }
        $address=M('ZyVideo')->where(array('id'=>$keyinfo[1]))->getField("video_address");
        if($address){
            echo $address;
            exit;
        }else{
            echo "非法请求！";
            exit;
        }


    }

    /**
     * 获取随堂课题
     *   
     */
    public function lesson() {
        //用户权限控制
        $vid = intval($_POST['vid']);
        $questions = M('zyQuestions')->where(array('video_id' => $vid))->field('qid,q_title,q_options')->order('qid asc')->limit(5)->select();
        $show_time = M('zyVideo')->where(array('id' => $vid))->getField('show_time');
        foreach ($questions as $key => $val) {
            //反序列化选项
            $options = unserialize($val['q_options']);
            $questions[$key]['q_options'] = $options;
        }
        $json['questions'] = $questions;
        $json['showtime'] = $show_time;
        echo json_encode($json);
    }

    /**
     * 处理随堂课题
     *
     */
    public function exercises() {
        //转换成A B C D数组
        $arr = array('A', 'B', 'C', 'D');
        $selectIds = $_POST['ids'];
        $vid = $_POST['vid'];
        if (empty($selectIds)) {
            $this->assign('isAdmin', 1);
            $this->error('异常操作');
        };
        $times = 0;
        $uid = $this->uid;

        /**
         * 根据题  拼接选项答案和数据库的作判断
         */
        foreach ($selectIds as $key => $val) {
            $tarr = explode('_', $val);
            $where[$key] = $qid = $tarr['0'];
            $answer = $arr[$tarr['1']];
            //把多选的  拼接查询条件
            if (in_array($qid, $where)) {
                $seek[$qid] .= $answer . ',';
            } else {
                $seek[$qid] = $answer;
            }
        }
        //去除数组重复的题id
        $where = array_flip(array_flip($where));
        //根据题的id逐个去验证答题是否正确
        foreach ($where as $k => $v) {
            $seek[$v] = substr($seek[$v], 0, -1);
            $values .= "($uid,$v,$vid,'$seek[$v]'),";
            $rtn = M('zyQuestions')->where(array('qid' => $v, 'q_answer' => $seek[$v]))->find();
            //如果选择正确
            if (!is_null($rtn)) {
                $times += 1;
                $ex = explode(',', $seek[$v]);
                if (is_array($ex)) {
                    foreach ($ex as $keys => $vals) {
                        $key = array_search($vals, $arr);
                        $data[] = $v . '_' . $key;
                    }
                } else {
                    $key = array_search($seek[$v], $arr);
                    $data[] = $v . '_' . $key;
                }
            }
        }
        $values = substr($values, 0, -1);
        $sql = "insert into `".C('DB_PREFIX')."zy_answers` (`uid`,`qid`,`video_id`,`a_answer`) values $values";
        $result = M('zyAnswers')->query($sql);
        $json['data'] = $data;
        $json['times'] = $times;
        echo json_encode($json);
    }

    /**
     *   观看记录保存到session中
     */
    public function save_session() {
        $vid = intval($_POST['vid']); //获取观看视频id
        $uid = $this->mid; //用户id
        if (!empty($uid)) {
            $watch_history = array();
            $watch_history = session('watch_history');
            if (count($watch_history) <= 0) {
                $session_data[] = $vid;
                $watch_history = $session_data;
            } elseif (!in_array($vid, $watch_history)) {
                array_unshift($watch_history, $vid);
            }
            if (count($watch_history) > 3) {
                array_splice($watch_history, 3);
            }

            session('watch_history', $watch_history);
        }
    }

    /**
     * 删除操作
     * 1:购买的;2:收藏的;3：上传的---审核中;4:上传的---已发布
     * @param int $return 
     * @return void|array
     */
    public function delalbum() {
        $id = intval($_POST['id']);
        $type = intval($_POST['type']);
        $rtype = intval($_POST['rtype']);

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
     * 删除购买的专辑和课程 <!--type   1:课程;2:专辑;-->
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
            $this->mzError('删除失败');
        } else {
            $this->mzSuccess('删除成功');
        }
    }

    /**
     * 删除收藏的专辑和课程 <!--type   1:课程;2:专辑;-->
     * @param int $return 
     * @return void|array
     */
    private function delcollectalandvi($id, $type) {
        $map['collection_id'] = array('eq', $id);
        $i = M('ZyCollection')->where($map)->delete();
        if ($i === false) {
            $this->mzError('删除失败');
        } else {
            $this->mzSuccess('删除成功');
        }
    }

    /**
     * 删除上传的专辑和课程 <!--type   1:课程;2:专辑;-->
     * @param int $return 
     * @return void|array
     */
    private function delalbumorvideo($id, $type) {
        $data['is_del'] = 1;
        $map['id'] = array('eq', $id);

        if ($type == 1) {
            $i = M('ZyVideo')->where($map)->save($data);
        } else {
            $i = M('ZyAlbum')->where($map)->save($data);
        }

        if ($i === false) {
            $this->mzError('删除失败');
        } else {
            $this->mzSuccess('删除成功');
        }
    }

    /**
     * 把专辑分享到点播去
     * @param int $return 
     * @return void|array
     */
    public function sharetodianbo() {
        $id = intval($_POST['id']);

        $data['is_share'] = 1;
        $map['id'] = array('eq', $id);

        $i = M('ZyAlbum')->where($map)->save($data);
        if ($i === false) {
            $this->mzError('分享失败');
        } else {
            $this->mzSuccess('分享成功');
        }
    }

    /**
     * 前台创建专辑
     */
    public function creat_album() {
        $post = $_POST;
        $data['album_title'] = $post['album_title'];
        $data['album_intro'] = $post['album_intro'];
        $data['uid'] = $this->mid;
        $data['ctime'] = time();
        $data['album_videos'] = $post['album_videos'];
        $data['is_offical'] = '0';
        $data['is_share'] = '0';
        $total_price_post = floatval($post['total_price']);
        if (empty($post['album_videos']))
            exit(json_encode(array('status' => '999', 'info' => '说好的课程呢？')));
        $avideos['data'] = explode(',', $post['album_videos']);
        $total_price = 0;
        foreach ($avideos['data'] as $key => $value) {
            $avideos['data'][$value]['video_info'] = D("ZyVideo")->getVideoById($value);
            $avideos['data'][$value]['is_buy'] = D("ZyOrder")->isBuyVideo($this->mid, $avideos['data'][$value]['video_info']['id']);
            $avideos['data'][$value]['price'] = getPrice($avideos['data'][$value]['video_info'], $this->mid, true, true);

            //当购买过之后，或者课程的创建者是当前购买者的话，价格为0
            $total_price += ($avideos['data'][$value]['is_buy'] || $value['uid'] == $this->mid) ? 0 : round($avideos['data'][$value]['price']['price'], 2);
            $avideos['data'][$value]['legal'] = $avideos['data'][$value]['video_info']['uctime'] > time() ? 1 : 0;
            if ($avideos['data'][$value]['video_info']['is_offical']) {
                $avideos['data'][$value]['percent'] = 0;
            } else {
                $avideos['data'][$value]['percent'] = getUserIncomePercent($avideos['data'][$value]['video_info']['uid']);
            }
            $avideos['data'][$value]['user_num'] = $avideos['data'][$value]['percent'] * $avideos['data'][$value]['video_info']['v_price'];
            $avideos['data'][$value]['master_num'] = $avideos['data'][$value]['price']['price'] - $avideos['data'][$value]['user_num'];
            unset($avideos['data'][$key]);
        }
        //前台post的价格和后台计算的价格不相等，防止篡改价格
        if (bccomp($total_price, $total_price_post) != 0) {
            exit(json_encode(array('status' => '999', 'info' => '亲，可不要随便改价格哦，我们会发现的!')));
        }
        //创建专辑
        $create_result = M("ZyAlbum")->data($data)->add();
        $total_price = floatval($total_price);

        //创建专辑失败
        if (!$create_result)
            exit(json_encode(array('status' => '0', 'info' => '创建专辑失败，请稍后再试')));

        //创建专辑之后付款，并且添加专辑购买记录 不成功则向前台发送对应的错误信息
        $pay_result = D("ZyService")->buyAlbum($this->mid, $create_result, $total_price);
        if ($pay_result['status'] != '1') {
            M("zy_album")->where(' id = ' . $create_result)->delete();
            exit(json_encode(array('status' => $pay_result['status'], 'info' => $pay_result['info'])));
        }
        //添加消费记录
        M('ZyLearnc')->addFlow($this->mid, 0, $total_price, $note = '购买专辑<' . $data['album_title'] . '>', $pay_result['rid'], 'zy_order_album');


        //添加专辑中的课程购买记录
        $insert_value = "";
        foreach ($avideos['data'] as $key => $video) {
            if (!$video['is_buy']) {
                if($video['video_info']['uid'] != $this->mid){
                //卖家分成
                    D('ZyLearnc')->income($video['video_info']['uid'], $video['user_num']);
                    $insert_value .= "('" . $this->mid . "','" . $video['video_info']['uid'] . "','" . $video['video_info']['id'] . "','" . $video['video_info']['v_price'] . "','" . ($video['video_info']['price']['discount'] / 10) . "','" . $video['video_info']['price']['dis_type'] . "','" . $video['video_info']['price']['price'] . "','" . $create_result . "','" . $video['percent'] . "','" . $video['user_num'] . "','" . $video['master_num'] . "','0'," . time() . ",0),";
                }
            }
        }
        if(!empty($insert_value)){
            $query = "INSERT INTO " . C("DB_PREFIX") . "zy_order (`uid`,`muid`,`video_id`,`old_price`,`discount`,`discount_type`,`price`,`order_album_id`,`percent`,`user_num`,`master_num`,`learn_status`,`ctime`,`is_del`) VALUE " . trim($insert_value, ',');
            $table = new Model();
            if ($table->query($query) !== false || $total_price == 0) {
                echo(json_encode(array('status' => '1', 'info' => '创建专辑成功', 'album_id' => $create_result)));
                foreach ($avideos['data'] as $key => $video) {
                    if (!$video['is_buy'] && ($this->mid != $video['video_info']['uid'])) {
                        $rvid = M("zy_order")->where('video_id=' . $video['video_info']['id'])->field("id")->find();
                        //添加消费记录
                        D('ZyLearnc')->addFlow($video['video_info']['uid'], 5, $video['user_num'], $note = '卖出课程<' . $video['video_info']['video_title'] . '>', $rvid['id'], 'zy_order_album');
                    }
                    D('ZyVideoMerge')->delVideo($video['video_info']['id'], $this->mid);
                }
            }
        } else {
            echo(json_encode(array('status' => '1', 'info' => '创建专辑成功', 'album_id' => $create_result)));
        }

    }

    /**
     * 购买专辑操作
     */
    public function buyOperating() {
        if(!$this->mid){
            $this->ajaxReturn('', '请先登录', '1');
        }
        if (!$_POST['id']) {
            $this->ajaxReturn('', '没有选择专辑', '1');
        }
        if (isBuyAlbum($this->mid, $_POST['id'])) {
            $this->ajaxReturn('', '您已经买了本专辑', '0');
        }
        $album = D("ZyAlbum")->getAlbumById($_POST['id']);
        $albumId = intval($_POST['id']);
        $video_ids = trim(D("ZyAlbum")->getVideoId(intval($_POST['id'])), ',');
        $map['id'] = array('in', array($video_ids));
        $map["is_del"]=0;
        $album_info = M("zy_video")->where($map)->select();
        $illegal_count = 0;
        $total_price = 0;
        foreach ($album_info as $key => $video) {
            $album_info[$key]['price'] = getPrice($video, $this->mid, true, true);
            //价格为0的/限时免费的  不加入购物记录
            if($album_info[$key]['price']['price'] == 0){
                unset($album_info[$key]);
                continue;
            }
            $album_info[$key]['is_buy'] = D("ZyOrder")->isBuyVideo($this->mid, $video['id']);
            $total_price += ($album_info[$key]['is_buy'] || $video['uid'] == $this->mid) ? 0 : round($album_info[$key]['price']['price'], 2);
            if ($video['uctime'] < time()) {
                $illegal_count += 1;
                $video_id=$video['id'];
            }
        }
        if ($illegal_count > 0) {
            $this->ajaxReturn('', '专辑中包含有过期的课程，无法整辑购买', '0');
        }

        if (!D('ZyLearnc')->isSufficient($this->mid, $total_price, 'balance')) {
            $this->ajaxReturn('', '可支配的学币不足', '3');
        }

        //无过期非法信息则付款
        $pay_result = D("ZyService")->buyAlbum($this->mid, intval($_POST['id']), $total_price);
        if ($pay_result['status'] != '1') {
            exit(json_encode(array('status' => $pay_result['status'], 'info' => $pay_result['info'])));
        }
        //订单数量加1
        M()->query("UPDATE `".C('DB_PREFIX')."zy_album` SET `album_order_count`=`album_order_count`+1 WHERE `id`=$albumId");
        //添加消费记录
        M('ZyLearnc')->addFlow($this->mid, 0, $total_price, $note = '购买专辑<' . $album['album_title'] . '>', $pay_result['rid'], 'zy_order_album');
       //添加订单记录到课程
        $sql="update `".C('DB_PREFIX')."zy_video`  set video_order_count=video_order_count+1 where `id` in('$video_ids')";
        M()->query($sql);
        //添加专辑中的课程购买记录
        $insert_value = "";
        foreach ($album_info as $key => $video) {
            if (!$video['is_buy']) {
                if($video['uid'] != $this->mid){
                    $insert_value .= "('" . $this->mid . "','" . $video['uid'] . "','" . $video['id'] . "','" . $video['v_price'] . "','" . ($video['price']['discount'] / 10) . "','" . $video['price']['dis_type'] . "','" . $video['price']['price'] . "','" . $pay_result['rid'] . "','0'," . time() . ",0),";
                }
            }
        }
        if(!empty($insert_value)){
            $query = "INSERT INTO " . C("DB_PREFIX") . "zy_order (`uid`,`muid`,`video_id`,`old_price`,`discount`,`discount_type`,`price`,`order_album_id`,`learn_status`,`ctime`,`is_del`) VALUE " . trim($insert_value, ',');
            $table = new Model();
            if ($table->query($query) !== false || $total_price == 0) {
                $this->ajaxReturn('', '购买专辑成功!', '1');

                
                foreach ($album_info as $key => $video) {
                    if (!$video['is_buy'] && ($this->mid != $video['uid'])) {
                        $rvid = M("zy_order")->where('video_id=' . $video['id'])->field("id")->find();
                        //添加消费记录
                        M('ZyLearnc')->addFlow($video['uid'], 5, $video['price']['price'], $note = '卖出课程<' . $video['video_title'] . '>', $rvid['id'], 'zy_order_album');
                    }
                }
            }
        }
        else {
            $albumname= D("ZyAlbum")->getAlbumTitleById($_POST['id']);
            $s['uid']=$this->mid;
            $s['title'] = "恭喜您购买专辑成功";
            $s['body'] = "恭喜您成功购买专辑：《".$albumname."》";
            $s['ctime'] = time();
            model('Notify')->sendMessage($s);
            $this->ajaxReturn('', '购买专辑成功!', '1');
        }
    }
}
