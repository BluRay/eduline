<?php
/**
 * 云课堂分类模型 - 数据对象模型
 * @author jason <yangjs17@yeah.net> 
 * @version TS3.0
 */
class VideoCategoryModel extends Model {

    protected $tableName = 'zy_video_category';

    protected static $categoryNames = null;

    /**
     * 
     * 取得顶级分类
     * @param $type 类型 1:课程分类，2:点播分类
     * @return array
     */
    public function getTopCategory($type){
        return $this->getChildCategory(0, $type);
    }

    /**
     * 取得分类名称
     * @param $id 分类的ID
     * @param $isTop 是否取得顶级分类名称
     * @return string 如果找到则返回，没有返回null
     */
    public function getCategoryName($id, $isTop = false){
        if(null === self::$categoryNames){
            $field = 'zy_video_category_id as id,pid,title';
            $data = $this->field($field)->select();
            $cate = array();
            foreach($data as $rs){
                $cate[$rs['id']] = $rs;
            }
            unset($data);
            self::$categoryNames = $cate;
        }else{
            $cate = self::$categoryNames;
        }
        if($isTop){
            $title = null;
            while (true) {
                if(!isset($cate[$id])) break;
                $title = $cate[$id]['title'];
                $id    = $cate[$id]['pid'];
            }
            return $title;
        }else{
            return isset($cate[$id])?$cate[$id]['title']:null;
        }
    }

    /**
     * 通过选择的ID，取得树形结构数组，数组包含上级全部，注意，此树是倒立的
     * @param $id 选择的ID
     * @param $type 类型1:课程分类，2:点播分类
     */
    public function getTreeById($id, $type){
        $data = array();
        $data['list'] = $this->getChildCategory($id, $type);
        if(!$data['list']) $data['list'] = null;
        if($id > 0){
            $id = $this->getParentId($id);
            $data['parent']= $this->getTreeById($id, $type);
        }
        return $data;
    }


    /**
     * 
     */
    public function getParentId($id){
        return intval($this->where(array('zy_video_category_id'=>$id))->getField('pid'));
    }
    /**
     * 取得下级分类
     * @param $pid 上级分类ID
     * @param $type 类型 1:课程分类，2:点播分类
     * @return array
     */
    public function getChildCategory($pid, $type){
        return $this->where(array('pid'=>$pid, 'type'=>$type))
               ->order('sort DESC,zy_video_category_id')->select();
    }

/**
	 * 当指定pid时，查询该父分类的所有子分类；否则查询所有分类
	 * @param integer $pid 父地区ID
	 * @return array 相应地区列表
	 */
	public function getCategoryList($pid = -1) {
		$map = array();
		$pid != -1 && $map['pid'] = $pid;
		$data = $this->where($map)->order('`zy_video_category_id` ASC')->findAll(); 
		return $data;
	}
	
	
/**
	 * 获取分类的树形结构 - 目前为两级结构 - TODO
	 * @param integer $pid 分类的父级ID
	 * @return array 指定父级ID的树形结构
	 */
	public function getCategoryTree($pid) {
		$output	= array();
		$list = $this->getCategoryList();
		// 获取省级
		foreach($list as $k1 => $p) {
			if($p['pid'] == 0) {
				// 获取当前省的市
				$city = array();
				foreach($list as $k2 => $c) {
					if($c['pid'] == $p['zy_video_category_id']) {
						$city[] = array($c['zy_video_category_id'] => $c['title']);
						unset($list[$k2]);
					}
				}
				$output['provinces'][] = array(
					'id' => $p['zy_video_category_id'],
					'name' => $p['title'],
					'citys' => $city,
				);
				unset($list[$k1], $city);
			}
		}
		unset($list);
		return $output;
	}
	
/**
	 * 获取指定分类ID下分类信息
	 * @param integer $id 分类ID
	 * @return array 指定分裂ID下的分类信息
	 */
	public function getCategoryById($id) {
		$result = array();
		if(!empty($id)) {
			$map['zy_video_category_id'] = $id;
			$result = $this->where($map)->find();
		}

		return $result;
	}
	
