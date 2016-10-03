<?php
/**
 * 单页
 */
tsload(APPS_PATH.'/classroom/Lib/Action/CommonAction.class.php');
class SingleAction extends CommonAction {

    /**
    * 初始化，配置内容标题
    * @return void
    */
    public function _initialize() {
        parent::_initialize();
    }

    /**
     * 首页
     * @return void
     */
    public function info() {
    	$id = intval($_GET['id']);
    	$res = M('single')->where('id = '.$id.' and is_del=0')->find();
    	//获取单页分类
    	$cate = D('Single','admin')->getCate();
    	//获取当前单页所在分类的所有相关单页
    	$single_list = M('single')->where('cate_id = '.$res['cate_id'].' and is_del=0')->findAll();
    	$this->assign('data' , $res);
    	$this->assign('cate' , $cate[$res['cate_id']]);
    	$this->assign('single_list' , $single_list);
        $this->display();
    }
    
    

}