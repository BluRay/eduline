<?php
tsload(APPS_PATH.'/classroom/Lib/Action/CommonAction.class.php');
require_once './api/qiniu/rs.php';
class UserAction extends CommonAction{
	/**
    * 初始化
    * @return void
    */
    public function _initialize() {
        parent::_initialize();
    }
    public function index(){
        //目前没有首页，此处可跳转至指定页面
        $this->redirect('classroom/Home/video', $params);
    }
    public function recharge(){
        $learnc = D('ZyLearnc');
        $data = $learnc->getUser($this->mid);
        if($data['vip_type'] > 0){
            if($data['vip_expire'] < time()){
                $learnc->cleanExpireVip();
                $data['vip_type'] = 0;
                $data['time']     = 0;
            }
        }
        $vipPrice = getAppConfig('vip_price');
        $vipYearPrice = getAppConfig('vip_year_price');
        $this->assign('vipPrice', array(
            $vipYearPrice,
            $vipPrice*12,
            $vipPrice,
            round($vipYearPrice/12, 1),
        ));
        $this->assign('learnc', $data);
        $this->display();
    }
    //用户账户管理
    public function account(){

        $this->assign('userLearnc', D('ZyLearnc')->getUser($this->mid));

        //选择模版
        $tab = intval($_GET['tab']);
        $tpls = array('index','income','pay','take_list','take','recharge');
        if(!isset($tpls[$tab])) $tab = 0;
        $method = 'account_'.$tpls[$tab];
        if(method_exists($this, $method)){
            $this->$method();
        }
        $this->assign('tab', $tab);
        $this->display('account/'.$tpls[$tab]);
    }
    //充值记录
    protected function account_recharge(){
    
    	$map = array('uid'=>$this->mid);  //获取用户id
    
    	$st = strtotime($_GET['st'])+0;
    	$et = strtotime($_GET['et'])+0;
    	if(!$st) $_GET['st'] = '';
    	if(!$et) $_GET['et'] = '';
    
    	if($_GET['st']){
    		$map['ctime'][] = array('gt', $st);
    	}
    	if($_GET['et']){
    		$map['ctime'][] = array('lt', $et);
    	}
    	$map['status'] = array('gt',0);
    	$data = D('ZyRecharge')->where($map)->order('stime DESC,id DESC')->findPage(12);
    	$total= D('ZyRecharge')->where($map)->sum('money');
    	$this->assign('data', $data);
    	$this->assign('total', $total);
    }
    //营收记录
    protected function account_income(){

        $map = array('muid'=>$this->mid);

        $st = strtotime($_GET['st'])+0;
        $et = strtotime($_GET['et'])+0;
        if(!$st) $_GET['st'] = '';
        if(!$et) $_GET['et'] = '';

        if($_GET['st']){
            $map['ctime'][] = array('gt', $st);
        }
        if($_GET['et']){
            $map['ctime'][] = array('lt', $et);
        }
        $data = D('ZyOrder')->where($map)->order('ctime DESC,id DESC')->findPage(12);
        $total= D('ZyOrder')->where(array('muid'=>$this->mid))->sum('user_num');
        $this->assign('data', $data);
        $this->assign('total', $total);
    }

