<?php

/**
 * 云课堂服务层
 * 实现一些公共服务，充值，提现，购买等等
 * @author xiewei <master@xiew.net>
 * @version 1.0
 */
class ZyServiceModel {

    /**
     * 申请提现
     * @param integer $uid 提现用户UID
     * @param integer $wnum 提现数量/金额
     * @param integer $bcard_id 提现银行卡ID
     * @return mixed 成功后返回true，失败返回错误状态码
     * 错误状态一览：
     * 1:申请提现的学币不是系统指定的倍数，或小于0
     * 2:没有找到用户对应的提现银行卡/账户
     * 3:有未完成的提现记录，需要等待完成
     * 4:余额转冻结失败：可能是余额不足
     * 5:提现记录添加失败
     */
    public function applyWithdraw($uid, $wnum, $bcard_id) {

        //检查提现金额是否按照系统规定的倍数
        $wb = intval(getAppConfig('withdraw_basenum'));
        if ($wnum < 0 || $wnum < $wb || $wnum % $wb != 0) {
            return 1;
        }

        //检查用户是否拥有银行卡
        if (!D('ZyBcard')->hasBcard($bcard_id, $uid)) {
            return 2;
        }

        //检查是否已经有未完成的提现记录
        if (D('ZyWithdraw','classroom')->hasUnfinished($uid)) {
            return 3;
        }

        //model
        $zyLearnc = D('ZyLearnc','classroom');

        //余额转冻结
        if (!$zyLearnc->freeze($uid, $wnum)) {
            return 4;
        }

        //申请提现
        $id = D('ZyWithdraw','classroom')->apply($uid, $wnum, $bcard_id);
        if (!$id)
            return 5;

        //添加流水记录
        $zyLearnc->addFlow($uid, 2, $wnum, '申请提现', $id, 'zy_withdraw');

        return true;
    }

    /**
     * 设置提现状态
     * @param integer $id 需要设置的提现记录ID
     * @param integer $uid 该条提现记录对应的UID，后台操作设置为false
     * @param integer $status 要设置的状态 0:提交成功,1:正在处理,2:处理成功,3:处理失败,4:用户取消
     * @param string $reason 如果是失败或取消，那么输入原因
     * @return mixed 成功后返回true，失败返回错误状态码
     * 错误状态一览：
     * 1:设置的状态不存在
     * 2:没有找到对应的提现记录
     * 3:学币冻结扣除失败
     * 4:学币解冻失败
     * 5:提现记录状态改变失败
     * 6:提现已完成或已经关闭
     */
    public function setWithdrawStatus($id, $uid, $status, $reason = '') {
        $ZyWithdraw = D('ZyWithdraw');
        //防止状态码溢出
        if (!$ZyWithdraw->statusExists($status))
            return 1;

        //查找数据记录，如果$uid为false，则不检查uid；
        //ps:uid可以防止前台用户操作非自己的提现记录
        $map['id'] = $id;
        if (false !== $uid)
            $map['uid'] = $uid;
        $rs = $ZyWithdraw->where($map)->find();
        if (!$rs)
            return 2;

        //当状态小于2时才能进行操作
        if ($rs['status'] < 2) {
            //学币及流水操作流程
            $zyLearnc = D('ZyLearnc');
            //提现成功则扣除冻结
            if ($status == 2) {
                $func = 'rmfreeze';
            } elseif ($status == 3 || $status == 4) {
                //如果是失败或用户自动取消，则将冻结转为余额
                $func = 'unfreeze';
            }
            //执行对应的操作
            if (isset($func) && !$zyLearnc->$func($rs['uid'], $rs['wnum'])) {
                return $status == 2 ? 3 : 4;
            }
            //保存记录状态
            $result = $ZyWithdraw->save(array(
                'id' => $id,
                'status' => $status,
                'reason' => ( $status == 2 ? '' : $reason ),
                'rtime' => ( $status < 2 ? 0 : time() ),
            ));
            if (false === $result)
                return 5;
            //添加流水记录
            if ($status == 2) {
                $type = 4;
                $note = '提现成功';
            } elseif ($status == 3) {
                $type = 3;
                $note = '提现失败';
            } elseif ($status == 4) {
                $type = 3;
                $note = '用户取消提现';
            }
            if (isset($type)) {
                $zyLearnc->addFlow(
                        $rs['uid'], $type, $rs['wnum'], $note, $rs['id'], 'zy_withdraw'
                );
            }
            return true;
        } else {
            return 6;
        }
    }

/**
     * 购买单个课程
     * @param $uid
     * @param $video_id
     * @return mixed 成功后返回true，失败返回错误状态码
     * 错误状态一览：
     * 1:可以直接观看，可能的原因是用户自己发布的，用户为管理员，价格为0，已经购买过了
     * 2:找不到课程
     * 3:余额扣除失败，可能原因是余额不足
     * 4:购买记录/订单，添加失败
     */
    public function buyVideo($uid, $video_id) {
        if ($this->checkVideoAccess($uid, $video_id)) {
            return 1;
        }
        $time = time();
        //取得课程
        $video = D('ZyVideo')->where(array(
                    'id' => $video_id,
                    'is_del' => 0,
                    'is_activity' => 1,
                    'listingtime' => array('lt', $time),
                ))->find();
        //找不到课程
        if (!$video)
            return 2;
        //取得价格
        $prices = getPrice($video, $uid, false, true);
        
        $learnc = D('ZyLearnc');
        if (!$learnc->consume($uid, $prices['price'])) {
            return 3; //余额扣除失败，可能原因是余额不足
        }
        //订单数据
        $order = D('ZyOrder');
        $data = array(
            'uid' => $uid,
            'muid' => $video['uid'],
            'video_id' => $video['id'],
            'old_price' => $prices['oriPrice'],
            'discount' => $prices['discount'],
            'discount_type' => $prices['dis_type'],
            'price' => $prices['price'],
            'order_album_id' => 0,
            'learn_status' => 0,
            'ctime' => $time,
        );
        $id = $order->add($data);
        if (!$id)
            return 4; //购买记录/订单，添加失败
            
        //更新订单数量
        D('ZyVideo')->where(array('id' => $video['id']))->save(
                array('video_order_count' => $order->where(
                            array('video_id' => $video['id']))->count()));

        //添加流水记录
        $learnc->addFlow($uid, 0, $prices['price'], '购买课程<'.$video['video_title'].'>', $id, 'zy_order');
        return true;
    }

