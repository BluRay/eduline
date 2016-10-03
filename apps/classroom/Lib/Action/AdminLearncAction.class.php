<?php
/**
 * 学币列表信息管理控制器
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class AdminLearncAction extends AdministratorAction {
    /**
     * 初始化，访问控制及配置
     * @return void
     */
    public function _initialize() {
        parent::_initialize();
        $this->pageTab[] = array('title'=>'云课堂用户列表','tabHash'=>'index','url'=>U('classroom/AdminLearnc/index'));
        $this->pageTab[] = array('title'=>'所有流水记录','tabHash'=>'flow','url'=>U('classroom/AdminLearnc/flow'));
		$this->pageTab[] = array('title'=>'用户充值记录', 'tabHash'=>'recharge', 'url'=>U('classroom/AdminLearnc/recharge'));
    }
    
    /**
     * 学币列表信息管理
     * @return void
     */
    public function index(){
        // 页面具有的字段，可以移动到配置文件中！
        $this->pageKeyList = array('uid','balance','frozen','vip_type','vip_expire','DOACTION');
        $this->pageTitle['index'] = '云课堂用户列表';
        //按钮
        $this->pageButton[] = array('title'=>'搜索','onclick'=>"admin.fold('search_form')");
        //搜索项
        $this->searchKey = array('uid', 'vip_type');
        $this->opt['vip_type'] = array('全部','普通会员','包月VIP','包年VIP','全部VIP');
        $this->searchPostUrl = U('classroom/AdminLearnc/index', array('tabHash'=>'index'));
        //根据用户查找
        if(!empty($_POST['uid'])){
            $_POST['uid'] = t($_POST['uid']);
            $map['uid'] = array('in', $_POST['uid']);
        }
        if(!empty($_POST['vip_type'])){
            switch (intval($_POST['vip_type'])){
                case 1: $map['vip_type'] = 0; break;
                case 2: $map['vip_type'] = 1; break;
                case 3: $map['vip_type'] = 2; break;
                case 4: $map['vip_type'] = array('gt', 0); break;
            }
        }
        $list = D('ZyLearnc')->where($map)->order('id DESC')->findPage();
        foreach($list['data'] as $key=>$value){
            $list['data'][$key]['uid']      = getUserSpace($value['uid'], null, '_blank');
            $list['data'][$key]['balance']  = '<span style=color:red>￥'.$value['balance'].'</span>';
            $list['data'][$key]['frozen']   = '<span style=color:green>￥'.$value['frozen'].'</span>';
            switch ($value['vip_type']){
                case 0: $list['data'][$key]['vip_type']    = '-';break;
                case 1: $list['data'][$key]['vip_type']    = '<span style=color:blue>月费VIP</span>';break;
                case 2: $list['data'][$key]['vip_type']    = '<span style=color:blue>年费VIP</span>';break;
                
            }
            if($value['vip_type'] == 0){
                $list['data'][$key]['vip_expire']    = "-";
            }else{
                $list['data'][$key]['vip_expire']    = date('Y-m-d H:i:s',$value['vip_expire']);
            }    
            $list['data'][$key]['DOACTION'] = '<a href="'.U(APP_NAME.'/'.MODULE_NAME.'/edit', array('id'=>$value['id'], 'tabHash'=>'edit')).'">编辑</a> | <a href="'.U('classroom/AdminLearnc/uflow',array('uid'=>$value['uid'],'tabHash'=>'uflow')).'">TA的账户流水</a>';
        }

        $this->displayList($list);
    }


    /**
     * 编辑操作
     */
    public function edit(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST['vip_type'] = intval($_POST['vip_type']);
            if($_POST['vip_type'] == 0){
                $_POST['vip_expire'] = 0;
            }else{
                $_POST['vip_expire'] = strtotime($_POST['vip_expire'])+0;
            }
            $_POST['balance'] = floatval($_POST['balance']);
            $_POST['frozen'] = floatval($_POST['frozen']);
            $set = array(
                'id' => intval($_POST['id']),
                'vip_type' => $_POST['vip_type'],
                'vip_expire' => $_POST['vip_expire'],
                'balance'    => $_POST['balance'],
                'frozen'     => $_POST['frozen'],
            );
            if(false !== D('ZyLearnc')->save($set)){
                $this->success('保存成功！');
            }else{
                $this->error('保存失败！');
            }
            exit;
        }
        $_GET['id'] = intval($_GET['id']);
        $this->pageTab[] = array('title'=>'查看/修改','tabHash'=>'edit','url'=>U(APP_NAME.'/'.MODULE_NAME.'/edit', array('id'=>$_GET['id'],'tabHash'=>'edit')));
        $this->pageTitle['edit'] = '用户信息查看/修改';
        $this->savePostUrl = U(APP_NAME.'/'.MODULE_NAME.'/edit');
        $this->submitAlias = '确 定';
        $this->pageKeyList = array('id','uid','balance','frozen','vip_type','vip_expire');
        $this->opt['vip_type'] = array('普通会员', '月费VIP', '年费VIP');
        $data = D('ZyLearnc')->find($_GET['id']);
        $data['uid'] = getUserSpace($data['uid'], null, '_blank');
        $data['vip_expire'] = $data['vip_expire']>0?date('Y-m-d H:i:s', $data['vip_expire']):'';
        $this->displayConfig($data);
    }

    /**
     * 流水列表
     */
    public function flow(){
        $this->_flow(false);
    }

    
    /**
     * 用户流水列表
     */
    public function uflow(){
        $this->_flow(intval($_GET['uid']));
    }


    public function _flow($uid){
        
        $this->pageKeyList = array('id','uid','type','num','balance','rel_id','note','ctime');
        $this->pageButton[] = array('title'=>'搜索记录','onclick'=>"admin.fold('search_form')");
        $this->pageTitle[ACTION_NAME] = $uid?'账户流水-'.getUserName($uid):'所有流水记录';
        if($uid){
            $this->pageTab[]    = array('title'=>'账户流水-'.getUserName($_GET['uid']),'tabHash'=>ACTION_NAME,'url'=>U(APP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME,array('uid'=>$uid)));
            $this->pageButton[] = array('title'=>'&lt;&lt;&nbsp;返回来源页','onclick'=>"admin.zyPageBack()");
            $this->searchKey    = array('type','note','startTime','endTime');
        }else{
            $this->searchKey    = array('uid','type','note','startTime','endTime');
        }

        $this->opt['type']  = array('全部','消费','充值','冻结','解冻','冻结扣除','分成收入');
        $this->searchPostUrl= U(APP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME, array('uid'=>$uid, 'tabHash'=>ACTION_NAME));

        $map = array();
        if($uid){
            $map['uid'] = $uid;
        }elseif(!empty($_POST['uid'])){
            $_POST['uid'] = t($_POST['uid']);
            $map['uid'] = array('in', $_POST['uid']);
        }

        if(!empty($_POST['type']) && $_POST['type']>0){
            $map['type'] = $_POST['type']-1;
        }
        if(!empty($_POST['note'])){
            $map['note'] = array('like', '%'.t($_POST['note']).'%');
        }
        //时间范围内进行查找
        if(!empty($_POST['startTime'])){
            $map['ctime'][] = array('gt', strtotime($_POST['startTime']));
        }
        if(!empty($_POST['endTime'])){
            $map['ctime'][] = array('lt', strtotime($_POST['endTime']));
        }

        $list = D('ZyLearnc')->flowModel()->where($map)->order('ctime DESC,id DESC')->findPage();
        $relTypes = D('ZyLearnc')->getRelTypes();
        foreach($list['data'] as $key=>$value){
            $list['data'][$key]['uid']      = getUserSpace($value['uid'], null, '_blank');
            switch ($value['type']){
                case 0:$list['data'][$key]['type'] = "消费";break;
                case 1:$list['data'][$key]['type'] = "充值";break;
                case 2:$list['data'][$key]['type'] = "冻结";break;
                case 3:$list['data'][$key]['type'] = "解冻";break;
                case 4:$list['data'][$key]['type'] = "冻结扣除";break;
                case 5:$list['data'][$key]['type'] = "分成收入";break;
            }
            if($value['ctime'] == 0){
                $list['data'][$key]['ctime']    =  '-';
            }else{
                $list['data'][$key]['ctime']    = date('Y-m-d H:i:s', $value['ctime']);
            }
            
            $list['data'][$key]['num']        = '<span style=color:red>￥'.$value['num'].'</span>';        
            $list['data'][$key]['balance']    = '<span style=color:green>￥'.$value['balance'].'</span>';
            $list['data'][$key]['rel_id']     = $value['rel_id']>0?$value['rel_id']:'-';
            if(isset($relTypes[$value['rel_type']])&&$value['rel_id']>0){
                $list['data'][$key]['rel_id'] = $relTypes[$value['rel_type']].'-ID:'.$value['rel_id'];
            }
        }

        $this->displayList($list);
    }

	public function recharge(){
		$this->pageTitle['recharge'] = '用户充值记录';
		$this->pageKeyList = array('id','uid','money','type','vip_length','note','ctime','status','stime','pay_order','pay_type');
        $this->pageButton[] = array('title'=>'搜索记录','onclick'=>"admin.fold('search_form')");
		$this->searchKey    = array('uid','startTime','endTime');
		$this->searchPostUrl= U(APP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME, array('uid'=>$uid, 'tabHash'=>ACTION_NAME));
		$recharge = D('ZyRecharge');
		$map['status'] = array('gt', 0);
		if(!empty($_POST['uid'])){
            $_POST['uid'] = t($_POST['uid']);
            $map['uid'] = array('in', $_POST['uid']);
        }
		//时间范围内进行查找
        if(!empty($_POST['startTime'])){
            $map['ctime'][] = array('gt', strtotime($_POST['startTime']));
        }
        if(!empty($_POST['endTime'])){
            $map['ctime'][] = array('lt', strtotime($_POST['endTime']));
        }
		$data = $recharge->where($map)->order('stime DESC,id DESC')->findPage();
		$types = array('学币充值', '会员充值');
		$status= array('未支付', '已成功', '失败');
		$payType = array('alipay'=>'支付宝', 'unionpay'=>'银联');
		foreach($data['data'] as &$val){
			$val['uid']   = getUserSpace($val['uid'], null, '_blank');
			$val['ctime'] = friendlyDate($val['ctime']);
			$val['type']  = isset($types[$val['type']])?$types[$val['type']]:'-';
			$val['money'] = '￥'.$val['money'];
			$val['status']= $status[$val['status']];
			$val['stime'] = friendlyDate($val['stime']);
			$val['stime'] = $val['stime']?$val['stime']:'-';
			$val['pay_type']  = isset($payType[$val['pay_type']])?$payType[$val['pay_type']]:'-';
		}
		$this->displayList($data);
	}
}