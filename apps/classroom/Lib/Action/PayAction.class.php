<?php
tsload(APPS_PATH.'/classroom/Lib/Action/CommonAction.class.php');
class PayAction extends CommonAction{

    /**
     * 充值学币
     */
    public function recharge(){
        if($_SERVER['REQUEST_METHOD']!='POST') exit;

        //使用后台提示模版
        $this->assign('isAdmin', 1);

        //必须要先登陆才能进行操作
        if($this->mid <= 0) $this->error('请先登录在进行充值');

        if($_POST['pay']!='alipay'&&$_POST['pay']!='unionpay'){
            $this->error('支付方式错误');
        }

        $money = floatval($_POST['money']);
        if($money <= 0){
            $this->error('请选择或填写充值金额');
        }
        $rechange_base = getAppConfig('rechange_basenum');
        if($rechange_base>0 && $money%$rechange_base != 0){
            if($rechange_base == 1){
                $this->error('充值金额必须为整数');
            }else{
                $this->error("充值金额必须为{$rechange_base}的倍数");
            }
        }
        //$money = 0.01;
        $re = D('ZyRecharge');
        $id = $re->addRechange(array(
            'uid'      => $this->mid,
            'type'     => '0',
            'money'    => $money,
            'note'     => "Eduline-学币充值-{$money}元",
            'pay_type' => $_POST['pay'],
        ));
        if(!$id) $this->error("操作异常");
        if($_POST['pay'] == 'alipay'){
            $this->alipay(array(
                'out_trade_no' => $id,
                'subject'      => 'Eduline-学币充值',
                'total_fee'    => $money,
            ));
        }elseif($_POST['pay'] == 'unionpay'){
            $this->unionpay(array(
                'id' => $id,
                'money' => $money,
                'subject' => 'Eduline-学币充值'
            ));
        }
    }

    /**
     * 充值VIP
     */
    public function rechargeVip(){
        if($_SERVER['REQUEST_METHOD']!='POST') exit;

        //使用后台提示模版
        $this->assign('isAdmin', 1);

        //必须要先登陆才能进行操作
        if($this->mid <= 0) $this->error('请先登录在进行充值');

        //检查支付方式
        if($_POST['pay']!='alipay'&&$_POST['pay']!='unionpay'){
            $this->error('支付方式错误');
        }

        //检查充值类型
        if($_POST['type']!=1 && $_POST['type']!=0){
            $this->error('支付类型错误');
        }
        $type = $_POST['type']==1?1:2;
        if($type == 1){
            $month = intval($_POST['month']);
            if($month<=0) $this->error('请输入充值月数');
            $money = getAppConfig('vip_price')*$month;
            $vip_length = "+{$month} month";
            $txt = "({$month}个月)";;
        }else{
            $money = getAppConfig('vip_year_price');
            $vip_length = "+1 year";
            $txt = '(一年)';
        }
        //$money = 0.01;
        $re = D('ZyRecharge');
        $id = $re->addRechange(array(
            'uid'      => $this->mid,
            'type'     => $type,
            'vip_length' => $vip_length,
            'money'    => $money,
            'note'     => "Eduline-VIP充值{$txt}",
            'pay_type' => $_POST['pay'],
        ));
        if(!$id) $this->error("操作异常");
        if($_POST['pay'] == 'alipay'){
            $this->alipay(array(
                'out_trade_no' => $id,
                'subject'      => 'Eduline-VIP充值'.$txt,
                'total_fee'    => $money,
            ));
        }elseif($_POST['pay'] == 'unionpay'){
            $this->unionpay(array(
                'id' => $id,
                'money' => $money,
                'subject' => 'Eduline-VIP充值'.$txt,
            ));
        }
    }

    public function alinu(){
        include SITE_PATH.'/api/pay/alipay/alipay.php';
        $alipay_config = $this->getAlipayConfig($alipay_config);
        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        if(!$verify_result) exit('fail');
        //商户订单号
        $out_trade_no = $_POST['out_trade_no'];
        //支付宝交易号
        $trade_no = $_POST['trade_no'];
        //交易状态
        $trade_status = $_POST['trade_status'];
        $re = D('ZyRecharge');
        if($trade_status == 'TRADE_FINISHED') {
            $re->setSuccess($out_trade_no, $trade_no);
        }elseif($trade_status == 'TRADE_SUCCESS'){
            $re->setSuccess($out_trade_no, $trade_no);
        }
        echo 'success';
    }

    public function aliru(){
        unset($_GET['app'],$_GET['mod'],$_GET['act']);
        include SITE_PATH.'/api/pay/alipay/alipay.php';
        $alipay_config = $this->getAlipayConfig($alipay_config);
        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyReturn();
        $this->assign('isAdmin', 1);
        $this->assign('jumpUrl', U('classroom/User/recharge'));
        if(!$verify_result) $this->error('操作异常');
        //商户订单号
        $out_trade_no = $_GET['out_trade_no'];
        //支付宝交易号
        $trade_no = $_GET['trade_no'];
        //交易状态
        $trade_status = $_GET['trade_status'];
        $re = D('ZyRecharge');
        if($trade_status == 'TRADE_FINISHED'||$trade_status == 'TRADE_SUCCESS') {
            $result = $re->setSuccess($out_trade_no, $trade_no);
        }
        if($result){
            $this->success('充值成功！');
        }else{
            $this->error('充值失败！');
        }
    }

