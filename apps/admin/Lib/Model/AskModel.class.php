<?php
/**
 * 问答管理模型
 * @author dengjb <dengjiebin@higher-edu.cn>
 * @version GJW2.0
 */
class AskModel extends Model
{
	var $tableName = 'g_cource_ask'; //映射到问答表

	/**
	 * 获取问答列表
	 * @return array res
	 */
	public function getAskList()
	{
		$res =  $this->order('utime DESC')->findPage(15);//分页查询
		return $res;
	}
	
	/**
	 * 根据问题标题搜索问题
	 */
	
	public function getAskByKey($key)
	{
		$res =  $this->order('utime DESC')->where("title LIKE'%".$key."%'")->findPage(15);//分页模糊查询
		return $res;
	}
	
	/**
	 * 根据问题ID获取问题信息
	 */
	
	public function _getAskInfo($id){
		$ask_info = $this->where('id='.$id)->select();
		return $ask_info;
	}
	// 删除问题
	function delAsk($id){
		return $res = $this->where('id='.$id)->delete();
	}
	
	// 通过ID获取问题详细信息
	function getAskById($id){
		return $this->where('id='.$id)->find();
	}
	
	// 编辑问题信息
	function editAsk($id,$data){
		return $this->where('id='.$id)->save($data);
	}
	//获取问题下的回答信息
	function getAnsList($id){
		return M('g_cource_answer')->order('ctime DESC')->where('aid='.$id)->findPage(15);
		
	}
}
?>