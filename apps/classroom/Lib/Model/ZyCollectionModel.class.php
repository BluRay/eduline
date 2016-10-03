<?php
/**
 * 收藏管理模型
 * @author MissZhou <misszhou@renrenlo.com>
 * @version GJW2.0
 */
class ZyCollectionModel extends Model
{
	public $tableName   = 'zy_collection'; //映射到收藏表
	
	//可收藏的资源类型
	public $_collType  = array(
		1=>'zy_album',//专辑收藏
		2=>'zy_video',//课程收藏
		3=>'zy_question',//提问收藏
		4=>'zy_note',//笔记收藏
		5=>'zy_review',//点评收藏
	);

	//可收藏的资源集合
	private $_collList  = array(
		'zy_album',//专辑收藏
		'zy_video',//课程收藏
		'zy_question',//提问收藏
		'zy_note',//笔记收藏
		'zy_review',//点评收藏
	);
	
	//可收藏的资源集合
	private $_collListField  = array(
		'ZyAlbum'=>'id,`album_title`',//专辑要取得字段
		'ZyVideo'=>'id,`video_title`',//课程收藏
		'ZyQuestion'=>'id,`qst_title`,`qst_description`,`qst_help_count`',//提问收藏
		'ZyNote'=>'id,`note_title`',//笔记收藏
		'ZyReview'=>'id,`review_description`',//点评收藏
	);
	//可收藏的资源集合
	private $_collField  = array(
		'ZyAlbum'=>'album_collect_count',//专辑收藏
		'ZyVideo'=>'video_collect_count',//课程收藏
		'ZyQuestion'=>'qst_collect_count',//提问收藏
		'ZyNote'=>'note_collect_count',//笔记收藏
		'ZyReview'=>'review_collect_count',//点评收藏
	);
	
	
	/**
	 * 添加收藏记录
	 * @param array $data 收藏相关数据
	 * @return boolean|array 是否收藏成功|收藏的数据
	 */
	public function addcollection($data){
		//将表名转换为小写
		$data['source_table_name'] = strtolower($data['source_table_name']);
		//判断是否登录
		$data['uid'] = (!$data['uid']) ? $GLOBALS['ts']['mid'] : $data['uid'];
		if ( !intval($data['uid']) ){
			$this->error = '未登录收藏失败';		// 收藏失败
			return false;
		}
		//验证数据
		if(empty($data['source_id']) || empty($data['source_table_name'])) {
			$this->error = '收藏所需信息不完整!';			// 资源ID,资源所在表名,资源所在应用不能为空
			return false;
		}
		//判断传入的资源是否为可收藏资源
		if(!in_array($data['source_table_name'],$this->_collList)){
			$this->error = '该资源不可收藏!';			// 资源ID,资源所在表名,资源所在应用不能为空
			return false;
		}
		// 判断是否已收藏 
		$isExist = $this->where(array('source_id'=>$data['source_id'],'uid'=>$data['uid'],'source_table_name'=>$data['source_table_name']))->count();
		if(intval($isExist)) {
			$this->error = '您已经收藏过了';		// 您已经收藏过了
			return false;
		}
		//添加数据
		$data['uid'] = intval($data['uid']);
		$data['source_id'] = intval($data['source_id']);
		$data['source_table_name'] = t($data['source_table_name']);
		$data['ctime'] = time();
		if($data['collection_id'] = $this->add($data)){
			// 生成缓存
			model('Cache')->set('mzcollect_'.$data['uid'].'_'.$data['source_table_name'].'_'.$data['source_id'], $data);
			
			// 收藏数加1
			$this->_collectcount($data['source_id'],$data['source_table_name'],true);
			return $data;
		}else{
			$this->error = '收藏失败!';
			return false;
		}
		return null;
	}
	
	
	
	/**
	 * 取消收藏
	 * @param integer $sid 资源ID
	 * @param string $stable 资源表名称
	 * @param integer $uid 用户UID
	 * @return boolean 是否取消收藏成功
	 */
	public function delcollection($sid, $stable, $uid = '') {
		$stable = strtolower($stable);
		// 验证数据
		if(empty($sid) || empty($stable)) {
			$this->error = '错误的参数';		// 错误的参数
			return false;
		}
		if (!in_array($stable, $this->_collList)) {
			$this->error = '没有要取消的资源错误';		// 错误的参数
			return false;
		}
		$uid = (empty($uid) || ($uid == '')) ? $GLOBALS['ts']['mid'] : $uid;
		$map['uid'] = $uid;
		$map['source_id'] = $sid;
		$map['source_table_name'] = $stable;
		// 取消收藏操作
		// dump($map);
		if($this->where( $map )->delete()){
			// 设置缓存
			model('Cache')->set('mzcollect_'.$uid.'_'.$stable.'_'.$sid, '');
			// 收藏数减1
			$this->_collectcount($sid,$stable,false);
			return true;
		} else {
			$this->error = '取消收藏失败';
			return false;
		}
	}
	
