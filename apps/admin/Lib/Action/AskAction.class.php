<?php
/**
 * 后台问答数据管理控制器
 * @author dengjb <dengjiebin@higher-edu.cn>
 * @version GJW2.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class AskAction extends AdministratorAction
{
    /**
     * 问答批量管理
     */
    public function index()
    {
	    //获取问答列表
	    $ask_list = D('Ask','admin')->getAskList();
	    $this->assign('ask_list',$ask_list);
		$this->display();
	}
	
	/**
	 * 搜索问题
	 */
	
	public function doSearchAsk(){
		$ask_title = $_POST['title'];//问题标题
		$ask_list = D('Ask','admin')->getAskByKey($ask_title);
		$this->assign('ask_list',$ask_list);
		$this->display('index');
	}
	
	/**
	 * 删除问题
	 */
	public function delAsk(){
		$id = intval($_POST['ids']);//问题ID
		$res = D('Ask','admin')->delAsk($id);
		echo json_decode($res);
	}
	
	/**
	 * 编辑问题
	 */
	public function editAsk(){
		$id = intval($_GET['id']);//问题ID
		$info = D('Ask','admin')->_getAskInfo($id);
		$this->assign('info',$info);
		$this->display();
		
	}
	/**
	 * 异步执行修改问题
	 */
	public function doEditAsk(){
		$id = intval($_POST['aid']);//问题ID
		$data['title'] = $_POST['title'];
		$data['ades'] = $_POST['ades'];
		$data['utime'] = time();
		$res = D('Ask','admin')->editAsk($id,$data);
		echo json_decode($res);
	}
	
	/**
	 * 根据问题ID获取回答列表
	 */
	public function anslist(){
		$id = intval($_GET['id']);//问题ID
		$list = D('Ask','admin')->getAnsList($id);
		$this->assign('aid',$id);
		$this->assign('list',$list);
		$this->display();
	}
	
	/**
	 * 删除指定问题下的回答
	 */
	public function doDeleteAns(){
		$id = intval($_POST['ids']);//回答ID
		$aid = intval($_POST['aid']);//对应问题ID
		$res = M('g_cource_answer')->where('id='.$id.' and aid='.$aid)->delete();
		if($res){
			$num = M('g_cource_ask')->where('id='.$aid)->getField('reward');
			$new_num = $num -1;
			$data['reward'] = $new_num;
			$r = M('g_cource_ask')->where('id='.$aid)->save($data);
			if($r){
				$res = $res;
			}
			else{
				$res = 0;
			}
		}
		echo json_decode($res);
	}
	
	
    
}