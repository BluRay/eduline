<?php
/**
 * 云播问题控制器
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH.'/classroom/Lib/Action/CommonAction.class.php');
class QuestionAction extends CommonAction
{
	/**
    * 初始化，配置内容标题
    * @return void
    */
    public function _initialize() {
        parent::_initialize();
    }
	
	//http://127.0.0.1/gaojiao/index.php?app=classroom&mod=Question&act=index
	/**
	 * 云播问题控制器
	 * @return void
	 */
	public function index()
	{
		
		$this->display();
	}
	
	/**
	 * 添加提问
	 * @return void
	 */
	public function add(){
		//$data['qst_title']        = filter_keyword(t($_POST['title']));
		$data['qst_description']  = filter_keyword(t($_POST['content']));
		$data['type']		      = intval($_POST['kztype']);//提问类型【1:课程;2:专辑;】
		$data['parent_id']		  = intval($_POST['parent_id']);//顶级为0其它为提问表ID
		$data['uid'] 			  = intval($this->mid);
		$data['oid'] 			  = intval($_POST['kzid']);//对应的ID【专辑ID/课程ID】
		$data['qst_source'] 	  = 'web网页';
		$data['ctime']			  = time();
		
		if(!trim($data['qst_title'])){
			$data['qst_title'] = msubstr($data['qst_description'],0,14);
		}
        if(strlen($data['qst_title'])>45){
            $this->mzError('对不起，标题最多45个字符');
        }
        if(strlen($data['qst_description'])>450){
            $this->mzError('对不起，内容最多450个字符');
        }
		
		if(!$data['uid']){
			$this->mzError('添加问题需要先登录');
		}
		if(!$data['qst_title']){
			$this->mzError('请输入问题标题');
		}
		if(!$data['qst_description']){
			$this->mzError('请输入问题内容');
		}
		
		/*if(session('mzaddQuestion'.$data['oid'].$data['type']) >= time()){		
			//请不要重复刷新
			$this->mzError('请不要重复添加,3分钟之后再试!');
		}*/
		
		$i = M('ZyQuestion')->add($data);
		if($i){
			//更改专辑或课程的总提问数
			if(intval($_POST['kztype']) == 1){
				$_data['video_question_count'] = array('exp','`video_question_count` + 1');
				//课程
				M('ZyVideo')->where(array('id'=>array('eq',$data['oid'])))->save($_data);
			}else{
				$_data['album_question_count'] = array('exp','`album_question_count` + 1');
				//专辑	
				M('ZyAlbum')->where(array('id'=>array('eq',$data['oid'])))->save($_data);
			}
			//session('mzaddQuestion'.$data['oid'].$data['type'],time()+180);
			$this->mzSuccess('添加成功');
		}else{
			$this->mzError('添加失败');
		}
	}
	
	/**
	 * 编辑提问
	 * @return void
	 */
	public function editqst(){
		$data['id']              = intval($_POST['id']);
		$data['qst_description'] = filter_keyword(t($_POST['edittxt']));
		
		if(!$this->mid){
			$this->mzError('编辑问题需要先登录');
		}
		
		if(!trim($data['id'])){
			$this->mzError('问题不存在!');
		}
		if(!trim($data['qst_description'])){
			$this->mzError('问题内容不能为空!');
		}
		
		$i = M('ZyQuestion')->save($data);
		if($i === false){
			$this->mzError('修改失败');
		}
		$this->mzSuccess('修改成功','selfhref');
	}
	
	
	
	
}