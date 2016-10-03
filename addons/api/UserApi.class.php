<?php
/**
 * 用户信息api
 * utime : 2016-03-06
 */
class UserApi extends Api{

	/**
	 * 按用户UID或昵称返回用户资料，同时也将返回用户的最新发布的微博
	 * 
	 */
	function show(){
		$this->user_id = empty($this->user_id) ? $this->mid : $this->user_id;
		//用户基本信息
		if(empty($this->user_id) && empty($this->user_name)){
			$this->exitJson( array() ,10003,"没有用户uid");
		}
		if(empty($this->user_id)){
			$data = model('User')->getUserInfoByName($this->user_name);	
			$this->user_id = $data['uid'];
		}else{
			$data = model('User')->getUserInfo($this->user_id);	
		}
		if(empty($data)){
            $this->exitJson( array() ,10004,"用户不存在");
		}
		$data['sex'] = $data['sex'] ==1 ? '男':'女';
		
		$data['profile'] = model('UserProfile')->getUserProfileForApi($this->user_id);

		$profileHash = model('UserProfile')->getUserProfileSetting();
		$data['profile']['email'] = array('name'=>'邮箱','value'=>$data['email']);
		foreach(UserProfileModel::$sysProfile as $k){
			if(!isset($data['profile'][$k])){
				$data['profile'][$k] = array('name'=>$profileHash[$k]['field_name'],'value'=>'');
			}
		}
		//用户统计信息
		$defaultCount =  array('following_count'=>0,'follower_count'=>0,'feed_count'=>0,'favorite_count'=>0,'unread_atme'=>0,'weibo_count'=>0);
		$count   = model('UserData')->getUserData($this->user_id);
		if(empty($count)){
			$count = array();	
		}
		$data['count_info'] = array_merge($defaultCount,$count);
		//用户标签
		$data['user_tag'] = model('Tag')->setAppName('User')->setAppTable('user')->getAppTags($this->user_id);
		$data['user_tag'] = empty($data['user_tag']) ? '' : implode('、',$data['user_tag']);
		//关注情况
		$followState  = model('Follow')->getFollowState($this->mid,$this->user_id); 
		$data['follow_state'] = $followState;
		//最后一条微博
		$lastFeed = model('Feed')->getLastFeed($this->user_id);
		$data['last_feed'] = $lastFeed;
		// 判断用户是否被登录用户收藏通讯录
		$data['isMyContact'] = 0;
		if($this->user_id != $this->mid) {
			$cmap['uid'] = $this->mid;
			$cmap['contact_uid'] = $this->user_id;
			$myCount = D('Contact', 'contact')->where($cmap)->count();
			if($myCount == 1) {
				$data['isMyContact'] = 1;
			}
		}
        $this->exitJson($data);
	}
		
	/**
	 * 上传头像 API
	 * 传入的头像变量 $_FILES['filedata']
	 */
	function upload_face(){
		$_FILES['Filedata'] = $_FILES['filedata'] = $_FILES['face'];
        $dAvatar = model('Avatar');
        $dAvatar->init($this->mid); // 初始化Model用户id
        $res = $dAvatar->upload(true);
        if($res['status'] == 1){
            $data['picurl'] = $res['data']['picurl'];
            $data['picwidth'] = $res['data']['picwidth'];
            $scaling = 5;
            $data['w'] = $res['data']['picwidth'] * $scaling;
            $data['h'] = $res['data']['picheight'] * $scaling;
            $data['x1'] = $data['y1'] = 0;
            $data['x2'] = $data['w'];
            $data['y2'] = $data['h'];
            $r = $dAvatar->dosave($data);
            unset($r['status']);
            $r=$r['data'];
            $this->exitJson($r);
        }else{
            $this->exitJson( array() ,10008,'不是有效的图片格式，请重新选择照片上传');
        }
	}

	/**
	 *	关注一个用户
	 */
	public function follow_create(){
		$user_id = intval($this->data["user_id"]);
		if(empty($this->mid) || empty($user_id)){
			$this->exitJson( array() ,10008,'关注失败!');
		}
		if( $this->mid == intval($this->data["user_id"]) ) $this->exitJson( array() ,10008,'自己不能关注自己!');
		$r = model('Follow')->doFollow($this->mid,$this->data["user_id"]);
		if($r){
			$res=model('Follow')->getFollowState($this->mid,$this->user_id);
			if($res){
				$this->exitJson($res);
			}else{
				$this->exitJson( array() ,10008,'关注失败!');
			}
		}else{
			$this->exitJson( array() ,10008,'关注失败!');
		}
	}

