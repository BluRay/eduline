<?php
class LoginAddons extends NormalAddons{
	protected $version = "1.0";
	protected $author  = "智艺创想";
	protected $site    = "http://www.zhiyicx.com";
	protected $info    = "支持新浪微博、QQ、人人、帐号登录";
    protected $pluginName = "第三方登录插件V3";
    protected $tsVersion = '1.0';
    public function getHooksInfo(){
        $hooks['list']=array('LoginHooks');
        return $hooks;
    }
    public function adminMenu(){
	    return array('login_plugin_login'=>"同步登录管理");
    }
    public function start(){
        return true;
    }
    public function install(){
        return true;
    }
    public function uninstall(){
        return true;
    }
}
