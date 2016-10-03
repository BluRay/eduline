<?php

tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');

class IndexAction extends AdministratorAction {

	public function _initialize(){
		parent::_initialize();
	}

	public function index() {
		$nav = array();
		$this->setTitle( L('PUBLIC_SYSTEM_MANAGEMENT') );
		$channel = C('admin_channel');
		$menu = C('admin_menu');
		
		foreach($channel as $k => $v){
			if(!CheckPermission('core_admin', 'top_'.$k)){
				unset($channel[$k]);
				unset($menu[$k]);

			}
		}
		if(isset($channel['apps'])){
			foreach($this->navList as $k=>$v){
				$nav[] = array('name'=>L('PUBLIC_APPNAME_'.strtoupper($k)),'appname'=>$k,'url'=>$v);
			}
		}
		$this->assign('nav',$nav);
		$this->assign('channel', $channel);
		
		if($menu['classroom']){
			foreach($menu['classroom'] as $i=>$arr){
				foreach($arr as $j=>$val){
					preg_match('#[/=]Admin([\w]+)#', $val, $match);
					$match = 'classroom_'.$match[1];
					if(!$match) continue;
					if(!CheckPermission('classroom_admin',$match)){
						unset($menu['classroom'][$i][$j]);
					}
				}
			}
			foreach($menu['classroom'] as $i=>$arr){
				if(empty($arr)) unset($menu['classroom'][$i]);
			}
		}
		
    	$this->assign('menu',    $menu);
		$this->display();
	}
	
}
?>