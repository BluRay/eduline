<?php
/**
 * 专题管理模型
 * @author MissZhou <misszhou@renrenlo.com>
 * @version GJW2.0
 */
class ZySpecialModel extends Model
{
	var $tableName = 'zy_special'; //映射到专题表

	/**
	 * 专题关联搜索
	 * @param int $limit 分页数据
	 * @param array $map 分页条件
	 * @param string $order 排序
	 * @return array 相关数据
	 */
	public function getSpecialList($limit,$map = array(), $order = "id DESC"){
		if (isset ( $_POST )) {
			$_POST ['id'] && $map ['id'] = intval ( $_POST ['id'] );
			$_POST ['sc_id'] && $map ['sc_id'] = intval ( $_POST ['sc_id'] );
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
	 * 删除专题
	 * @param array|int $ids 专题ID
	 * @return array 操作状态【1:删除成功;100003:要删除的ID不合法;false:删除失败】
	 */
	public function doDeleteSpecial($ids){
		if(is_array($ids)){
			$ids = implode(',',$ids);	
		}
		if(!trim($ids)){
			return array('status'=>100003);
		}
		$attachIds = $this->_getAttachIds($ids);
		//删除附件--只删除数据库不删除真是数据
		model('Attach')->where(array('attach_id'=>array('in',(string)$attachIds)))->delete();
		
		//删除文件夹
		$data = $this->where(array('id'=>array('in',(string)$ids)))->field('id,foldername')->select();
		$special_path = SITE_PATH.DIRECTORY_SEPARATOR.'special'.DIRECTORY_SEPARATOR;
		tsload(ADDON_PATH.'/library/io/Dir.class.php');
		$dirs = new Dir();
		foreach($data as $value){
			//拼接新的目录
			$new = $special_path.$value['foldername'].DIRECTORY_SEPARATOR;
			if (is_dir($new)){
				$dirs->delDir($new);
			}
		}
		//删除本身的专题
		$i = $this->where(array('id'=>array('in',(string)$ids)))->delete();
		
		if($i === false){
			return false;
		}else{
			return array('status'=>1);
		}
	}
	
	//先找到这个专题下面的所有附件
	private function _getAttachIds($ids){
		$_ids  = array();
		$pids = $this->where(array('id'=>array('in',(string)$ids)))->field('attach_id')->select();
		foreach($pids as $value){
			$_ids[] = intval(str_replace('|','',$value['attach_id']));
		}
		//去掉重复的
		$_ids = array_unique($_ids);
		$_ids = $_ids?implode(',',$_ids):0;
		return $_ids;
	}
	
}
?>