<?php
/**
 * 连载期管理模型
 * @author MissZhou <misszhou@renrenlo.com>
 * @version GJW2.0
 */
class ZyLzDateModel extends Model
{
	var $tableName = 'zy_lz_date'; //映射到连载期表
	/**
	 * 连载期关联搜索
	 * @param int $limit 分页数据
	 * @param array $map 分页条件
	 * @param string $order 排序
	 * @return array 相关数据
	 */
	public function getXDateList($limit,$map = array(), $order = "id DESC"){
		if (isset ( $_POST )) {
			$_POST ['id'] && $map ['id'] = intval ( $_POST ['id'] );
			$_POST ['cid'] && $map ['cid'] = intval ( $_POST ['cid'] );
			$_POST ['name'] && $map ['name'] = array('LIKE', '%'.t($_POST['name']).'%');
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
	 * 连载期---删除
	 */
	public function dodelxdate($ids){
		if(is_array($ids)){
			$ids = implode(',',$ids);	
		}
		if(!trim($ids)){
			return array('status'=>100003);
		}
		$count = M('ZyLzContent')->where(array('did'=>array('in',(string)$ids)))->count();
		if($count){
			//该分期下面有内容，不能删除
			return array('status'=>100004);
		}
		//删除本身的专题
		$i = $this->where(array('id'=>array('in',(string)$ids)))->delete();
		
		if($i === false){
			return false;
		}else{
			return array('status'=>1);
		}
	}
	
	
	
	/**
	 * 获取系列连载分类Hash数组
	 * @param string $k Hash数组的Key值字段
	 * @param string $v Hash数组的Value值字段
	 * @return array 专题分类的Hash数组
	 */
	public function getHashXdate($k = 'id', $v = 'name') {
	    $list  = $this->order('`ctime` desc')->select();
		$clist = D('ZyLzCategory')->getHashCategory();
	    $r = array();
	    foreach($list as $lv) {
			$display = $clist[$lv['cid']];
	    	$r[$lv[$k]] = $display.'-'.$lv[$v];
	    }
	    return $r;
    }
	
}
?>