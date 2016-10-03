<?php
/**
 * 后台知识点数据管理
 * @author dengjb <dengjiebin@higher-edu.cn>
 * @version GJW2.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class PointAction extends AdministratorAction
{
    /**
     * 知识点创建页面
     * @return void
     */
    public function addPoint()
    {
	    
		$this->display();
	}

    
}