	/**
	 * 取消关注
	 */
	public function follow_destroy(){
		if(empty($this->mid) || empty($this->user_id)){
			$this->exitJson( array() );
		}
		
		$r = model('Follow')->unFollow($this->mid,$this->user_id);
		if(!$r){
            $this->exitJson( model('Follow')->getFollowState($this->mid,$this->user_id));
		}
        $this->exitJson($r);
	}

	/**
	 * 用户粉丝列表
	 */
	public function user_followers(){
		$this->user_id = empty($this->user_id) ? $this->mid : $this->user_id;
		// 清空新粉丝提醒数字
		if($this->user_id == $this->mid){
			$udata = model('UserData')->getUserData($this->mid);
			$udata['new_folower_count'] > 0 && model('UserData')->setKeyValue($this->mid,'new_folower_count',0);	
		}
		$res=model('Follow')->getFollowerListForApi($this->mid,$this->user_id,$this->since_id,$this->max_id,$this->count,$this->page);
		if($res){
        	$this->exitJson($res);
        }else{
        	 $this->exitJson( array() ,10016,'你还没有用户粉丝!');
        }
	}

	/**
	 * 获取用户关注的人列表
 	 */
	public function user_following(){
		$this->user_id = empty($this->user_id) ? $this->mid : $this->user_id;
		$res=model('Follow')->getFollowingListForApi($this->mid,$this->user_id,$this->since_id,$this->max_id,$this->count,$this->page);
        if($res){
        	$this->exitJson($res);
        }else{
        	 $this->exitJson( array() ,10016,'没有关注我的人!');
        }
	}

	/**
	 * 获取用户的朋友列表
	 * 
	 */
	public function user_friends(){
		$this->user_id = empty($this->user_id) ? $this->mid : $this->user_id;
		return model('Follow')->getFriendsForApi($this->mid, $this->user_id, $this->since_id, $this->max_id, $this->count, $this->page);
	}

	// 按名字搜索用户
	public function wap_search_user(){
		$key = t($this->data['key']);
		$map['uname'] = array('LIKE',$key);
		$userlist = M('user')->where($map)->findAll();
		return $userlist;
	}

	/**
	 * 获取用户相关信息
	 * @param array $uids 用户ID数组
	 * @return array 用户相关数组
	 */
	public function getUserInfos($uids, $data, $type = 'basic')
	{
		// 获取用户基本信息
		$userInfos = model('User')->getUserInfoByUids($uids);
		$userDataInfo = model('UserData')->getUserKeyDataByUids('follower_count',$uids);

		if($type=='all'){
			// 获取关注信息
			$followStatusInfo = model('Follow')->getFollowStateByFids($GLOBALS['ts']['mid'], $uids);
			// 获取用户组信息
			$userGroupInfo = model('UserGroupLink')->getUserGroupData($uids);
		}

		// 组装数据
		foreach($data as &$value) {
			$value = array_merge($value, $userInfos[$value['uid']]);
			$value['user_data'] = $userDataInfo[$value['uid']];
			if($type=='all'){	
				$value['follow_state'] = $followStatusInfo[$value['uid']];
				$value['user_group'] = $userGroupInfo[$value['uid']];
			}
		}
	
		return $data;
	}
	// 按标签搜索用户
	public function search_by_tag()
	{
		$limit = 20;
		$this->data['limit'] && $limit = intval( $this->data['limit'] );
		$tagid = intval ( $this->data['tagid'] );
		if ( !$tagid ){
			return 0;
		}
		$data = model('UserCategory')->getUidsByCid($tagid,  array()  ,$limit);
		$data['data'] = $this->getUserInfos ( getSubByKey( $data['data'] , 'uid' ) , $data['data'],'basic');
		return $data['data'] ? $data : 0;
	}