    /**
     * 购买一个专辑
     */
    public function buyAlbum($uid, $album_id, $total_price) {
        //获取$uid的学币数量
        if (!D('ZyLearnc')->isSufficient($uid, $total_price, 'balance')) {
            return array('status' => '3', 'info' => '可支配的学币不足');
            exit;
        }
        if (!D("ZyLearnc")->consume($uid, $total_price)) {
            return array('status' => '2', 'info' => '合并付款失败，请稍后再试');
            exit;
        }
        $cuid = M("zy_album")->where('id=' . $album_id)->getField('uid');
        $data['uid'] = $uid;
        $data['cuid'] = $cuid;
        $data['album_id'] = $album_id;
        $data['price'] = $total_price;
        $data['ctime'] = time();
        $data['is_del'] = 0;
        $result = M("zy_order_album")->data($data)->add();
        if ($result) {
            return array('status' => '1', 'info' => '添加购买专辑记录成功！', 'rid' => $result);
        }
    }

    /**
     * 检查一个用户是否可以直接观看某个课程
     * @param integer $uid 用户ID
     * @param integer $video_id 课程ID
     * @return boolean 可以直接观看返回true，否则返回false
     */
    public function checkVideoAccess($uid, $video_id) {
        $time = time();
        //取得课程
        $video = D('ZyVideo')->where(array(
                    'id' => $video_id,
                    'is_del' => 0,
                    'is_activity' => 1,
                    'uctime' => array('gt', $time),
                    'listingtime' => array('lt', $time),
                ))->find();
        //找不到课程
        if (!$video)
            return false;
        //管理员
        if (model('UserGroup')->isAdmin($uid)) {
            return true;
        }
        //自己的课程
        if ($video['uid'] == $uid)
            return true;
        $order = D('ZyOrder','classroom');
        //检查是否已经购买过了
        if ($order->isBuyVideo($uid, $video_id))
            return true;
        //取得价格
        $prices = getPrice($video, $uid, false, true);
        //限时免费和价格为0的  不需购买
        if ($prices['price'] <= 0)
            return true;
        return false;
    }

}
