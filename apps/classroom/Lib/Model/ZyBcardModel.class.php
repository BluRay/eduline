<?php
/**
 * 银行卡号管理模型
 * @author MissZhou <misszhou@renrenlo.com>
 * @version GJW2.0
 */
class ZyBcardModel extends Model {

    var $tableName = 'zy_bcard'; //映射到银行卡号表
    protected static $banks = array(
        '中国银行',
        '中国工商银行',
        '中国农业银行',
        '中国建设银行',
        '交通银行',
        '招商银行',
        '民生银行',
        '中信银行',
        '北京银行',
        '广东发展银行',
        '上海浦东发展银行',
        '中国邮政储蓄银行',
    );
	/**
	 * 银行卡号关联搜索
	 * @param int $limit 分页数据
	 * @param array $map 分页条件
	 * @param string $order 排序
	 * @return array 相关数据
	 */
	public function getBCardList($limit,$map = array(), $order = "id DESC"){
		if (isset ( $_POST )) {
			$_POST ['uid'] && $map ['uid'] = array('in',(string)$_POST ['uid']);
			$_POST ['account'] && $map ['account'] = array('LIKE', '%'.t($_POST['account']).'%');
			$_POST ['accountmaster'] && $map ['accountmaster'] = array('LIKE', '%'.t($_POST['accountmaster']).'%');
		}
		// 查询数据
		$list = $this->where ( $map )->order ( $order )->findPage ( $limit );
		return $list;
	}
	
    /**
     * 银行卡操作，删除提示【如果已经有提现记录,则不能删除】
     * @param integer|array $id 银行卡,可以是单个也可以是多个
     * @return array 操作状态【100001:已有提现记录;1:删除成功;100003:要删除的ID不合法;false:删除失败】
     */
    public function doDeleteBankCard($id){
        if(is_array($id)){
            $id = implode(',',$id);
        }
        if(!trim($id)){
            return array('status'=>100003);
        }
        //如果已经有提现记录,则不能删除
        $count = model('ZyWithdraw')->where(array('bcard_id'=>array('in',(string)$id)))->count();
        if($count){
            return array('status'=>100001);
        }
        $i = $this->where(array('id'=>array('in',(string)$id)))->delete();
        if($i === false){
            return false;
        }else{
            return array('status'=>1);
        }
    }


    /**
     * 检查是否有对应ID的提现账户信息
     * @param integer $id 提现账户记录ID
     * @param integer $uid 账户归属用户UID，不设置则不检查是否归属该用户
     * @return boolean
     */
    public function hasBcard($id, $uid = null){
        $map['id'] = $id;
        if(null !== $uid) $map['uid'] = $uid;
        return $this->where($map)->count() > 0 ? true : false;
    }


    /**
     * 获取一个用户的唯一一条银行卡记录
     * @param integer $uid 提现账户记录ID
     * @return array 如果有记录则返回数组
     */
    public function getUserOnly($uid){
        return $this->where(array('uid'=>$uid))->order('id')->find();
    }

    public function getBanks(){
        return self::$banks;
    }
}