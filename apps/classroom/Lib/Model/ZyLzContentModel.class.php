<?php
/**
 * 连载内容管理模型
 * @author MissZhou <misszhou@renrenlo.com>
 * @version GJW2.0
 */
class ZyLzContentModel extends Model
{
	var $tableName = 'zy_lz_content'; //映射到连载内容表

	/**
	 * 连载内容关联搜索
	 * @param int $limit 分页数据
	 * @param array $map 分页条件
	 * @param string $order 排序
	 * @return array 相关数据
	 */
	public function getContentList($limit,$map = array(), $order = "id DESC"){
		if (isset ( $_POST )) {
			$_POST ['id'] && $map ['id'] = intval ( $_POST ['id'] );
			$_POST ['did'] && $map ['did'] = intval ( $_POST ['did'] );
			$_POST ['type'] && $map ['type'] = intval ( $_POST ['type'] );
			$_POST ['title'] && $map ['title'] = array('LIKE', '%'.t($_POST['title']).'%');
			$_POST ['description'] && $map ['description'] = array('LIKE', '%'.t($_POST['description']).'%');
			$_POST ['source'] && $map ['source'] = array('LIKE', '%'.t($_POST['source']).'%');
			
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
	 * 连载内容---删除
	 */
	public function dodellzcontent($ids){
		if(is_array($ids)){
			$ids = implode(',',$ids);	
		}
		if(!trim($ids)){
			return array('status'=>100003);
		}
		//删除本身的专题
		$i = $this->where(array('id'=>array('in',(string)$ids)))->delete();
		
		if($i === false){
			return false;
		}else{
			return array('status'=>1);
		}
	}
	
	
	
	
	
	
	
}
?>