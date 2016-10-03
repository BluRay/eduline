<?php
/**
 * 后台视频数据管理
 * @author dengjb <dengjiebin@higher-edu.cn>
 * @version GJW2.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class OrderAction extends AdministratorAction
{
    /**
     * 订单管理页面
     * @return void
     */
    public function index()
    {
	    
		$this->display();
	}

    
}