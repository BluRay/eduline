<?php
/**
 * 后台视频数据管理
 * @author dengjb <dengjiebin@higher-edu.cn>
 * @version GJW2.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class VideoAction extends AdministratorAction
{
    /**
     * 视频创建页面
     * @return void
     */
    public function addVideo()
    {
	    //获取视频列表
		$videoList = getVideoList();
		$this->assign('list',$videoList);
		$this->display();
	}
	
	/**
	*删除本地视频信息
	*@author dengjb
	*@time 2014-4-18
	*/
    public function deleteVideo(){
		$videoid = $_GET['videoid'];
		$res = M('g_video')->where('videoid='.$videoid)->delete();
		echo json_encode($res);
	}
}