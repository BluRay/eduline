<?php
/**
 * 专题分类管理模型
 * @author MissZhou <misszhou@renrenlo.com>
 * @version GJW2.0
 */
class ZySpecialCategoryModel extends Model
{
	var $tableName = 'zy_special_category'; //映射到专题分类表

	/**
	 * 专题分类关联搜索
	 * @param int $limit 分页数据
	 * @param array $map 分页条件
	 * @param string $order 排序
	 * @return array 相关数据
	 */
	public function getSpecialCategoryList($limit,$map = array(), $order = "id DESC"){
		if (isset ( $_POST )) {
			$_POST ['id'] && $map ['id'] = intval ( $_POST ['id'] );
			$_POST ['title'] && $map ['title'] = array('LIKE', '%'.t($_POST['title']).'%');
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
	 * 清除导航缓存
	 * @return void
	 */
	public function cleanCache() {
		model('Cache')->rm('AllSpecialCategory');
	}	
	
	
	
	/**
	 * 返回专题分类
	 * @param integer $scid 专题分类ID，如果不传则返回全部
	 * @return array 专题分类
	 */
	public function getSpecialCategory($scid = ''){
		if(($data = model('Cache')->get('AllSpecialCategory')) == false) {
			$list = $this->findAll();
			foreach($list as $k => $v) {
				$data[$v['id']] = $v;
			}
			model('Cache')->set('AllSpecialCategory', $data);
		}
		if(empty($scid)){
			// 返回全部专题分类
			return $data;
		} else {
			// 返回指定的专题分类
			if(is_array($scid)){
				$r = array();
				foreach($scid as $v){
					$r[$v] = $data[$v];
				}
				return $r;
			} else {
				return $data[$scid];
			}
		}
	}
	
	
	/**
	 * 获取专题分类Hash数组
	 * @param string $k Hash数组的Key值字段
	 * @param string $v Hash数组的Value值字段
	 * @return array 专题分类的Hash数组
	 */
	public function getHashSpecialCategory($k = 'id', $v = 'title') {
	    $list = $this->getSpecialCategory();
	    $r = array();
	    foreach($list as $lv) {
	    	$r[$lv[$k]] = $lv[$v];
	    }
	    return $r;
    }
	
	/**
	 * 删除专题分类
	 * @param array|int $ids 专题分类ID
	 * @return array 操作状态【100001:已有记录;1:删除成功;100003:要删除的ID不合法;false:删除失败】
	 */
	public function doDeleteSpecialCategory($ids){
		if(is_array($ids)){
			$ids = implode(',',$ids);	
		}
		if(!trim($ids)){
			return array('status'=>100003);
		}
		
		//要判断分类下面是否有专题--有就不能删除
		$count = model('ZySpecial')->where(array('sc_id'=>array('in',(string)$ids)))->count();
		if($count){
			return array('status'=>100001);
		}
		$attachIds = $this->_getAttachIds($ids);
		//删除附件--只删除数据库不删除真是数据
		model('Attach')->where(array('attach_id'=>array('in',(string)$attachIds)))->delete();
		//删除本身的专题分类
		$i = $this->where(array('id'=>array('in',(string)$ids)))->delete();
		if($i === false){
			return false;
		}else{
			return array('status'=>1);
		}
	}
	//先找到这个专题分类下面的所有附件
	private function _getAttachIds($_ids){
		$ids  = array();
		$pids = $this->where(array('id'=>array('in',(string)$_ids)))->field('cover')->select();
		foreach($pids as $value){
			$ids[] = intval(str_replace('|','',$value['cover']));
		}
		$ids = $ids?implode(',',$ids):0;
		return $ids;
	}
	
}
?>