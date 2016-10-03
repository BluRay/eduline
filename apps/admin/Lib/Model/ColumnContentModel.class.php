<?php
// +----------------------------------------------------------------------
// | ColumnContentModel（获取各个级别栏目表模型）
// +----------------------------------------------------------------------
// | Copyright 2013-2014 
// +----------------------------------------------------------------------
// | Last modified: 2014.4.10 16:00
// +----------------------------------------------------------------------
// | Author: ZhengRui <zhangrui@higher-edu.cn>
// +----------------------------------------------------------------------
class ColumnContentModel extends Model
{
	var $tableName = 'g_basecontent';
	
	// 增加栏目
	function addColumnContent($data){
		return $res    = $this->data($data)->add();
	}
	
	// 通过ID删除指定栏目内容
	function delColumnContentById($conid){
		return $res    = $this->where('conid='.$conid)->delete();
	}
	
	// 通过ID获得指定栏目下内容
	function getColumnContentById($conid){
		 return $this->where('conid='.$conid)->find();
	}
	
	// 通过ID修改指定内容
	function ColumnContentEdit($conid,$data){
		 return $this->where('conid='.$conid)->save($data);
	}
}
?>