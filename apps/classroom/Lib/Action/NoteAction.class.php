<?php
/**
 * 笔记控制器
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH.'/classroom/Lib/Action/CommonAction.class.php');
class NoteAction extends CommonAction
{
	/**
    * 初始化
    * @return void
    */
    public function _initialize() {
        parent::_initialize();
    }
	
	/**
	 * 笔记控制器
	 * @return void
	 */
	public function index()
	{
		$id   = intval($_GET['id']);
		$type = intval($_GET['type']);
		
		$map['id'] = array('eq',$id);
		if($type == 1){
			//课程
			if(!$id){
				$this->assign('isAdmin',1);
				$this->error('课程不存在!');	
			}
			$field = '`video_title` as `title`,`video_category` as `category`,`video_score` as `score`,`uid`,`id`,`ctime`,`video_comment_count` as `comment_count`';
			//取课程信息
			$data = M('ZyVideo')->where($map)->field($field)->find();
		}else if($type == 2){
			//专辑
			if(!$id){
				$this->assign('isAdmin',1);
				$this->error('专辑不存在!');	
			}
			$field = '`album_title` as `title`,`album_category` as `category`,`album_score` as `score`,`uid`,`id`,`ctime`,`album_comment_count` as `comment_count`';
			//取专辑信息
			$data = M('ZyAlbum')->where($map)->field($field)->find();
		}else{
			$this->assign('isAdmin',1);
			$this->error('参数错误!');	
		}
		$data['score'] = floor($data['score']/20);

		$this->assign('datainfo',$data);
		$this->assign('id',$id);
		$this->assign('type',$type);
		
		$this->display();
	}
	public function getlist()
	{
		$limit  = intval($_POST['limit']);
		$type   = t($_POST['type']);
		if($type == 'new'){
			$order = 'ctime DESC';
		}else{
			$order = 'note_help_count DESC';
		}
		$map['is_open'] = array('eq',1);
		$map['parent_id'] = array('eq',0);
		$data = M('ZyNote')->where($map)->order($order)->findPage($limit);
		foreach($data['data'] as &$value){
			$value['note_src']  = U('classroom/Index/resource','rid='.$value['id'].'&type=4');
			$value['note_description']  = msubstr($value['note_description'],0,25,'utf-8',false);
			$value['username'] = msubstr($value['username'],0,10,'utf-8',true);	
			$value['strtime']  = friendlyDate($value['ctime']);
		}
		echo json_encode($data);exit;
	}
	
	/**
	 * 添加笔记
	 * @return void
	 */
	public function addnote(){
		if(!$this->mid){
			$this->assign('isAdmin',1);
			$this->error('添加笔记需要先登录');
		}
		$id   = intval($_GET['id']);
		$type = intval($_GET['type']);
		
		$videoList = M('ZyVideo')->field('video_title,id')->order('ctime asc')->getBuyVideo(intval($this->mid));
		//print_r($videoList);
		$this->assign('videoList',$videoList);
		
		$this->getRInfo($id,$type);
		
		$this->assign('id',$id);
		$this->assign('type',$type);
		
		$this->display();
	}
	
	private function getRInfo($id,$type){
		$map['id'] = array('eq',$id);
		if($type == 1){
			//课程
			if(!$id){
				$this->assign('isAdmin',1);
				$this->error('课程不存在!');	
			}
			$field = '`video_title` as `title`,`video_category` as `category`,`video_score` as `score`,`uid`,`id`,`ctime`,`video_comment_count` as `comment_count`';
			//取课程信息
			$data = M('ZyVideo')->where($map)->field($field)->find();
		}else if($type == 2){
			//专辑
			if(!$id){
				$this->assign('isAdmin',1);
				$this->error('专辑不存在!');	
			}
			$field = '`album_title` as `title`,`album_category` as `category`,`album_score` as `score`,`uid`,`id`,`ctime`,`album_comment_count` as `comment_count`';
			//取专辑信息
			$data = M('ZyAlbum')->where($map)->field($field)->find();
		}else{
				$this->assign('isAdmin',1);
			$this->error('参数错误!');	
		}
		$data['score'] = floor($data['score']/20);
		

		$this->assign('datainfo',$data);
		$this->assign('id',$id);
		$this->assign('type',$type);
	}
	
	/**
	 * 添加笔记
	 * @return void
	 */
	public function add(){
		$data['parent_id']           = 0;
		$data['type']		         = intval($_POST['kztype']);//
		$data['uid'] 			     = intval($this->mid);
		$data['oid'] 			     = intval($_POST['kzid']);//对应的ID【专辑ID/课程ID】
		$data['is_open']             = intval($_POST['is_open']);
		$data['note_source'] 	     = 'web网页';
		$data['note_title']          = $data['qst_title']        = filter_keyword(t($_POST['title']));
		$data['qst_description'] 	 = filter_keyword(t($_POST['title']));
		$data['note_description']    = filter_keyword(t($_POST['content']));
		$data['ctime']			     = time();
		
		if(!trim($data['note_title'])){
			$data['note_title'] = msubstr($data['note_description'],0,14);
		}
		
		if(!$data['uid']){
			$this->mzError('添加笔记需要先登录');
		}
		if(!$data['oid']){
			$this->mzError('请选择课程或专辑');
		}
		if(!$data['note_title']){
			$this->mzError('请输入笔记标题');
		}
		if(!$data['note_description']){
			$this->mzError('请输入笔记内容');
		}
        if(strlen($data['note_title'])>45){
            $this->mzError('对不起，标题最多45个字符');
        }
        if(strlen($data['note_description'])>300){
            $this->mzError('对不起，内容最多300个字符');
        }
		$i = M('ZyNote')->add($data);
		if($i){
			//更改专辑或课程的总提问数
			if(intval($_POST['kztype']) == 1){
				$_data['video_note_count'] = array('exp','`video_note_count` + 1');
				//课程
				M('ZyVideo')->where(array('id'=>array('eq',$data['oid'])))->save($_data);
			}else{
				$_data['album_note_count'] = array('exp','`album_note_count` + 1');
				//专辑	
				M('ZyAlbum')->where(array('id'=>array('eq',$data['oid'])))->save($_data);
			}
			//session('mzaddnote'.$data['oid'].$data['type'],time()+180);
			$this->mzSuccess('添加成功');
		}else{
			$this->mzSuccess('添加失败');
		}
		$this->filter_keyword($data);
	}
	/*
	 * 添加笔记
	 */
	public function dowritenote(){
		$data['parent_id']           = 0;
		$data['type']		         = 1;//
		$data['uid'] 			     = intval($this->mid);
		$data['oid'] 			     = intval($_POST['kecid']);//对应的ID【专辑ID/课程ID】
		$data['is_open']             = intval($_POST['is_open']);
		$data['note_source'] 	     = 'web网页';
		$data['note_title']          = filter_keyword(t($_POST['title']));
		$data['note_description']    = filter_keyword($_POST['content']);
		$data['ctime']			     = time();
		
		if(!$data['uid']){
			$this->mzError('添加笔记需要先登录');
		}
		if(!$data['note_title']){
			$this->mzError('请输入笔记标题');
		}
		if(!$data['note_description']){
			$this->mzError('请输入笔记内容1');
		}
		
		if(session('mzaddnote'.$data['oid'].$data['type']) >= time()){		
			//请不要重复刷新
			$this->mzError('请不要重复添加,3分钟之后再试!');
		}
		
		$i = M('ZyNote')->add($data);
		if($i){
			//更改专辑或课程的总提问数
			$_data['video_note_count'] = array('exp','`video_note_count` + 1');
			//课程
			M('ZyVideo')->where(array('id'=>array('eq',$data['oid'])))->save($_data);
			session('mzaddnote'.$data['oid'].$data['type'],time()+180);
			$this->mzSuccess('添加成功');
		}else{
			$this->mzError('添加失败');
		}
		$this->filter_keyword($data);
	}
	
	/**
	 * 编辑笔记
	 * @return void
	 */
	public function editnote(){
		$data['id']               = intval($_POST['id']);
		$data['note_description'] = filter_keyword(t($_POST['edittxt']));
		
		if(!$this->mid){
			$this->mzError('编辑笔记需要先登录');
		}
		
		if(!trim($data['id'])){
			$this->mzError('笔记不存在!');
		}
		if(!trim($data['note_description'])){
			$this->mzError('笔记内容不能为空!');
		}
		
		$i = M('ZyNote')->save($data);
		if($i === false){
			$this->mzError('修改失败');
		}
		$this->mzSuccess('修改成功','selfhref');
	}
	

}