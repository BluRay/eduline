<?php
/**
 * 云课堂课程(视频)控制器
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload ( APPS_PATH . '/classroom/Lib/Action/CommonAction.class.php' );
class VideoAction extends CommonAction {
	protected $video = null; // 课程模型对象
	protected $category = null; // 分类数据模型
	
	/**
	 * 初始化
	 */
	public function _initialize() {
		$this->video = D ( 'ZyVideo' );
		$this->category = model ( 'VideoCategory' );
	}
	
	/**
	 * 课程播放页面
	 */
	public function watch() {
		if (! $_GET ['vid']) {
			$this->assign ( 'isAdmin', 1 );
			$this->error ( '参数错误啦' );
		}
		$map ['id'] = intval ( $_GET ['vid'] );
		$data = $this->video->where ( $map )->find ();
		$this->assign ( 'sp_config', $spark_config );
		$this->assign ( $data );
		$this->display ();
	}
	
	/**
	 * 课程详情页面
	 * 
	 * @return void
	 */
	public function view() {
		$id = intval ( $_GET ['id'] );
		$map ['id'] = array (
				'eq',
				$id 
		);
		$data = D ( 'ZyVideo' )->where ( $map )->find ();
		if (! $data) {
			$this->assign ( 'isAdmin', 1 );
			$this->error ( '课程不存在!' );
		}
		if($data['is_tlimit']==1 && $data['starttime'] < time() && $data['endtime'] > time() ){
            $data['is_tlimit']=1;
        }else{
        	$data['is_tlimit']=0;
        }
		// 处理数据
		$data ['video_score'] = floor ( $data ['video_score'] / 20 ); // 四舍五入
		$data ['reviewCount'] = D ( 'ZyReview' )->getReviewCount ( 1, intval ( $data ['id'] ) );
		$data ['video_category_name'] = getCategoryName ( $data ['video_category'], true );
		$data ['iscollect'] = D ( 'ZyCollection' )->isCollect ( $data ['id'], 'zy_video', intval ( $this->mid ) );
		$data ['mzprice'] = getPrice ( $data, $this->mid, true, true );
		$data ['isSufficient'] = D ( 'ZyLearnc' )->isSufficient ( $this->mid, $data ['mzprice'] ['price'] );
		$data ['isGetResource'] = isGetResource ( 1, $data ['id'], array (
				'video',
				'upload',
				'note',
				'question' 
		) );
		$data ['user'] = M("zy_teacher")->where("id=".$data["teacher_id"])->find();
		// 课程标签
		$data ['video_str_tag'] = array_chunk ( explode ( ',', $data ['str_tag'] ), 3, false );
		
		// 是否已经加入了购物车
		$this->assign ( 'hasVideo', D ( 'ZyVideoMerge' )->hasVideo ( $id, $this->mid, session_id () ) );
		$data['balance'] = D("zyLearnc")->getUser($this->mid);
		// 是否已购买
		$data['is_buy'] = D('ZyOrder','classroom')->isBuyVideo($this->mid ,$id );
		$this->assign ( 'id', $id );
		$this->assign ( 'data', $data );
		$this->display ();
	}
	
	/**
	 * 课程(视频)首页页面
	 * 
	 * @return void
	 */
	public function index() {
		$cateId = intval ( $_GET ['cateId'] );
		$selCat = $this->category->getTreeById ( $cateId, 1 );
		// 循环取出所有下级分类
		$datalist = array ();
		foreach ( $selCat ['list'] as &$val ) {
			$val ['childlist'] = $this->category->getChildCategory ( $val ['zy_video_category_id'], 1 );
			array_push ( $datalist, $val );
		}
		
		$this->assign ( 'cateId', $cateId );
		$this->assign ( 'catId', $cateId );
		$this->assign ( 'selCate', $datalist );
		$this->assign ( 'mid', $this->mid );
		
		$this->display ();
	}
	
	/**
	 * 取得课程分类
	 */
	public function getCategroy() {
		$id = intval ( $_GET ['id'] );
		if ($id > 0) {
			$data = $this->category->getChildCategory ( $id, 1 );
		}
		if (empty ( $data ))
			$data = null;
		$this->ajaxReturn ( $data );
	}
	
	/**
	 * 取得课程列表
	 * 
	 * @param boolean $return
	 *        	是否返回数据，如果不是返回，则会直接输出Ajax JSON数据
	 * @return void|array
	 */
	public function getList($return = false) {
		// 销量和评论排序
		$orders = array (
				'default' => 'video_order_count DESC,video_score DESC,video_comment_count DESC',
				'saledesc' => 'video_order_count DESC',
				'saleasc' => 'video_order_count ASC',
				'scoredesc' => 'video_score DESC',
				'scoreasc' => 'video_score ASC' 
		);
		if (isset ( $orders [$_GET ['orderBy']] )) {
			$order = $orders [$_GET ['orderBy']];
		} else {
			$order = $orders ['default'];
		}
		
		$time = time ();
		$where = "is_del=0 AND is_activity=1 AND uctime>$time AND listingtime<$time";
		$_GET ['cateId'] = intval ( $_GET ['cateId'] );
		if ($_GET ['cateId'] > 0) {
			$idlist = implode ( ',', $this->category->getVideoChildCategory ( intval ( $_GET ['cateId'] ), 1 ) );
			if ($idlist)
				$where .= " AND video_category IN($idlist)";
		}

		if ($_GET ['pType'] == 3 || $_GET ['pType'] == 2) {
			$oc = $_GET ['pType'] == 3 ? '>' : '=';
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
				$ptWhere .= " OR ((is_tlimit<>1 OR starttime>{$time} OR endtime<{$time}) AND (is_offical=1 AND v_price*{$mvd}/10{$oc}0) OR (is_offical=0 AND v_price*{$vd}/10{$oc}0))";
			}
			// 查询价格 $oc 于0的数据，当不在限时折扣并且当前用户不是VIP的时候
			$ptWhere .= " OR ((is_tlimit<>1 OR starttime>{$time} OR endtime<{$time}) AND (0={$isVip}) AND v_price{$oc}0)";
			$where .= " AND ({$ptWhere})";
		}
		
		$size = intval ( getAppConfig ( 'video_list_num', 'page', 9 ) );
		$data = $this->video->where ( $where )->order ( $order )->findPage ( $size );
		if ($data ['data']) {
			$buyVideos = D ( 'zyOrder' )->where ( "`uid`=" . $this->mid . " AND `is_del`=0" )->field ( 'video_id' )->select ();
			foreach ( $buyVideos as $key => &$val ) {
				$val = $val ['video_id'];
			}
			// 计算价格
			foreach ( $data ['data'] as $key => &$value ) {
				$value['mzprice'] = getPrice ( $value, $this->mid, true, true );
			}
			$this->assign ( 'buyVideos', $buyVideos );
			$vms = D ( 'ZyVideoMerge' )->getList ( $this->mid, session_id () );
			$this->assign ( 'vms', getSubByKey ( $vms, 'video_id' ) );
			$this->assign ( 'listData', $data ['data'] );
			$this->assign ( 'orderBy', $_GET ['orderBy'] ); // 定义排序
			$this->assign ( 'cateId', $_GET ['cateId'] ); // 定义分类
			$this->assign ( 'pType', $_GET ['pType'] ); // 定义收费类型
			$html = $this->fetch ( 'index_list' );
		} else {
			$html = '暂无此类课程';
		}
		
		$data ['data'] = $html;
		
		if ($return) {
			return $data;
		} else {
			echo json_encode ( $data );
			exit ();
		}
	}
	
	// 添加一个课程到课程列表
	public function addVideoMerge() {
		if (! $this->mid) {
			$this->mzError ( '需要先登录' );
		}
		$id = intval ( $_GET ['id'] );
		if (D ( 'zyOrder' )->where ( "`video_id`=$id AND `is_del`=0 AND `uid`=" . $this->mid )->count () > 0) {
			$this->mzError ( '你已经购买' );
		}
		if ($this->video->where ( "id={$id}" )->count () > 0) {
			if (D ( 'ZyVideoMerge' )->addVideo ( $id, $this->mid, session_id () )) {
				$this->ajaxReturn ( true, '', true );
			}
		}
		$this->ajaxReturn ( false, '', false );
	}
	
	// 删除一个课程从课程列表
	public function delVideoMerge() {
		$id = intval ( $_GET ['id'] );
		if (D ( 'zyOrder' )->where ( "`video_id`=$id AND `is_del`=0 AND `uid`=" . $this->mid )->count () > 0) {
			$this->mzError ( '你已经购买' );
		}
		if (D ( 'ZyVideoMerge' )->delVideo ( $id, $this->mid, session_id () )) {
			$this->ajaxReturn ( true, '', true );
		}
		$this->ajaxReturn ( false, '', false );
	}
	
	// 删除购物车中的课程
	public function delVideoMerges() {
		if (! $this->mid)
			$this->mzError ( '请先登录' );
		$map = array ();
		$map ['video_id'] = array (
				'IN',
				$_POST ['videoIds'] 
		);
		$map ['uid'] = array (
				'eq',
				$this->mid 
		);
		if (session_id ())
			$map ['tmp_id'] = session_id ();
		
		$rst = model ( 'ZyVideoMerge' )->where ( $map )->delete ();
		if ($rst !== false) {
			$this->ajaxReturn ( true, '', true );
		}
		$this->ajaxReturn ( false, '', false );
	}
	
	// 购物车
	public function merge() {
		if (! $this->mid) {
			$this->assign ( 'isAdmin', 1 );
			$this->error ( "请登录先，客官!" );
		}
		import ( session_id (), $this->mid );
		$merge_video_list ['data'] = D ( "ZyVideoMerge" )->getList ( $this->mid, session_id () );
		$merge_video_list ['total_price'] = 0;
		foreach ( $merge_video_list ['data'] as $key => $value ) {
			$merge_video_list ['data'] [$key] ['tlimit_state'] = 0; // 判断是否限时
			$merge_video_list ['data'] [$key] ['video_info'] = D ( "ZyVideo" )->getVideoById ( $value ['video_id'] );
			$merge_video_list ['data'] [$key] ['is_buy'] = D ( "ZyOrder" )->isBuyVideo ( $this->mid, $value ['video_id'] );
			$merge_video_list ['data'] [$key] ['price'] = getPrice ( $merge_video_list ['data'] [$key] ['video_info'], $this->mid );
			$merge_video_list ['total_price'] += $merge_video_list ['data'] [$key] ['is_buy'] ? 0 : round ( $merge_video_list ['data'] [$key] ['price'], 2 );
			$merge_video_list ['data'] [$key] ['legal'] = $merge_video_list ['data'] [$key] ['video_info'] ['uctime'] > time () ? 1 : 0;
			if ($merge_video_list ['data'] [$key] ['video_info'] ['is_tlimit'] == 1 && $merge_video_list ['data'] [$key] ['video_info'] ['starttime'] <= time () && $merge_video_list ['data'] [$key] ['video_info'] ['endtime'] >= time ()) {
				$merge_video_list ['data'] [$key] ['tlimit_state'] = 1;
			}
		}
		$user_info = D ( "ZyLearnc", "classroom" )->getUser ( $this->mid );
		$this->assign ( 'user_info', $user_info );
		$this->assign ( 'merge_video_list', $merge_video_list );
		$this->display ();
	}
	
	/**
	 * 批量购买课程
	 */
	public function buyVideos() {
		$post = $_POST;
		$price = intval ( $post ['price'] ); // 总价
		$vids = $post ['vids']; // 课程id
		$uid = $this->mid;
		if (empty ( $vids ))$this->error ( '请勾选要提交的课程' );
		$total_price = 0;
		$vidsnum = "";
		foreach ( $vids as $key => $val ) {
			$avideos [$val] = D ( "ZyVideo" )->getVideoById ( $val );
			$avideos [$val] ['price'] = getPrice ( $avideos [$val], $uid, true, true );
			$videodata = $videodata . D ( 'ZyVideo' )->getVideoTitleById ( $val ) . ",";
			$vidsnum = $vidsnum . $val . ",";
			// 价格为0的/限时免费的 不加入购物记录
			if ($avideos [$val] ['price'] ['price'] == 0) {
				unset ( $avideos [$val] );
				continue;
			}
			
			// 当购买过之后，或者课程的创建者是当前购买者的话，价格为0
			$avideos [$val] ['is_buy'] = D ( "ZyOrder" )->isBuyVideo ( $uid, $val );
			$total_price += ($avideos [$val] ['is_buy'] || $avideos [$val] ['uid'] == $uid) ? 0 : round ( $avideos [$val] ['price'] ['price'], 2 );
		}
		// 前台post的价格和后台计算的价格不相等，防止篡改价格
		if (bccomp ( $total_price, $price ) != 0) {
			$this->error ( '亲，可不要随便改价格哦，我们会发现的!' );
		}
		// 获取$uid的学币数量
		if (! D ( 'ZyLearnc' )->isSufficient ( $uid, $total_price, 'balance' )) {
			$this->error ( '可支配的学币不足' );
		}
		if (! D ( "ZyLearnc" )->consume ( $uid, $total_price )) {
			$this->error ( '合并付款失败，请稍后再试' );
		}
		// 添加消费记录
		D ( 'ZyLearnc' )->addFlows ( $this->mid, 0, $total_price, $avideos, 'zy_order_video' );
		// 添加每个课程的订单数量
		$vidsnum = trim ( $vidsnum, "," );
		$sql = "update `". C("DB_PREFIX") ."zy_video`  set video_order_count=video_order_count+1 where `id` in($vidsnum)";
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
		$rst = M ( 'zyVideoMerge' )->where ( $map )->delete ();
		if ($rst) {
			$s ['uid'] = $this->mid;
			$s ['is_read'] = 0;
			$s ['title'] = "恭喜您购买课程成功";
			$s ['body'] = "恭喜您成功购买如下课程：" . trim ( $videodata, "," );
			$s ['ctime'] = time ();
			model ( 'Notify' )->sendMessage ( $s );
			$this->success ( '购买成功' );
		} else {
			$this->error ( '购买失败' );
		}
	}
	
	/**
	 * 批量购买课程
	 */
	public function delVideos() {
		if (! $this->mid)
			$this->error ( '请先登录' );
		$map = array ();
		$post = $_POST;
		$map ['video_id'] = array (
				'IN',
				$post ['vids'] 
		);
		$map ['uid'] = array (
				'eq',
				$this->mid 
		);
		$rst = M ( 'zyVideoMerge' )->where ( $map )->delete ();
		$rst !== false ? $this->success ( '删除成功' ) : $this->error ( '删除失败' );
	}
	
	//
	public function doAddVideo() {
		$post = $_POST;
		if (empty ( $post ['video_id'] ))
			exit ( json_encode ( array (
					'status' => '0',
					'info' => '课程所包含的视频id有误' 
			) ) );
		if (empty ( $post ['video_title'] ))
			exit ( json_encode ( array (
					'status' => '0',
					'info' => '课程标题为空' 
			) ) );
		if (empty ( $post ['video_intro'] ))
			exit ( json_encode ( array (
					'status' => '0',
					'info' => '课程简介为空' 
			) ) );
		if (empty ( $post ['video_tag'] ))
			exit ( json_encode ( array (
					'status' => '0',
					'info' => '课程标签为空' 
			) ) );
		if (empty ( $post ['v_price'] )) {
			exit ( json_encode ( array (
					'status' => '0',
					'info' => '课程价格为空' 
			) ) );
		} else if (intval ( $post ['v_price'] ) > 1000 || intval ( $post ['v_price'] ) < 0) {
			exit ( json_encode ( array (
					'status' => '0',
					'info' => '课程价格不符合规定' 
			) ) );
		}
		if (empty ( $post ['cover'] ))
			exit ( json_encode ( array (
					'status' => '0',
					'info' => '请上传封面' 
			) ) );
		if (empty ( $post ['video_category'] ))
			exit ( json_encode ( array (
					'status' => '0',
					'info' => '课程分类不能为空' 
			) ) );
		if (empty ( $post ['uctime'] )) {
			exit ( json_encode ( array (
					'status' => '0',
					'info' => '下架时间不能为空' 
			) ) );
		} else if (strtotime ( $post ['uctime'] ) < time ()) {
			exit ( json_encode ( array (
					'status' => '0',
					'info' => '下架时间不能小于当前时间' 
			) ) );
		}
		$fullcategorypath = array ();
		$fullcategorypath = explode ( ',', $post ['video_category'] );
		$data ['fullcategorypath'] = t ( $post ['video_category'] );
		$category = array_pop ( $fullcategorypath );
		$category = $category == '0' ? array_pop ( $fullcategorypath ) : $category;
		$this->assign ( 'isAdmin', 1 );
		if (empty ( $category ))
			$this->error ( '您还没选择课程分类' );
		
		$video_tag = explode ( ',', $post ['video_tag'] );
		$data ['video_title'] = t ( $post ['video_title'] );
		$data ['video_intro'] = t ( $post ['video_intro'] );
		$data ['v_price'] 	  =  $post ['v_price'];
		$data ['cover'] 	  = intval ( $post ['cover'] );
		$data ['video_category'] = $category;
		$data ['videofile_ids'] = isset ( $post ['video_course_ids'] ) ? intval ( $post ['video_course_ids'] ) : 0; // 课件id
		$data ['listingtime']   = strtotime ( $post ['listingtime'] );
		$data ['uctime']        = strtotime ( $post ['uctime'] );
		$data ['uid'] 	        = $this->mid;
		$data ['ctime']         = time ();
		if ($post ['id']) {
			$result = M ( 'zy_video' )->where ( 'id=' . $post ['id'] )->data ( $data )->save ();
		} else {
			$result = M ( 'zy_video' )->data ( $data )->add ();
		}
		if ($result) {
			unset ( $data );
			if ($post ['id']) {
				model ( 'Tag' )->setAppName ( 'classroom' )->setAppTable ( 'zy_video' )->deleteSourceTag ( $post ['id'] );
				$tag_reslut = model ( 'Tag' )->setAppName ( 'classroom' )->setAppTable ( 'zy_video' )->addAppTags ( $post ['id'], $video_tag );
			} else {
				$tag_reslut = model ( 'Tag' )->setAppName ( 'classroom' )->setAppTable ( 'zy_video' )->addAppTags ( $result, $video_tag );
			}
			$tag_reslut = model ( 'Tag' )->setAppName ( 'classroom' )->setAppTable ( 'zy_video' )->addAppTags ( $result, $video_tag );
			$data ['str_tag'] = implode ( ',', getSubByKey ( $tag_reslut, 'name' ) );
			$data ['tag_id'] = ',' . implode ( ',', getSubByKey ( $tag_reslut, 'tag_id' ) ) . ',';
			$map ['id'] = $post ['id'] ? $post ['id'] : $result;
			M ( 'zy_video' )->where ( $map )->data ( $data )->save ();
			exit ( json_encode ( array (
					'status' => '1',
					'info' => '操作成功，等待审核' 
			) ) );
		} else {
			exit ( json_encode ( array (
					'status' => '0',
					'info' => '服务器繁忙，请稍后提交' 
			) ) );
		}
	}
	// 购买课程
	/*
	 * 1:可以直接观看，用户为管理员，限时免费,价格为0，已经购买过了
	 * 2:找不到课程
	 * 3:余额扣除失败，可能原因是余额不足
	 * 4:购买记录/订单，添加失败
	 */
	public function buyOperating() {
		if (! $this->mid)
			$this->mzError ( '请先登录!' );
		$vid = intval ( $_POST ['id'] );
		$i = D ( 'ZyService' )->buyVideo ( intval ( $this->mid ), $vid );
		if ($i === true) {
			// 记录购买的课程的ID
			session ( 'mzbugvideoid', $vid );
			$this->mzSuccess ( '购买成功', 'selfhref' );
		}
		if ($i === 1) {
			$this->mzError ( '该课程你不需要购买!' );
		} else if ($i === 2) {
			$this->mzError ( '找不到课程!' );
		} else if ($i === 3) {
			$this->mzError ( '余额不足!' );
		} else if ($i === 4) {
			$this->mzError ( '购买失败!' );
		}
	}
	/*
	 * 清除上一次购买的课程iD
	 */
	public function cleansession() {
		session ( 'mzbugvideoid', null );
		echo '';
		exit ();
	}








}