	// 按地区搜索用户
	public function search_by_area($value='')
	{
		$this->data['p'] = $this->data['page'] = $this->page;
		$limit = 20;
		$this->data['limit'] && $limit = intval( $this->data['limit'] );
		$areaid = intval ( $this->data['areaid'] );
		if ( !$areaid && $this->data['areaname']){
			$amap['title'] = t( $this->data['areaname'] );
			$areaid = D('area')->where($amap)->getField('area_id');
		}
		if ( !$areaid ){
			return 0;
		}
		
		$pid1 = model('Area')->where('area_id='.$areaid)->getField('pid');
		$level = 1;
		if($pid1 != 0){
			$level = $level +1;
			$pid2 = model('Area')->where('area_id='.$pid1)->getField('pid');
			if($pid2 != 0){
				$level = $level +1;
			}
		}
		switch ($level) {
			case 1:
				$map['province'] = $areaid;
				break;
			case 2:
				$map['city'] = $areaid;
				break;
			case 3:
				$map['area'] = $areaid;
				break;
		}
		
		$map['is_del'] = 0;
		$map['is_active'] = 1;
		$map['is_audit'] = 1;
		$map['is_init'] = 1;
		
		$data = D('user')->where($map)->field('uid')->order("uid desc")->findPage($limit);
		$data['data'] = $this->getUserInfos ( getSubByKey( $data['data'] , 'uid' ) , $data['data'],'basic');
		
		return $data['data'] ? $data : 0;
	}

	// 按认证分类搜索用户
	public function search_by_verify_category($value='')
	{
		$limit = 20;
		$this->data['limit'] && $limit = intval( $this->data['limit'] );
		$verifyid = intval ( $this->data['verifyid'] );
		if ( !$verifyid && $this->data['verifyname']){
			$amap['title'] = t( $this->data['verifyname'] );
			$verifyid = D('user_verify_category')->where($amap)->getField('user_verified_category_id');
		}
		if ( !$verifyid ){
			return 0;
		}
		$maps['user_verified_category_id'] = $verifyid;
		$maps['verified'] = 1;
		$data = D('user_verified')->where($maps)->field('uid, info AS verify_info')->findPage($limit);
		$data['data'] = $this->getUserInfos ( getSubByKey( $data['data'] , 'uid' ) , $data['data'],'basic');
		return $data['data'] ? $data : 0;
	}

	// 按官方推荐分类搜索用户
	public function search_by_uesr_category($value='')
	{
		$limit = 20;
		$this->data['limit'] && $limit = intval( $this->data['limit'] );
		$cateid = intval ( $this->data['cateid'] );
		if ( !$cateid && $this->data['catename']){
			$amap['title'] = t( $this->data['catename'] );
			$cateid = D('user_official_category')->where($amap)->getField('user_official_category_id');
		}
		if ( !$cateid ){
			return 0;
		}
	 	$maps['user_official_category_id'] = $cateid;
		$data = D('user_official')->where($maps)->field('uid, info AS verify_info')->findPage($limit);
		$data['data'] = $this->getUserInfos ( getSubByKey( $data['data'] , 'uid' ) , $data['data'],'basic');
		return $data['data'] ? $data : 0;
	} 

	public function get_user_category()
	{
		$type = t ( $this->data['type'] );
		switch ($type) {
			//地区分类 最多只列出二级
			case 'area':
				$category = model('CategoryTree')->setTable('area')->getNetworkList();
				break;

			//认证分类 最多只列出二级
			case 'verify_category':
				$category = model('UserGroup')->where('is_authenticate=1')->findAll();
				foreach($category as $k=>$v){
					$category[$k]['child'] = D('user_verified_category')->where('pid='.$v['user_group_id'])->findAll();
				}
				break;

			//推荐分类 最多只列出二级
			case 'user_category':
				$category = model('CategoryTree')->setTable('user_official_category')->getNetworkList();
				break;

			//标签 tag 最多只列出二级
			default:
				$category = model('UserCategory')->getNetworkList();
				break;
		}
		return $category;
	}
	/**
	 * 粉丝最多
	 * @return Ambigous <number, 返回新的一维数组, boolean, multitype:Ambigous <array, string> >
	 */
	public function get_user_follower(){
		$limit = 20;
		$this->data['limit'] && $limit = intval( $this->data['limit'] );
		$page = $this->data['page'] ? intval($this->data['page']) : 1;
		$limit = ($page - 1) * $limit.', '.$limit;
		
		$followermap['key'] = 'follower_count';
		$followeruids = model('UserData')->where($followermap)->field('uid')->order('`value`+0 desc,uid')->limit($limit)->findAll();
		$followeruids = $this->getUserInfos ( getSubByKey( $followeruids , 'uid' ) , $followeruids,'basic');
		return $followeruids ? $followeruids : 0;
	}