	/**
	 * 获取指定分类的子分类
	 */
	
	public function getChildCategoryById($id){
		$result = array();
		if(!empty($id)){
			$map['pid'] = intval($id);
			$result['data'] = $this->where($map)->select();
			$next_name = $this->where('zy_video_category_id = '.$id)->field('next_name')->find();
			$result['next_name'] = $next_name['next_name'];
		}
		return $result;
	}
	
/**
	 * 获取指定父分类的树形结构
	 * @param integer $pid 父分类ID
	 * @param integer $type 分类类型(点播或者课程)
	 * @return array 指定树形结构
	 */
	public function getNetworkList($pid = '0',$type = '1') {
		// 子地区树形结构
		if($pid != 0) {
			return $this->_MakeTree($pid,'0',$type);
		}
		// 全部地区树形结构
//		$list = S('VideoCategory');
//		if(empty($list)) {
//			set_time_limit(0);
			$list = $this->_MakeTree($pid,'0',$type);
//			S('VideoCategory', $list);
//		}
	
		return $list;
	}
	
	public function getNetworkSList($pid = '0',$type='1'){
		//地区-学校树形结构
		if($pid != 0){
			return $this->_MakeSTree($pid,'0',$type);
		}
		$list = $this->_MakeSTree($pid,'0',$type);
		return $list;
	}
	
	/**
	 * 清除地区数据PHP文件
	 * @return void
	 */
	public function remakeVedioCategoryCache() {
		S('VideoCategory', null);
	}
	
/**
	 * 递归形成树形结构
	 * @param integer $pid 父级ID
	 * @param integer $level 等级
	 * @return array 树形结构
	 */
	private function _MakeTree($pid, $level = '0', $type = '1') {
		$result = $this->where('pid='.$pid.' and type='.$type)->findAll();
		if($result) {
			foreach($result as $key => $value) {
				$id = $value['zy_video_category_id'];
				$list[$id]['id'] = $value['zy_video_category_id'];
				$list[$id]['pid'] = $value['pid'];
				$list[$id]['title'] = $value['title'];
				$list[$id]['level'] = $level;
				$list[$id]['child'] = $this->_MakeTree($value['zy_video_category_id'], $level + 1,$type);
				$list[$id]['next_name'] = $value['next_name'];
			}
		}

		return $list;
	}
	
	private function _MakeSTree($pid,$level = '0', $type='1'){
		$result = M('zy_school_category')->where('pid='.$pid.' and type='.$type)->findAll();
		if($result) {
			foreach($result as $key => $value) {
				$id = $value['zy_school_category_id'];
				$list[$id]['id'] = $value['zy_school_category_id'];
				$list[$id]['pid'] = $value['pid'];
				$list[$id]['title'] = $value['title'];
				$list[$id]['level'] = $level;
				$list[$id]['child'] = $this->_MakeSTree($value['zy_school_category_id'], $level + 1,$type);
			}
		}

		return $list;
	}
	
	//获取当前分类下的所有子分类
	public function getVideoChildCategory($pid = 0,$type = '1'){
		$_ids = intval($pid);
		$myIds[] = $_ids;
		$this->_getPids($_ids,$myIds,$type);
//		$myIds = $myIds?implode(',',$myIds):0;
		return $myIds;
	}
	private function _getPids($_ids,&$myIds=array(),$type){
		$ids  = array();
		$pids = $this->where(array('pid'=>array('in',(string)$_ids),'type'=>$type))->field('zy_video_category_id')->select();
		
		foreach($pids as $value){
			$ids[] = $value['zy_video_category_id'];
			$myIds[] = $value['zy_video_category_id'];
		}
		$ids = $ids?implode(',',$ids):0;
		
		if(count($pids)){
			$this->_getPids($ids,$myIds,$type);
		}
		//return null;
	}
}