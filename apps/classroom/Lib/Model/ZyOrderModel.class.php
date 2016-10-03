<?php
/**
 * 课程订单模型
 * @author xiewei <master@xiew.net>
 * @version 1.0
 */
class ZyOrderModel extends Model{

    //专辑订单模型
    protected $albumOrder = null;

    /**
     * 模型初始化
     * @return void
     */
    public function _initialize(){
        $this->albumOrder = D('ZyOrderAlbum','classroom');
    }

    /**
     * 通过专辑取得某个用户的课程订单列表
     * @param integer $id 专辑订单ID，
     * @param string $vl 该专辑包含的全部课程的id列表，逗号分割值
     * @param boolean $useVidKey 返回数组是否使用video_id作为键名
     * @return array 返回包含该用户订购某个专辑下面的课程订单列表
     */
    public function getAlbumOrderList($id, $vl = null, $useVidKey = false){
        $where = "order_album_id = '$id'";
        //设置了$vl 那么将单独购买的课程也查询出来
        $vl = $vl?getCsvInt($vl, 0, true):false;
        if($vl){
            $where .= " OR video_id IN($vl)";
        }

        $data = $this->where($where)->order('order_album_id DESC,id')->select();
        if(!$data) return array();
        if($useVidKey){
            $array = array();
            foreach($data as $val){
                $array[$val['video_id']] = $val;
            }
            return $array;
        }
        return $data;
    }

    /**
     * 取得专辑学习状态
     * @param integer $uid 用户UID
     * @param integer $albumId 课程ID
     * @return integer|false (课程学习状态(0:未开始,1:学习中,2:已完成))，失败返回false
     */
    public function getVideoLearnStatus($uid, $videoId){
        return $this->where(array('uid'=>$uid,'video_id'=>$videoId))->getField('learn_status');
    }


    /**
     * 取得专辑学习状态
     * @param integer $uid 用户UID
     * @param integer $albumId 专辑ID
     * @return integer|false (专辑学习状态(0:未开始,1:学习中,2:已完成))，失败返回false
     */
    public function getAlbumLearnStatus($uid, $albumId){
        $id = $this->albumOrder->getAlbumOrderId($uid, $albumId);
        if(!$id) return false;
        $array = $this->where("order_album_id='$id' AND learn_status IN(0,1,2)")
                 ->field('distinct learn_status')->select();
        if(!$array) return false;
        $count = count($array);
        //状态次数
        $status = array(0,0,0);
        foreach($array as $val){
            $status[$val['learn_status']] += 1;
        }
        if($status[0] == $count){//全部没开始
            return 0;
        }elseif($status[2] == $count){//全部学习完成
            return 2;
        }else{//各种状态都有
            return 1;
        }
    }


    /**
     * 课程或专辑的学习状态
     * @param integer $uid 用户UID
     * @param integer $id 课程ID/专辑ID
     * @param $type 1为课程，否则为专辑
     * @return integer|false (课程/专辑学习状态(0:未开始,1:学习中,2:已完成))，失败返回false
     */
    public function getLearnStatus($uid, $id, $type){
        if($type == 1){
            return $this->getVideoLearnStatus($uid, $id);
        }else{
            return $this->getAlbumLearnStatus($uid, $id);
        }
    }

    /**
     * 设置课程学习状态
     * @param integer $uid 用户ID
     * @param integer $video_id 视频ID
     * @param integer $status 学习状态(0:未开始,1:学习中,2:已完成)
     */
    public function setLearnStatus($uid, $video_id, $status){
        return $this->where(array('uid'=>$uid,'video_id'=>$video_id))->save(array('learn_status'=>$status));
    }

    /**
     * 查询一个用户是否购买过一个专辑
     * @param integer $uid 用户UID
     * @param integer $albumId 专辑ID
     * @return integer|false 返回对应的专辑订单ID，如果失败则返回false
     */
    public function isBuyAlbum($uid, $albumId){
        return $this->albumOrder->getAlbumOrderId($uid, $albumId);
    }


    /**
     * 查询一个用户是否购买过一个课程
     * @param integer $uid 用户UID
     * @param integer $albumId 课程ID
     * @return integer|false 返回对应的课程订单ID，如果失败则返回false
     */
    public function isBuyVideo($uid, $videoId){
        $id = $this->where(array('uid'=>$uid, 'video_id'=>$videoId))->getField('id');
        return $id ? $id : false;
    }

}