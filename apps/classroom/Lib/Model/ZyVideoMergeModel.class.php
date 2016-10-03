<?php
class ZyVideoMergeModel extends Model{

    /**
     * 查询一个课程到组合列表，$uid和$tmp_id必须选填一个
     * @param integer $vid 课程ID
     * @param integer $uid 用户ID
     * @param integer $tmp_id 临时用户ID
     * @return mixed 如果成功返回记录ID 失败返回false
     */
    public function addVideo($vid, $uid, $tmp_id = ''){
        if(!$uid&&!$tmp_id) return false;
        if($this->hasVideo($vid, $uid, $tmp_id) > 0){
            return false;
        }
        //数据
        $data['video_id'] = $vid;
        $data['uid']      = $uid;
        $data['tmp_id']   = $tmp_id;
        $data['ctime']     = time();
        //添加
        $id = $this->add($data);
        return $id ? $id : false;
    }

    /**
     * 删除某个用户的一条课程组合记录，$uid和$tmp_id必须选填一个
     * @param integer $uid 用户ID
     * @param integer $tmp_id 临时用户ID
     * @return boolean 如果成功返回true 失败返回false
     */
    public function delVideo($vid, $uid, $tmp_id = ''){
        if(!$uid&&!$tmp_id) return false;
        $map['video_id'] = $vid; 
        if($uid) $map['uid'] = $uid;
        if($tmp_id) $map['tmp_id'] = $tmp_id;

        if(false !== $this->where($map)->delete()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 取得一个用户的全部课程列表，$uid和$tmp_id必须选填一个
     * @param integer $uid 用户ID
     * @param integer $tmp_id 临时用户ID
     * @return array 如果有则返回数组
     */
    public function getList($uid, $tmp_id = ''){
        if(!$uid&&!$tmp_id) return false;
        if($uid) $map['uid'] = $uid;
        if($tmp_id) $map['tmp_id'] = $tmp_id;
        return $this->where($map)->order('ctime DESC,id DESC')->select();
    }

    /**
     * 清除一个用户的课程列表，$uid和$tmp_id必须选填一个
     * @param integer $uid 用户ID
     * @param integer $tmp_id 临时用户ID
     * @return boolean 如果成功返回true 失败返回false
     */
    public function cleanVideo($uid, $tmp_id = ''){
        if(!$uid&&!$tmp_id) return false;
        if($uid) $map['uid'] = $uid;
        if($tmp_id) $map['tmp_id'] = $tmp_id;
        if(false !== $this->where($map)->delete()){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 查询一个课程已经添加到组合列表，$uid和$tmp_id必须选填一个
     * @param integer $vid 课程ID
     * @param integer $uid 用户ID
     * @param integer $tmp_id 临时用户ID
     * @return integer 如果存在则返回数量，不存在返回0
     */
    public function hasVideo($vid, $uid, $tmp_id = ''){
        if(!$uid&&!$tmp_id) return false;
        $map['video_id'] = $vid; 
        if($uid) $map['uid'] = $uid;
        if($tmp_id) $map['tmp_id'] = $tmp_id;
        return $this->where($map)->count();
    }


    /**
     * 将临时用户的课程组合列表导入到真实用户
     * @param integer $tmp_id 临时用户ID
     * @param integer $uid 用户ID
     * @return 如果成功返回true 失败返回false
     */
    public function import($tmp_id, $uid){
        $map['tmp_id'] = $tmp_id;
        if(false !== $this->where(array('tmp_id'=>$tmp_id))->save(array('uid'=>$uid))){
            $ids = $this->where(array('uid'=>$uid))->group('video_id')->select();
            $ids = implode(',', getSubByKey($ids, 'id'));
            if($ids){
                $this->where("id NOT IN($ids) AND uid={$uid}")->delete();
            }
            return true;
        }else{
            return false;
        }
    }

    /**
     * 返回一个用户课程列表的数量
     * @param $uid 用户ID
     * @param $tmp_id 临时用户ID
     * @return integer
     */
    public function getNum($uid, $tmp_id = ''){
        if(!$uid&&!$tmp_id) return false;
        if($uid) $map['uid'] = $uid;
        if($tmp_id) $map['tmp_id'] = $tmp_id;
        return $this->where($map)->count();
    }
}