<?php
/**
 * 专辑api
 * utime : 2016-03-06
 */

class AlbumApi extends Api{
    protected $album = null; //课程模型对象
    protected $category = null; //分类数据模型

    /**
     * 初始化模型
     */
    public function _initialize() {
        $this->album 	= D('ZyAlbum','classroom');
        $this->zyorder  = D('ZyOrder','classroom');
        $this->zylearnc = D('ZyLearnc','classroom');
        $this->category = model('VideoCategory');
    }

    //获取专辑列表api
    public function getAlbumList(){
        //排序
        $order = 'id DESC';
        $time = time();
        $where = "is_del=0 AND uctime>$time AND listingtime<$time";
        $cate_id = intval($this->data['cateId']);
        $tag_id  = intval($this->data['tagId']);
        if ($cate_id > 0) {
            $idlist = implode(',', $this->category->getVideoChildCategory($cate_id, 2));
            if ($idlist)
                $where .= " AND album_category IN($idlist)";
        }

        if ($tag_id > 0) {
            $inSql = "SELECT row_id FROM ".C('DB_PREFIX')."app_tag WHERE app='classroom' AND `table`='zy_album' AND tag_id={$tag_id}";
            $where .= " AND id IN($inSql)";
        }
        
        $data = $this->album->where($where)->field('id,uid,album_title,album_intro,cover,album_score')->order($order)->limit($this->_limit())->select();
        if( !$data ) {
        	$this->exitJson( array() );
        }
        //计算总课程
        foreach($data as &$val){
            $video_ids = trim( $this->album->getVideoId($val['id']) , ',');
            $val['album_video'] = $video_ids;
            //分割数组
            $video_ids = explode(',' , $video_ids);
            $val['video_cont'] = count($video_ids);
        }
        
        //格式化专辑评分
        foreach ($data as &$val){
            $val['album_score'] = round($val['album_score']/20);
			$val['cover']       = getCover($val['cover'] , 280 , 160);
        }
        //计算一个专辑集合的价格
        $data = $this->album->getAlbumMoneyList($data);
        $this->exitJson($data);
    }
    
    //查看一个专辑的详情
    public function albumView(){
        $id = $this->data['id'];
        $map['id'] = array('eq', $id);
        $data = D('ZyAlbum')->where($map)->find();
        if (!$data) {
            $this->exitJson( array() );
        }
        //处理数据
        $data['album_score'] = round($data['album_score'] / 20); //四舍五入
        //获取评论数
        $data['reviewCount'] = D('ZyReview','classroom')->getReviewCount(2, intval($data['id']));
        $data['album_title'] = $data['album_title'];
        $data['album_intro'] = $data['album_intro'];
        //专辑分类
        $data['album_category_name'] = getCategoryName($data['album_category'], true);
        //获取专辑图片
        $data['cover'] = getCover($data['cover'] , 280 , 160);
        //是否收藏
        $data['iscollect'] = D('ZyCollection','classroom')->isCollect($id, 'zy_album', intval($this->mid));
        //获取专辑价格
        $data['mzprice'] = $this->album->getAlbumMoeny( $this->album->getVideoId($data['id']) );
        //检查一个用户的余额/冻结的数量是否够支配
        $data['isSufficient'] = D('ZyLearnc','classroom')->isSufficient($this->mid, $data['mzprice']['price']);
        //查询资源是否存在
        $data['isGetResource'] = isGetResource(2, $data['id'], array('video', 'upload', 'note', 'question'));
        //是否购买
        $data['is_buy'] = D ( 'ZyOrder','classroom' )->isBuyAlbum($this->mid , $id);
        //是否全部播放视频
        $data['is_play_all'] = ($data['isBuy'] || intval( $data ['mzprice']['overplus'] )  <= 0 ) ? 1 : 0;
        $this->exitJson($data);
    }

    //获取专辑目录
    public function getCatalog(){
        $id = intval($this->data['id']);
        $videos = $this->album->getVideoId($id);
        $videos = getCsvInt($videos);
        $sql = 'SELECT `id`,`video_title` FROM ' . C("DB_PREFIX") . 'zy_video WHERE `id` IN (' . (string) $videos . ') ORDER BY find_in_set(id,"' . (string) $videos . '")';
        $result = M('')->query($sql);
        $this->exitJson($result);
    }
    
    //获取专辑标签集合
    public function getAlbumTag(){
        $tagSize = intval(getAppConfig('album_list_tag_num', 'page', 30));
        $tags = model('Tag')->setAppName('classroom')
            ->setAppTable('zy_album')
            ->getHotTags($tagSize, 1200);
		$taglist=array();
		foreach($tags as $key=>$val){
			array_push($taglist,$val);
		}
        $this->exitJson($taglist);
    }
	
	/**
    * 加载我购买的专辑
    * @return void
    */
	public function getBuyAlbumsList(){
		$uid        = intval($this->mid);
		//拼接两个表名
		$atablename = C('DB_PREFIX').'zy_album';
		$otablename = C('DB_PREFIX').'zy_order_album';
		//拼接字段
		$fields     = ''; 
		$fields .= "{$otablename}.`uid`,{$otablename}.`id` as `oid`,";
		$fields .= "{$atablename}.`id`,{$atablename}.`album_title`,{$atablename}.`album_category`,{$atablename}.`album_intro`,";
		$fields .= "{$atablename}.`cover`,{$atablename}.`album_order_count`,{$atablename}.`album_score`";
		//不是通过专辑购买的
		$where     = "{$otablename}.`is_del`=0 and {$otablename}.`uid`={$uid}";
		$data = M('ZyOrderAlbum')->join("{$atablename} on {$otablename}.`album_id`={$atablename}.`id`")->where($where)->field($fields)->limit($this->_limit())->select();
		if( !$data ) {
			$this->exitJson( array() );
		}
		//计算总课程
        foreach($data as &$val){
            $video_ids = trim( $this->album->getVideoId($val['id']) , ',');
            $val['album_video'] = $video_ids;
            //分割数组
            $video_ids = explode(',' , $video_ids);
            $val['video_cont'] = count($video_ids);
        }
        //计算一个专辑集合的价格
        $data = $this->album->getAlbumMoneyList($data);
        foreach($data as &$val){
        	$val['album_score'] = round($val['album_score']/20);
            $val['cover']       = getCover($val['cover'] , 280 , 160);
        }
        $data = $this->album->getAlbumMoneyList($data);
        $this->exitJson($data);
	}
	