	// 按地理位置搜索邻居
	public function neighbors(){
		//经度latitude 
		//纬度longitude
		//距离distance
		$latitude = floatval ( $this->data['latitude'] );
		$longitude = floatval( $this->data['longitude'] );
		//根据经度、纬度查询周边用户 1度是 111 公里
		//根据ts_mobile_user 表查找，经度和纬度在一个范围内。  
		//latitude < ($latitude + 1) AND latitude > ($latitude - 1)
		//longitude < ($longitude + 1) AND longitude > ($longitude - 1)
		$limit = 20;
		$this->data['limit'] && $limit = intval( $this->data['limit'] );
		$map['last_latitude'] = array( 'between' , ($latitude - 1).','.($latitude + 1) );
		$map['last_longitude'] = array( 'between' , ($longitude - 1).','.($longitude + 1) );
		
		$data = D('mobile_user')->where($map)->field('uid')->findpage($limit);
		$data['data'] = $this->getUserInfos ( getSubByKey( $data['data'] , 'uid' ) , $data['data'],'basic');
		return $data['data'] ? $data : 0;
	}

	// 记录用户的最后活动位置
	public function checkin(){
		$latitude = floatval ( $this->data['latitude'] );
		$longitude = floatval ( $this->data['longitude'] );
		//记录用户的UID、经度、纬度、checkin_time、checkin_count
		//如果没有记录则写入，如果有记录则更新传过来的字段包括：sex\nickname\infomation（用于对周边人进行搜索）
		$checkin_count = D('mobile_user')->where('uid='.$this->mid)->getField('checkin_count');
		$data['last_latitude'] = $latitude;
		$data['last_longitude'] = $longitude;
		$data['last_checkin'] = time();
		
		if ( $checkin_count ){
			$data['checkin_count'] = $checkin_count + 1;
			$res = D('mobile_user')->where('uid='.$this->mid)->save($data);
		} else {
			
			$user = model('User')->where('uid='.$this->mid)->field('uname,intro,sex')->find();
			$data['nickname'] = $user['uname'];
			$data['infomation'] = $user['intro'];
			$data['sex'] = $user['sex'];
			
			$data['checkin_count'] = 1;
			$data['uid'] = $this->mid;
			$res = D('mobile_user')->add($data);
		}
		return $res ? 1 : 0;
	}
	
	
	/**
	 * 修改登录用户帐号密码操作
	 * @return json 返回操作后的JSON信息数据
	 */
    public function doModifyPassword() {
    	$_POST['oldpassword'] = t($this->data['oldpassword']);//原密码
    	$_POST['password'] = t($this->data['password']);//新密码
    	$_POST['repassword'] = t($this->data['repassword']);//确认新密码
    	// 验证信息
    	if ($_POST['oldpassword'] === '') {
    		$this->exitJson( array() ,10026,'请填写原始密码');
    	}
    	if ($_POST['password'] === '') {
    		$this->exitJson( array() ,10026,'请填写新密码');
    	}
    	if ($_POST['repassword'] === '') {
    		$this->exitJson( array() ,10026,'请填写确认密码');
    	}
    	if($_POST['password'] != $_POST['repassword']) {
    		$this->exitJson( array() ,10026,L('PUBLIC_PASSWORD_UNSIMILAR'));			// 新密码与确认密码不一致
    	}
    	if(strlen($_POST['password']) < 6) {
			$this->exitJson( array() ,10026,'密码太短了，最少6位');				
		}
		if(strlen($_POST['password']) > 15) {
			$this->exitJson( array() ,10026,'密码太长了，最多15位');				
		}
		if($_POST['password'] == $_POST['oldpassword']) {
			$this->exitJson( array() ,10026,L('PUBLIC_PASSWORD_SAME'));				// 新密码与旧密码相同
		}

    	$user_model = model('User');
    	$map['uid'] = $this->mid;
    	$user_info = $user_model->where($map)->find();

    	if($user_info['password'] == $user_model->encryptPassword($_POST['oldpassword'], $user_info['login_salt'])) {
			$data['login_salt'] = rand(11111, 99999);
			$data['password'] = $user_model->encryptPassword($_POST['password'], $data['login_salt']);
			$res = $user_model->where("`uid`={$this->mid}")->save($data);
			if($res){
				$this->exitJson(true);
			}else{
				$this->exitJson( array() ,10026,L('PUBLIC_PASSWORD_MODIFY_FAIL'));	
			}
    	} else {
    		$this->exitJson( array() ,10026,L('PUBLIC_ORIGINAL_PASSWORD_ERROR'));			// 原始密码错误
    	}
    }

