<?php
/**
 * 系列连载管理
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class AdminLianZaiAction extends AdministratorAction{
    //系列连载内容模型对象
    protected $zy_lz_contentMod = null;
    //系列连载栏目模型对象
    protected $zy_lz_categoryMod = null;
	//系列连载期模型对象
    protected $zy_lz_dateMod = null;
	
    /**
     * 初始化，配置页面标题；创建模型对象
     * @return void
     */
    public function _initialize(){
		$this->assign('isAdmin',1);	//是否后台
		
		//实例化模型
		$this->zy_lz_contentMod  = D('ZyLzContent');
		$this->zy_lz_categoryMod = D('ZyLzCategory');
		$this->zy_lz_dateMod     = D('ZyLzDate');
		
		
		$this->pageTitle['index']       = '连载内容管理';
		$this->pageTitle['xdate']       = '连载分期管理';
		$this->pageTitle ['addcontent'] = intval($_GET['id'])?'编辑内容':'添加内容';
		$this->pageTitle ['addvideo']   = intval($_GET['id'])?'编辑视频':'添加视频';
		$this->pageTitle ['addarticle'] = intval($_GET['id'])?'编辑文章':'添加文章';
		$this->pageTitle ['addxdate']   = intval($_GET['id'])?'编辑分期':'添加分期';
		
		parent::_initialize();
    }
	
	/**
     * 初始化页面tab
     */
	private function _initTab(){
		// Tab选项
		$this->pageTab [] = array (
				'title' => '连载内容管理',
				'tabHash' => 'index',
				'url' => U ( 'classroom/AdminLianZai/index' ) 
		);
		$this->pageTab [] = array (
				'title' => '连载分期管理',
				'tabHash' => 'xdate',
				'url' => U ( 'classroom/AdminLianZai/xdate' ) 
		);
		$this->pageTab [] = array (
				'title' => '添加内容',
				'tabHash' => 'addcontent',
				'url' => U ( 'classroom/AdminLianZai/addcontent' ) 
		);
		$this->pageTab [] = array (
				'title' => '添加视频',
				'tabHash' => 'addvideo',
				'url' => U ( 'classroom/AdminLianZai/addvideo' ) 
		);
		$this->pageTab [] = array (
				'title' => '添加文章',
				'tabHash' => 'addarticle',
				'url' => U ( 'classroom/AdminLianZai/addarticle' ) 
		);
		$this->pageTab [] = array (
				'title' => '添加分期',
				'tabHash' => 'addxdate',
				'url' => U ( 'classroom/AdminLianZai/addxdate' ) 
		);
	}

    /**
     * 系列连载内容列表
     */
    public function index(){
       $_REQUEST['tabHash'] = 'index';
		$sCategory = $this->zy_lz_dateMod->getHashXdate();
		$sCategory[0] = "全部";
		arsort($sCategory,SORT_NUMERIC);
		
		$this->_initTab();
		//搜索配置
		$this->searchKey    = array('id','type','did','title','description','source',array('ctime','ctime1'));
		$this->opt['did']   = $sCategory;
		$this->opt['type']  = array(
			'0'=>'全部',
			'1'=>'图文类型',
			'2'=>'文章类型',
			'3'=>'视频类型',
		);
		
		$this->pageKeyList = array (
			'id','type','did','title','description','source','attach','ctime','DOACTION'
		);
		
		$this->pageButton[] = array('title'=>'添加内容','onclick'=>"location.href='".U('classroom/AdminLianZai/addcontent','tabHash=addcontent')."'");
		$this->pageButton[] = array('title'=>'添加视频','onclick'=>"location.href='".U('classroom/AdminLianZai/addvideo','tabHash=addvideo')."'");
		$this->pageButton[] = array('title'=>'添加文章','onclick'=>"location.href='".U('classroom/AdminLianZai/addarticle','tabHash=addarticle')."'");
		$this->pageButton[] = array('title'=>'搜索内容','onclick'=>"admin.fold('search_form')");
		
		
		$listData = $this->zy_lz_contentMod->getContentList(20,array(),"ctime DESC");
		foreach($listData['data'] as &$value){
			$value['ctime']  = date('Y-m-d H:i:s',$value['ctime']);
			
			$value['title']        = '<div style="width:200px;">'.msubstr($value['title'],0,16).'</div>';
			$value['description']  = '<div style="width:200px;">'.msubstr($value['description'],0,16).'</div>';
			$value['source']       = '<div style="width:200px;">'.msubstr($value['source'],0,16).'</div>';
			
			if($value['type'] == 1){
				$value['attach']       = '<a target="_blank" href="'.getImageUrlByAttachId(attachzh($value['attach'])).'">预览图片</a>';
				$value['DOACTION']     = '<a href="'.U('classroom/AdminLianZai/addcontent','tabHash=index&id='.$value['id']).'">编辑</a>';
				$value['did']          = $sCategory[$value['did']];
				$value['type']         = '图文';
			}else if($value['type'] == 2){
				$value['attach']       = '无';
				$value['DOACTION']     = '<a href="'.U('classroom/AdminLianZai/addarticle','tabHash=index&id='.$value['id']).'">编辑</a>';
				$value['did']          = '无';
				$value['type']         = '文章';
			}else if($value['type'] == 3){
				$value['attach']       = '<a target="_blank" href="'.getImageUrlByAttachId(attachzh($value['attach'])).'">预览图片</a>';
				$value['DOACTION']     = '<a href="'.U('classroom/AdminLianZai/addvideo','tabHash=index&id='.$value['id']).'">编辑</a>';	
				$value['did']          = $sCategory[$value['did']];
				$value['type']         = '视频';
			}
			
			$value['DOACTION'] .= '&nbsp;&nbsp;<a href="javascript:void(0);" onclick="admin.mzLzContentEdit('.$value['id'].',\'dellzContent\',\'删除\',\'连载内容\');">删除</a>';	
		}
		$this->allSelected = false;
		$this->displayList($listData);
    }

	/**
     * 系列连载期列表
     */
    public function xdate(){
		$_REQUEST['tabHash'] = 'xdate';
		$this->_initTab();
		
		//取得所有的连载分类
		$sCategory = $this->zy_lz_categoryMod->getHashCategory();
		$sCategory[0] = "全部";
		arsort($sCategory,SORT_NUMERIC);
		
		//搜索配置
		$this->searchKey    = array('id','cid','name',array('ctime','ctime1'));
		$this->opt['cid'] = $sCategory;
		
		
		$this->pageKeyList = array (
			'id','cid','name','ctime','DOACTION'
		);
		$this->pageButton[] = array('title'=>'添加分期','onclick'=>"location.href='".U('classroom/AdminLianZai/addxdate','tabHash=addxdate')."'");
		$this->pageButton[] = array('title'=>'搜索分期','onclick'=>"admin.fold('search_form')");
		
		$listData = $this->zy_lz_dateMod->getXDateList(20,array(),"ctime DESC");
		foreach($listData['data'] as &$value){
			$value['name']   = '<a href="'.U('classroom/Serial/scontent','cid='.$value['cid'].'&did='.$value['id']).'" target="_blank">'.$value['name'].'</a>';
			$value['cid']    = $sCategory[$value['cid']];
			$value['ctime']  = date('Y-m-d H:i:s',$value['ctime']);
			
			
			$value['DOACTION']  = '<a href="'.U('classroom/AdminLianZai/addxdate','tabHash=xdate&id='.$value['id']).'">编辑</a>';
			$value['DOACTION'] .= '&nbsp;&nbsp;<a href="javascript:void(0);" onclick="admin.mzXdateEdit('.$value['id'].',\'delxdate\',\'删除\',\'分期\');">删除</a>';
			$value['DOACTION'] .= '&nbsp;&nbsp;<a href="'.U('classroom/AdminLianZai/addcontent','tabHash=addcontent&did='.$value['id']).'">添加内容</a>';
			$value['DOACTION'] .= '&nbsp;&nbsp;<a href="'.U('classroom/AdminLianZai/addvideo','tabHash=addvideo&did='.$value['id']).'">添加视频</a>';
			$value['DOACTION'] .= '&nbsp;&nbsp;<a href="'.U('classroom/AdminLianZai/addarticle','tabHash=addarticle&did='.$value['id']).'">添加文章</a>';
		}
		
		$this->allSelected = false;
		$this->displayList($listData);
    }
	/**
     * 添加连载分期
     */
	public function addxdate(){
		$id   = intval($_GET['id']);
		$this->_initTab();
		
		if($id){
			$this->pageKeyList = array (
				'name'
			);
			$this->notEmpty = array (
				'name'
			);
			$this->onsubmit = 'admin.checkxdateInfo(this,\'save\')';
			
			$this->savePostUrl = U ( 'classroom/AdminLianZai/doaddxdate','type=save&id='.$id);
			$zySpecial = $this->zy_lz_dateMod->where( 'id=' .$id )->find ();
			$this->displayConfig($zySpecial);
		}else{
			//取得所有的连载分类
			$sCategory = $this->zy_lz_categoryMod->getHashCategory();
			$this->opt ['cid'] = $sCategory;
			$this->pageKeyList = array (
				'cid','name'
			);
			
			$this->notEmpty = array (
				'name',
				'cid',
			);
			$this->onsubmit = 'admin.checkxdateInfo(this,\'add\')';
			
			$this->savePostUrl = U ('classroom/AdminLianZai/doaddxdate','type=add');
			//说明是添加
			$this->displayConfig ();
		}
	}
	/**
     * 添加连载内容
     */
	public function addcontent(){
		$id    = intval($_GET['id']);
		$did   = intval($_GET['did']);
		$this->_initTab();
		
		if($id){
			$this->pageKeyList = array (
				'istop','title','description','source','attach'
			);
			$this->opt['istop'] = array(
				'1'=>'是',
				'0'=>'不是',
			);
			$this->notEmpty = array (
				'istop','title','description','source','attach'
			);
			
			$this->onsubmit = 'j_validateCallback(this,addcontentcheckForm,addcontentpost_callback)';
			
			$this->savePostUrl = U ( 'classroom/AdminLianZai/doaddcontent','type=save&id='.$id);
			$zySpecial = $this->zy_lz_contentMod->where( 'id=' .$id )->find ();
			
			//默认值
			$this->assign('defaultS',attachzh($zySpecial['attach']));
			//上传内容类型
			$this->assign('type',$zySpecial['type']);
			//标志是修改
			$this->assign('stype','save');
			//自定义文件内容
			$mydefine = $this->fetch('myfile');
			$zySpecial['definename'] = $mydefine;
			$this->displayConfig($zySpecial);
		}else{
			//取得所有的连载分期
			$sCategory = $this->zy_lz_dateMod->getHashXdate();
			$this->opt ['did'] = $sCategory;
			$this->pageKeyList = array (
				'istop','did','title','description','source','attach'
			);
			$this->opt['istop'] = array(
				'1'=>'是',
				'0'=>'不是',
			);
			$this->notEmpty = array (
				'did',
				'title',
				'description',
				'source',
				'attach'
			);
			$this->onsubmit = 'j_validateCallback(this,checklzindexInfo,addcontentpost_callback)';
			
			$this->savePostUrl = U ('classroom/AdminLianZai/doaddcontent','type=add');
			
			//说明是添加
			$this->displayConfig (array('did'=>$did));
		}
	}
	
	/**
     * 添加连载分类
     */
	public function doaddxdate(){
		$id   = intval($_GET['id']);
		$type = t($_GET['type']);
		if($type == 'add'){
			//添加数据了
			$map['cid']   = intval($_POST['cid']);
			$map['name']  = t($_POST['name']);
			$map['ctime'] = time();
			
			$i = $this->zy_lz_dateMod->add($map);
			if($i === false){
				$this->error('添加分期失败!');
			}
			$this->assign ( 'jumpUrl', U ( 'classroom/AdminLianZai/xdate','tabHash=xdate') );
			$this->success ('添加分期成功!');
		}else if($id && $type == 'save'){
			//保存数据了
			$map['id']    = $id;
			$map['name']  = t($_POST['name']);
			$i = $this->zy_lz_dateMod->save($map);
			if($i === false){
				$this->error('保存分期失败!');
			}
			$this->assign ( 'jumpUrl', U ( 'classroom/AdminLianZai/xdate','tabHash=xdate') );
			$this->success ('保存分期成功!');
		}else{
			$this->error('参数错误!');
		}
	}
	/**
     * 删除连载分期
     */
	public function delxdate(){
		$return =  $this->zy_lz_dateMod->dodelxdate(intval($_POST['id']));
		
		if($return['status'] == 1){
			$return['data'] = L('PUBLIC_DELETE_SUCCESS');
		}elseif($return['status'] === false){
			$return['data'] = L('PUBLIC_DELETE_FAIL');
		}elseif($return['status'] == 100003){
			$return['data'] = '请选择要删除的内容';
		}elseif($return['status'] == 100004){
			$return['data'] = '该分期下面有内容，不能删除';
		}else{
			$return['data'] = '操作错误';	
		}
		echo json_encode($return);exit();
	}
	/**
     * 删除连载内容
     */
	public function dellzContent(){
		$return =  $this->zy_lz_contentMod->dodellzcontent($_POST['id']);
		
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
     * 添加连载内容
     */
	public function doaddcontent(){
		$id   = intval($_GET['id']);
		$type = t($_GET['type']);
		if($type == 'add'){
			//添加数据了
			$map['istop']       = intval($_POST['istop']);
			$map['did']         = intval($_POST['did']);
			$map['title']       = t($_POST['title']);
			$map['description'] = t($_POST['description']);
			$map['source']      = t($_POST['source']);
			$map['attach']      = t($_POST['attach']);
			$map['type']        = 1;
			$map['ctime']       = time();
			
			if(!$map['attach']){
				$this->error('图片附件不能为空!');
			}
			
			$i = $this->zy_lz_contentMod->add($map);
			if($i === false){
				$this->error('添加连载内容失败!');
			}
			$this->assign ( 'jumpUrl', U ( 'classroom/AdminLianZai/index','tabHash=index') );
			$this->success ('添加连载内容成功!');
		}else if($id && $type == 'save'){
			//保存数据了
			$map['id']    = $id;
			$map['istop']       = intval($_POST['istop']);
			$map['title']       = t($_POST['title']);
			$map['description'] = t($_POST['description']);
			$map['source']      = t($_POST['source']);
			$map['attach']      = t($_POST['attach']);
			
			if(!$map['attach']){
				$this->error('图片附件不能为空!');
			}
			
			$i = $this->zy_lz_contentMod->save($map);
			if($i === false){
				$this->error('保存连载内容失败!');
			}
			$this->assign ( 'jumpUrl', U ( 'classroom/AdminLianZai/index','tabHash=index') );
			$this->success ('保存连载内容成功!');
		}else{
			$this->error('参数错误!');
		}
		
	}
	
	/**
     * 添加连载文章内容
     */
	public function addarticle(){
		$id    = intval($_GET['id']);
		$this->_initTab();
		
		if($id){
			$this->pageKeyList = array (
				'istop','title','description','source'
			);
			$this->notEmpty = array (
				'title','description','source'
			);
			$this->opt['istop'] = array(
				'1'=>'是',
				'0'=>'不是',
			);
			$this->onsubmit = 'admin.checklzarticleInfo(this)';
			
			$this->savePostUrl = U ( 'classroom/AdminLianZai/doaddarticle','type=save&id='.$id);
			$zySpecial = $this->zy_lz_contentMod->where( 'id=' .$id )->find ();
			$this->displayConfig($zySpecial);
		}else{
			$this->pageKeyList = array (
				'istop','title','source','description',
			);
			$this->opt['istop'] = array(
				'1'=>'是',
				'0'=>'不是',
			);
			$this->notEmpty = array (
				'title',
				'source',
				'description',
			);
			$this->onsubmit = 'admin.checklzarticleInfo(this)';
			
			$this->savePostUrl = U ('classroom/AdminLianZai/doaddarticle','type=add');
			//说明是添加
			$this->displayConfig ();
		}
	}
	/**
     * 处理  添加和修改--连载文章内容
     */
	public function doaddarticle(){
		$id      = intval($_GET['id']);
		$type    = t($_GET['type']);
		
		if($type == 'add'){
			//添加数据了
			$map['title']       = t($_POST['title']);
			$map['istop']       = intval($_POST['istop']);
			$map['description'] = t($_POST['description']);
			$map['source']      = t($_POST['source']);
			$map['type']        = 2;
			$map['ctime']       = time();
			
			$i = $this->zy_lz_contentMod->add($map);
			if($i === false){
				$this->error('添加文章失败!');
			}
			$this->assign ( 'jumpUrl', U ( 'classroom/AdminLianZai/index','tabHash=index') );
			$this->success ('添加文章成功!');
		}else if($id && $type == 'save'){
			//保存数据了
			$map['id']    = $id;
			$map['title']       = t($_POST['title']);
			$map['istop']       = intval($_POST['istop']);
			$map['description'] = t($_POST['description']);
			$map['source']      = t($_POST['source']);
			
			$i = $this->zy_lz_contentMod->save($map);
			if($i === false){
				$this->error('保存文章失败!');
			}
			$this->assign ( 'jumpUrl', U ( 'classroom/AdminLianZai/index','tabHash=index') );
			$this->success ('保存文章成功!');
		}else{
			$this->error('参数错误!');
		}
		
	}
	
	
	/**
     * 添加连载视频
     */
	public function addvideo(){
		$id    = intval($_GET['id']);
		$did   = intval($_GET['did']);
		$this->_initTab();
		
		if($id){
			$this->pageKeyList = array (
				'istop','title','description','source','attach','definename'
			);
			$this->opt['istop'] = array(
				'1'=>'是',
				'0'=>'不是',
			);
			$this->notEmpty = array (
				'title','description','source','attach','definename'
			);
			
			$this->onsubmit = 'j_validateCallback(this,addcontentcheckForm,addcontentpost_callback)';
			
			$this->savePostUrl = U ( 'classroom/AdminLianZai/doaddvideo','type=save&id='.$id);
			$zySpecial = $this->zy_lz_contentMod->where( 'id=' .$id )->find ();
			
			$this->assign('defaultS',$zySpecial['video_id']);
			//自定义文件内容
			$mydefine = $this->fetch('myfile');
			$zySpecial['definename'] = $mydefine;
			$this->displayConfig($zySpecial);
		}else{
			//取得所有的连载分期
			$sCategory = $this->zy_lz_dateMod->getHashXdate();
			$this->opt ['did'] = $sCategory;
			
			$this->pageKeyList = array (
				'istop','did','title','description','source','attach','definename'
			);
			$this->opt['istop'] = array(
				'1'=>'是',
				'0'=>'不是',
			);
			$this->notEmpty = array (
				'did',
				'title',
				'description',
				'source',
				'attach',
			);
			$this->onsubmit = 'j_validateCallback(this,checklzindexInfo,addcontentpost_callback)';
			
			$this->savePostUrl = U ('classroom/AdminLianZai/doaddvideo','type=add');
			
			
			
			$this->assign('defaultS','');
			//自定义文件内容
			$mydefine = $this->fetch('myfile');
			//说明是添加
			$this->displayConfig (array('did'=>$did,'definename'=>$mydefine));
		}
	}
	
	
	
	
	/**
     * 处理  添加和修改--连载文章内容
     */
	public function doaddvideo(){
		$id      = intval($_GET['id']);
		$type    = t($_GET['type']);
		
		if($type == 'add'){
			//添加数据了
			$map['istop']       = intval($_POST['istop']);
			$map['did']         = t($_POST['did']);
			$map['title']       = t($_POST['title']);
			$map['description'] = t($_POST['description']);
			$map['source']      = t($_POST['source']);
			$map['attach']      = t($_POST['attach']);
			$map['video_id']    = t($_POST['video_id']);
			$map['type']        = 3;
			$map['ctime']       = time();
			
			if(!$map['attach']){
				$this->error('图片附件不能为空!');
			}
			if(!$map['video_id']){
				$this->error('视频不能为空!');
			}
			
			$i = $this->zy_lz_contentMod->add($map);
			if($i === false){
				$this->error('添加视频失败!');
			}
			$this->assign ( 'jumpUrl', U ( 'classroom/AdminLianZai/index','tabHash=index') );
			$this->success ('添加视频成功!');
		}else if($id && $type == 'save'){
			//保存数据了
			$map['id']    = $id;
			$map['title']       = t($_POST['title']);
			$map['istop']       = intval($_POST['istop']);
			$map['description'] = t($_POST['description']);
			$map['source']      = t($_POST['source']);
			$map['attach']      = t($_POST['attach']);
			$map['video_id']    = t($_POST['video_id']);
			
			if(!$map['attach']){
				$this->error('图片附件不能为空!');
			}
			if(!$map['video_id']){
				$this->error('视频不能为空!');
			}
			
			$i = $this->zy_lz_contentMod->save($map);
			if($i === false){
				$this->error('保存视频失败!');
			}
			$this->assign ( 'jumpUrl', U ( 'classroom/AdminLianZai/index','tabHash=index') );
			$this->success ('保存视频成功!');
		}else{
			$this->error('参数错误!');
		}
		
	}
	
	

}