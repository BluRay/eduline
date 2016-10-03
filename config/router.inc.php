<?php
return array(
	/**
	 * 路由的key必须写全称. 比如: 使用'wap/Index/index', 而非'wap'.
	 */
	'router' => array(
		'classroom/Index/index'		=> 	SITE_URL.'/',
		// 基本
		'page/Index/index'			=>  SITE_URL.'/page/[page].html',
 		'public/Index/index'		=> 	SITE_URL.'/home',
		
		'public/Passport/login'  	=>  SITE_URL.'/welcome',
		'public/Register/index'  	=>  SITE_URL.'/register',
		'public/Register/waitForActivation'  =>  SITE_URL.'/activate/[uid]',
		'public/Register/waitForAudit'  =>  SITE_URL.'/review/[uid]',
		'public/Register/step2'  	=>  SITE_URL.'/register/upload_photo',
		'public/Register/step3'  	=>  SITE_URL.'/register/work_information',
		'public/Register/step4'  	=>  SITE_URL.'/register/follow_interesting',
		'public/Profile/feed'  		=>  SITE_URL.'/weibo/[feed_id]',
		'public/Topic/index'  		=>  SITE_URL.'/topic',

		'public/Profile/index'		=>	SITE_URL.'/space/[uid]',
		'public/Profile/data'  		=>  SITE_URL.'/space/[uid]/profile',
		'public/Profile/following'  =>  SITE_URL.'/space/[uid]/following',
		'public/Profile/follower'  	=>  SITE_URL.'/space/[uid]/follower',
		
		'public/Index/myFeed'  		=>  SITE_URL.'/myFeed',
		'public/Index/following'  	=>  SITE_URL.'/myFollowing',
		'public/Index/follower'  	=>  SITE_URL.'/myFollower',
		'public/Collection/index'  	=>  SITE_URL.'/myCollection',
		'public/Mention/index'  	=>  SITE_URL.'/myMention',
 		'public/Comment/index'  	=>  SITE_URL.'/myComment',
		'public/Task/index'  		=>  SITE_URL.'/myTask',
		'public/Medal/index'  		=>  SITE_URL.'/myMedal',
		'public/Rank/index'  		=>  SITE_URL.'/myRank',
		'public/Invite/invite'  	=>  SITE_URL.'/invite',
		
			
		'public/Account/index'  	=>  SITE_URL.'/setting/index',
		'public/Account/avatar'  	=>  SITE_URL.'/setting/avatar',
		'public/Account/domain'  	=>  SITE_URL.'/setting/domain',
		'public/Account/authenticate'=>  SITE_URL.'/setting/authenticate',
		'public/Account/privacy'  	=>  SITE_URL.'/setting/privacy',
		'public/Account/notify'  	=>  SITE_URL.'/setting/notify',
		'public/Account/blacklist'  =>  SITE_URL.'/setting/blacklist',
		'public/Account/security'  	=>  SITE_URL.'/setting/security',
		'public/Account/bind'  		=>  SITE_URL.'/setting/bind',
		'public/Account/tag'  		=>  SITE_URL.'/setting/tag',

		// 活动
		'event/Index/index'			=>	SITE_URL.'/app/event',
		'event/Index/personal'		=>	SITE_URL.'/app/event/events',
		'event/Index/addEvent'		=>	SITE_URL.'/app/event/post',
		'event/Index/edit'			=>	SITE_URL.'/app/event/edit/[id]',
		'event/Index/eventDetail'	=>	SITE_URL.'/app/event/detail/[id]',
		'event/Index/member'		=>	SITE_URL.'/app/event/member/[id]',


		
		// 小组
		'group/Index/index'			=>	SITE_URL.'/app/group',
		'group/Index/newIndex'		=>	SITE_URL.'/app/group/index',
		'group/Index/post'			=>	SITE_URL.'/app/group/my_post',
		'group/Index/replied'		=>	SITE_URL.'/app/group/replied',
		'group/Index/comment'		=>	SITE_URL.'/app/group/comment',
		'group/Index/atme'			=>	SITE_URL.'/app/group/atme',
		'group/SomeOne/index'		=>	SITE_URL.'/app/group/groups',
		'group/Index/find'			=>	SITE_URL.'/app/group/class',
		'group/Index/search'		=>	SITE_URL.'/app/group/search',
		'group/Index/add'			=>	SITE_URL.'/app/group/add',
		'group/Group/index'			=>	SITE_URL.'/app/group/[gid]',
		'group/Group/search'		=>	SITE_URL.'/app/group/[gid]/search',
		'group/Group/detail'		=>	SITE_URL.'/app/group/[gid]/detail/[feed_id]',
		'group/Invite/create'		=>	SITE_URL.'/app/group/[gid]/invite',
		'group/Manage/index'		=>	SITE_URL.'/app/group/[gid]/setting/baseinfo',
		'group/Manage/privacy'		=>	SITE_URL.'/app/group/[gid]/setting/private',
		'group/Manage/membermanage'	=>	SITE_URL.'/app/group/[gid]/setting/member',
		'group/Manage/announce'		=>	SITE_URL.'/app/group/[gid]/setting/announcement',
		'group/Log/index'			=>	SITE_URL.'/app/group/[gid]/setting/log',
		'group/Topic/index'			=>	SITE_URL.'/app/group/[gid]/bbs',
		'group/Topic/add'			=>	SITE_URL.'/app/group/[gid]/bbs/post',
		'group/Topic/edit'			=>	SITE_URL.'/app/group/[gid]/bbs/edit/[tid]',
		'group/Topic/editPost'		=>	SITE_URL.'/app/group/[gid]/bbs_reply/edit/[pid]',
		'group/Topic/topic'			=>	SITE_URL.'/app/group/[gid]/bbs/[tid]',
		'group/Dir/index'			=>	SITE_URL.'/app/group/[gid]/file',
		'group/Dir/upload'			=>	SITE_URL.'/app/group/[gid]/file/upload',
		'group/Member/index'		=>	SITE_URL.'/app/group/[gid]/member',
		

		//课程
		'classroom/Video/index'     => SITE_URL.'/app/video/index',
		'classroom/Video/view'     => SITE_URL.'/app/video/[id]',

		//专辑
		'classroom/Album/index'     => SITE_URL.'/app/album/index',
		'classroom/Album/view'     => SITE_URL.'/app/album/[id]',
		'classroom/Album/watch'     => SITE_URL.'/app/watch/[aid]/[type]',
		'classroom/Album/synvideo'     => SITE_URL.'/app/synvideo/[vid]/[type]/[aid]',

		//讲师
		'classroom/Teacher/index'     => SITE_URL.'/app/teacher/index',
		'classroom/Teacher/view'     => SITE_URL.'/app/teacher/[id]',

		//问答
		'wenda/Index/index'     => SITE_URL.'/app/wenda/index',
        
		'wenda/Index/question'     => SITE_URL.'/app/wenda/question',
		'wenda/Index/detail'     => SITE_URL.'/wenda/[id]/detail',
		'wenda/Index/classifywd'     => SITE_URL.'/wenda/[type]/[tpid]/[wdtype]',

		//资讯
		'classroom/Topic/index'     => SITE_URL.'/app/topic/index',
		'classroom/Topic/view'     => SITE_URL.'/app/topic/[id]',
		//管理中心
		'classroom/Home/video'     => SITE_URL.'/app/home/video',
		'classroom/Home/album'     => SITE_URL.'/app/home/album',
		'classroom/Home/wenti'     => SITE_URL.'/app/home/wenti',
		'classroom/Home/wenda'     => SITE_URL.'/app/home/wenda',
		'classroom/Home/note'     => SITE_URL.'/app/home/note',
		'classroom/Home/review'     => SITE_URL.'/app/home/review',
		'classroom/User/recharge'     => SITE_URL.'/app/user/recharge',
        'classroom/User/account'     => SITE_URL.'/app/user/account',
        'classroom/User/card'     => SITE_URL.'/app/user/card',
        'classroom/User/setInfo'     => SITE_URL.'/app/user/setInfo',
		'classroom/UserShow/index'     => SITE_URL.'/app/userShow/[uid]',
		'classroom/UserShow/wenda'     => SITE_URL.'/app/userShow/[uid]/wenda',
		'classroom/UserShow/note'     => SITE_URL.'/app/userShow/[uid]/note',
		'classroom/UserShow/fans'     => SITE_URL.'/app/userShow/[uid]/fans',
		'classroom/Video/merge'     => SITE_URL.'/app/video/merge',

	)
);