     //我的银行卡方法
    public function card(){
        $userAuthInfo = D('user_verified')->where('verified=1 AND uid=' .$this->mid)->find();
//         if(!$userAuthInfo){
//            $this->exitJson( array() ,10027,'请先进行认证！');
//         }
        $data = D('ZyBcard','classroom')->getUserOnly($this->mid);
        $this->exitJson($data);
    }
    
    //添加新的银行卡界面
	public function addCard(){
        $data["bank"] = array(
	        '中国银行',
	        '中国工商银行',
	        '中国农业银行',
	        '中国建设银行',
	        '交通银行',
	        '招商银行',
	        '民生银行',
	        '中信银行',
	        '北京银行',
	        '广东发展银行',
	        '上海浦东发展银行',
	        '中国邮政储蓄银行',
	    );
        $data["area"] = M("area")->findAll();
        $this->exitJson($data);
	}
	
	//添加银行卡方法
	public function saveCard(){
        $id=intval($this->data['id']);
		$account=$this->data['account'];//获取银行卡号
		$accountmaster=$this->data['accountmaster'];//获取姓名
		$accounttype=$this->data['accounttype'];//获取账号类型
		$bankofdeposit=$this->data['bankofdeposit'];//开户行地址
		$location=$this->data['location'];//省市区名称
		$province=$this->data['province'];//省ID
		$city=$this->data['city'];//市ID
		$area=$this->data['area'];//区ID
		$tel_num=$this->data['tel_num'];//获取银行预留手机号
		$userAuthInfo = D('user_verified')->where('verified=1 AND uid=' .$this->mid)->find();;
//         if(!$userAuthInfo){
//            $this->exitJson( array() ,10028,'请先进行认证！');
//         }
        if($account=="")$this->exitJson( array() ,10028,'请输入账号！');
		if($accountmaster=="")$this->exitJson( array() ,10028,'请输入开户姓名！');
		if(strlen($account)<16)$this->exitJson( array() ,10028,'银行卡号不合法！');
		if($accounttype=="")$this->exitJson( array() ,10028,'银行卡类型不合法！');
		if(empty($id)){
			$res=M("zy_bcard")->where("account=".$account." and id !=".$id)->select();
			if($res)$this->exitJson( array() ,10028,'该账号已存在,请重新输入！');
		}
		if(!preg_match("/^1[34578]\d{9}$/", $tel_num)){
			$this->exitJson( array() ,10028,'手机号不合法！');
		}
		$data=array(
		'account'=>$account,
		'accountmaster'=>$accountmaster,
		'accounttype'=>$accounttype,
		'bankofdeposit'=>$bankofdeposit,
		'location'=>$location,
		'province'=>$province,
		'city'=>$city,
		'area'=>$area,
		'tel_num'=>$tel_num,
        'uid'=>$this->mid
		);
        if($id){
            $res=M('ZyBcard')->where(array('id'=>$id,'uid'=>$this->mid))->save($data);
        }else{
            $res=M('ZyBcard')->add($data);
        }
		if($res){
			$this->exitJson(true);
		}
		$this->exitJson( array() ,10028,'对不起，添加失败！');
	}
	
	//充值
	function pay(){
		//充值  
		$money = floatval($this->data['money']);
		if($money <= 0) $this->exitJson( array() ,10030,'充值金额不正确');
		$re = D('ZyRecharge','classroom');
		$le = D('ZyLearnc','classroom');
		//添加充值记录 
		$id = $re->addRechange(array(
				'uid'      => $this->mid,
				'type'     => '0',
				'money'    => $money,
				'note'     => "Eduline-学币充值-{$money}元",
				'pay_type' => $this->data['pay_type'],
		));
		//增加金币
		$id_add = $le->recharge($this->mid,$money);
		if($id && $id_add) {
			$re->setSuccess( $id, t($this->data['pay_order']) );
			$this->exitJson($id,1,'充值成功');
		} else {
			$this->exitJson( array() ,10040,'对不起，充值失败');
		}
	}
	
