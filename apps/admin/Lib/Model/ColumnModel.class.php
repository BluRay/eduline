<?php
// +----------------------------------------------------------------------
// | ColumnModel（获取各个级别栏目表模型）
// +----------------------------------------------------------------------
// | Copyright 2013-2014 
// +----------------------------------------------------------------------
// | Last modified: 2014.4.4 13:50
// +----------------------------------------------------------------------
// | Author: ZhengRui <zhangrui@higher-edu.cn>
// +----------------------------------------------------------------------
class ColumnModel extends Model
{
	var $tableName = 'g_column';

	// 获取栏目
	public function getColumn($topid=0,$level=1)
	{
		switch($level){
			case 1:
			    return $this->where('topid=0')->limit(10)->findAll();
			    break;
			case 2:
				return $this->where('topid='.$topid)->limit(10)->findAll();
			    break;
		}
	}
	
	// 增加栏目
	function addColumn($data){
		return $res    = $this->data($data)->add();
	}
	
	// 删除栏目
	function delColumn($id){
		return $res    = $this->where('typeid='.$id)->delete();
	}
	
	// 通过ID获取栏目详细信息
	function getColumnById($typeid){
		return $this->where('typeid='.$typeid)->find();
	}
	
	// 编辑栏目信息
	function editColumn($id,$data){
		return $this->where('typeid='.$id)->save($data);
	}
	
	// 编辑栏目状态
	function columnExamine($id,$data){
		return $this->where('typeid='.$id)->save($data);
	}
	
	//获取栏目等级状态 setInc setDec
	function getLevleById($reid){
		switch($reid){
			case 0:
				return 1;
				break;
			default:
				$res = $this->where('typeid='.$reid)->field('level')->find();
				return $res['level']+1;
		}
	}
	
	//获取符合条件ID结合
	function getColumnIds($typeid,$level){
		switch($level){
			case 1:
				$condition   = 'topid='.$typeid.' or typeid='.$typeid;
				break;
			default:
				$info                 = $this->getColumnById($typeid);
				$topid                = $info['topid']; 
				$condition   = 'topid='.$topid.' and level>='.$level;
		}

		$data   = $this->where($condition)->field('typeid')->findAll();
		$count  = count($data);
		
		for($i=0;$i<$count;$i++){
			$arr[] = $data[$i]['typeid'];
		}

		return $arr;
	}
}
?>