	/**
    * 加载我收藏的专辑
    * @return void
    */
	public function getCollectAlbumsList(){
		$uid        = intval($this->mid);
		//拼接两个表名
		$atablename = C('DB_PREFIX').'zy_album';
		$ctablename = C('DB_PREFIX').'zy_collection';
		//拼接字段
		$fields     = ''; 
		$fields .= "{$ctablename}.`uid`,{$ctablename}.`collection_id` as `oid`,";
		$fields .= "{$atablename}.`id`,{$atablename}.`album_title`,{$atablename}.`album_category`,{$atablename}.`album_intro`,";
		$fields .= "{$atablename}.`cover`,{$atablename}.`album_order_count`,{$atablename}.`album_score`";		//拼接字段
		$where      = "{$ctablename}.`source_table_name` = 'zy_album' and {$ctablename}.`uid`={$uid}";
		
		$data = M('ZyCollection')->join("{$atablename} on {$ctablename}.`source_id`={$atablename}.`id`")->where($where)->field($fields)->limit($this->_limit())->select();
        foreach($data as &$val){
        	$val['cover'] = getCover($val['cover'] , 280 , 160);
        	$val['album_score']= $val['album_score']/20;
        }
		$data=$this->album->getAlbumMoneyList($data);
        if($data){
            $this->exitJson($data);
        }else{
            $this->exitJson(array(),10016,'你还没有收藏的专辑!');
        }
	}
    /**
     * 购买专辑操作
     */
    public function buyOperating() {
        $albumId = intval($this->data['id']);
        if(!$this->mid){
            $this->exitJson(array() , 10017,'请先登录!');
        }
        if (!$albumId) {
            $this->exitJson(array() , 10017,'没有选择专辑!');
        }
        if ($this->zyorder->isBuyAlbum($this->mid,$albumId)) {
            $this->exitJson(array() , 10017,'您已经买了本专辑!');
        }
        $album = $this->album->getAlbumById($albumId);
        $video_ids = trim($this->album->getVideoId($albumId), ',');
        $map['id']     = array('in', array($video_ids));
        $map["is_del"] = 0;
        $album_info = M("zy_video")->where($map)->select();
        $illegal_count = 0;
        $total_price = 0;
        foreach ($album_info as $key => &$video) {
            $video['price'] = getPrice($video, $this->mid, true, true);
            //价格为0的/限时免费的  不加入购物记录
            if($album_info[$key]['price']['price'] == 0){
                unset($album_info[$key]);
                continue;
            }
            $album_info[$key]['is_buy'] = $this->zyorder->isBuyVideo($this->mid, $video['id']);
            $total_price += ($album_info[$key]['is_buy'] || $video['uid'] == $this->mid) ? 0 : round($album_info[$key]['price']['price'], 2);
            if ($video['uctime'] < time()) {
                $illegal_count += 1;
                $video_id=$video['id'];
            }
        }
        if ($illegal_count > 0) {
            $this->exitJson( array() ,10017,'专辑中包含有过期的课程，无法整辑购买!');
        }

        if (!$this->zylearnc->isSufficient($this->mid, $total_price, 'balance')) {
            $this->exitJson( array() ,10017,'可支配的学币不足!');
        }
        //无过期非法信息则付款
        $pay_result = D("ZyService","classroom")->buyAlbum($this->mid, intval($albumId), $total_price);
        if ($pay_result['status'] != '1') {
            $this->exitJson(array() ,10017,$pay_result['info']);
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
        $insert_value;
        if(!empty($insert_value)){
            $query = "INSERT INTO " . C("DB_PREFIX") . "zy_order (`uid`,`muid`,`video_id`,`old_price`,`discount`,`discount_type`,`price`,`order_album_id`,`learn_status`,`ctime`,`is_del`) VALUE " . trim($insert_value, ',');
            $table = new Model();
            if ($table->query($query) !== false || $total_price == 0) {
                $this->exitJson(true);
                foreach ($album_info as $key => $video) {
                    if (!$video['is_buy'] && ($this->mid != $video['uid'])) {
                        $rvid = M("zy_order")->where('video_id=' . $video['id'])->field("id")->find();
                        //添加消费记录
                        M('ZyLearnc')->addFlow($video['uid'], 5, $video['price']['price'], $note = '卖出课程<' . $video['video_title'] . '>', $rvid['id'], 'zy_order_album');
                    }
                }
            }
        }else {
            $albumname= $this->album->getAlbumTitleById($albumId);
            $s['uid']=$this->mid;
            $s['is_read'] = 0;
            $s['title'] = "恭喜您购买专辑成功";
            $s['body'] = "恭喜您成功购买专辑：《".$albumname."》";
            $s['ctime'] = time();
            model('Notify')->sendMessage($s);
            $this->exitJson( array() ,0,'购买成功!');
        }
    }

}

?> 