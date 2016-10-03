<?php
/**
 * 学币管理模型
 * @author dengjb <dengjiebin@higher-edu.cn>
 * @version GJW2.0
 */
class ZyLearncModel extends Model {

    protected $tableName = 'zy_learncoin'; //映射到学币表
    protected $flowModel = null;//流水模型对象

    //关联ID的类型描述
    protected static $relTypes = array(
        'zy_order'       => '课程订单',
        'zy_order_album' => '专辑订单',
        'zy_withdraw'    => '提现记录',
        'zy_recharge'    => '充值记录'
    );

    public function __construct($name=''){
        parent::__construct($name);
        $uid = @intval($GLOBALS['ts']['mid']);
        if($uid>0) $this->initUser($uid);
        $vct = F('VIP_CLEAN_TIME');
        //每小时清除一次过期VIP
        if($vct < time()){
            $this->cleanExpireVip();
            F('VIP_CLEAN_TIME', time()+3600);
        }
    }
    /**
     * 模型初始化
     * @return void
     */
    public function _initialize(){
        $this->flowModel = M('zy_learncoin_flow');
    }



    /**
     * 检查一个用户的余额/冻结的数量是否够支配
     * @param integer $uid 用户ID
     * @param integer $num 需要支配的数量
     * @param string $fieid balance or frozen
     * @return boolean 够支配返回true，不够支配则返回false
     */
    public function isSufficient($uid, $num, $fieid = 'balance'){
        $total = $this->where(array('uid'=>$uid))->getField($fieid);
        return $total >= $num ? true : false;
    }


    /**
     * 余额转冻结
     * @param integer $uid 用户ID
     * @param integer $num 需要冻结的数量
     * @return 如果成功则返回true，失败返回false
     */
    public function freeze($uid, $num){
        //取得用户
        $user = $this->getUser($uid);
        //检查用户的余额是否够支配
        if(!$user || $num > $user['balance']){
            return false;
        }
        //余额转冻结
        $user['balance'] -= $num;
        $user['frozen']  += $num;
        //保存并返回结果
        $result = $this->save($user);
        return $result !== true;
    }



    /**
     * 冻结转余额
     * @param integer $uid 用户ID
     * @param integer $num 需要解冻的数量
     * @return 如果成功则返回true，失败返回false
     */
    public function unfreeze($uid, $num){
        //取得用户
        $user = $this->getUser($uid);
        //检查用户的冻结是否够支配
        if(!$user || $num > $user['frozen']){
            return false;
        }
        //冻结转余额
        $user['balance'] += $num;
        $user['frozen']  -= $num;
        //保存并返回结果
        $result = $this->save($user);
        return $result !== true;
    }



    /**
     * 余额消费/扣除
     * @param integer $uid 用户ID
     * @param integer $num 需要扣除的余额数量
     * @return 如果成功则返回true，失败返回false
     */
    public function consume($uid, $num){
        //取得用户
        $user = $this->getUser($uid);
        //检查用户的余额是否够支配
        if(!$user || $num > $user['balance']){
            return false;
        }
        //余额扣除
        $user['balance'] -= $num;
        //保存并返回结果
        $result = $this->save($user);
        return $result !== true;
    }


    /**
     * 冻结扣除
     * @param integer $uid 用户ID
     * @param integer $num 需要扣除冻结的数量
     * @return 如果成功则返回true，失败返回false
     */
    public function rmfreeze($uid, $num){
        //取得用户
        $user = $this->getUser($uid);
        //检查用户的冻结是否够支配
        if(!$user || $num > $user['frozen']){
            return false;
        }
        //冻结扣除
        $user['frozen']  -= $num;
        //保存并返回结果
        $result = $this->save($user);
        return $result !== true;
    }


    /**
     * 余额充值
     * @param integer $uid 用户ID
     * @param integer $num 需要充值的数量
     * @return 如果成功则返回true，失败返回false
     */
    public function recharge($uid, $num){
        //取得用户
        $user = $this->getUser($uid);
        if(!$user) return false;

        //余额充值
        $user['balance']  += $num;
        //保存并返回结果
        $result = $this->save($user);
        return $result !== true;
    }


    /**
     * 添加分成/收益
     * @param integer $uid 用户ID
     * @param integer $num 需要添加分成/收益的数量
     * @return 如果成功则返回true，失败返回false
     */
    public function income($uid, $num){
        return $this->recharge($uid, $num);
    }


    /**
     * 添加一条流水记录
     * @param integer $uid 用户ID
     * @param integer $type 流水类型(0:消费,1:充值,2:冻结,3:解冻,4:冻结扣除,5:分成收入)
     * @param integer $num 变动数量
     * @param string $note 业务备注
     * @param integer $relId 关联ID
     * @param string $relType 关联类型
     * @return 如果成功则返回true，失败返回false
     */
    public function addFlow($uid, $type, $num, $note = '', $relId = 0, $relType = ''){
        //取得用户
        $user = $this->getUser($uid);
        if(!$user) return false;
        $data['uid']      = $uid;
        $data['type']     = $type;
        $data['num']      = $num;
        $data['note']     = $note;
        $data['rel_id']   = $relId;
        $data['rel_type'] = $relType;
        $data['ctime']    = time();
        $data['balance']  = $user['balance'];
        return $this->flowModel()->add($data)? true : false;
    }
    
    
    
