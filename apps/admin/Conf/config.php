<?php
$menu = array(
    //后台头部TAB配置
	'admin_channel'	=>	array(
		'index'		=> '首页', //L('PUBLIC_SYSTEM'),
		'system'	=> L('PUBLIC_SYSTEM'),
		'content'	=> L('PUBLIC_CONTENT'),
		'user'		=> L('PUBLIC_USER'),
		'classroom' => '云课堂',
		'exam'      => '考试系统',
		'live'      => '直播系统',
		'apps'		=> L('PUBLIC_APPLICATION'),
		'extends'	=> '扩展',//L('PUBLIC_EXPANSION'),
	),
	//后台菜单配置
	'admin_menu'	=> array(
		'index'	=> array(
			'首页'	=> array(
				L('PUBLIC_BASIC_INFORMATION')	=>	U('admin/Home/statistics'),
				L('PUBLIC_VISIT_CALCULATION')	=>	U('admin/Home/visitorCount'),
				L('PUBLIC_MANAGEMENT_LOG')	=>	U('admin/Home/logs'),
				'群发消息'	=>	U('admin/Home/message'),
				'数据备份'				=> U('admin/Tool/backup'),
				L('PUBLIC_CLEANCACHE')	=>  U('admin/Tool/cleancache'),
			)
		),
		'system'	=> array(
			L('PUBLIC_SYSTEM_SETTING')	=>	array(
				L('PUBLIC_WEBSITE_SETTING')	=>	U('admin/Config/site'),
				L('PUBLIC_REGISTER_SETTING')    =>    U('admin/Config/register'),
				'邀请配置'    =>    U('admin/Config/invite'),
				'银联配置'          =>  U('admin/Config/unionpay'),
				'支付宝配置'          =>  U('admin/Config/alipay'),
				'短信接口配置'=>U('admin/Config/sms'),
				L('PUBLIC_EMAIL_SETTING')	=>	U('admin/Config/email'),
				L('PUBLIC_FILE_SETTING')	=>	U('admin/Config/attach'),
				L('PUBLIC_FILTER_SETTING')	=>	U('admin/Config/audit'),
				'地区配置'			=>  U('admin/Config/area'),
				L('PUBLIC_NAVIGATION_SETTING') =>    U('admin/Config/nav'),
				L('PUBLIC_MAILTITLE_ADMIN')	=>	U('admin/Config/notify'),
	    		L('PUBLIC_AUTHORITY_SETTING')	=>  U('admin/Apps/setPermNode'),
				'缓存配置'				=> U('admin/Home/cacheConfig'),
			),
		),

    	'user'	=>	array(
    		L('PUBLIC_USER')				=>	array(
    			L('PUBLIC_USER_MANAGEMENT')	=>	U('admin/User/index'),
    			L('PUBLIC_USER_GROUP_MANAGEMENT')	=>	U('admin/UserGroup/index'),
    			'用户认证'	=>  U('admin/User/verifyCategory'),
    		),
    	),
    	
    	'content'	=> array(
    		L('PUBLIC_CONTENT_MANAGEMENT')			=>	array(
    			L('PUBLIC_PRIVATE_MESSAGE_MANAGEMENT')	=>	U('admin/Content/message'),
    			L('PUBLIC_FILE_MANAGEMENT')	=>	U('admin/Content/attach'),
				L('PUBLIC_TAG_MANAGEMENT')		=>  U('admin/Home/tag'),
                '资讯管理'				=> U('admin/Topic/index'),
                '活动'=>	U('event/Admin/index'),
                '小组'=>	U('group/Admin/index'),
    			'单页管理' =>	U('admin/Single/index'),
	    	),
    	),
    	'task'	=> array(
			L('PUBLIC_TASK_INFO')			=> array(
	 			L('PUBLIC_TASK_LIST')	=> U('admin/Task/index'),
	 			L('PUBLIC_TASK_REWARD') => U('admin/Task/reward'),
	 			'勋章列表'				=> U('admin/Medal/index'),
	 			'用户勋章'				=> U('admin/Medal/userMedal'),
				'任务配置'				=> U('admin/Task/taskConfig')
	 		)
	 	),
    	'apps'	=> array(
			L('PUBLIC_APP_MANAGEMENT')			=>	array(
	    		L('PUBLIC_INSTALLED_APPLIST')	=>	U('admin/Apps/index'),
	    		L('PUBLIC_UNINSTALLED_APPLIST')	=>	U('admin/Apps/install'),
	    	),
	 	),
	    'extends'		=> array(
	 		'插件管理' => array(

    		),
	 	),
		'classroom' => array(
			'内容管理' => array(
				'课程管理' => U('classroom/AdminVideo/index'),
				'专辑管理' => U('classroom/AdminAlbum/index'),
				'讲师管理' => U('classroom/AdminTeacher/index'),
                '问答管理'=>U('wenda/AdminIndex/index'),
				'笔记管理' => U('classroom/AdminNote/index'),
				'提问管理' => U('classroom/AdminQuestion/index'),
				'点评管理' => U('classroom/AdminReview/index'),
	 			'约课管理' => U('classroom/AdminCourse/index'),
				'分类配置' => U('classroom/AdminVideoCategory/index'),
			),
			'订单与账户' => array(
                '云课堂用户' => U('classroom/AdminLearnc/index'),
                '订单管理' => U('classroom/AdminOrder/index'),
                '提现申请' => U('classroom/AdminWithdraw/index'),
                '卡号列表' => U('classroom/AdminCard/index'),
			),

			'其他' => array(
                '云课堂配置' => U('classroom/AdminConfig/index'),
            ),
		),
			
		'exam' => array(
				'考试系统' => array(
						'题库管理'    => U('exam/AdminQuestion/index'),
						'试卷管理'    => U('exam/AdminPaper/index'),
						'考试管理'    => U('exam/AdminExam/index'),
						'分类配置'    => U('exam/AdminCategory/index'),
						'用户考试记录' => U('exam/AdminUserExam/index'),

				),

		),
			
		'live' => array(
				'直播系统' => array(
				'直播间管理' => U('live/Admin/index'),
				),
		),

    )
);

