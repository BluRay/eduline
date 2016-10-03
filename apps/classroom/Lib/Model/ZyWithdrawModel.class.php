<?php
/**
 * 申请提现管理模型
 * @author xiewei <master@xiew.net>
 * @version 1.0
 */
class ZyWithdrawModel extends Model {

    //提现记录状态码及对应描述
    protected $status = array('待处理','处理中','处理成功','处理失败','用户取消');


    /**
     * 申请提现
     * @param integer $uid 提现用户UID
     * @param integer $wnum 提现数量/金额
     * @param integer $bcard_id 提现银行卡ID
     * return integer 成功后返回提现记录ID
     */
    public function apply($uid, $wnum, $bcard_id){
        //添加提现记录
        $id = $this->add(array(
            'uid'      => $uid,
            'wnum'     => $wnum,
            'bcard_id' => $bcard_id,
            'status'   => 0,
            'reason'   => '',
            'ctime'    => time(),
            'rtime'    => 0,
        ));
        return intval($id);
    }



    /**
     * 检查是否有未完成的正在提现记录
     */
    public function hasUnfinished($uid){
        return $this->where("uid='$uid' AND (status=0 OR status=1)")->count()>0?true:false;
    }

    /**
     * 取得没有完成的提现记录
     * @param unknown_type $status
     */
    public function getUnfinished($uid){
        return $this->where("uid='$uid' AND (status=0 OR status=1)")->select();
    }


    /**
     * 查询一个提现状态是否存在
     * @param unknown_type $status
     */
    public function statusExists($status){
        return isset($this->status[$status]);
    }
}