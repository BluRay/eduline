<?php
/**
 * 连载内容栏目管理模型
 * @author MissZhou <misszhou@renrenlo.com>
 * @version GJW2.0
 */
class ZyLzCategoryModel extends Model
{
	var $tableName = 'zy_lz_category'; //映射到连载内容栏目表

	/**
	 * 获取系列连载分类Hash数组
	 * @param string $k Hash数组的Key值字段
	 * @param string $v Hash数组的Value值字段
	 * @return array 专题分类的Hash数组
	 */
	public function getHashCategory($k = 'id', $v = 'display') {
	    $list = $this->order('`sort` desc')->select();
	    $r = array();
	    foreach($list as $lv) {
	    	$r[$lv[$k]] = $lv[$v];
	    }
	    return $r;
    }
	
	
}
?>