    /**
     * 添加多条流水记录
     * @param integer $uid 用户ID
     * @param integer $type 流水类型(0:消费,1:充值,2:冻结,3:解冻,4:冻结扣除,5:分成收入)
     * @param integer $num 变动数量
     * @param array   $data 多条数据参数
     * @param string $relType 关联类型
     * @return 如果成功则返回true，失败返回false
     */
    public function addFlows($uid, $type, $num, $data, $relType = ''){
        //取得用户
        $user = $this->getUser($uid);
        if(!$user) return false;
        $time = time();
        foreach($data as $key=>$val){
            $insert_value .= "('" . $uid . "','" . $type . "','" . $num . "',' 购买课程<" .  $val['video_title'] . "> ','" . $val['id'] . "','" . $relType . "','" . $time . "','" . $user['balance'] . "'),";
        }
        $query = "INSERT INTO " . C("DB_PREFIX") . "zy_learncoin_flow (`uid`,`type`,`num`,`note`,`rel_id`,`rel_type`,`ctime`,`balance`) VALUE " . trim($insert_value, ',');
        return $this->flowModel()->query($query)? true : false;
    }



    /**
     * 清除到期的VIP
     * @return 成功返回true或失败返回false
     */
    public function cleanExpireVip(){
        $result = $this->where('vip_type>0 AND vip_expire<'.time())->save(array(
            'vip_type'   => 0,
            'vip_expire' => 0,
        ));
        return $result !== false;
    }



    /**
     * 取得用户的VIP状态
     * @param integer $uid
     * @return integer (0:普通用户,1:包月VIP,2:包年vip)
     */
    public function getVip($uid){
        return $this->where(array('uid'=>$uid))->getField('vip_type');
    }


    /**
     * 设置用户的vip状态，自动累加和设置类型
     * @param integer $uid
     * @param integer|string $time vip时间，+秒 或 str time 如+12 month,+1 year
     * @param integer $type 时间类型 (1:按月,2:按年)
     * @return boolean 成功返回true或失败返回false
     */
    public function setVip($uid, $time, $type = 1){
        if($type != 1 && $type != 2) return false;
        $user = $this->getUser($uid);
        if(!$user) return false;
        //如果当前是VIP，并且过期时间大于0，那么继续累加时间
        if($user['vip_type']>0 && $user['vip_expire']>0){
            $now = $user['vip_expire'];
        }else{ /*从当前时间开始计算*/
            $now = time();
        }
        //时间累加
        if(is_string($time)){
            $time = strtotime($time, $now)+0;
        }else{
            $time += $now;
        }
        //如果之前是年费，现在是月费，那么继续是年费
        if($user['vip_type']==2 && $type==1){
            $type = 2;
        }
        //set
        $set = array(
            'vip_type'    => $type,
            'vip_expire'  => $time
        );
        $result = $this->where(array('uid'=>$uid))->save($set);
        return $result !== false;
    }


    /**
     * 强制设置VIP状态
     * @param integer $uid
     * @param integer $vip_type (0:普通用户,1:包月VIP,2:包年vip)
     * @param integer|string $time vip时间，0|过期时间戳或者 字符串时间如+12 month,+1 year
     * @return boolean 成功返回true或失败返回false
     */
    public function setVipForce($uid, $vip_type = 0, $time = 0){
        if(!in_array($vip_type, array(0,1,2))) return false;
        if($vip_type == 0){
            $time = 0;
        }elseif(is_string($time)){
            $time = strtotime($time)+0;
        }
        $result = $this->where(array('uid'=>$uid))->save(array(
            'uid'      => $uid,
            'vip_type' => $vip_type,
            'time'     => $time,
        ));
        return false !== $result;
    }


    /**
     * 取得一个用户的扩展信息
     * @param integer $uid 用户UID
     * @return array
     */
    public function getUser($uid){
        return $this->where(array('uid'=>$uid))->find();
    }


    /**
     * 初始化一个用户的扩展信息
     * @param integer $uid 用户UID
     * @param array $data 初始化数据
     * @param boolean $isForce 是否强制初始化，强制初始化将删除之前的信息，谨慎操作
     * @return boolean 成功返回true或失败返回false
     */
    public function initUser($uid, $data = null, $isForce = false){
        if($isForce){
            if(false === $this->delUser($uid)) return false;
        }
        if($isForce || !$this->userExists($uid)){
            static $init = array(
                'balance' => 0,
                'frozen'  => 0,
                'vip_type'=> 0,
                'vip_expire' => 0
            );
            $init['uid'] = $uid;
            if(is_array($data)){
                $data = array_merge($init, $data);
            }else{
                $data = $init;
            }
            return $this->add($data)? true : false;
        }
        return true;
    }


    /**
     * 查询用户扩展信息是否存在
     * @param integer $uid 用户UID
     * @return boolean 存在返回true，不存在返回false
     */
    public function userExists($uid){
        return $this->where(array('uid'=>$uid))->count()>0?true:false;
    }


    /**
     * 删除用户扩展信息，不可恢复，谨慎操作
     * @param integer $uid 用户UID
     * @return boolean 成功返回true，失败返回false
     */
    public function delUser($uid){
        return $this->where(array('uid'=>$uid))->delete()!==false ? true : false;
    }

    /**
     * 取得rel_type 的描述
     * @return array
     */
    public function getRelTypes(){
        return self::$relTypes;
    }


    /**
     * 取得流水记录模型对象
     * return Model
     */
    public function flowModel(){
        return $this->flowModel;
    }


}