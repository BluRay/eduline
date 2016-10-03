<?php
/**
 * 后台视频数据管理
 * @author dengjb <dengjiebin@higher-edu.cn>
 * @version GJW2.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class CourceAction extends AdministratorAction
{
    /**
     * 课程创建页面
     * @return void
     */
    public function addCource()
    {
	    
		$this->display();
	}

    
}