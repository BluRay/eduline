<?php
/**
 * 
 * @author xiewei <master@xiew.net>
 * @version 1.0
 */
class ZyRechargeModel extends Model{

    public function __construct($name=''){
        parent::__construct($name);
        //自动删除3个月前的
        $time = strtotime('-3 month');
        $this->where("status=0 AND ctime<'{$time}'")->delete();
    }

    /**
     * 添加充值记录
     * @param array $data
     * @return integer 如果成功返回记录号
     */
    public function addRechange($data){
        $data['ctime'] = time();
        $data['status'] = 0;
        $data['stime']  = 0;
        $data['pay_order']= '';
        $data['pay_data'] = '';
        $id = $this->add($data);
        return $id ? $id : false;
    }

    public function setSuccess($id, $order){
        $data = $this->find($id);
        if(!$data) return false;
        if($data['status'] == 0){
            $data['status'] = 1;
            $data['stime']  = time();
            $data['pay_order'] = $order;
            //修改充值记录状态
            $l = D('ZyLearnc');
            if(false !== $this->save($data)){
                if($data['type']==1||$data['type']==2){
                    //设置VIP
                    $type = $data['type']==1?2:1;
                    $time = $data['vip_length'];
                    if(!$l->setVip($data['uid'], $time, $type)){
                        return false;
                    }
                    $note = $type==1?'充值年费VIP会员':'充值月费VIP会员';
                }else{
                    //添加学币
                    if(!$l->recharge($data['uid'], $data['money'])){
                        return false;
                    }
                    $note = '充值学币';
                }
                $l->addFlow($data['uid'], 1, $data['money'], $note, $data['id'], 'zy_rechange');
                return true;
            }
        }
        return $data['status']==1;
    }
}