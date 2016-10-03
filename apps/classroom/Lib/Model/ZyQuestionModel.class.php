<?php
/**
 * 提问管理模型
 * @author MissZhou <misszhou@renrenlo.com>
 * @version GJW2.0
 */
class ZyQuestionModel extends Model
{
	var $tableName = 'zy_question'; //映射到提问表

	/**
	 * 提问关联搜索
	 * @param int $limit 分页数据
	 * @param array $map 分页条件
	 * @param string $order 排序
	 * @return array 相关数据
	 */
	public function getQuestionList($limit,$map = array(), $order = "id DESC"){
		if (isset ( $_POST )) {
			$_POST ['id'] && $map ['id'] = intval ( $_POST ['id'] );
			$_POST ['uid'] && $map ['uid'] = array('in',(string)$_POST ['uid']);
			$_POST ['type'] && $map ['type'] = intval ( $_POST ['type'] );
			$_POST ['qst_title'] && $map ['qst_title'] = array('LIKE', '%'.t($_POST['qst_title']).'%');
			// 注册时间判断，ctime为数组格式
			if (! empty ( $_POST ['ctime'] )) {
				if (! empty ( $_POST ['ctime'] [0] ) && ! empty ( $_POST ['ctime'] [1] )) {
					// 时间区间条件
					$map ['ctime'] = array (
							'BETWEEN',
							array (
									strtotime ( $_POST ['ctime'] [0] ),
									strtotime ( $_POST ['ctime'] [1] ) 
							) 
					);
				} else if (! empty ( $_POST ['ctime'] [0] )) {
					// 时间大于条件
					$map ['ctime'] = array (
							'GT',
							strtotime ( $_POST ['ctime'] [0] ) 
					);
				} elseif (! empty ( $_POST ['ctime'] [1] )) {
					// 时间小于条件
					$map ['ctime'] = array (
							'LT',
							strtotime ( $_POST ['ctime'] [1] ) 
					);
				}
			}
		}
		// 查询数据
		$list = $this->where ( $map )->order ( $order )->findPage ( $limit );
		return $list;
	}
	
	/**
	 * 更具课程或者专辑ID来取提问
	 * @param array $map 分页条件
	 * @param int $limit 分页数据
	 * @param string $field 分页数据
	 * @param string $order 排序
	 * @return array|bool 提问列表
	 */
	public function getListForId($map = array(),$limit=20,$field="*",$order = "id DESC"){
		// 查询数据
		$list = $this->where ( $map )->order ( $order )->field ( $field )->findPage ( $limit );
		return $list?$list:false;
	}
	
	/**
	 * 取得我回答的问题
	 * @param int $uid 用户ID
	 * @param int $limit 分页数据
	 */
	public function myAnswer($limit,$uid=''){
		$thistable = $this->getTableName();
		//取得用户ID
		$uid = (empty($uid) || ($uid == '')) ? $GLOBALS['ts']['mid'] : $uid;
		//找到所有的答案
		$sql = "SELECT `id`,`parent_id` FROM {$thistable} WHERE `uid` = {$uid} and `parent_id` in(SELECT `id` FROM {$thistable} WHERE parent_id =0)";
		$data = M()->query($sql);
		
		$_myIds = array();
		$_myPIds = array();
		foreach($data as $key=>$value){
			$_myIds[] = $value['id'];
			$_myPIds[] = $value['parent_id'];
		}
		$_myIds = array_unique($_myIds);
		$_myIds = $_myIds?implode(',',$_myIds):0;
		
		$_myPIds = array_unique($_myPIds);
		$_myPIds = $_myPIds?implode(',',$_myPIds):0;
		//找到所有的答案
		$data = $this->where(array('id'=>array('in',(string)$_myIds)))->order('ctime desc')->findPage($limit);
		//把答案的问题
		$_data = $this->where(array('id'=>array('in',(string)$_myPIds)))->select();
		
		foreach($data['data'] as $key => &$value){
			$_value = array();
			foreach($_data as $k => $v){
				if($value['parent_id'] == $v['id']){
					$_value = $v;
					break;	
				}
			}
			$value['wenti'] = $_value;
		}
		return $data;
	}
	
	
	/**
	 * 删除提问
	 * @param array|int $ids 提问ID
	 * @return array 操作状态【1:删除成功;100003:要删除的ID不合法;false:删除失败】
	 */
	public function doDeleteQuestion($ids){
		$myIds = array();
		if(is_array($ids)){
			$_ids = implode(',',$ids);
			$myIds = array_merge($myIds,$ids);
		}else{
			$_ids = intval($ids);	
			$myIds[] = $_ids;
		}
		if(!trim($_ids)){
			return array('status'=>100003);
		}
		//先找到问题和评论和回复的所有ID
		$this->_getPids($_ids,$myIds);
		$myIds = $myIds?implode(',',$myIds):0;
		//开始删除提问
		$i = $this->where(array('id'=>array('in',(string)$myIds)))->delete();
		if($i === false){
			return false;
		}else{
			return array('status'=>1);
		}
	}
	
	//先找到这个提问下面的所有子项
	private function _getPids($_ids,&$myIds=array()){
		$ids  = array();
		$pids = $this->where(array('parent_id'=>array('in',(string)$_ids)))->field('id')->select();
		
		foreach($pids as $value){
			$ids[] = $value['id'];
			$myIds[] = $value['id'];
		}
		$ids = $ids?implode(',',$ids):0;
		if(count($pids)){
			$this->_getPids($ids,$myIds);
		}
		return null;
	}
	
	
	
}
?>