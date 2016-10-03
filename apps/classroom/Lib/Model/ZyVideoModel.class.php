<?php
/**
 * 课程模型 - 数据对象模型
 * @author wayne <idafoo@sina.com> 
 * @version TS3.0
 */
class ZyVideoModel extends Model {
	var $tableName = 'zy_video';
	protected $error = '';
	/**
	 * 获取课程列表
	 * @param $limit 记录数量
	 * @param $is_activity 课程是否通过审核
	 */
	public function getVideosList($limit = 20, $is_activity = 1, $is_del = 0){
		$map['is_del'] = $is_del; //搜索非隐藏内容
		$map['is_activity'] = $is_activity;
		$list = $this->where($map)->field('id,title,uid,is_activity,ctime')->order('ctime desc,id desc')->findPage($limit);
		foreach ($list['data'] as $key => $value){
			$list['data'][$key]['user_title'] = getUserSpace($value['uid']); 
		}
		return $list;
	}
	/**
	 * 加载畅销榜单
	 * @param $limit 记录数量
	 * @param $is_activity 课程是否通过审核
	 */
	public function getSellWell($limit = 20, $is_activity = 1, $is_del = 0){
		$time=time();
		$where = "is_del=0 AND is_activity=1 AND uctime>$time AND listingtime<$time";
		$list = $this->where($where)->order('video_order_count desc')->limit($limit)->select();
		foreach ($list as  &$value){
			$value['user_title'] = getUserSpace($value['uid']);
			$value['money_data'] = getPrice($value, $this->mid);
			$value['score']=round($value['video_score']/20);
		}
		return $list;
	}
	
	/**
	 * 根据分类ID获取课程列表
	 */
	public function getVideoListByIds($ids,$limit = 6){
		$map['video_category'] = array('in',$ids);
		$data = $this->where($map)->findPage($limit);
		foreach($data['data'] as $key=>$vo){
			$data['data'][$key]['vuid'] = $vo['uid'];
			$data['data'][$key]['userinfo'] = model('Follow')->getFollowStateByFids($GLOBALS['ts']['mid'], $vo['uid']);
			$data['data'][$key]['is_buy'] = D("ZyOrder",'classroom')->isBuyVideo($GLOBALS['ts']['mid'],$vo['id']);
		}
		return $data;
	}
	
	
	/**
	 * 获取课程信息
	 * @param $id 课程id
	 */
	public function getVideoById($id){
		$map['id'] = $id;
		$data = $this->where($map)->field($filed)->find();
		$data['uid'] = !$data['uid'] ? 0 : $data['uid'];
		$data['cover_path'] = getAttachUrlByAttachId($data['cover']);
		return $data;
	}
	
	/**
	 * 获取课程标题
	 */
	public function getVideoTitleById($id){
		$field = 'video_title';
		$map['id'] = $id;
		$data = $this->where($map)->field($field)->find();
		return $data['video_title'];
	}

    /**
     * 
     * 获取某个用户订购的课程
     * @param integer $uid
     * @param integer|null|false $findPage
     */
    public function getBuyVideo($uid, $findPage = false){
        $order = D('ZyOrder')->getTableName();
        $inSql = "SELECT video_id FROM {$order} WHERE uid={$uid}";
        $where = "is_del=0 AND uctime>".time()." AND is_activity=1 AND ".
             "(video_id IN($inSql) OR uid='{$uid}')";
        if(false !== $findPage){
            return $this->where($where)->findPage($findPage);
        }else{
            return $this->where($where)->select();
        }
    }
}