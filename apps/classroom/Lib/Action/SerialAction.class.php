<?php
/**
 * 系列连载控制器
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH.'/classroom/Lib/Action/CommonAction.class.php');
class SerialAction extends CommonAction
{
	//连载栏目模型
	private $zyLzCategoryMod = null;
	//连载栏目模型
	private $zyLzContentMod = null;
	//连载分期模型
	private $zyLzDateMod = null;
	/**
    * 初始化
    * @return void
    */
    public function _initialize() {
        parent::_initialize();
		
		//连载栏目模型
		$this->zyLzCategoryMod = D('ZyLzCategory');
		$this->zyLzContentMod  = D('ZyLzContent');
		$this->zyLzDateMod     = D('ZyLzDate');
		
		$zyLzCategorys   =  $this->zyLzCategoryMod->order('`sort` desc')->select();
		//print_r($zyLzCategorys);
		$this->assign('zyLzCategory',$zyLzCategorys);
    }
	
	//http://192.168.1.122:8085/index.php?app=classroom&mod=Serial&act=serial
	/**
	 * 系列连载首页控制器
	 * @return void
	 */
	public function serial()
	{
		//取图文类型的推荐前10条
		$this->myserial();
		
		$this->display('serial');
	}
	/**
	 * 取图文类型的推荐前10条
	 * @return void
	 */
	private function myserial(){
		$map['istop'] = 1;
		$map['type']  = 1;
		
		$data = $this->zyLzContentMod->where($map)->order('`istop` desc,`id` desc')->limit('0,10')->select();
		$data = array_chunk($data,2);
		
		//print_r($data);
		$this->assign('myserial',$data);
	}
	//跳转
	public function turncontent(){
		$did = intval($_GET['did']);
		//找到
		$cid = $this->zyLzDateMod->where('id='.$did)->getField('cid');
		//跳转  &mod=&act=&=4&did=18
		$this->redirect('classroom/Serial/scontent','cid='.$cid.'&did='.$did);
	}
	
	
	/**
	 * 系列连载-图文列表
	 * @return void
	 */
	public function slist()
	{
		//取得栏目ID
		$cid  = intval($_GET['cid']);
		$type = intval($_GET['type']);
		
		$this->assign('type',$type);
		$this->assign('cid',$cid);
		$this->display('slist');
	}
	
	/**
	 * 系列连载-图文列表---获取数据
	 * @return void
	 */
	public function slistdata()
	{
		//取得栏目ID
		$cid   = intval($_POST['cid']);
		$type  = intval($_POST['type']);
		$limit = intval($_POST['limit']);
		
		$map['cid'] = $cid;
		$data = $this->zyLzDateMod->where($map)->order('`ctime` desc')->findPage($limit);
		//处理---分期下面的推荐内容
		foreach($data['data'] as &$value){
			$_data = $this->zyLzContentMod->where(array('did'=>$value['id'],'istop'=>1,'type'=>$type))->order('`ctime` desc')->find();
			//推荐内容
			$value['topdate'] = $_data?$_data:array();
		}
		//栏目ID
		$this->assign('cid',$cid);
		//取得data的模板-并解析
		$this->assign('data',$data['data']);
		$content = $this->fetch('pinterestlist');
		$data['_data'] = $data['data'];
		//重新赋值
		$data['data'] = $content;
		echo json_encode($data);exit;
	}
	
	/**
	 * 系列连载-图文内容
	 * @return void
	 */
	public function scontent()
	{
		//栏目ID
		$cid  = intval($_GET['cid']);
		//取得分期ID
		$did  = intval($_GET['did']);
		//取得该分期下面所有的内容
		$data = $this->zyLzContentMod->where(array('did'=>$did))->order('`istop` desc,`ctime` desc')->select();
		//print_r($data);
		
		$this->getotherdate($cid,$did);
		
		$this->assign('cid',$cid);
		$this->assign('did',$did);
		$this->assign('data',$data);
		$this->display('scontent');
	}
	/**
	 * 取得其它期的函数
	 * @return void
	 */
	private function getotherdate($cid,$did){
		$map['cid'] = $cid;
		$map['id']  = array('neq',$did);
		
		$data = $this->zyLzDateMod->where($map)->order('`ctime` desc')->select();
		//处理---分期下面的推荐内容
		foreach($data as &$value){
			$_data = $this->zyLzContentMod->where(array('did'=>$value['id'],'istop'=>1))->order('`ctime` desc')->find();
			//推荐内容
			$value['topdate'] = $_data?$_data:array();
		}
		//print_r($data);
		$this->assign('otherdate',$data);
	}
	
	
	
	
	/**
	 * 系列连载-视频列表
	 * @return void
	 */
	public function vlist()
	{
		//取得栏目ID
		$cid  = intval($_GET['cid']);
		$type = intval($_GET['type']);
		
		$this->assign('type',$type);
		$this->assign('cid',$cid);
		$this->display('vlist');
	}
	
	/**
	 * 系列连载视频列表---获取数据
	 * @return void
	 */
	public function vlistdata()
	{
		//取得栏目ID
		$cid   = intval($_POST['cid']);
		$type  = intval($_POST['type']);
		$limit = intval($_POST['limit']);
		
		$map['cid'] = $cid;
		$data = $this->zyLzDateMod->where($map)->order('`ctime` desc')->findPage($limit);
		//处理---分期下面的推荐内容
		foreach($data['data'] as &$value){
			$_data = $this->zyLzContentMod->where(array('did'=>$value['id'],'istop'=>1,'type'=>$type))->order('`ctime` desc')->find();
			//推荐内容
			$value['topdate'] = $_data?$_data:array();
		}
		//栏目ID
		$this->assign('cid',$cid);
		//取得data的模板-并解析
		$this->assign('data',$data['data']);
		$content = $this->fetch('vpinterestlist');
		$data['_data'] = $data['data'];
		//重新赋值
		$data['data'] = $content;
		echo json_encode($data);exit;
	}
	
	
	
	/**
	 * 系列连载-图文内容---图组展示形式
	 * @return void
	 */
	public function icontent()
	{
		//栏目ID
		$cid  = intval($_GET['cid']);
		//取得分期ID
		$did  = intval($_GET['did']);
		//取得该分期下面所有的内容
		$data = $this->zyLzContentMod->where(array('did'=>$did))->order('`istop` desc,`ctime` desc')->select();
		//print_r($data);
		
		$this->getotherdate($cid,$did);
		
		$this->assign('cid',$cid);
		$this->assign('did',$did);
		$this->assign('data',$data);
		$this->display('icontent');
	}
	
	
	/**
	 * 系列连载-视频播放
	 * @return void
	 */
	public function video()
	{
		include SITE_PATH.'/api/cc/spark_config.php';
    	$this->assign('sp_config',$spark_config);
		
		//栏目ID
		$id        = intval($_GET['id']);
		$video_id  = t($_GET['video_id']);
		
		
		
		$this->assign('video_id',$video_id);
		$this->assign('id',$id);
		$this->display('video');
	}
	
	
	
	
	
	
	
	
	
}