$app_list = model('App')->getConfigList();
foreach($app_list as $k=>$v){
	$menu['admin_menu']['apps'][L('PUBLIC_APP_MANAGEMENT')][$k] = $v;
}
$plugin_list = model('Addon')->getAddonsAdminUrl();
foreach($plugin_list as $k=>$v){
	$menu['admin_menu']['extends']['插件管理'][$k] = $v;
}

//防护云激活代码
/*
//1.如果防护云库文件存在，但是配置不存在，新注册key
if(!file_exists(DATA_PATH.'/iswaf/config.php') && file_exists(ADDON_PATH.'/library/iswaf/iswaf.php')){
	$dir   =  SITE_PATH.'/data/iswaf';
	function iswaf_create_key() {
	    PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	    $hash = '';
	    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	    $max = strlen($chars) - 1;
	    for($i = 0; $i < 128; $i++) {
	        $hash .= $chars[mt_rand(0, $max)];
	    }
	    return md5($hash.rand(1,3000).print_r($_SERVER,1));
	}
	// 目录不存在则创建
	if(!is_dir($dir))  mkdir($dir,0777,true);
	$iswafKey = iswaf_create_key(SITE_URL);
	$iswafConfig = array(
		'iswaf_database' => $dir.'/',
		'iswaf_connenct_key' => $iswafKey,
		'iswaf_status' => 1,
		'defences'=>array(
					'callback_xss'=>'On',
					'upload'=>'On',
					'inject'=>'On',	
					'filemode'=>'On',
					'webshell'=>'On',
					'server_args'=>'On',
					'webserver'=>'On',
					'hotfixs'=>'On',
					)
	);
	//注册ts站点
	$context = stream_context_create(array(
	'http'=>array(
	  'method' => "GET",
	  'timeout' => 10, //超时30秒
	  'user_agent'=>"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)"
	  )));
	$url = 'http://www.fanghuyun.com/api.php?do=tsreg&IDKey='.$iswafKey.'&url='.SITE_URL.'&ip='.get_client_ip();
	$res = file_get_contents($url, false, $context);
	//dump($res);exit;
	file_put_contents($dir.'/config.php',"<?php\nreturn ".var_export($iswafConfig,true).";\n?>");
	$menu['admin_menu']['index']['首页']['安全防护'] = 'http://www.fanghuyun.com/?do=simple&IDKey='.md5($iswafKey);
//2.如果防护云配置文件存在，但是没有关闭，启用防护云
}else if(defined('iswaf_status') && iswaf_status!=0){
	$context = stream_context_create(array(
	'http'=>array(
	  'method' => "GET",
	  'timeout' => 10, //超时30秒
	  'user_agent'=>"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)"
	  )));
	$res = file_get_contents('http://www.fanghuyun.com/api.php?IDKey='.iswaf_connenct_key.'&url='.SITE_URL.'&ip='.get_client_ip(), false, $context);
	//dump($res);exit;
	$menu['admin_menu']['index']['首页']['安全防护'] = 'http://www.fanghuyun.com/?do=simple&IDKey='.md5(iswaf_connenct_key);
}
*/
return $menu;