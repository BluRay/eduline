<?php
/**
 * 高教网分类选择 widget
 * @example W('VideoLevel',array('type'=>1,'template'=>'admin_level','default'=>'1,5,7'))
 * @author MissZhou
 * @version TS3.0
 */
class UserLevelWidget extends Widget {
	/**
	 * @return string
	 */
	public function render($data) {
		$content = '';
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

		if(($parentLevelAll = model('Cache')->get('mzParentCLevelAll_school'.$pid.$type)) === false){
			//预先取第一级分类
			$map['type'] = array('eq',$type);
			$map['pid']  = array('eq',$pid);
			$_parentLevelAll = M('ZySchoolCategory')->where($map)->order('`sort` DESC')->select();

			$parentLevelAll = array();
			//$parentLevelAll['sql'] = M('ZySchoolCategory')->getLastSql();
			foreach((array)$_parentLevelAll as $key=>$value){
				$parentLevelAll[$value['zy_school_category_id']] = $value;
			}
			model('Cache')->set('mzParentCLevelAll_school'.$pid.$type,$parentLevelAll);
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
		$map['zy_school_category_id']   = array('in',(string)$ids);
		$_parentLevelAll = M('ZySchoolCategory')->where($map)->order("field(zy_school_category_id,{$ids})")->select();
		
		echo json_encode($_parentLevelAll?$_parentLevelAll:null);
		exit;
	}
	
	
	/**
	 * 获取所有的顶级分类
	 * @param integer type 分类类型 1:课程分类，2:点播分类
	 */
	public function getParentLevelAll($type){
		$type = intval($type)?intval($type):intval($_POST['type']);
		
		if(($parentLevelAll = model('Cache')->get('mzParentLevelAll_school'.$type)) === false){
			//预先取第一级分类
			$map['type'] = array('eq',$type);
			$map['pid']  = array('eq',0);
			$_parentLevelAll = M('ZySchoolCategory')->where($map)->order('`sort` DESC')->select();
			
			$parentLevelAll = array();
			foreach((array)$_parentLevelAll as $key=>$value){
				$parentLevelAll[$value['zy_school_category_id']] = $value;
			}
			model('Cache')->set('mzParentLevelAll_school'.$type,$parentLevelAll);
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
		
		if(($parentLevelAll = model('Cache')->get($pid.'mzgetChildrenAll_school'.$type)) === false){
			//预先取第一级分类
			$map['type'] = array('eq',$type);
			$map['pid']  = array('eq',$pid);
			$_parentLevelAll = M('ZySchoolCategory')->where($map)->order('`sort` DESC')->select();
			
			$parentLevelAll = array();
			foreach((array)$_parentLevelAll as $key=>$value){
				$parentLevelAll[$value['zy_school_category_id']] = $value;
			}
			model('Cache')->set($pid.'mzgetChildrenAll_school'.$type,$parentLevelAll);
		}
		
		echo json_encode($parentLevelAll?$parentLevelAll:null);
		exit;
	}

	
	
	
	
}