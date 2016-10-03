<?php

/**
 * Eduline直播首页控制器
 * @author Ashang <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
class IndexAction extends Action {
	
    /**
     * Eduline直播首页方法
     * @return void
     */ 
    public function index() {
        header('Location: http://view.l.bokecc.com/api/view/lecturer?roomid=745A5A9B3B7B7EBC9C33DC5901307461&userid=6E44FDD0403EB80B');
    }
  
}