	/**
	 * 获取指定收藏的信息
	 * @param string $stable 资源表名称
	 * @param integer $sid 资源ID
	 * @param integer $uid 用户UID
	 * @return array 指定收藏的信息
	 */
	public function getcollection($stable,$sid, $uid = '') {
		$stable = strtolower($stable);
		// 验证数据
		if(empty($source_id) || empty($stable)) {
			$this->error = '错误的参数';		// 错误的参数
			return false;
		}
		if (!in_array($stable, $this->_collList)) {
			$this->error = '没有要取消的资源错误';		// 错误的参数
			return false;
		}
		$uid = (empty($uid) || ($uid == '')) ? $GLOBALS['ts']['mid'] : $uid;
		
		// 获取收藏信息
		if(($cache = model('Cache')->get('mzcollect_'.$uid.'_'.$stable.'_'.$sid) ) === false) {
			$map['source_table_name'] = $stable;
			$map['source_id'] = $sid;
			$map['uid'] = $uid;
			$cache = $this->where($map)->find();
			model('Cache')->set('mzcollect_'.$uid.'_'.$stable.'_'.$sid,$cache);
		}
		//取出对应表的数据
		$cache = $this->_getcollectsource($cache,$stable);
		return $cache;
	}
	
	
	public function myCollection($stable,$limit,$uid=''){
		$ctablename = $this->getTableName();
		$otablename = M(parse_name(ucwords($stable)))->getTableName();
		
		//取得用户ID
		$uid = (empty($uid) || ($uid == '')) ? $GLOBALS['ts']['mid'] : $uid;
		//查询数据
		$data =	M()->table(array($ctablename,$otablename))->where("{$ctablename}.source_id={$otablename}.id and {$ctablename}.uid={$uid} and  {$ctablename}.source_table_name='{$stable}'")->findPage($limit);
		
		return $data;
	}
	/**
	 * 检查某个资源是否已经被收藏
	 * @param int source_id 资源ID
	 * @param int stable 资源位置表名
	 * @param bool type 1:加;0:减;
	 * @author MissZhou <misszhou@renrenlo.com>
	 */
	public function isCollect($oid,$stable,$uid=''){
		//取得用户ID
		$uid = (empty($uid) || ($uid == '')) ? $GLOBALS['ts']['mid'] : intval($uid);
		$map['uid'] = $uid;
		$map['source_id'] = intval($oid);
		$map['source_table_name'] = $stable;
		return $this->where($map)->count();
	}
	
	
	/**
	 * 收藏个数【+/-】1
	 * @param int source_id 资源ID
	 * @param int stable 资源位置表名
	 * @param bool type 1:加;0:减;
	 * @author MissZhou <misszhou@renrenlo.com>
	 */
	private function _collectcount($source_id,$stable,$type){
		$stable = ucwords($stable);
		$stable = parse_name($stable,1);
		
		if($type){
			M($stable)->where(array('id'=>array('eq',$source_id)))->setInc($this->_collField[$stable]);
		}else{
			M($stable)->where(array('id'=>array('eq',$source_id)))->setDec($this->_collField[$stable]);
		}
		return true;
	}
	/**
	 * 取出指定的资源数据
	 * @param array $data 收藏数据
	 * @param string $stable 资源位置表名
	 * @author MissZhou <misszhou@renrenlo.com>
	 */
	private function _getcollectsource($data,$stable){
		$stable = ucwords($stable);
		$stable = parse_name($stable,1);
		
		if(isset($data[0]['collection_id']) && $data[0]['collection_id']){
			$sids = array();
			//说明是二维数组---取
			foreach((array)$data as $value){
				$sids[] = $value['source_id'];
			}
			$sids = $sids?implode(',',$sids):0;
			//取出所需的资源集合
			$_data = $this->_getsource(array('id'=>array('in',(string)$sids)),$stable);
			
			foreach((array)$data as $key=>$value){
				$data[$key] = array_merge($value,$_data[$value['source_id']]);
			}
			return $data;
		}else{
			$_data = $this->_getsource(array('id'=>array('eq',intval($data['collection_id']))),$stable);
			foreach((array)$data as $key=>$value){
				$data[$key] = array_merge($value,$_data[$value['source_id']]);
			}
			return $data;
		}
		return null;
	}
	/**
	 * 去数据库取资源数据
	 * @param array $map 条件
	 * @param string $stable 资源位置表名
	 * @author MissZhou <misszhou@renrenlo.com>
	 */
	private function _getsource($map,$stable){
		$data  = array();
		$_data = M($stable)->where($map)->field($this->_collListField[$stable])->select();
		foreach($_data as $value){
			$data[$value['id']] = $value;
		}
		return $data;
	}
	
	
	
	
}
?>