<?php
/**
 * 云课堂默认控制器
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class AdminAction extends AdministratorAction {

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
    public function index() {
        $this->display();
    }

}