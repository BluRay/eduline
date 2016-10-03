<?php
/**
 * 专辑管理模型
 * @author Ashang <Ashang@phpzsm.com>
 * @version CY1.0
 */
class ZyAlbumModel extends Model {


	/**
	 * 获取专辑信息
	 * @param $id 专辑id
	 * array 返回的专辑数据
	 */
	public function getAlbumById($id){
		$map['id'] = $id;
		$data = $this->where($map)->find();
		$data['listingtime'] = $data['listingtime'] ? date("Y-m-d H:i:s", $data['listingtime']) : '';
		$data['uctime'] 	 = $data['uctime'] ? date("Y-m-d H:i:s", $data['uctime']) : '';
		return $data;
	}

    //根据专辑ID获取课程ID方法的暂留缓存
    protected static $_getVideoId = array();


    /*
     * 根据专辑ID获取课程ID
     * @param integer $id 专辑ID
     * @return string 包含课程ID的字符串，用逗号分割的，首尾都有逗号
     */ 
    public function getVideoId($id){
        if(!isset(self::$_getVideoId[$id])){
        	$video_ids = M('zy_album_video_link')->where('album_id='.$id)->findAll();
            self::$_getVideoId[$id] = ','.implode(',' , getSubByKey($video_ids , 'video_id') ).',';
        }

        return self::$_getVideoId[$id];
    }
    
    //根据ID获取专辑名称
    public function getAlbumTitleById($id){
    	$field = 'album_title';
		$map['id'] = $id;
		$data = $this->where($map)->field($field)->find();
		return $data['album_title'];
    }
    
    /**获取精选专辑
     * @param int $limit
     * @return mixed
     */
    public function getBestRecommend($limit=20){
        $map=array(
        	'is_del'=>0,
        	'is_best'=>1
         );
         $dataList=$this->where($map)->limit($limit)->select();
         return $dataList;

    }

    /**获取畅销榜单
     * @param int $limit
     * @return mixed
     */
    public function getSellWell($limit=20){
        $map=array(
            'is_del'=>0
        );
        $dataList = $this->where($map)->limit($limit)->select();
        return $dataList;
    }
    
    /**获取一个专辑集合的价格详细
     * @param array $list专辑集合
     * @return array
     */
    public function getAlbumMoneyList($list=array()){
    	if(empty($list)) return array();
    	foreach ($list as &$val){
    		$val['money_data'] = $this->getAlbumMoeny($val['album_video']);
    	}
    	return $list;
    }

    /**获取一个专辑的价格数组
     * @param $ids课程id集合
     * @return mixed
     */
    public function getAlbumMoeny($ids){
    	$_data = array();
        $oriPrice = 0;   //市场价格/原价
        $vipPrice = 0;   //vip价格
        $disPrice = 0;   //折扣价格
        $discount = 0;   //折扣
        $dis_type = 0;   //折扣类型
        $price = 0;      //价格
        //剩余需要支付的学习币
        $overplus = 0;
        //取得课程
        $data = M('ZyVideo')->where(array('id' => array('in', (string) getCsvInt($ids)),'is_del'=>0))->select();
        foreach ((array) $data as $value) {
            $prices = getPrice($value, $this->mid, true, true);
            $oriPrice += floatval($prices['oriPrice']);
            $vipPrice += floatval($prices['vipPrice']);
            $disPrice += floatval($prices['disPrice']);
            $discount += floatval($prices['discount']);
            $dis_type += floatval($prices['dis_type']);
            $price += floatval($prices['price']);
            if (!isBuyVideo($this->mid, $value['id'])) {
                $overplus += floatval($prices['price']);
            }
        }
        $_data['oriPrice'] = $oriPrice;
        $_data['vipPrice'] = $vipPrice;
        $_data['disPrice'] = $disPrice;
        $_data['discount'] = $discount;
        $_data['dis_type'] = $dis_type;
        $_data['price']    = $price;
        //剩余需要支付的学习币
        $_data['overplus'] = $overplus;
        return $_data;
    }

    /**格式化专辑评分
     * @param $list
     * @return array
     */
    public function getAlbumScore($list){
        if(empty($list)) return array();
        foreach ($list as &$val){
            $val['score'] = round($val['score']/20);
        }
        return $list;
    }

}
