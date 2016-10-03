<?php
/*
 * 游客访问的黑/白名单，不需要开放的，可以注释掉
 * 此处只配置不能后台修改的项目
 */
return array(
	"access" => array(
		'Oauth/*'        	     => true,
		'Attach/*' 			     => true,
		'Login/*' 			     => true,
		'Teacher/*' 		     => true,

		'Album/getAlbumList' 	 => true,
		'Album/albumView' 		 => true,
		'Album/albumprices' 	 => true,
		'Album/getCatalog' 		 => true,
		'Album/getAlbumTag' 	 => true,

		'Video/videoList' 		 => true,
		'Video/videoInfo' 		 => true,
		'Video/getAttrImage' 	 => true,
		'Video/getListCount' 	 => true,
		'Video/getVideoGroup' 	 => true,
		'Video/questionDetail' 	 => true,
		'Video/strSearch' 		 => true,
		'Video/tagSearch' 		 => true,

		'Wenda/getWendaList' 	 => true,
		'Wenda/getWendaByCourse' => true,
		'Wenda/sevendayHot' 	 => true,
		'Wenda/tagSearch' 		 => true,
		'Wenda/strSearch' 		 => true,
		'Wenda/detail' 			 => true,
		'Wenda/wendaComment' 	 => true,
		'Wenda/wendaCommentDesc' => true,
		'Wenda/getSonComment'    => true,
		'User/addCard'           => true,
		'News/*'                 => true,
	) 
);