    protected function alipay($args){
        include SITE_PATH.'/api/pay/alipay/alipay.php';
        
        //获取后台配置的支付宝接口数据
        $alipay_config = $this->getAlipayConfig($alipay_config);
        //构造要请求的参数数组，无需改动
        $parameter = array(
                "service" => "create_direct_pay_by_user",
                "partner" => trim($alipay_config['partner']),
                "payment_type"  => '1',//支付类型
                "notify_url"    => SITE_URL.'/classroom/Pay/alinu',//服务器异步通知页面路径
                "return_url"    => SITE_URL.'/classroom/Pay/aliru',//页面跳转同步通知页面路径
                //读取支付宝卖家账户配置
                "seller_email"  => $alipay_config['seller_email'],//卖家支付宝帐户
                "out_trade_no"  => $args['out_trade_no'],//商户网站订单系统中唯一订单号，必填
                "subject"   => $args['subject'],//订单名称
                "total_fee" => $args['total_fee'],//付款金额
                "body"  => isset($args['body'])?$args['body']:'',//订单描述
                "show_url"  => isset($args['show_url'])?$args['show_url']:'',//商品展示地址
                "anti_phishing_key" => '',//防钓鱼时间戳
                "exter_invoke_ip"   => get_client_ip(),//客户端的IP地址
                "_input_charset"    => trim(strtolower($alipay_config['input_charset']))
        );

        //建立请求
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get");
        echo $html_text;
    }

    public function unionnu(){
        include SITE_PATH.'/api/pay/unionpay/quickpay_service.php';
        try {
            $response = new quickpay_service($_POST, quickpay_conf::RESPONSE);
            if ($response->get('respCode') != quickpay_service::RESP_SUCCESS) {
                $err = sprintf("Error: %d => %s", $response->get('respCode'), $response->get('respMsg'));
                throw new Exception($err);
            }

            $arr_ret = $response->get_args();
            $id = $arr_ret['orderNumber']-10000000;
            $qid = $arr_ret['qid'];
            $re = D('ZyRecharge');
            $result = $re->setSuccess($id, $qid);
            if($result){
                echo 'success';
            }else{
                echo 'fail';
            }
        }catch(Exception $exp) {
            exit('fail');
            //后台通知出错
            //file_put_contents('notify.txt', var_export($exp, true));
        }
    }


    public function unionru(){
        include SITE_PATH.'/api/pay/unionpay/quickpay_service.php';
        $this->assign('isAdmin', 1);
        $this->assign('jumpUrl', U('classroom/User/recharge'));
        try {
            $response = new quickpay_service($_POST, quickpay_conf::RESPONSE);
            if ($response->get('respCode') != quickpay_service::RESP_SUCCESS) {
                $err = sprintf("Error: %d => %s", $response->get('respCode'), $response->get('respMsg'));
                throw new Exception($err);
            }
            $arr_ret = $response->get_args();
            $id = $arr_ret['orderNumber']-10000000;
            $qid = $arr_ret['qid'];
            $re = D('ZyRecharge');
            $result = $re->setSuccess($id, $qid);
            if($result){
                $this->success('充值成功！');
            }else{
                $this->error('充值失败！');
            }
        }catch(Exception $exp) {
            $this->error('操作异常！');
            //$str .= var_export($exp, true);
            //die("error happend: " . $str);
        }
    }

    protected function unionpay($args){
        include SITE_PATH.'/api/pay/unionpay/quickpay_service.php';
        $param['transType']     = quickpay_conf::CONSUME;  //交易类型，CONSUME or PRE_AUTH
        $param['commodityName'] = $args['subject'];
        $param['orderAmount']   = $args['money']*100;        //交易金额
        $param['orderNumber']   = $args['id']+10000000; //订单号，必须唯一
        $param['orderTime']     = date('YmdHis');   //交易时间, YYYYmmhhddHHMMSS
        $param['orderCurrency'] = quickpay_conf::CURRENCY_CNY;  //交易币种，CURRENCY_CNY=>人民币
        $param['customerIp']    = get_client_ip();//客户端的IP地址
        $param['frontEndUrl']   = SITE_URL.'/classroom/Pay/unionru';    //前台回调URL
        $param['backEndUrl']    = SITE_URL.'/classroom/Pay/unionnu';    //后台回调URL
        //print_r($param);exit;
        $pay_service = new quickpay_service($param, quickpay_conf::FRONT_PAY);
        $html = $pay_service->create_html();
        header("Content-Type: text/html; charset=" . quickpay_conf::$pay_params['charset']);
        echo $html; //自动post表单
    }


    protected function getAlipayConfig($config){
        $conf = unserialize(M('system_data')->where("`list`='admin_Config' AND `key`='alipay'")->getField('value'));
        if(is_array($conf)){
            $config = array_merge($config, array(
                'partner'=>$conf['alipay_partner'],
                'key'=>$conf['alipay_key'],
                'seller_email'=> $conf['seller_email'],
            ));
        }
        return $config;
    }
}