    //支付记录
    protected function account_pay(){

        $map = array('uid'=>$this->mid);

        $st = strtotime($_GET['st'])+0;
        $et = strtotime($_GET['et'])+0;
        if(!$st) $_GET['st'] = '';
        if(!$et) $_GET['et'] = '';

        if($_GET['st']){
            $map['ctime'][] = array('gt', $st);
        }
        if($_GET['et']){
            $map['ctime'][] = array('lt', $et);
        }
        $data = D('ZyOrder')->where($map)->order('ctime DESC,id DESC')->findPage(12);
        $total= D('ZyOrder')->where(array('uid'=>$this->mid))->sum('price');
        $this->assign('data', $data);
        $this->assign('total', $total);
    }
    //申请提现页面
    protected function account_take(){
        $card = D('ZyBcard')->getUserOnly($this->mid);
        if(!$card){
            $this->assign('isAdmin', 1);
            $this->assign('jumpUrl', U('classroom/User/card'));
            $this->error('请先绑定银行卡！'); exit;
        }
        //申请提现
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $num = intval($_POST['num']);
            $result = D('ZyService')->applyWithdraw(
                      $this->mid, $num, $card['id']);
            if(true === $result){
                $this->ajaxReturn(null, '', true);
            }else{
                $this->ajaxReturn(null, $result, false);
            }
            exit;
        }
        $ZyWithdraw = D('ZyWithdraw');
        $data = $ZyWithdraw->getUnfinished($this->mid);
        //读取系统配置的客服电话
        $tel = M('system_data')->where("`list`='admin_config' AND `key`='site'")->field('value')->find();
        $system_config = unserialize($tel['value']);
        $this->assign('sys_tel',$system_config['sys_tel']);
        $this->assign('data', $data);
    }

    //申请提现列表页面
    protected function account_take_list(){
        if(!empty($_GET['id'])){
            $id = intval($_GET['id']);
            $result = D('ZyService')->setWithdrawStatus($id, $this->mid, 4);
            if(true === $result){
                $this->ajaxReturn(null, null, true);
            }else{
                $this->ajaxReturn(null, $result, false);
            }
            exit;
        }

        $map = array('uid'=>$this->mid);

        $st = strtotime($_GET['st'])+0;
        $et = strtotime($_GET['et'])+0;
        if(!$st) $_GET['st'] = '';
        if(!$et) $_GET['et'] = '';


        if($_GET['st']){
            $map['ctime'][] = array('gt', $st);
        }
        if($_GET['et']){
            $map['ctime'][] = array('lt', $et);
        }

        $data = D('ZyWithdraw')->order('ctime DESC, id DESC')
                ->where($map)->findPage(12);

        $total= D('ZyWithdraw')->where(array('uid'=>$this->mid,
                 'status'=>2))->sum('wnum');
        $this->assign('data', $data);
        $this->assign('total', $total);
    }

    //银行卡管理方法
    public function card(){
        $data = D('ZyBcard')->getUserOnly($this->mid);
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $set['uid'] = $this->mid;
            $set['account'] = t($_POST['account']);
            $set['accountmaster'] = t($_POST['accountmaster']);
            $set['accounttype'] = t($_POST['accounttype']);
            $set['bankofdeposit'] = t($_POST['bankofdeposit']);
            $set['tel_num'] = t($_POST['tel_num']);
            $set['location'] = t($_POST['city_names']);
            $set['province'] = intval($_POST['province']);
            $set['area'] = intval($_POST['area']);
            $set['city'] = intval($_POST['city']);
            if($data){
                $set['id'] = $data['id'];
                if(false !== D('ZyBcard')->save($set)){
                    $this->ajaxReturn(null, '', true);
                }else{
                    $this->ajaxReturn(null, '', false);
                }
            }else{
                if(D('ZyBcard')->add($set) > 0){
                    $this->ajaxReturn(null, '', true);
                }else{
                    $this->ajaxReturn(null, '', false);
                }
            }
            exit;
        }
        $this->assign('isEditCard', !$data || $_GET['edit']=='yes');
        if(!$data){
            $array = array(
                'account'  => '',
                'tel_num'  => '',
                'location' => '',
                'province' => 0,
                'city'     => 0,
                'area'     => 0,
                'accountmaster' => '',
                'accounttype'   => '',
                'bankofdeposit' => '',
            );
        }
        $this->assign('data', $data);
        $this->assign('banks', D('ZyBcard')->getBanks());
        $this->display();
    }
    
    /**
     * 上传视频页面
     * Enter description here ...
     */
    public function upload(){
    	
    	if(!getUserAuthInfo($this->mid)){
			$this->assign('isAdmin',1);
    		$this->assign('jumpUrl',U('classroom/User/setInfo',array('tab'=>'3')));
    		$this->error('您还没有认证，即将跳转到认证页面!');
    	} 
    	$this->display();
    }
    
    public function upload_z(){
//    	if(!getUserAuthInfo($this->mid)){
//			$this->assign('isAdmin',1);
//    		$this->assign('jumpUrl',U('classroom/User/setInfo',array('tab'=>'3')));
//    		$this->error('您还没有认证，即将跳转到认证页面!');
//    	}
    	if($_GET['id']){
			$data = D('ZyVideo','classroom')->getVideoById(intval($_GET['id']));
			if(!$data['is_activity']){
				$this->assign('isAdmin',1);
				$this->error("课程审核中，不能修改!");
			}
			$this->assign($data);
		}
		//print_r($_GET['id']);
    	$this->display();
    }


    //用户设置
    public function setInfo(){
        //用户信息
        $this->setUser();
        //认证
        $this->rz();
        //帐号绑定
        $bindData = array();
        Addons::hook('account_bind_after',array('bindInfo'=>&$bindData));
        $bindType = array();
        foreach($bindData as $k=>$rs) $bindType[$rs['type']] = $k;
        $verified_category=M("user_verified_category")->where("pid=3")->field("title,user_verified_category_id")->select();
        $this->assign("verified_category",$verified_category);
        $data['bindType']  = $bindType;
        $data['bindData']  = $bindData;
        $this->assign($data);
        $this->display();
    }

    public function saveUser(){
        //简介
        $save['intro'] = t($_POST['intro']);
        //性别
        $save['sex']   = 1 == intval($_POST['sex']) ? 1 : 2;
        //位置信息
        $save['location'] = t($_POST['city_names']);
		//职业
		$save['profession'] = t($_POST['profession']);
        //地区
        $cityIds = t($_POST['city_ids']);
        $cityIds = explode(',', $cityIds);
       
				$this->assign('isAdmin',1);
        if(!$cityIds[0] || !$cityIds[1]) $this->error('请选择完整地区');
        isset($cityIds[0]) && $save['province'] = intval($cityIds[0]);
        isset($cityIds[1]) && $save['city'] = intval($cityIds[1]);
        isset($cityIds[2]) && $save['area'] = intval($cityIds[2]);
        //昵称
        $user = $this->get('user');
        $uname = t($_POST['uname']);
        $oldName = t($user['uname']);
        $save['uname'] = filter_keyword($uname);
        $res = model('Register')->isValidName($uname, $oldName);
        if(!$res) {
            $error = model('Register')->getLastError();
            return $this->ajaxReturn(null, model('Register')->getLastError(), $res);
        }
        //如果包含中文将中文翻译成拼音
        if ( preg_match('/[\x7f-\xff]+/', $save['uname'] ) ){
            //昵称和呢称拼音保存到搜索字段
            $save['search_key'] = $save['uname'].' '.model('PinYin')->Pinyin( $save['uname'] );
        } else {
            $save['search_key'] = $save['uname'];
        }
        $res = model('User')->where("`uid`={$this->mid}")->save($save);
        $res && model('User')->cleanCache($this->mid);
        $user_feeds = model('Feed')->where('uid='.$this->mid)->field('feed_id')->findAll();
        if($user_feeds){
            $feed_ids = getSubByKey($user_feeds, 'feed_id');
            model('Feed')->cleanCache($feed_ids,$this->mid);
        }
        $this->ajaxReturn(null, '', true);
    }

    protected function setUser(){
        $user = $this->get('user');
        $my_college = D('ZySchoolCategory')->getParentIdList($user['my_college']);
        $signup_college = D('ZySchoolCategory')->getParentIdList($user['signup_college']);
        $this->assign('my_college', $my_college?$my_college:'');
        $this->assign('signup_college', $signup_college?$signup_college:'');
    }

    //用户认证
    protected function rz(){
        $auType = model('UserGroup')->where('is_authenticate=1')->findall();
        $this->assign('auType', $auType);
        $verifyInfo = D('user_verified')->where('uid='.$this->mid)->find();
        if($verifyInfo['attach_id']){
              $a = explode('|', $verifyInfo['attach_id']);
              foreach($a as $key=>$val){
                if($val !== "") {
                    $attachInfo = D('attach')->where("attach_id=$a[$key]")->find();
                    $verifyInfo['attachment'] .= $attachInfo['name'].'&nbsp;<a href="'.getImageUrl($attachInfo['save_path'].$attachInfo['save_name']).'" target="_blank">下载</a><br />';
                }
              }
        }
        if($verifyInfo['other_data']){
              $a = explode('|', $verifyInfo['other_data']);
              foreach($a as $key=>$val){
                if($val !== "") {
                    $attachInfo = D('attach')->where("attach_id=$a[$key]")->find();
                    $verifyInfo['other_data_list'] .= $attachInfo['name'].'&nbsp;<a href="'.getImageUrl($attachInfo['save_path'].$attachInfo['save_name']).'" target="_blank">下载</a><br />';
                }
              }
        }
        // 获取认证分类信息
        if(!empty($verifyInfo['user_verified_category_id'])) {
            $verifyInfo['category']['title'] = D('user_verified_category')->where('user_verified_category_id='.$verifyInfo['user_verified_category_id'])->getField('title');
        }

        switch ($verifyInfo['verified']) {
            case '1':
                $status = '<i class="ico-ok"></i>已认证 <a href="javascript:void(0);" onclick="delverify()" style="color:#65addd">注销认证</a>';
                break;
            case '0':
                $status = '<i class="ico-wait"></i>已提交认证，等待审核';
                break;
            case '-1':
                // 安全过滤
                $type = t($_GET['type']);
                if($type == 'edit'){
                    $status = '<i class="ico-no"></i>未通过认证，请修改资料后重新提交';
                    $this->assign('edit',1);
                    $verifyInfo['attachIds'] = str_replace('|', ',', substr($verifyInfo['attach_id'],1,strlen($verifyInfo['attach_id'])-2));
                    $verifyInfo['other_data_ids'] = str_replace('|', ',', substr($verifyInfo['other_data'],1,strlen($verifyInfo['other_data'])-2));
                }else{
                    $status = '<i class="ico-no"></i>未通过认证，请修改资料后重新提交 <a style="color:#65addd" href="'.U('classroom/User/setInfo',array('type'=>'edit', 'tab'=>3)).'">修改认证资料</a>';
                }
                break;
            default:
                //$verifyInfo['usergroup_id'] = 5;
                $status = '未认证';
                break;
        }
        //附件限制
        $attach = model('Xdata')->get("admin_Config:attachimage");
        $imageArr = array('gif','jpg','jpeg','png','bmp');
        foreach($imageArr as $v){
            if(strstr($attach['attach_allow_extension'],$v)){
                $imageAllow[] = $v;
            }
        }
        $attachOption['attach_allow_extension'] = implode(', ', $imageAllow);
        $attachOption['attach_max_size'] = $attach['attach_max_size'];
        $this->assign('attachOption',$attachOption);

        // 获取认证分类
        $category = D('user_verified_category')->findAll();
        foreach($category as $k=>$v){
            $option[$v['pid']] .= '<option ';
            if($verifyInfo['user_verified_category_id']==$v['user_verified_category_id']){
                $option[$v['pid']] .= 'selected';
            }
            $option[$v['pid']] .= ' value="'.$v['user_verified_category_id'].'">'.$v['title'].'</option>';
        }
        //dump($option);exit;
        $this->assign('option', json_encode($option));
        $this->assign('options', $option);
        $this->assign('category', $category);
        $this->assign('status' , $status);
        $this->assign('verifyInfo' , $verifyInfo);
        //dump($verifyInfo);exit;

        $user = model('User')->getUserInfo($this->mid);

        // 获取用户职业信息
        $userCategory = model('UserCategory')->getRelatedUserInfo($this->mid);
        $userCateArray = array();
        if(!empty($userCategory)) {
            foreach($userCategory as $value) {
                $user['category'] .= '<a href="#" class="link btn-cancel"><span>'.$value['title'].'</span></a>&nbsp;&nbsp;';
            }
        }
        $user_tag = model('Tag')->setAppName('User')->setAppTable('user')->getAppTags(array($this->mid));
    }
    
	
	
    /**
     * 修改专辑内容
     */
    public function album_edit(){
   		if(!intval($_GET['id'])){
   			$this->assign('isAdmin',1);
   			$this->error('输入参数出错!');
   		}
    	$get = $_GET;
    	$data = D("ZyAlbum","classroom")->getAlbumById($_GET['id']);
		
		//print_r($data);
		$data['album_video']      = trim( D('Album','classroom')->getVideoId($data['id']) , ',');
    	$data['fullcategorypath'] = trim($data['fullcategorypath'],',');
		
		
		//print_r($data);
    	$this->assign($data);
    	
    	$this->display();
    }
    
    /**
     * 保存专辑修改
     */
    public function doAlbum_edit(){
		//必须要登录之后才能修改
		if(!intval($this->mid)){
			$this->mzError("未登录,不能修改!");	
		}
		
		$data['id'] 		      = intval($_POST['id']);
		$data['album_title']      = t($_POST['album_title']);
		$data['album_intro']      = t($_POST['album_intro']);
		$data['fullcategorypath'] = t($_POST['fullcategorypath']);
		$data['cover']            = t($_POST['cover_ids']);
		$data['uctime']           = t($_POST['uctime'])?t($_POST['uctime']):0;
		$data['uctime']           = strtotime($data['uctime']);
		$album_tag 			      = explode(',',t($_POST['album_tag']));
		
		if(!$data['id']){
			$this->mzError("专辑信息错误!");	
		}
		//要检查是不是自己的
		$count = M('ZyAlbum')->where(array('uid'=>intval($this->mid),'id'=>$data['id']))->count();
		if(!$count){
			$this->mzError("没有权限修改此专辑,可能不是你创建的!");	
		}
		//数据校验
		if(!trim($data['album_title'])){	
			$this->mzError("专辑标题不能为空!");	
		}
		if(!trim($data['album_intro'])){
			$this->mzError("专辑简介不能为空!");	
		}
		if(!trim($data['fullcategorypath'])){
			$this->mzError("专辑分类不能为空!");	
		}
		if(!trim($data['cover'])){
			$this->mzError("请上传封面!");	
		}

		if(empty($album_tag)){
			$this->mzError("专辑标签不能为空!");	
		}
		if(!$data['uctime']){
			$this->mzError("请选择下架时间!");	
		}
		if($data['uctime'] <= time()){
			$this->mzError("下架时间应该大于当前时间!");	
		}
		
		
		
		$i = M('ZyAlbum')->where("id = {$data['id']}")->data($data)->save();
		if($i !== false){
			//先删除tag
			model('Tag')->setAppName('classroom')->setAppTable('zy_album')->deleteSourceTag($data['id']);
			//再创建tag
			$tag_reslut = model('Tag')->setAppName('classroom')->setAppTable('zy_album')->addAppTags($data['id'],$album_tag);
			
			$_data['str_tag']   = implode(',' ,getSubByKey($tag_reslut,'name'));
			$_data['album_tag'] = ','.implode(',',getSubByKey($tag_reslut,'tag_id')).',';
			$_data['id']        = $data['id'];
			
			M('ZyAlbum')->save($_data);
			$this->mzSuccess('修改成功!',U('classroom/Home/album'));
		}else{
			$this->mzError("修改失败!");	
		}
    }

    public function sendEmailActivate(){
        $time = time();
        if(session('send_time') > $time){
            exit('请勿重复操作！');
        }
        $user = $this->get('user');
        $code = md5(md5($time.get_client_ip().$user['email']));
        session('email_activate', $code);
        session('send_time', $time+90);
        $url  = U('classroom/User/emailActivate', array('activeCode'=>$code));
        $body = "<p>{$user['uname']}，你好</p>
<p style=\"color:#666\">欢迎加入Eduline，请点击下面的链接地址来验证你的邮箱：</p>
<p><a href=\"{$url}\" target=\"_blank\" style=\"color:#06c\">$url</a></p>
<p style=\"color:#666\">如果链接无法点击，请将链接复制到浏览器的地址栏中进行访问。</p>";
        $res = model('Mail')->send_email($user['email'], '[Eduline] Email地址验证', $body);
        exit($res?'ok':'邮件投递失败！');
    }

    public function emailActivate(){
        $this->assign('isAdmin', 1);
        $this->assign('jumpUrl', U('classroom/User/setInfo'));
        $code = $_GET['activeCode'];
        if(!$code || $code!=session('email_activate')){
            $this->error('操作异常');
        }

        session('email_activate', null);
        session('send_time', null);
        $res = model('User')->where(array('uid'=>$this->mid))->save(array(
            'mail_activate' => 1,
        ));
        $res && model('User')->cleanCache($this->mid);
        if($res){
        	$this->assign('isAdmin',1);
            $this->success('Email地址验证成功！');
        }
    }

    //设置邮箱
    public function setEmail(){
        $email = $_POST['email'];
        $reg  = model('Register');
        $user = $this->get('user');
        if(!$reg->isValidEmail($email, $user['email'])){
            exit($reg->getLastError());
        }
		$save = array(
            'email' => $email,
            'mail_activate' => 0,
        );
		if($user['login']==$user['email']){
			$save['login'] = $email;
		}
        $res = model('User')->where(array('uid'=>$this->mid))->save($save);
        $res && model('User')->cleanCache($this->mid);
        if(false !== $res){
            exit('ok');
        }else{
            exit('Email修改失败');
        }
    }

    public function sendCode(){
        $time = time();
        $phoneCodes = session('phone_code');
        $user = $this->get('user');
        $old  = $user['phone'];
        if(!empty($_POST['phone'])){
            $phone = $_POST['phone'];
            if(!preg_match('/^1[3458]\d{9}$/', $phone)){
                exit('请输入正确的手机号码');
            }
            if($phone == $old) exit('输入的手机号和之前的相同');
            $id = model('User')->where(array('phone'=>$phone))->getField('uid');
            if($id > 0) exit('该手机号已被其他用户使用');
            $phoneCodes[$phone]['setd'] = true;
        }else{
            $phone = $old;
            if(!$phone) exit('还未设置手机号');
            $phoneCodes[$phone]['setd'] = false;
        }

        if($phoneCodes[$phone]['send_time'] > $time){
            exit('请勿频繁获取短信验证码');
        }
        
        $phoneCodes[$phone]['err'] = 0;
        $phoneCodes[$phone]['send_time'] = $time+90;
        
        $code = rand(100000, 999999);
        $phoneCodes[$phone]['code'] = md5($code);
        $txt = "您本次获取的验证码是：{$code}，请在页面指定处填写。如非本人操作，请忽略此信息";
        if(model('Sms')->send($phone, $txt)){
            session('phone_code', $phoneCodes);
            exit('ok');
        }else{
            exit('发送失败');
        }
    }

    public function checkCode(){
        $time = time();
        $phoneCodes = session('phone_code');
		//print_r($phoneCodes);
        $user = $this->get('user');
        $old  = $user['phone'];
        $phone = empty($_POST['phone'])?$old:$_POST['phone'];
        $code  = md5($_POST['code']);

        //常规检查
        if(!empty($_POST['phone'])){
            $b1 = !preg_match('/^1[3458]\d{9}$/', $phone);
            $b2 = $phone == $old;
            $id = model('User')->where(array('phone'=>$phone))->getField('uid');
            $b3 = $id > 0;
            $b4 = $old&&empty($phoneCodes[$old]);
            if($b1 || $b2 || $b3 || $b4){
                exit('操作异常');
            }
        }

        //没有获取验证码
        if(!isset($phoneCodes[$phone])){
            exit('请先获取短信验证码');
        }
        $phoneCode = $phoneCodes[$phone];
        //允许尝试4次验证码
        if($code != $phoneCode['code']){
            $phoneCode['err'] += 1;
            if($phoneCode['err'] >= 4){
                $phoneCodes[$phone] = null;
                session('phone_code', $phoneCodes);
                exit('请重新获取短信验证码');
            }else{
                $phoneCodes[$phone] = $phoneCode;
                session('phone_code', $phoneCodes);
                exit('验证码错误，您还可以尝试'.(4-$phoneCode['err']).'次');
            }
        }

        if($phoneCode['setd']){
			$save = array(
                'phone' => $phone,
                'phone_activate' => 1,
            );
			if($user['login'] == $user['phone']){
				$save['login'] = $phone;
			}
            $res = model('User')->where(array('uid'=>$this->mid))->save($save);
            $res && model('User')->cleanCache($this->mid);
            if(false !== $res){
        		session('phone_code', null);
                exit('ok');
            }else{
                exit('手机号更改失败');
            }
        }
        exit('ok');
    }
	
    
	/**
	 * 邀请页面 - 页面
	 * @return void
	 */
	public function invite()
	{
		if( !CheckPermission('core_normal','invite_user') ){
			
				$this->assign('isAdmin',1);
			$this->error('对不起，您没有权限进行该操作！');
		}
		// 获取选中类型
		$type = isset($_GET['type']) ? t($_GET['type']) : 'link';
		$this->assign('type', $type);
		// 获取不同列表的相关数据
		switch($type) {
			case 'email':
				$this->_getInviteEmail();
				break;
			case 'link':
				$this->_getInviteLink();
				break;
		}
		$userInfo = model('User')->getUserInfo($this->mid);
		$this->assign('invite', $userInfo);
		$this->assign('config', model('Xdata')->get('admin_Config:register'));
		// 获取后台积分配置
		$creditRule = model('Credit')->getCreditRules();
		foreach ($creditRule as $v) {
			if ($v['name'] === 'core_code') {
				$applyCredit = abs($v['score']);
				break;
			}
		}
		$this->assign('applyCredit', $applyCredit);
		// 后台配置邀请数目
		$inviteConf = model('Xdata')->get('admin_Config:invite');
		$this->assign('emailNum', $inviteConf['send_email_num']);

		$this->display();
	}

    public function doFollow(){
        $uid=intval($_POST['uid']);//获取用户id
        if(empty($uid)){
            echo "关注失败！";
            exit;
        }
        //先查询是否已关注
        $map=array(
            'uid'=>$this->mid,
            'fid'=>$uid
        );
        $res=D('UserFollow')->where($map)->find();
        if($res){
            echo "您已关注对方！";
            exit;
        }
        $result=D('UserFollow')->add($map);
        if($result){
            echo "200";
            exit;
        }else{
            echo "关注失败！";
            exit;
        }

    }

	/**
	 * 邮箱邀请相关数据
	 * @return void
	 */
	private function _getInviteEmail()
	{
		// 获取邮箱后缀
		$config = model('Xdata')->get('admin_Config:register');
		$this->assign('emailSuffix', $config['email_suffix']);
		// 获取已邀请用户信息
		$inviteList = model('Invite')->getInviteUserList($this->mid, 'email');
		$this->assign('inviteList', $inviteList);
		// 获取有多少可用的邀请码
		$count = model('Invite')->getAvailableCodeCount($this->mid, 'email');
		$this->assign('count', $count);
	}

	/**
	 * 链接邀请相关数据
	 * @return void
	 */
	private function _getInviteLink()
	{
		// 获取邀请码列表
		$codeList = model('Invite')->getInviteCode($this->mid, 'link');
		$this->assign('codeList', $codeList);
		// 获取已邀请用户信息
		$inviteList = model('Invite')->getInviteUserList($this->mid, 'link');
		$this->assign('inviteList', $inviteList);
		// 获取有多少可用的邀请码
		$count = model('Invite')->getAvailableCodeCount($this->mid, 'link');
		$this->assign('count', $count);
	}
    /**
     *教师课程
     * @return void
     */
    public function teacherVideo()
    {
        $id=intval($_GET['id']);
        if($_GET['id']){
            $data = D('ZyVideo','classroom')->getVideoById(intval($_GET['id']));
            $this->assign($data);
        }
        //生成上传凭证
        $bucket = getAppConfig('qiniu_Bucket','qiniuyun');
        Qiniu_SetKeys(getAppConfig('qiniu_AccessKey','qiniuyun'), getAppConfig('qiniu_SecretKey','qiniuyun'));
        $putPolicy = new Qiniu_RS_PutPolicy($bucket);
        $filename="chuyou".rand(5,8).time();
        $str="{$bucket}:{$filename}";
        $entryCode=Qiniu_Encode($str);
        $putPolicy->PersistentOps= "avthumb/mp4/ab/160k/ar/44100/acodec/libfaac/r/30/vb/5400k/vcodec/libx264/s/1920x1080/autoscale/1/strpmeta/0|saveas/".$entryCode;
        $upToken=$putPolicy->Token(null);
        $video_category=M("zy_video_category")->where("type=1")->findAll();
        $this->assign("category",$video_category);
        $this->assign("uptoken",$upToken);
        $this->assign("filename",$filename);
        $id ? $this->display("updateVideo") : $this->display();
    }
    /**
     * 教师上传的视频
     * @return void
     */
    public function getmyvideolist()
    {
        $uid        = intval($this->mid);
        $limit      = 9;
        $data = M('zy_video')->where("uid=".$uid)->findPage($limit);
    
        //把数据传入模板
        $this->assign('data',$data['data']);

        //取得数据
        $data['data'] = $this->fetch('_video_my');
        echo json_encode($data);exit;
    }
    /**
     * 删除课程
     * @return void
     */
    public function delvideo(){
        $id=$_POST["id"];
        $res=M('zy_video')->where("id=".$id)->delete();
        if($res){
            exit(json_encode(array('status'=>'1','info'=>'已删除')));
        }else{
            exit(json_encode(array('status'=>'0','info'=> '操作繁忙,请稍后再试')));
        }
    }
    /**
     * 上传新课程    
     * @return void
     */
    public function doAddVideo()
    {
       $post = $_POST;
        if(empty($post['video_title'])) exit(json_encode(array('status'=>'0','info'=>"课程标题不能为空")));
        if(empty($post['video_intro'])) exit(json_encode(array('status'=>'0','info'=>"课程简介不能为空")));
        if(empty($post['video_tag'])) exit(json_encode(array('status'=>'0','info'=>"课程标签不能为空")));
        if(empty($post['v_price'])) exit(json_encode(array('status'=>'0','info'=>"课程价格不能为空")));
        if(intval($post['video_category'])==0) exit(json_encode(array('status'=>'0','info'=>"请选择课程分类")));
        if(empty($post['cover_ids'])) exit(json_encode(array('status'=>'0','info'=>"课程封面不能为空")));
        if(empty($post['videokey'])) exit(json_encode(array('status'=>'0','info'=>"请上传视频")));

        if($post['limit_discount'] > 1 || $post['limit_discount'] < 0){
            exit(json_encode(array('status'=>'0','info'=>'折扣的区间填写错误')));
        }
        $data['starttime']           = $post['starttime'] ? strtotime($post['starttime']) : 0; //限时开始时间
        $data['endtime']             = $post['endtime'] ? strtotime($post['endtime']) : 0; //限时结束时间
        $data['listingtime']         = $post['listingtime'] ? strtotime($post['listingtime']) : 0; //上架时间
        $data['uctime']              = $post['uctime'] ? strtotime($post['uctime']) : 0; //下架时间
        if($data['endtime'] < $data['starttime'] || $data['uctime'] < $data['listingtime']){
            exit(json_encode(array('status'=>'0','info'=>'结束时间不能小于开始时间')));
        }
        //格式化七牛数据
        $videokey=t($_POST['videokey']);
        $video_address="http://".getAppConfig('qiniu_Domain','qiniuyun')."/".$videokey;
        $data['qiniu_key']=$videokey;
        $video_tag                   = t($post['video_tag']);
        $data['video_category']      = $post["video_category"];
        $data['fullcategorypath']    = $post["video_category"];
        $data['video_title']         = t($post['video_title']); //课程名称
        $data['video_intro']         = t($post['video_intro']); //课程简介
        $data['v_price']             = $post['v_price']; //市场价格
        $data['video_address']       = $video_address;//正确的视频地址
        $data['cover']               = intval($post['cover_ids']); //封面
        $data['videofile_ids']       = isset($post['videofile_ids']) ? intval($post['videofile_ids']) : 0; //课件id
        $data['is_tlimit']           = isset($post['is_tlimit']) ? intval($post['is_tlimit']) : 0; //限时打折
        $data['limit_discount']      = isset($post['is_tlimit']) && ($post['limit_discount'] <= 1 && $post['limit_discount'] >= 0) ? $post['limit_discount'] : 1; //限时折扣
        $data['t_price'] 			 = $data['v_price'] * $data['limit_discount'];
        $data["teacher_id"]          = M('zy_teacher')->where('uid='.$this->mid)->getField('uid');
        if($post['id']){
            $data['utime'] = time();
            $result = M('zy_video')->where('id = '.$post['id'])->data($data)->save();
        } else {
            $data['ctime'] = time();
            $data['utime'] = time();
            $data['uid'] = $this->mid;
            $result = M('zy_video')->data($data)->add();
        }
        if($result){
            unset($data);
            if($post['id']){
                model('Tag')->setAppName('classroom')->setAppTable('zy_video')->deleteSourceTag($post['id']);
                $tag_reslut = model('Tag')->setAppName('classroom')->setAppTable('zy_video')->addAppTags($post['id'],$video_tag);
            } else {
                $tag_reslut = model('Tag')->setAppName('classroom')->setAppTable('zy_video')->addAppTags($result,$video_tag);
            }
            $data['str_tag'] = implode(',' ,getSubByKey($tag_reslut,'name'));
            $data['tag_id'] = ','.implode(',',getSubByKey($tag_reslut,'tag_id')).',';
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
    function teacherDeatil(){
        $teacher_info=M("zy_teacher")->where("uid=".$this->mid)->find();
        $teacherschedule=$teacher_info["teacher_schedule"];
        $teacher_info["teacher_schedule"]=explode(",",$teacher_info["teacher_schedule"]);
        $teacher_schedule=M("zy_teacher_schedule")->where("pid=0")->findALL();
        $teacher_level=array();
        for ($i=0; $i <3 ; $i++) { 
            foreach ($teacher_schedule as $key => $value) {
                $level=M("zy_teacher_schedule")->where("pid=".$value["id"])->findALL();
                $teacher_level[$i][]=$level[$i];
            }
        }
        $this->assign('teacher_level',$teacher_level);
        $this->assign("teacher_schedule",$teacher_schedule);
        $this->assign("teacherschedule",$teacherschedule);
        $this->assign("teacher_info",$teacher_info);
        $this->display(); 
    }
    function doteacherDeatil(){
        $id = intval($_POST['id']);
        //要添加的数据
        $map=array(
        'name'=>t($_POST['name']),
        'inro'=>t($_POST['inro']),
        'title'=>t($_POST['title']),
        'ctime'=>time(),
        'teacher_age'=>t($_POST['teacher_age']),
        'label'=>t($_POST['label']),
        'high_school'=>t($_POST['high_school']),
        'teacher_schedule'=>t($_POST['teacher_schedule']),
        'graduate_school'=>t($_POST['graduate_school']),
        'teach_evaluation'=>t($_POST['teach_evaluation']),
        'teach_way'=>t($_POST['teach_way']),
        'Teach_areas'=>t($_POST['Teach_areas'])
        );
        $res=D('ZyTeacher')->where("id=".$id)->save($map);
        if(!$res)exit(json_encode(array('status'=>'0','info'=>'编辑失败')));
        exit(json_encode(array('status'=>'1','info'=>'编辑成功')));
    }
    function teachcourse(){
        $old_num_list="";
        $course_list=M("zy_teacher_course")->where(array('course_teacher'=>$this->mid,'is_del'=>0))->findALL();
        foreach ($course_list as $key => $value) {
            $num=$key+1;
            $old_num_list.=$num."-".$value["course_id"]."-0,";
        }
        $this->assign("course_list",$course_list);
        $this->assign("old_num_list",$old_num_list);
        $this->display(); 
    }
    function doteachcourse(){
        if($_POST['num_list']){
            $num=explode(",",$_POST['num_list']);
            foreach ($num as $key =>$value) {
               $map=array(
                    'course_name'=>$_POST['course_name_'.$value],
                    'course_teacher'=>$this->mid,
                    'course_price'=>$_POST['course_price_'.$value],
                    'course_inro'=>$_POST['course_inro_'.$value],
                    'ctime'=>time()
                );
               M('zy_teacher_course')->data($map)->add();
            }    
        }
        if($_POST['old_num_list']){
            $old_num_list=explode(",",$_POST['old_num_list']);
            foreach ($old_num_list as $key => $value) {
                $list=explode("-",$value);
                if($list[2]==0){
                    $map=array(
                        'course_name'=>$_POST['course_name_'.$list[0]],
                        'course_teacher'=>$this->mid,
                        'course_price'=>$_POST['course_price_'.$list[0]],
                        'course_inro'=>$_POST['course_inro_'.$list[0]],
                        'ctime'=>time()
                    );
                   M('zy_teacher_course')->data($map)->where("course_id=".$list[1])->save();
                }else{
                    M('zy_teacher_course')->data('is_del=1')->where("course_id=".$list[1])->save();
                }
            }
        }
        exit(json_encode(array('status'=>'1','info'=>'操作成功')));
    }
    function delteachcourse(){
        $result = M('zy_teacher_course')->where('course_id='.$_POST['id'])->data(array('is_del'=>1))->save();
        if($result){
            exit(json_encode(array('status'=>'1','info'=>'已删除')));
        } else {
            exit(json_encode(array('status'=>'0','info'=> '操作繁忙,请稍后再试')));
        }
    }
}