<?php
/**
 * 专题管理配置
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class AdminSpecialAction extends AdministratorAction
{
	
	/**
	 * 初始化，配置内容标题
	 * @return void
	 */
	public function _initialize()
	{
		$this->pageTitle ['special'] = '专题列表';
		$this->pageTitle ['category'] = '专题分类列表';
		$this->pageTitle ['addspecial'] = intval($_GET['id'])?'编辑专题':'添加专题';
		$this->pageTitle ['addcategory'] = intval($_GET['id'])?'编辑专题分类':'添加专题分类';
		$this->assign('isAdmin',1);	//是否后台
		parent::_initialize();
	}
	
	
	/**
	 * 初始化专题配置
	 * 
	 * @return void
	 */
	private function _initTabSpecial() {
		// Tab选项
		$this->pageTab [] = array (
				'title' => '专题列表',
				'tabHash' => 'special',
				'url' => U ( 'classroom/AdminSpecial/special' ) 
		);
		$this->pageTab [] = array (
				'title' => '专题分类列表',
				'tabHash' => 'category',
				'url' => U ( 'classroom/AdminSpecial/category' ) 
		);
		$this->pageTab [] = array (
				'title' => '添加专题',
				'tabHash' => 'addspecial',
				'url' => U ( 'classroom/AdminSpecial/addspecial' ) 
		);
		$this->pageTab [] = array (
				'title' => '添加专题分类',
				'tabHash' => 'addcategory',
				'url' => U ( 'classroom/AdminSpecial/addcategory' ) 
		);
	}
	
	/**
	 * 专题列表管理
	 * @return void
	 */
	public function special()
	{
		$_REQUEST['tabHash'] = 'special';
		//取得所有的专辑分类
		$specialCategory = model ( 'ZySpecialCategory' )->getHashSpecialCategory();
		$specialCategory[0] = "全部";
		arsort($specialCategory,SORT_NUMERIC);
		$this->_initTabSpecial();
		$this->pageKeyList = array (
			'id','sc_id','title','intro','cover','attach_id','src','foldername','sort','ctime','utime','DOACTION'
		);	
		$this->searchKey    = array('id','sc_id','title',array('ctime','ctime1'));
		$this->opt['sc_id'] = $specialCategory;		
		$this->pageButton[] = array('title'=>'添加专题','onclick'=>"location.href='".U('classroom/AdminSpecial/addspecial','tabHash=addspecial')."'");
		$this->pageButton[] = array('title'=>'添加专题分类','onclick'=>"location.href='".U('classroom/AdminSpecial/addcategory','tabHash=addcategory')."'");
		$this->pageButton[] = array('title'=>'删除专题','onclick'=>"admin.mzSpecialEdit('','delspecial','删除','专题')");
		$this->pageButton[] = array('title'=>'搜索','onclick'=>"admin.fold('search_form')");
		$listData = model ( 'ZySpecial' )->getSpecialList(20,array(),"sort DESC");
		foreach($listData['data'] as &$value){
			$cover              = str_replace('|','',$value['cover']);
			$value['title']     = '<a target="_bank" href="'.$value['src'].'">'.$value['title'].'</a>';
			$value['cover']     = '<a target="_bank" href="'.getAttachUrlByAttachId($cover).'">封面预览</a>';
			$value['attach_id'] = '<a href="'.U('widget/Upload/down','attach_id='.(str_replace('|','',$value['attach_id']))).'">点击下载</a>';
			$value['sc_id']     = $specialCategory[$value['sc_id']];
			$value['ctime']     = date('Y-m-d',$value['ctime']);
			$value['DOACTION']  = '<a href="'.U('classroom/AdminSpecial/addspecial','tabHash=special&id='.$value['id']).'">编辑</a>';
			$value['DOACTION'] .= '&nbsp;&nbsp;<a href="javascript:void(0);" onclick="admin.mzSpecialEdit('.$value['id'].',\'delspecial\',\'删除\',\'专题\');">删除</a>';
			$value['DOACTION'] .= '&nbsp;&nbsp;<a href="#" onclick="admin.mzSpecialAddCover('.$value['id'].');">添加封面</a>';
		}
		$this->allSelected = true;
		$this->displayList($listData);
	}
	
	/**
	 * 专题分类列表管理
	 * @return void
	 */
	public function category()
	{
		$_REQUEST['tabHash'] = 'category';
		$this->_initTabSpecial();
		$this->pageKeyList = array (
			'id','title','cover','intro','sort','src','templet','ctime','DOACTION'
		);
		$this->searchKey    = array('id','title',array('ctime','ctime1'));
		$this->pageButton[] = array('title'=>'添加专题分类','onclick'=>"location.href='".U('classroom/AdminSpecial/addcategory','tabHash=addcategory')."'");
		$this->pageButton[] = array('title'=>'删除专题分类','onclick'=>"admin.mzSpecialEdit('','delcategory','删除','专题分类')");
		$this->pageButton[] = array('title'=>'搜索','onclick'=>"admin.fold('search_form')");
		$listData = model ( 'ZySpecialCategory' )->getSpecialCategoryList(20,array(),"sort DESC");
		foreach($listData['data'] as &$value){
			$value['title'] = '<a target="_bank" href="'.$value['src'].'">'.$value['title'].'</a>';
			$value['cover'] = '<a target="_bank" href="'.getAttachUrlByAttachId($value['cover']).'">封面预览</a>';
			$value['ctime']     = date('Y-m-d',$value['ctime']);
			
			$value['DOACTION']  = '<a href="'.U('classroom/AdminSpecial/addcategory','tabHash=category&id='.$value['id']).'">编辑</a>';
			$value['DOACTION'] .= '&nbsp;&nbsp;<a href="javascript:void(0);" onclick="admin.mzSpecialEdit('.$value['id'].',\'delcategory\',\'删除\',\'专题分类\');">删除</a>';
		}
		$this->allSelected = true;
		$this->displayList($listData);
	}
	
	/**
	 * 处理【添加/保存】专题分类
	 * @return void
	 */
	public function docategory(){
		$id   = intval($_GET['id']);
		$type = t($_GET['type']);
		
		if($type == 'add'){
			//添加数据了
			$map ['title']      = t($_POST['title']) ;
			$map ['cover']      = intval($_POST['cover']) ;
			$map ['templet']    = t($_POST['templet']) ;
			$map ['sort']       = intval($_POST['sort']) ;
			$map ['intro']      = t ($_POST['intro']) ;
			$map ['utime']      = $map ['ctime'] = time() ;
			
			//数据验证
			if(!$map ['title']){
				$this->error('专题分类标题不能为空!');
			}
			if(!$map ['cover']){
				$this->error('请上传封面!');
			}
			if(!$map ['templet']){
				$this->error('模板名称不能为空!');
			}
			
			$i = model ( 'ZySpecialCategory' )->add($map);
			if($i === false){
				$this->error('添加专题分类失败!');
			}
			$this->assign ( 'jumpUrl', U ( 'classroom/AdminSpecial/category','tabHash=category') );
						
			//重新取得url
			$where['id']  = $i;
			$where['src'] = U('classroom/Index/special','scid='.$i);
			model ('ZySpecialCategory' )->save($where);
			
			//清楚缓存
			model ( 'ZySpecialCategory' )->cleanCache ();
			
			$this->success ('添加专题分类成功!');
		}else if($id && $type == 'save'){
			//保存数据了
			$map ['id']         = $id;
			//添加数据了
			$map ['title']      = t($_POST['title']) ;
			$map ['cover']      = intval($_POST['cover']) ;
			$map ['templet']    = t($_POST['templet']) ;
			$map ['sort']       = intval($_POST['sort']) ;
			$map ['intro']      = t ($_POST['intro']) ;
			$map ['utime']      = time() ;
			
			//数据验证
			if(!$map ['title']){
				$this->error('专题分类标题不能为空!');
			}
			if(!$map ['cover']){
				$this->error('请上传封面!');
			}
			if(!$map ['templet']){
				$this->error('模板名称不能为空!');
			}
			
			$i = model ( 'ZySpecialCategory' )->save($map);
			if($i === false){
				$this->error('保存专题失败!');
			}
			$this->assign ( 'jumpUrl', U ( 'classroom/AdminSpecial/category','tabHash=category') );
			
			//重新取得url
			$where['id']  = $map ['id'];
			$where['src'] = U('classroom/Index/special','scid='.$map ['id']);
			model ('ZySpecialCategory' )->save($where);
			//清楚缓存
			model ( 'ZySpecialCategory' )->cleanCache ();
			
			$this->success ('保存专题分类成功!');
		}else{
			$this->error('参数错误!');
		}
	}
	
	/**
	 * 处理专题src
	 * @return void
	 */
	private function _dealSpecialSrc($data,$type){
		$special_path = SITE_PATH.DIRECTORY_SEPARATOR.'special'.DIRECTORY_SEPARATOR;
		if (is_dir($special_path) == false){
			mkdir($special_path, 0700);
		}
		//拼接新的目录
		$new = $special_path.$data['foldername'].DIRECTORY_SEPARATOR;
		
		if($type == 'save'){
			tsload(ADDON_PATH.'/library/io/Dir.class.php');
			$dirs = new Dir();
			//拼接旧的目录
			$old = $special_path.$data['oldfoldername'].DIRECTORY_SEPARATOR;
			if (is_dir($old)){
				$dirs->delDir($old);
			}
			
		}
		//创建新的目录
		if (is_dir($new) == false){
			mkdir($new, 0700);
		}
		//处理附件ID
		$attach_id = str_replace('|','',$data['attach_id']);
		//获取附件物理路径
		$attachPath = getAttachPathByAttachId(intval($attach_id));
		//取得路径基础信息
		$attachInfo = pathinfo($attachPath);
		if(strtolower($attachInfo['extension']) !== 'zip'){
			$this->_dealData($new,$data);
			$this->error('请上传ZIP格式的压缩文件!');
		}
		
		/*if(!function_exists('ZipArchive')){
			$this->error('PHP 【ZipArchive】 模块不存在,请开启之后重试!');
		}*/
		
		//解压压缩包---到新的目录
		$zip = new ZipArchive();
		if ($zip->open(UPLOAD_PATH.DIRECTORY_SEPARATOR.$attachPath) !== TRUE){
			$this->_dealData($new,$data);
			$this->error('解压出错,文件可能已损坏,此数据将会被删除!');
		}
		$zip->extractTo($new);
		$zip->close();
		
		//更新src
		$map['id']  = $data['id'];
		$map['src'] = SITE_URL.'/special/'.$data['foldername'];
		//print_r($map);exit;
		model ( 'ZySpecial' )->save($map);
		return true;
	}
	/**
	 * 解压处理不正确之后  数据回滚
	 * @return void
	 */
	private function _dealData($new,$data){
		//删除数据库和文件夹
		if (is_dir($new)){
			tsload(ADDON_PATH.'/library/io/Dir.class.php');
			$dirs = new Dir();
			$dirs->delDir($new);
		}
		model ( 'ZySpecial' )->where(array('id'=>array('eq',$data ['id'])))->delete();
	}
	/**
	 * 处理【添加/保存】专题
	 * @return void
	 */
	public function dospecial(){
		$id   = intval($_GET['id']);
		$type = t($_GET['type']);
		
		if($type == 'add'){
			//添加数据了
			$map ['sc_id']      = intval($_POST['sc_id']);
			$map ['title']      = t($_POST['title']) ;
			$map ['attach_id']  = t($_POST['attach_id_ids']) ;
			$map ['cover']      = t($_POST['cover']) ;
			$map ['foldername'] = t ($_POST['foldername']) ;
			$map ['sort']       = intval($_POST['sort']) ;
			$map ['intro']      = t ($_POST['intro']) ;
			$map ['utime']      = $map ['ctime'] = time() ;
			
			//数据验证
			if(!$map ['sc_id']){
				$this->error('请选择专题分类!');
			}
			if(!$map ['title']){
				$this->error('专题名称不能为空!');
			}
			if(!$map ['attach_id']){
				$this->error('请上传附件!');
			}
			if(!$map ['foldername']){
				$this->error('文件夹不能为空!');
			}
			
			//判断文件夹是否有重复的
			$count = model ( 'ZySpecial' )->where(array('foldername'=>array('eq',$map ['foldername'])))->count();
			if($count){
				$this->error('文件夹已经存在了!');
			}
			$i = model ( 'ZySpecial' )->add($map);
			if($i === false){
				$this->error('添加专题失败!');
			}
			$this->assign ( 'jumpUrl', U ( 'classroom/AdminSpecial/special','tabHash=special') );
			
			$map['id'] = $i;
			$this->_dealSpecialSrc($map,$type);
			
			$this->success ('添加专题成功!');
		}else if($id && $type == 'save'){
			//保存数据了
			$map ['id']         = $id;
			$map ['sc_id']      = intval($_POST['sc_id']);
			$map ['title']      = t($_POST['title']) ;
			$map ['attach_id']  = t($_POST['attach_id_ids']) ;
			$map ['foldername'] = t($_POST['foldername']) ;
			$map ['sort']       = intval($_POST['sort']) ;
			$map ['intro']      = t($_POST['intro']) ;
			$map ['utime']      = time() ;
			
			//数据验证
			if(!$map ['sc_id']){
				$this->error('请选择专题分类!');
			}
			if(!$map ['title']){
				$this->error('专题名称不能为空!');
			}
			if(!$map ['attach_id']){
				$this->error('请上传附件!');
			}
			if(!$map ['foldername']){
				$this->error('文件夹不能为空!');
			}
			
			//判断修改了文件夹没有
			$foldername = model ( 'ZySpecial' )->where(array('id'=>array('eq',$map ['id'])))->getField('foldername');
			if($foldername != $map ['foldername']){
				$map ['oldfoldername'] = $foldername;
				//判断文件夹是否有重复的
				$count = model ( 'ZySpecial' )->where(array('foldername'=>array('eq',$map ['foldername'])))->count();
				if($count){
					$this->error('文件夹已经存在了!');
				}
			}else{
				$map ['oldfoldername'] = $map ['foldername'];
			}
			
			$i = model ( 'ZySpecial' )->save($map);
			if($i === false){
				$this->error('保存专题失败!');
			}
			$this->assign ( 'jumpUrl', U ( 'classroom/AdminSpecial/special','tabHash=special') );
			
			$this->_dealSpecialSrc($map,$type);
			
			$this->success ('保存专题成功!');
		}else{
			$this->error('参数错误!');
		}
	}
	
	
	/**
	 * 添加专题
	 * @return void
	 */
	public function addspecial()
	{
		$id   = intval($_GET['id']);
		
		//取得所有的专辑分类
		$specialCategory = model ( 'ZySpecialCategory' )->getHashSpecialCategory();
		
		$this->_initTabSpecial();
		
		$this->opt ['sc_id'] = $specialCategory;
		
		$this->pageKeyList = array (
			'sc_id','title','foldername','attach_id','sort','intro'
		);
		
		$this->notEmpty = array (
				'sc_id',
				'title',
				'attach_id',
				'foldername',
		);
		
		$this->onsubmit = 'admin.checkSpecialInfo(this)';
		
		if($id){
			$this->savePostUrl = U ( 'classroom/AdminSpecial/dospecial','type=save&id='.$id);
			$zySpecial = model ( 'ZySpecial' )->where( 'id=' .$id )->find ();
			$zySpecial['attach_id'] = attachzh($zySpecial['attach_id']);
			//print_r($zySpecial);
			//说明是编辑
			$this->displayConfig($zySpecial);
		}else{
			$this->savePostUrl = U ('classroom/AdminSpecial/dospecial','type=add');
			//说明是添加
			$this->displayConfig ();
		}
		
	}

	/**
	 * 添加专题分类
	 * @return void
	 */
	public function addcategory()
	{
		$id   = intval($_GET['id']);
		$this->_initTabSpecial();
		
		$this->pageKeyList = array (
			'title','cover','templet','sort','intro'
		);
		$this->notEmpty = array (
				'title',
				'cover',
				'templet',
		);
		$this->onsubmit = 'admin.checkCategoryInfo(this)';
		if($id){
			$this->savePostUrl = U ( 'classroom/AdminSpecial/docategory','type=save&id='.$id);
			$zySpecial = model ( 'ZySpecialCategory' )->where( 'id=' .$id )->find ();
			//说明是编辑
			$this->displayConfig($zySpecial);
		}else{
			$this->savePostUrl = U ('classroom/AdminSpecial/docategory','type=add');
			//说明是添加
			$this->displayConfig ();
		}
	}

	
	/**
	 * 删除专题分类
	 * @return void
	 */
	public function delcategory()
	{
		$return =  model('ZySpecialCategory')->doDeleteSpecialCategory($_POST['id']);
		
		if($return['status'] == 1){
			//清楚缓存
			model ( 'ZySpecialCategory' )->cleanCache ();
			$return['data'] = L('PUBLIC_DELETE_SUCCESS');
		}elseif($return['status'] === false){
			$return['data'] = L('PUBLIC_DELETE_FAIL');
		}elseif($return['status'] == 100003){
			$return['data'] = '请选择要删除的内容';
		}elseif($return['status'] == 100001){
			$return['data'] = '该分类存在专题,不能删除!';
		}else{
			$return['data'] = '操作错误';	
		}
		echo json_encode($return);exit();
	}
	/**
	 * 删除专题
	 * @return void
	 */
	public function delspecial()
	{
		$return =  model('ZySpecial')->doDeleteSpecial($_POST['id']);
		
		if($return['status'] == 1){
			$return['data'] = L('PUBLIC_DELETE_SUCCESS');
		}elseif($return['status'] === false){
			$return['data'] = L('PUBLIC_DELETE_FAIL');
		}elseif($return['status'] == 100003){
			$return['data'] = '请选择要删除的内容';
		}else{
			$return['data'] = '操作错误';	
		}
		echo json_encode($return);exit();
	}
	/**
	 * 添加专题封面
	 * @return void
	 */
	public function addcover(){
		$sid = intval($_GET['sid']);
		$cover = model('ZySpecial')->where(array('id'=>array('eq',$sid)))->getField('cover');
		
		$this->assign('cover',$cover);
		$this->assign('sid',$sid);
		
		$this->display('addcover');
	}
	/**
	 * 处理添加专题封面
	 * @return void
	 */
	public function doaddcover(){
		$id    = intval($_POST['id']);
		$cover = t($_POST['cover_ids']);
		
		$map['id'] = $id;
		$map['cover'] = $cover;
		$i = model('ZySpecial')->save($map);
		if($i === false){
			echo json_encode(array(
				'status'=>1000,
			));exit;
		}
		echo json_encode(array(
				'status'=>1001,
			));exit;
	}


}