<?php
class SingleModel extends Model{
	
	/**
	 * 获取单页分类
	 * @return array
	 */
    public function getCate(){
		return M('single_category')->order('sort asc')->getField('single_category_id,title');
    }
    
    /**
     *获取单页分类及分类下的单页 
     */
    public function getList(){
    	$cate = M('single_category')->order('sort asc')->getField('single_category_id,title');
    	foreach($cate as $k => $val){
    		$cate_list[$val] = M('single')->where('cate_id = '.$k.' and is_del=0')->findAll();
    	}
    	return $cate_list;
    }
	 
}