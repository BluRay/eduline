<?php
/**
 * 首页模块控制器
 * @author dengjb <dengjiebin@higher-edu.cn>
 * @version GJW2.0
 */
session_start();
class IndexAction extends Action
{
	//初始化
	public function _initialize()
	{
		$this->appCssList[] = 'people.css';
	}
	public function index()
	{
		//获取新品上架课程
		/**$new_cource = M('g_cource_base')->order('time DESC')->findPage(3);
		//获取学霸推荐课程
		$cids = M('g_cource_tj')->order('id DESC')->limit(3)->field('cid')->select();
		$bully_cource = array();
		foreach($cids as $k=>$v){
			$bully_cource[] = M('g_cource_base')->where('id='.$v['cid'])->find();
		}
		
		
		// 模板加载数据
		 
		$this->assign('new',$new_cource);
		$this->assign('tui',$bully_cource); 
		*/
		$this->display();
	}
	
		
	
}