	//提现
	public function withdraw(){
		$card = D('ZyBcard','classroom')->getUserOnly($this->mid);
		if(!$card){
			$this->exitJson( array() ,10037,'请先绑定银行卡');
		}
		$num = intval($this->data['money']);
		$result = D('ZyService','classroom')->applyWithdraw( $this->mid, $num, $card['id'] );
		if(true === $result){
			$this->exitJson(true,10038,'申请提现成功');
		}else{
			$this->exitJson( array() ,10039,'申请提现失败');
		}
	}
	
	
    //修改用户资料
    public function saveUserInfo(){
        $uname=t($this->data['uname']);
        $sex=intval($this->data['sex'])?intval($this->data['sex']):1;
        $intro=t($this->data['intro']);
        if($uname=="")$this->exitJson( array() ,10029,'请输入用户名');
        if(strlen($uname)<4)$this->exitJson( array() ,10029,'对不起，用户名不合法！');
        $res=M('user')->where(array('uname'=>$uname,array('uid'=>array('neq',$this->mid))))->find();
        if($res)$this->exitJson( array() ,10029,'对不起，此用户名已被使用！');
        $map['uname']=$uname;
        $map['sex']=$sex;
        $map['intro']=$intro;
        $id=M('user')->where(array('uid'=>$this->mid))->save($map);
        if($id !== false) {
        	model('User')->cleanCache($this->mid);
        	$this->exitJson(true);
        } else {
        	$this->exitJson( array() ,10029,'对不起，用户资料修改失败！');
        }
    }

    //用户统计接口
    public  function userContent(){
        //获取用户id
        $userid=intval($this->data['uid'])?intval($this->data['uid']):$this->mid;
        //获取我购买的课程总数
        $data['videocont']=D("ZyOrder",'classroom')->where(array('uid'=>$this->mid,'is_del'=>0))->count();//加载我购买的课程总数
        $data['wdcont']=M('ZyWenda')->where(array('uid'=>$this->mid,'is_del'=>0))->count();//加载我的问答数量
        $data['note']=M('ZyNote')->where(array('uid'=>$this->mid))->count();//加载笔记总数
        $data['follow']=M('UserFollow')->where(array('uid'=>$userid))->count();//加载关注数量
        $data['fans']=M('UserFollow')->where(array('fid'=>$userid))->count();//加载关注数量
		$data['collect_video']=M('zy_collection')->where(array('uid'=>$userid,'source_table_name'=>'zy_video'))->count();//我收藏的课程
		$data['collect_album']=M('zy_collection')->where(array('uid'=>$userid,'source_table_name'=>'zy_album'))->count();//我收藏的专辑
		$data['card'] = M('zy_bcard')->where('uid=' .$this->mid)->count();//我的银行卡
		$intr = M('user')->where('uid=' .$this->mid)->getField('intro');//我的简介
		$data['intr'] = $intr ? $intr : '你什么都还没说哦';
		$data['balance'] = intval ( M('zy_learncoin')->where('uid=' .$this->mid)->getField('balance') );//我的余额
		//未读评论数量
		$data['no_read_comment'] = M('ZyComment')->where( array('fid'=>$this->mid,'is_del'=>0,'is_read'=>0) )->count();
		//未读系统消息数量
		$data['no_read_notify']  = M('notify_message')->where( array('uid'=>$this->mid,'is_read'=>0) )->count();
		//未读私信数量
		$data['no_read_message'] = intval ( M('message_member')->where( array('member_uid'=>$this->mid,'is_del'=>0) )->getField('new') );
		
		
        if($data){
        	$this->exitJson($data);
        }else{
        	$this->exitJson( array() ,10029,'没有获取到相应的数据');
        }
    }
    //获取最近访客
    public  function getUserVisitor(){
        $this->mid = intval($this->data['uid']) ? intval($this->data['uid']) : $this->mid;
        $data=M('ZyUserVisitor')->where(array('fuid'=>$this->mid))->limit(5)->select();
        foreach($data as &$val){
            $val['userinfo']=model('User')->getUserInfo($val['uid']);
        }
        $this->exitJson($data);
    }


	
}