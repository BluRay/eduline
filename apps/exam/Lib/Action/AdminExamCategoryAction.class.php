<?php
/**
 * 考试系统后台配置
 * 分类管理
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class AdminVideoCategoryAction extends AdministratorAction
{
	/**
	 * 初始化，配置内容标题
	 * @return void
	 */
	public function _initialize()
	{
		// 管理标题项目
		$this->pageTitle['index'] = '试题列表';
		$this->pageTitle['albumCategory'] = '';

		// 管理分页项目
		$this->pageTab[] = array('title'=>$this->pageTitle['index'],'tabHash'=>'index','url'=>U('classroom/AdminVideoCategory/index'));
		$this->pageTab[] = array('title'=>$this->pageTitle['albumCategory'],'tabHash'=>'albumCategory','url'=>U('classroom/AdminVideoCategory/albumCategory'));

		parent::_initialize();
	}
	
	//试题分类列表
	public function index(){
		//$this->pageButton[] = array('title'=>'搜索试题','onclick'=>"admin.cancelRecommended()");
		$treeData = model('VideoCategory')->getNetworkList(0,1);
		//类型为试题
		$type = '1';
		$this->assign('type',$type);
		$this->displayTree($treeData,'zy_video_category');
	}
	
	

}