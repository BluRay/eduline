<?php
/**
 * 高教网分类选择 widget
 * @example W('VideoLevel',array('type'=>1,'template'=>'admin_level','default'=>'1,5,7'))
 * @author MissZhou
 * @version TS3.0
 */
class VideoLevelWidget extends Widget {
	/**
	 * @param integer type 分类类型
	 * @param string template 模板名称
	 * @param integer default 【1,5,7】
	 */
	public function render($data) {
		$var = array();
        $var['type']        = 1;//分类类型 1:课程分类，2:点播分类
		$var['template']    = 'admin_level';//模板名称
		$var['default']    = '';//默认选择
		
        is_array($data) && $var = array_merge($var,$data);
		
		$template = $var['template'].'.html';
		
		//渲染模版
        $content = $this->renderFile(dirname(__FILE__)."/".$template,$var);
        unset($var,$data);
        //输出数据
        return $content;
	}
	
	/**
	 * 获取所有的顶级分类
	 * @param integer type 分类类型 1:课程分类，2:点播分类
	 */
	public function getParent(){
		$type = intval($type)?intval($type):intval($_POST['type']);
		$pid  = intval($pid)?intval($pid):intval($_POST['pid']);
		
		if(($parentLevelAll = model('Cache')->get('mzParentCLevelAll'.$pid.$type)) === false){
			//预先取第一级分类
			$map['type'] = array('eq',$type);
			$map['pid']  = array('eq',$pid);
			$_parentLevelAll = M('VideoCategory')->where($map)->order('`sort` DESC')->select();
			
			$parentLevelAll = array();
			//$parentLevelAll['sql'] = M('VideoCategory')->getLastSql();
			foreach((array)$_parentLevelAll as $key=>$value){
				$parentLevelAll[$value['zy_video_category_id']] = $value;
			}
			model('Cache')->set('mzParentCLevelAll'.$pid.$type,$parentLevelAll);
		}
		
		echo json_encode($parentLevelAll?$parentLevelAll:null);
		exit;
	}
	/**
	 * 获取所有的顶级分类
	 * @param integer type 分类类型 1:课程分类，2:点播分类
	 */
	public function urlfirstdata(){
		$type = intval($type)?intval($type):intval($_POST['type']);
		$ids  = t($ids)?t($ids):t($_POST['ids']);
		
		$map['type']                   = array('eq',$type);
		$map['zy_video_category_id']   = array('in',(string)$ids);
		$_parentLevelAll = M('VideoCategory')->where($map)->order("field(zy_video_category_id,{$ids})")->select();
		
		echo json_encode($_parentLevelAll?$_parentLevelAll:null);
		exit;
	}
	
	
	/**
	 * 获取所有的顶级分类
	 * @param integer type 分类类型 1:课程分类，2:点播分类
	 */
	public function getParentLevelAll($type){
		$type = intval($type)?intval($type):intval($_POST['type']);
		
		if(($parentLevelAll = model('Cache')->get('mzParentLevelAll'.$type)) === false){
			//预先取第一级分类
			$map['type'] = array('eq',$type);
			$map['pid']  = array('eq',0);
			$_parentLevelAll = M('VideoCategory')->where($map)->order('`sort` DESC')->select();
			
			$parentLevelAll = array();
			foreach((array)$_parentLevelAll as $key=>$value){
				$parentLevelAll[$value['zy_video_category_id']] = $value;
			}
			model('Cache')->set('mzParentLevelAll'.$type,$parentLevelAll);
		}
		
		echo json_encode($parentLevelAll?$parentLevelAll:null);
		exit;
	}
	
	/**
	 * 获取所有的子集
	 * @param integer type 分类类型 1:课程分类，2:点播分类
	 * @param integer pid 父级ID
	 */
	public function getChildrenAll($pid,$type){
		$type = intval($type)?intval($type):intval($_POST['type']);
		$pid  = intval($pid)?intval($pid):intval($_POST['pid']);
		
		if(($parentLevelAll = model('Cache')->get($pid.'mzgetChildrenAll'.$type)) === false){
			//预先取第一级分类
			$map['type'] = array('eq',$type);
			$map['pid']  = array('eq',$pid);
			$_parentLevelAll = M('VideoCategory')->where($map)->order('`sort` DESC')->select();
			
			$parentLevelAll = array();
			foreach((array)$_parentLevelAll as $key=>$value){
				$parentLevelAll[$value['zy_video_category_id']] = $value;
			}
			model('Cache')->set($pid.'mzgetChildrenAll'.$type,$parentLevelAll);
		}
		
		echo json_encode($parentLevelAll?$parentLevelAll:null);
		exit;
	}

	
	
	
	
}