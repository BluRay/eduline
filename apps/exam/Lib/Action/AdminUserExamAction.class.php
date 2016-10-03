<?php
/**
 * 后台，用户管理控制器
 * @author liuxiaoqing <liuxiaoqing@zhishisoft.com>
 * @version TS3.0
 */
// 加载后台控制器
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class AdminUserExamAction extends AdministratorAction {

	public $pageTitle = array();
	
	/**
	 * 初始化，初始化页面表头信息，用于双语
	 */
	public function _initialize() {
		$this->pageTitle['index'] = L('PUBLIC_USER_MANAGEMENT');
		$this->pageTitle['ExamRrecord'] = "用户考试记录列表";
		$this->pageTitle['ExamDetail'] = "试卷详情";
		$this->pageTitle['postRecycle'] = "回收站";
		parent::_initialize();
	}
	/**
	 * 初始化用户列表管理菜单
	 */
	private function _initUserExamListAdminMenu() {
		// tab选项
		$this->pageTab[] = array('title'=>'用户列表','tabHash'=>'index','url'=>U('exam/AdminUserExam/index'));
        $this->pageTab[] = array('title'=>'回收站','tabHash'=>'postRecycle','url'=>U('exam/AdminUserExam/postRecycle'));
		$this->searchKey = array('uid','uname','email','sex','user_group','user_category',array('ctime','ctime1'));
		// 针对搜索的特殊选项
		$this->opt['sex'] = array('0'=>L('PUBLIC_SYSTEMD_NOACCEPT'),'1'=>L('PUBLIC_MALE'),'2'=>L('PUBLIC_FEMALE'));
		$this->opt['identity'] = array('0'=>L('PUBLIC_SYSTEMD_NOACCEPT'),'1'=>L('PUBLIC_PERSONAL'),'2'=>L('PUBLIC_ORGANIZATION'));
		$this->opt['user_group'] = model('UserGroup')->getHashUsergroup();
		$this->opt['user_group'][0] = L('PUBLIC_SYSTEMD_NOACCEPT');
		$map['pid'] = array('NEQ', 0);
		$categoryList = model('UserCategory')->getAllHash($map);
		$categoryList[0] = L('PUBLIC_SYSTEMD_NOACCEPT');
		ksort($categoryList);
		$this->opt['user_category'] = $categoryList;
		$this->pageKeyList = array('uid','uname','user_group','location','ctime','reg_ip','DOACTION');
		
	}
	/**
	 * 用户管理 - 用户列表
	 */
	public function index()
	{
		$_REQUEST['tabHash'] = 'index';
		// 初始化用户列表管理菜单
		$this->_initUserExamListAdminMenu();
		// 数据的格式化与listKey保持一致
		$listData = $this->_getUserList('20', $map, 'index');
		// 列表批量操作按钮
		$this->pageButton[] = array('title'=>L('PUBLIC_SEARCH_USER'),'onclick'=>"admin.fold('search_form')");
		$this->displayList($listData);
	}
	//考试管理回收站(被隐藏的考试信息)
    public function postRecycle(){
		$_REQUEST['tabHash'] = 'postRecycle';
		$this->pageTab[] = array('title'=>'用户列表','tabHash'=>'index','url'=>U('exam/AdminUserExam/index'));
        $this->pageTab[] = array('title'=>'回收站','tabHash'=>'postRecycle','url'=>U('exam/AdminUserExam/postRecycle'));
		$this->pageKeyList = array('user_exam_id','exam_name','paper_name','paper_point','exam_passing_grade','user_exam_number','user_total_date','user_exam_score','user_right_count','user_error_count','user_exam_time','DOACTION');
		$model=M();
		$list = $model->table(C('DB_PREFIX').'ex_paper p,'.C('DB_PREFIX').'ex_exam e,'.C('DB_PREFIX').'ex_user_exam ue' )->where('p.paper_id = ue.user_paper and e.exam_id=ue.user_exam and user_exam_is_del=1')->field('paper_name,paper_point,exam_name,exam_passing_grade,user_exam_id,user_id,user_exam,user_paper,user_exam_number,user_exam_time,user_exam_score,user_total_date,user_right_count,user_error_count,user_exam_is_del')->order('ue.user_exam_id')->findPage(20);
		foreach ($list['data'] as $key => $value){
            $list['data'][$key]['exam_name'] = msubstr($value['exam_name'],0,30);
            $list['data'][$key]['paper_name'] = msubstr($value['paper_name'],0,30);
            $list['data'][$key]['user_exam_time'] = date("Y-m-d H:i:s",$value['user_exam_time']);
            $list['data'][$key]['user_exam_time'] = date("Y-m-d H:i:s",$value['user_exam_time']);
            $list['data'][$key]['user_exam_number'] = "第<font style='color:#34876f;'>".$value['user_exam_number']."</font>次";
            $list['data'][$key]['user_total_date'] =$value['user_total_date']."分钟";
            $list['data'][$key]['DOACTION'] = '<a href="javascript:admin.mzUserExam('.$value['user_exam_id'].','.$value['user_exam_is_del'].',\'delUserExam\',\'恢复\',\'考试信息\');">恢复</a>' ;
        }
        $this->displayList($list);
    }
	/**
	 * 用户考试记录列表
	 */
	function ExamRrecord(){
		$_REQUEST['tabHash'] = 'index';
		$this->pageTab[] = array('title'=>'用户列表','tabHash'=>'index','url'=>U('exam/AdminUserExam/index'));
        $this->pageTab[] = array('title'=>'回收站','tabHash'=>'postRecycle','url'=>U('exam/AdminUserExam/postRecycle'));
		$this->pageKeyList = array('user_exam_id','exam_name','paper_name','paper_point','exam_passing_grade','user_exam_number','user_total_date','user_exam_score','user_right_count','user_error_count','user_exam_time','DOACTION');
		$model=M();
		$list = $model->table(C('DB_PREFIX').'ex_paper p,'.C('DB_PREFIX').'ex_exam e,'.C('DB_PREFIX').'ex_user_exam ue' )->where('p.paper_id = ue.user_paper and e.exam_id=ue.user_exam and user_exam_is_del=0 and user_id='.intval($_GET["uid"]))->field('paper_name,paper_point,exam_name,exam_passing_grade,user_exam_id,user_id,user_exam,user_paper,user_exam_number,user_exam_time,user_exam_score,user_total_date,user_right_count,user_error_count,user_exam_is_del')->order('ue.user_exam_id')->findPage(20);
		foreach ($list['data'] as $key => $value){
            $list['data'][$key]['exam_name'] = msubstr($value['exam_name'],0,30);
            $list['data'][$key]['paper_name'] = msubstr($value['paper_name'],0,30);
            $list['data'][$key]['user_exam_time'] = date("Y-m-d H:i:s",$value['user_exam_time']);
            $list['data'][$key]['user_exam_time'] = date("Y-m-d H:i:s",$value['user_exam_time']);
            $list['data'][$key]['user_exam_number'] = "第<font style='color:#34876f;'>".$value['user_exam_number']."</font>次";
            $list['data'][$key]['DOACTION'] = '<a href="'.U('exam/AdminUserExam/ExamDetail',array('user_id'=>$value['user_id'],'exam_id'=>$value['user_exam'],'paper_id'=>$value['user_paper'],'user_exam_number'=>$value['user_exam_number'],'tabHash'=>'index')).'">查看试卷</a> | <a href="javascript:admin.mzUserExam('.$value['user_exam_id'].','.$value['user_exam_is_del'].',\'delUserExam\',\'删除(隐藏)\',\'考试信息\');">删除(隐藏)</a> ';
        }
        $this->displayList($list);
	}
	/**
	 * 用户考试详情
	 */
	function ExamDetail(){
		$model=M();
		$data['user_exam_id']=$_GET["exam_id"];
		$data['user_paper_id']=$_GET["paper_id"];
		$data['user_exam_time']=$_GET["user_exam_number"];
		$data['user_id']=$_GET["user_id"];
		$list =$model->table(C('DB_PREFIX').'ex_paper_content pc,'.C('DB_PREFIX').'ex_question q' )->where('pc.paper_content_questionid=q.question_id and pc.paper_content_paperid='.$_GET["paper_id"])->order('pc.paper_content_item')->findAll();
		foreach ($list as $key => $v) {
			$data['user_question_id']=$v["question_id"];
			$option_list = $model->table(C('DB_PREFIX').'ex_option' )->where('option_question='.$v["question_id"])->order('option_item_id')->findAll();
			$answer=$model->table(C('DB_PREFIX').'ex_user_answer')->where($data)->find();
			$list[$key]["user_answer"]=$answer["user_question_answer"];
			$list[$key]["option_list"]=$option_list;
		}
		$quetion_type=$model->table(C('DB_PREFIX')."ex_question_type")->findAll();
		$this->assign("list",$list);
		$this->assign("quetion_type",$quetion_type);
		$this->display();
	}
	/**
     * 软删除考试信息
     * @return void
     */
    public function delUserExam(){
    	$data["user_exam_is_del"]=$_POST['is_del'];
        $result = M('ex_user_exam')->where('user_exam_id = '.$_POST['id'])->data($data)->save();
        if($result){
            exit(json_encode(array('status'=>'1','info'=>'已删除')));
        } else {
            exit(json_encode(array('status'=>'0','info'=> '操作繁忙,请稍后再试')));
        }
    }
	/**
	 * 解析用户列表数据
	 */
	private function _getUserList($limit = 20, $map = array(), $type = 'index') {
		// 设置列表主键 
		$this->_listpk = 'uid';
		// 取用户列表
		$listData = model('User')->getUserExamList($limit, $map);
		// 数据格式化
		foreach($listData['data'] as $k => $v) {
			// 获取用户身份信息
			$userTag = model('Tag')->setAppName('User')->setAppTable('user')->getAppTags($v['uid']);
			$userTagString = '';
			$userTagArray = array();
			if(!empty($userTag)) {
				$userTagString .= '<p>';
				foreach($userTag as $value) {
					$userTagArray[] = '<span style="color:blue;cursor:auto;">'.$value.'</span>';
				}
				$userTagString .= implode('&nbsp;', $userTagArray).'</p>';
			}
			//获取用户组信息
			$userGroupInfo = model('UserGroupLink')->getUserGroupData($v['uid']);
			foreach($userGroupInfo[$v['uid']] as $val){
				$userGroupIcon[$v['uid']] .= '<img style="width:auto;height:auto;display:inline;cursor:pointer;vertical-align:-2px;" src="'.$val['user_group_icon_url'].'" title="'.$val['user_group_name'].'" />&nbsp';
			}
			$listData['data'][$k]['uname'] = '<a href="'.U('admin/User/editUser',array('tabHash'=>'editUser','uid'=>$v['uid'])).'">'.$v['uname'].'</a>'.$userGroupIcon[$v['uid']].' <br/>'.$v['email'].' '.$userTagString;
			$listData['data'][$k]['ctime'] = date('Y-m-d H:i:s',$v['ctime']);
			$listData['data'][$k]['identity'] = ($v['identity'] == 1) ? L('PUBLIC_PERSONAL') : L('PUBLIC_ORGANIZATION');
			switch(strtolower($type)) {
				case 'index':
				case 'dellist':
				// 列表数据
				$listData['data'][$k]['is_active'] = ($v['is_active'] == 1) ? '<span style="color:blue;cursor:auto;">'.L('SSC_ALREADY_ACTIVATED').'</span>' : '<a href="javascript:void(0)" onclick="admin.activeUser(\''.$v['uid'].'\',1)" style="color:red">'.L('PUBLIC_NOT_ACTIVATED').'</a>';
				$listData['data'][$k]['is_audit'] = ($v['is_audit'] == 1) ? '<span style="color:blue;cursor:auto;">'.L('PUBLIC_AUDIT_USER_SUCCESS').'</span>' : '<a href="javascript:void(0)" onclick="admin.auditUser(\''.$v['uid'].'\',1)" style="color:red">'.L('PUBLIC_AUDIT_USER_ERROR').'</a>';
				$listData['data'][$k]['is_init'] = ($v['is_init'] == 1) ? '<span style="cursor:auto;">'.L('PUBLIC_SYSTEMD_TRUE').'</span>' : '<span style="cursor:auto;">'.L('PUBLIC_SYSTEMD_FALSE').'</span>';
				// 用户组数据
				if(!empty($v['user_group'])) {
					$group = array();
					foreach($v['user_group'] as $gid) {
						$group[] = $this->opt['user_group'][$gid];		
					}
					$listData['data'][$k]['user_group'] = implode('<br/>', $group);
				} else {
					$listData['data'][$k]['user_group'] = '';
				}
				$this->opt['user_group'][$v['user_group_id']];
				// 操作数据
				$listData['data'][$k]['DOACTION'] = '<a href="'.U('exam/AdminUserExam/ExamRrecord',array('tabHash'=>'ExamRrecord','uid'=>$v['uid'])).'">查看考试记录</a>';
				break;
			}
		}
		return $listData;
	}
}