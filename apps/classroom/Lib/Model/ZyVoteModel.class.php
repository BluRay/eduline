<?php
/**
 * 投票管理模型
 * @author MissZhou <misszhou@renrenlo.com>
 * @version GJW2.0
 */
class ZyVoteModel extends Model
{
	public $tableName   = 'zy_vote'; //映射到投票表
	//可收藏的资源类型
	public $_collType  = array(
		1=>'zy_album',//专辑收藏
		2=>'zy_video',//课程收藏
		3=>'zy_question',//提问收藏
		4=>'zy_note',//笔记收藏
		5=>'zy_review',//点评收藏
	);
	//可投票的资源集合
	private $_collList  = array(
		'zy_album',//专辑投票
		'zy_video',//课程投票
		'zy_question',//提问投票
		'zy_note',//笔记投票
		'zy_review',//点评投票
	);
	
	//可投票的资源集合
	private $_collListField  = array(
		'ZyAlbum'=>'id,`album_title`',//专辑要取得字段
		'ZyVideo'=>'id,`video_title`',//课程投票
		'ZyQuestion'=>'id,`qst_title`',//提问投票
		'ZyNote'=>'id,`note_title`',//笔记投票
		'ZyReview'=>'id,`review_description`',//点评投票
	);
	//可投票的资源集合
	private $_collField  = array(
		'ZyAlbum'=>'album_collect_count',//专辑投票
		'ZyVideo'=>'video_collect_count',//课程投票
		'ZyQuestion'=>'qst_collect_count',//提问投票
		'ZyNote'=>'note_collect_count',//笔记投票
		'ZyReview'=>'review_vote_count',//点评投票
	);
	
	
	/**
	 * 添加投票记录
	 * @param array $data 投票相关数据
	 * @return boolean|array 是否投票成功|投票的数据
	 */
	public function addvote($data){
		//将表名转换为小写
		$data['source_table_name'] = strtolower($data['source_table_name']);
		
		//判断是否登录
		$data['uid'] = (!$data['uid']) ? $GLOBALS['ts']['mid'] : $data['uid'];
		if ( !intval($data['uid']) ){
			$this->error = '未登录投票失败';		// 投票失败
			return false;
		}
		//验证数据
		if(empty($data['source_id']) || empty($data['source_table_name'])) {
			$this->error = '投票所需信息不完整!';			// 资源ID,资源所在表名,资源所在应用不能为空
			return false;
		}
		//判断传入的资源是否为可投票资源
		if(!in_array($data['source_table_name'],$this->_collList)){
			$this->error = '该资源不可投票!';			// 资源ID,资源所在表名,资源所在应用不能为空
			return false;
		}
		// 判断是否已投票 
		$isExist = $this->getvote($data['source_id'], $data['source_table_name']);
		if(!empty($isExist)) {
			$this->error = '您已经投票过了';		// 您已经投票过了
			return false;
		}
		//添加数据
		$data['uid'] = intval($data['uid']);
		$data['source_id'] = intval($data['source_id']);
		$data['source_table_name'] = t($data['source_table_name']);
		$data['ctime'] = time();
		if($data['vote_id'] = $this->add($data)){
			// 生成缓存
			model('Cache')->set('mzvote_'.$data['uid'].'_'.$data['source_table_name'].'_'.$data['source_id'], $data);
			
			// 投票数加1
			$this->_votecount($data['source_id'],$data['source_table_name'],true);
			return $data;
		}else{
			$this->error = '投票失败!';
			return false;
		}
		return null;
	}
	
	
	
	/**
	 * 取消投票
	 * @param integer $sid 资源ID
	 * @param string $stable 资源表名称
	 * @param integer $uid 用户UID
	 * @return boolean 是否取消投票成功
	 */
	public function delvote($sid, $stable, $uid = '') {
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
		// 取消投票操作
		if($this->where( $map )->delete()){
			// 设置缓存
			model('Cache')->set('mzvote_'.$uid.'_'.$stable.'_'.$sid, '');
			// 投票数减1
			$this->_votecount($sid,$stable,false);
			return true;
		} else {
			$this->error = '取消投票失败';
			return false;
		}
	}
	
	/**
	 * 获取指定投票的信息
	 * @param string $stable 资源表名称
	 * @param integer $sid 资源ID
	 * @param integer $uid 用户UID
	 * @return array 指定投票的信息
	 */
	public function getvote($stable,$sid, $uid = '') {
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
		
		// 获取投票信息
		if(($cache = model('Cache')->get('mzvote_'.$uid.'_'.$stable.'_'.$sid) ) === false) {
			$map['source_table_name'] = $stable;
			$map['source_id'] = $sid;
			$map['uid'] = $uid;
			$cache = $this->where($map)->find();
			model('Cache')->set('mzvote_'.$uid.'_'.$stable.'_'.$sid,$cache);
		}
		//取出对应表的数据
		$cache = $this->_getvotesource($cache,$stable);
		return $cache;
	}
	
	/**
	 * 检查某个资源是否已经被收藏
	 * @param int source_id 资源ID
	 * @param int stable 资源位置表名
	 * @param bool type 1:加;0:减;
	 * @author MissZhou <misszhou@renrenlo.com>
	 */
	public function isVote($oid,$stable,$uid=''){
		//取得用户ID
		$uid = (empty($uid) || ($uid == '')) ? $GLOBALS['ts']['mid'] : intval($uid);
		$map['uid'] = $uid;
		$map['source_id'] = intval($oid);
		$map['source_table_name'] = $stable;
		return $this->where($map)->count();
	}
	
	/**
	 * 投票个数【+/-】1
	 * @param int source_id 资源ID
	 * @param int stable 资源位置表名
	 * @param bool type 1:加;0:减;
	 * @author MissZhou <misszhou@renrenlo.com>
	 */
	private function _votecount($source_id,$stable,$type){
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
	 * @param array $data 投票数据
	 * @param string $stable 资源位置表名
	 * @author MissZhou <misszhou@renrenlo.com>
	 */
	private function _getvotesource($data,$stable){
		$stable = ucwords($stable);
		$stable = parse_name($stable,1);
		
		if(isset($data[0]['vote_id']) && $data[0]['vote_id']){
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
			$_data = $this->_getsource(array('id'=>array('eq',intval($data['vote_id']))),$stable);
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
		$_data = model($stable)->where($map)->field($this->_collListField[$stable])->select();
		foreach($_data as $value){
			$data[$value['id']] = $value;
		}
		return $data;
	}
	
	
	
	
}
?>