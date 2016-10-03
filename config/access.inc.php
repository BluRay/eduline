<?php
/*
 * 游客访问的黑/白名单，不需要开放的，可以注释掉
 * 应用的游客配置，转移到apps/app_name/Conf/access.inc.php下
 * 此处只配置不能后台修改的项目
 */
return array (
	"access" => array (
		'public/Register/*' => true, // 注册
		'public/Passport/*' => true, // 登录
		'public/Widget/*'	=> true, // 插件
		'page/Index/index'	=> true, // 自定义页面
		'public/Tool/*' 	=> true, // 升级查询
		'classroom/Public/*' => true,
		'classroom/Video/*' => true,
		'classroom/Album/*' => true,
		'classroom/Note/*' => true,
		'classroom/Public/*' => true,
		'classroom/Question/*' => true,
		'classroom/Index/*' => true,
		'classroom/Serial/*' =>true,
		'classroom/Search/*' => true,
		'classroom/Review/*' => true,
		'classroom/Xunsearch/*'=>true,
		'classroom/Sphinx/*' => true,
        'classroom/Limit/*'=>true,
        'classroom/Teacher/*'=>true,
        'classroom/UserShow/*'=>true,
        'classroom/Topic/*'=>true,//资讯
		'api/*/*' 			=> true, // API
		'wap/*/*' 			=> true, // wap版
		'w3g/*/*' 			=> true, // 3G版
		'zuhe/*/*'			=> true, //课程组合
		'index/*/*'			=> true, //首页
		'order/*/*'			=> true, //点播
		'xueyuan/*/*'		=> true, //学院
		'home/*/*'		=> true, //个人中心
        'wenda/*/*'=>true,//问答版块
        'group/Index/*'=>true,//小组

        'group/Topic/*'=>true,//小组
        'event/Index/*'=>true,//小组

	)
);
