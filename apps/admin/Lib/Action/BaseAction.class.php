<?php
/**
 * 后台基础数据管理
 * @author zhangr <zhangr@higher-edu.cn>
 * @version ZR2.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
tsload(Core_PATH.'/OpenSociax/Page.class.php');
class BaseAction extends Action{
    // 每页显示的条目
    var $count_per_page = 3;
	
    public function _initialize() {
		$this->assign('isAdmin', 1);
	}
	
    /**
     * 栏目创建页面
     * @return void
     */
    public function addLanmu()
    {   
	    $columns     = M('Column')->getColumn();   //获取所有栏目
		
		$this->assign('columns',$columns);
		
		$this->display();
	}
	
	 /**
     * 获取子菜单
     * @return void
     */
    public function ajax_addColumn()
    {   
		$reid              = intval($_POST['reid']);
		$level             = intval($_POST['level']);
		
		$columns           = M('Column')->getColumn($reid,$level);  //获取所有栏目
		
		$this->assign('columns',$columns);
		
		$this->display('sonLanmu');
	}
	
	 /**
     * 删除子栏目
     * @return void
     */
    public function ajax_delColumn()
    {   
		$id            = intval($_POST['id']);
		
		$res           = M('Column')->delColumn($id);  //通过ID删除栏目
		
		if($res>0){
			echo 1;
		}else{
			echo 0;
		}
	}
	
	/**
     * 添加栏目
     * @return void
     */
    public function addColumn()
    {   
		$this->assign('lists',$lists);
		$this->assign('topid',intval($_GET['topid']));
		$this->assign('reid',intval($_GET['reid']));
		
		$this->display('addColumn');
	}
	
	/**
     * JS添加栏目
     * @return void
     */
    public function js_AddColumn()
    {   		
		$topid             = intval($_GET['topid']);
		$reid              = intval($_GET['reid']);
		$caId              = intval($_POST['caId']);
		$desc              = h($_POST['desc']);
		$code              = h($_POST['code']);
		$remark            = h($_POST['remark']);
		$name              = h($_POST['name']);
		$level             = M('Column')->getLevleById($reid);
		$data              = array('reid'=>$reid,'name'=>$name,'topid'=>$topid,'code'=>$code,'level'=>$level,'desc'=>$desc,'remark'=>$remark);
		
		$res               = M('Column')->addColumn($data);  //增加所有栏目
		
		$this->success('添加成功！');
	}
	
	/**
     * 编辑栏目页面
     * @return void
     */
    public function editColumn()
    {   		
		$id         = intval($_GET['id']);
		
		$info       = M('Column')->getColumnById($id);             //通过ID获取指定栏目信息
		$firsts     = M('Column')->getColumn();                    //获取一级栏目
		$sections   = M('Column')->getColumn($info['typeid'],2);   //获取二级栏目
		
		$this->assign('firsts',$firsts);
		$this->assign('info',$info);
		$this->assign('id',$id);
		$this->assign('sections',$sections);
		
		$this->display();
	}
	
	/**
     * 编辑栏目
     * @return void
     */
    public function doEditColumn()
    {   		
		$id      = intval($_POST['column_id']);
	    
		$data    = array(
						 'topid'=>intval($_POST['first']),
						 'name'=>h($_POST['name']),
						 'status'=>intval($_POST['status']),
						 'desc'=>h($_POST['desc']),
						 'remark'=>h($_POST['remark'])
						 );
		
		$res    = M('Column')->editColumn($id,$data);
	
		$this->success('修改成功！');
	}
	
	/**
     * 视频栏目审核列表
     * @return void
     */
    public function examineList()
    {   
		$firsts     = M('Column')->getColumn();   //获取一级栏目
		
		$this->assign('firsts',$firsts);
		
		$this->display();
	}
	
	/**
     * 视频栏目审核
     * @return void
     */
    public function doStatusExamine()
    {   
		$id         = intval($_POST['id']);
		$data       = array('status'=>intval($_POST['status']));
		
        $res        = M('Column')->columnExamine($id,$data);   // 修改栏目状态
		
		echo $res;
	}
	
	/**
     * 视频审核子列表
     * @return void
     */
    public function sonExamineList()
    {   
		$typeid        = intval($_GET['typeid']);
		
		$this->__sonExaminePageList($typeid);
		
		$this->assign('columns',$columns);
		
		$this->display();
	}
	
	/**
     * 栏目内容管理列表
     * @return void
     */
    public function contentManageList()
    {   
		$typeid       = intval($_GET['id']);
		$level        = intval($_GET['level']);
		
		$this->__columnContentPageList($typeid,$level);
		
		$this->assign('typeid',$typeid);

		$this->display();
	}
	
	/**
     * 栏目内容管理列表
     * @return void
     */
    public function contentManageAdd()
    {   
		$typeid       = intval($_GET['typeid']);
		
		$info         = M('Column')->getColumnById($typeid); 
		
		$this->assign('typeid',$typeid);
		$this->assign('type_name',$info['name']);
		
		$this->display();
	}
	
	/**
     * 增加栏目内容列表
     * @return void
     */
    public function doContentAdd()
    {   
		$typeid     = intval($_POST['typeid']);
		
	    $info       = M('Column')->getColumnById($typeid); 
		
		$data       = array('typeid'=>$typeid,
		                    'uid'=>$this->uid,
							'reid'=>$info['reid'],
							'topid'=>$info['topid'],
						    'name'=>h($_POST['name']),
						    'code'=>h($_POST['code']),
						    'ctime'=>mktime(),
						    'description'=>h($_POST['desc']),
						    'remark'=>h($_POST['remark'])
						    );
		
		$res         = M('ColumnContent')->addColumnContent($data); 
		
		if($res>0){
		
			$this->success("添加成功！");
		}else{
			$this->error("删除失败!");
		}
    }
		
	/**
     * 删除指定ID的栏目下内容
     * @return void
     */
    public function doColumnContentDel()
    {   
		$res     = M('ColumnContent')->delColumnContentById(intval($_POST['conid']));
		
		echo $res;
	}
	
	/**
     * 编辑指定ID的栏目下内容
     * @return void
     */
    public function columnContentEdit()
    {   
		$conid   = intval($_GET['conid']);
		
		$info    = M('ColumnContent')->getColumnContentById($conid);
		
		$this->assign('info',$info);
		
		$this->display();
	}
	
	/**
     * 编辑指定ID的栏目下内容
     * @return void
     */
    public function doColumnContentEdit()
    {   
		$conid    = intval($_POST['conid']);
		
		$data     = array("uid"=>$this->uid,
		                  "name"=>h($_POST['name']),
						  "code"=>h($_POST['code']),
						  "ctime"=>mktime(),
						  "description"=>h($_POST['description']),
						  );
		
		$res     = M('ColumnContent')->ColumnContentEdit($conid,$data);
		
		echo $res;
	}
	
	/**
     * 搜索栏目
     * @return void
     */
    public function doColumnSearch()
    {   
		$search = h($_GET['search']);
		$this->__columnSearchPageList($search);
		
		$this->display();
	}
	
	/**
     * 图片上传
     * @return void
     */
    public function imageUpload()
    {   
		$conid     = intval($_REQUEST['conid']);
		
		$this->__image_upload($_FILES['img'],$conid);
		
		$this->assign('files',$files);
		$this->assign('conid',$conid);
		
		$this->display();
	}
	
	/**
     * 图片剪切
     * @return void
     */
    public function doImageUpload(){   
	
	error_reporting(0);
	
	if(!isset($_POST['x']) || !isset($_POST['y']) || !isset($_POST['w']) || !isset($_POST['h'])){
		$this->error("图片剪切错误!");
		die();
	}
	
	$conid          = intval($_POST['conid']);
	$src            = h($_POST['img_url']);
	$file_path      = pathinfo($src);
	$path           = h($_POST['path']);
	$img_n          = 'base_cut';
	$type           = ".".$file_path['extension'];
	$file           = $path.$img_n.$type; 
    $img            = $img_n.$type; 
	
	$targ_w         = 100; 
	$targ_h         = 100;
	$jpeg_quality   = 90;

	$img_r          = imagecreatefromjpeg($src);
	$dst_r          = ImageCreateTrueColor($targ_w,$targ_h);
	
	if(!file_exists($path)){
		mkdir($path,0777,true);
    }
    
	imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],$targ_w,$targ_h,$_POST['w'],$_POST['h']);
    
	imagejpeg($dst_r,$file);
	
	$data           = array('img'=>$file);
	
	$res            =  M('ColumnContent')->ColumnContentEdit($conid,$data);
	if(isset($res)){
		$this->success('图片剪切成功!');
	}else{
		$this->error('图片剪切失败!');
	}
}

	/**
     * 栏目内容分页列表显示
     * @return void
     */
	private function __columnContentPageList($typeid,$level)
	{
		$ids        = M('Column')->getColumnIds($typeid,$level);
		$where      = 'typeid in ('.implode(",",$ids).')';
		$count      = D('ColumnContent')->where($where)->count();
		$Page       = new Page($count,$this->count_per_page); 
		$list       = D('ColumnContent')->where($where)->order('conid')->limit($Page->firstRow.','.$Page->listRows)->select();
		$show       = $Page->show();
		
		$this->assign('show',$show);
		$this->assign('lists',$list);
	}
	
	/**
     * 栏目内容分页列表显示
     * @return void
     */
	private function __sonExaminePageList($typeid)
	{
		$count      = D('Column')->where('topid='.$typeid)->count();
		$Page       = new Page($count,$this->count_per_page); 
		$list       = D('Column')->where('topid='.$typeid)->order('typeid')->limit($Page->firstRow.','.$Page->listRows)->select();
		$show       = $Page->show();
		
		$this->assign('show',$show);
		$this->assign('lists',$list);
	}
	
	/**
     * 栏目查询分页列表显示
     * @return void
     */
	private function __columnSearchPageList($search)
	{
		$condition['name']  = array('like',"%".$search."%"); 
		$count              = D('Column')->where($condition)->count();
		$Page               = new Page($count,$this->count_per_page); 
		
		$list               = D('Column')->where($condition)->limit($Page->firstRow.','.$Page->listRows)->select();
		$show               = $Page->show();
		
		$this->assign('show',$show);
		$this->assign('lists',$list);
	}
	
	/**
     * 图片上传
     * @return void
     */
	private function __image_upload($files,$id)
	{
		if (!empty($files["name"]))                                                                                                                                                                                              { 
        $path='data/admin/base/'.$id.'/';
        if(!file_exists($path))
        {
            mkdir($path,0777,true);
        }//END IF
       $tp = array("image/gif","image/pjpeg","image/jpeg");
       if(!in_array($files["type"],$tp))
       {
          $this->error("格式不对!");
			exit;
	   }//END IF
       $filetype = $files['type'];
       if($filetype == 'image/jpeg'){
           $type = '.jpg';
       }
      if ($filetype == 'image/jpg') {
           $type = '.jpg';
       }
      if ($filetype == 'image/pjpeg') {
           $type = '.jpg';
      }
      if($filetype == 'image/gif'){
          $type = '.gif';
      }
     if($files["name"])
     {
        $today  ='base_big'; 
        $file2  = $path.$today.$type; 
        $img    = $today.$type; 
        $flag   = 1;
     }
    // w:516    h:386
	
    if($flag) $result=move_uploaded_file($files["tmp_name"],$file2);
   }
		list($width,$height,$type,$attr) =getimagesize($file2);
		
		$img_url  = $path.$img;
		switch($img_url){  
			case '':
				$img_url   = '__THEME__/js/Jcrop/demos/demo/flowers.jpg';
				$this->assign('height',386);
				$this->assign('width',516);
				$this->assign('img_url',$img_url);
				break;
			default:
				if($width>$height){
					$w   = 516;
					$h   = $height*(516/$width); 
				}else{
				    $h   = 386;
					$w   = $height*(386/$height); 
				}
				$this->assign('img_url',$img_url);
				$this->assign('height',$h);
				$this->assign('width',$w);
				$this->assign('img_name',$img_name);
				$this->assign('path',$path);
				$this->assign('img_type',$img_type);
		}
	}
}