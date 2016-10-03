<?php
/**
 * 笔记管理配置
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class AdminNoteAction extends AdministratorAction
{
	
	/**
	 * 初始化，配置内容标题
	 * @return void
	 */
	public function _initialize()
	{
		parent::_initialize();
	}

	/**
	 * 笔记列表管理
	 * @return void
	 */
	public function index()
	{
		// 页面具有的字段，可以移动到配置文件中！！！
        $this->pageKeyList = array(
			'id','note_title','note_description','type','parent_id','uid','oid','is_open',
			'note_help_count','note_comment_count','note_collect_count','note_source','ctime','DOACTION'
		);
		
		$this->pageButton[] = array('title'=>'删除笔记','onclick'=>"admin.mzNoteEdit('','delnote','删除','笔记')");
		$this->pageButton[] = array('title'=>'搜索','onclick'=>"admin.fold('search_form')");
		
		$this->searchKey = array('id','uid','type','is_open','note_title',array('ctime','ctime1'));
		$this->opt['type']    = array('0'=>'不限','1'=>'课程','2'=>'专辑');
		$this->opt['is_open'] = array('-1'=>'不限','0'=>'不公开','1'=>'公开');
		
        $list = model('ZyNote')->getNoteList('20',array('parent_id'=>array('eq',0)));
		
		foreach($list['data'] as $key=>$value){
			$list['data'][$key]['uid']      = getUserName($value['uid']);
			if($value['type']==1){
				$url = U('classroom/Video/view', array('id'=>$value['oid']));
			}else{
				$url = U('classroom/Album/view', array('id'=>$value['oid']));
			}
			$list['data'][$key]['note_title']  = '<div style="width:192px;height:30px;overflow:hidden;"><a href="'.$url.'" target="_bank">'.$value['note_title'].'</a></div>';
			$list['data'][$key]['note_description']  = '<div style="width:200px;height:30px;overflow:hidden;">'.$value['note_description'].'</div>';
			
			if($value['type']==1){
				$list['data'][$key]['oid']  = getVideoNameForID($value['oid']);
			}else if($value['type']==2){
				$list['data'][$key]['oid']  = getAlbumNameForID($value['oid']);
			}else{
				$list['data'][$key]['oid']  = '不存在';
			}
			$list['data'][$key]['oid'] = '<div style="width:200px;height:30px;overflow:hidden;">'.$list['data'][$key]['oid'].'</div>';
			
			$list['data'][$key]['type']     = ($value['type']==1)?'课程':'专辑';
			$list['data'][$key]['is_open']  = $value['uid']?'是':'不是';
			$list['data'][$key]['ctime']    = date('Y-m-d',$value['ctime']);
			$list['data'][$key]['DOACTION']  = '<a href="javascript:admin.mzNoteEdit('.$value['id'].',\'delnote\',\'删除\',\'笔记\');">删除</a>';
			
			$list['data'][$key]['DOACTION'] .= ' | <a href="'.U('classroom/AdminAlbum/noteCommentAlbum',array('oid'=>$value['oid'],'id'=>$value['id'],'tabHash'=>'noteCommentAlbum')).'">查看评论</a>';	
		}
		$this->assign('pageTitle','笔记管理');
        $this->_listpk = 'id';
        $this->allSelected = true;	
        $this->displayList($list);
	}

	/**
	 * 删除笔记
	 * @return void
	 */
	public function delnote()
	{
		$return =  model('ZyNote')->doDeleteNote($_POST['id']);
		
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


}