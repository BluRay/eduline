<?php
/**
 * 个人中心控制器
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
class IndexAction extends Action
{
	

	public function index()
	{
		if(!(model('Passport')->isLogged())){
			$this->error("请先登录!");
			U('index/Index/index','',true);
		}
		$this->display();
	}
	
	//app下载方法
	public function appDownload(){
		$file_ios 	  = 'https://www.pgyer.com/a6cn';
		$file_android = 'https://www.pgyer.com/B7d7';
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strripos($agent, 'iphone') || strripos($agent, 'ipad')) {
            if (strpos($agent, 'micromessenger')) {
                redirect($file_ios);
            } else {
                $type = 'ios';
                redirect($file_ios);
            }
        } else {
            redirect($file_android);
        }
	}
}