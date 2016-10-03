<?php
/**
 * 提现申请管理
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class AdminWithdrawAction extends AdministratorAction{

    //提现模型对象
    protected $model = null;
    //状态html，请勿添加引号
    protected $statusHtml = array(
        '<span style=color:red>待处理</span>',
        '<span style=color:red>处理中</span>',
        '<span style=color:green>处理成功</span>',
        '<span style=color:gray>处理失败</span>',
        '<span style=color:gray>用户取消</span>',
    );

    /**
     * 初始化，配置页面标题；创建模型对象
     * @return void
     */
    public function _initialize(){
        parent::_initialize();
        $this->pageTab[] = array('title'=>'待处理','tabHash'=>'index','url'=>U('classroom/AdminWithdraw/index'));
        $this->pageTab[] = array('title'=>'处理中','tabHash'=>'process','url'=>U('classroom/AdminWithdraw/process'));
        $this->pageTab[] = array('title'=>'处理成功','tabHash'=>'successful','url'=>U('classroom/AdminWithdraw/successful'));
        $this->pageTab[] = array('title'=>'处理失败','tabHash'=>'failure','url'=>U('classroom/AdminWithdraw/failure'));
        $this->pageTab[] = array('title'=>'用户取消','tabHash'=>'cancel','url'=>U('classroom/AdminWithdraw/cancel'));
        $this->pageTitle['index'] = '提现申请列表 - 待处理';
        $this->pageTitle['process'] = '提现申请列表 - 处理中';
        $this->pageTitle['successful'] = '提现申请列表 - 处理成功';
        $this->pageTitle['failure'] = '提现申请列表 - 处理失败';
        $this->pageTitle['cancel'] = '提现申请列表 - 用户取消';
        $this->model = D('ZyWithdraw');
    }


    /**
     * 提现申请列表 - 待处理
     * @return void
     */
    public function index(){
        $listData = $this->_list('index');
        $this->displayList($listData);
    }


    /**
     * 提现申请列表 - 处理中
     * @return void
     */
    public function process(){
        $listData = $this->_list('process');
        $this->displayList($listData);
    }


    /**
     * 提现申请列表 - 处理成功
     * @return void
     */
    public function successful(){
        $listData = $this->_list('successful');
        $this->displayList($listData);
    }


    /**
     * 提现申请列表 - 处理失败
     * @return void
     */
    public function failure(){
        $listData = $this->_list('failure');
        $this->displayList($listData);
    }


    /**
     *  提现申请列表 - 用户取消
     *  @return void
     */
    public function cancel(){
        $listData = $this->_list('cancel');
        $this->displayList($listData);
    }


    /**
     * 设置列表基本信息及取得列表数据
     * @param string $type 列表类型
     * @return array
     */
    protected function _list($type){
        //页面配置
        $this->pageKeyList = array('id','uid','wnum','ctime','status');
        if($type != 'index' && $type != 'process'){
            $this->pageKeyList[] = 'rtime';
            if($type != 'successful'){
                $this->pageKeyList[] = 'reason';
            }
        }
        $this->pageKeyList[] = 'DOACTION';
        //按钮
        $this->pageButton[] = array('title'=>'搜索记录','onclick'=>"admin.fold('search_form')");
        //$this->pageButton[] = array('title'=>'删除记录','onclick'=>"admin.delWithdraw()");
        //搜索项
        $this->searchKey = array('uid', 'startTime', 'endTime');
        $this->searchPostUrl = U(APP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME, array('tabHash'=>ACTION_NAME));

        /*查找数据*/
        //列表对应到状态
        $status = array(
            'index'      => 0,
            'process'    => 1,
            'successful' => 2,
            'failure'    => 3,
            'cancel'     => 4,
        );
        $map['status'] = $status[$type];
        //根据用户查找
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

        //列表排序
        if($status[$type] < 2){
            $order = 'ctime DESC,id DESC';
        }else{
            $order = 'rtime DESC,ctime DESC,id DESC';
        }

        //取得数据列表
        $listData = $this->model->where($map)->order($order)->findPage();

        //整理列表显示数据
        foreach($listData['data'] as $key => $val){
            $val['uid']      = getUserSpace($val['uid'], null, '_blank');
            $val['ctime']    = friendlyDate($val['ctime']);
            $val['rtime']    = $val['rtime']?friendlyDate($val['rtime']):'-';
            $val['wnum']     = '<span style="color:blue">￥'.$val['wnum'].'</span>';
            if($val['status'] < 2){
                $val['DOACTION'] = '<a href="'.U('classroom/AdminWithdraw/operate', 
                    array('id'=>$val['id'],'tabHash'=>'operate')).'">查看/操作</a>';
            }else{
                $val['DOACTION'] = '<a href="'.U('classroom/AdminWithdraw/view', 
                    array('id'=>$val['id'],'tabHash'=>'view')).'">查看详细</a>';
            }
            //$val['DOACTION'] .= '　<a href="javascript:;" onclick="admin.delWithdraw('.$val['id'].')">删除</a>';
            $val['status']   = $this->statusHtml[$val['status']];
            $val['reason']     = $val['reason']?$val['reason']:'-';
            $listData['data'][$key] = $val;
        }
        return $listData;
    }


    /**
     * 提现申请 - 查看详细
     * @return void
     */
    public function view(){
        //$this->onsubmit = 'admin.zyPageBack()';
        $this->displayConfig($this->_view('view', '查看详细'));
    }


    /**
     * 提现申请 - 查看和操作
     * @return void
     */
    public function operate(){
        $this->displayConfig($this->_view('operate', '查看/操作'));
    }


    /**
     * 处理提现申请
     * return void
     */
    public function dispose(){
        $id = intval($_POST['id']);
        $status = !empty($_POST['statusCode'])?intval($_POST['statusCode']):intval($_POST['status']);
        $result = D('ZyService')->setWithdrawStatus($id, false, $status, t($_POST['reason']));
        if($result === true){
            $url = $status > 1 ? U(APP_NAME.'/'.MODULE_NAME.'/view', array('id'=>$id, 'tabHash'=>'view')) : false;
            $this->assign('jumpUrl', $url);
            $this->assign('isAdmin',1);
            $this->success('操作成功');
        }else{
            $errs = array('???','状态码错误','找不到对应的提现记录','学币冻结扣除失败',
                '学币解冻失败','提现记录状态改变失败','completed',
            );
            //查看页面提交直接跳转对应页面
            if($result == 6){
                $actions= array('','','successful','failure','cancel');
                $this->redirect('classroom/AdminWithdraw/'.$actions[$status], array('tabHash'=>$actions[$status]));exit;
            }
				$this->assign('isAdmin',1);
            $this->error($errs[$result]);
        }
    }


    /**
     * 详细内容基本信息
     * @param string $type 视图类型
     * @param string $name 视图名称
     * return array
     */
    public function _view($type, $name){
        $_GET['id'] = intval($_GET['id']);
        $data = $this->model->find($_GET['id']);
				$this->assign('isAdmin',1);
        if(!$data) $this->error('没有找到记录');
        $this->pageTab[] = array('title'=>$name,'tabHash'=>$type,'url'=>U('classroom/AdminWithdraw/'.$type, array('id'=>$_GET['id'],'tabHash'=>$type)));
        $this->pageTitle[$type] = '提现申请 - '.$name;
        $this->savePostUrl = U('classroom/AdminWithdraw/dispose');
        $this->submitAlias = '确 定';
        $this->pageKeyList = array('id','uid','ctime','wnum','bcard_text','status','reason');
        if($type == 'view') {
            $this->pageKeyList[] = 'rtime';
            $this->pageKeyList[] = 'statusCode';
        }
        if($type == 'view'){
            $this->opt['status'] = array('待处理','处理中','处理成功','处理失败','用户取消');
        }else{
            $this->opt['status'] = $this->statusHtml;
        }
        $bcard = D('ZyBcard')->find($data['bcard_id']);
        $text  = "账户类型：{$bcard['accounttype']}<br/>";
        $text .= "　账户号：{$bcard['account']}<br/>";
        $text .= "　账户名：{$bcard['accountmaster']}<br/>";
        $text .= "　　地区：{$bcard['location']}<br/>";
        $text .= "　开户行：{$bcard['bankofdeposit']}<br/>";
        $text .= "　　电话：{$bcard['tel_num']}";
        $data['uid'] = getUserName($data['uid']).'(id:'.$data['uid'].')';
        $data['ctime'] = date('Y-m-d H:i:s', $data['ctime']);
        if($type == 'view'){
            $data['rtime']  = !$data['rtime']?'-':date('Y-m-d H:i:s', $data['rtime']);
            $data['statusCode'] = $data['status'];
            $data['status'] = $this->statusHtml[$data['status']];
            $data['reason'] = $data['reason']?$data['reason']:'-';
        }
        $data['wnum'] = '<span style=color:blue>￥'.$data['wnum'].'</span>';
        $data['bcard_text'] = $text;
        return $data;
    }

    /**
     * 删除提现记录
     * @return void
     */
    public function del(){
        //TODO 不能删除提现记录，如需要删除，还需要添加页面按钮及列表删除链接
				$this->assign('isAdmin',1);
        $this->error('不能删除提现记录');exit;
        if(is_array($_POST['id'])){
            $_POST['id'] = implode(',', $_POST['id']);
        }
        $id = "'".str_replace(array("'", ','), array('', "','"), $_POST['id'])."'";
        if($this->model->where("id IN($id)")->delete()){
            $this->ajaxReturn('删除成功');
        }else{
            $this->ajaxReturn('删除失败');
        }
    }

}