<?php
/**
 * 考试系统(试卷)后台配置
 * 1.试卷管理 - 目前支持1级分类
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
tsload(APPS_PATH.'/exam/Lib/Action/CommonAction.class.php');

class AdminPaperAction extends AdministratorAction
{
	/**
	 * 初始化，配置内容标题
	 * @return void
	 */
	public function _initialize()
	{
		parent::_initialize();
	}

	//试卷列表
	public function index(){
		$this->_initExamListAdminMenu();
		$this->_initExamListAdminTitle();
		$this->pageKeyList = array('paper_id','paper_name','paper_describe','paper_category_name','paper_type','paper_point','paper_question_count','paper_status','uname','paper_insert_date','DOACTION');
		$this->pageButton[] =  array('title'=>'搜索试卷','onclick'=>"admin.fold('search_form')");
		$this->searchKey = array('paper_id','paper_name','paper_category_name');
		$paper_cate = M('ex_paper_category')->field('paper_category_name')->select();
		$this->opt['paper_category_name'] = array_merge(array('0'=>L('PUBLIC_SYSTEMD_NOACCEPT')),getSubByKey($paper_cate,'paper_category_name'));
		$this->searchPostUrl = U('exam/AdminPaper/index');
		$listData = $this->_getData(20,0);
		$questions = $this->getQuestionList(0);
		$this->assign('questions',$questions);
		$this->displayList($listData);
	}
	//编辑、添加试卷
	public function addPaper(){
		$this->_initExamListAdminMenu();
		$this->_initExamListAdminTitle();
		$paper_category = M('ex_paper_category')->where($map)->order('paper_category_id')->select();
		if($_GET['paper_id']){
			$paper = M('ex_paper p')->join(C('DB_PREFIX').'ex_paper_category c ON p.paper_category = c.paper_category_id')->where('paper_id='.$_GET['paper_id'])->find();
			$this->assign('paper',$paper);
		}
		$question_type=D("ExQuestion")->getQuestion_type();
		$this->assign("question_type",$question_type);
		$this->assign('paper_category',$paper_category);
		$this->display();
	}

	//添加试卷操作
	public function doAddPaper(){
		$post = $_POST;
		$data['paper_name']= $post['paper_name'];
		$data['paper_describe']= $post['paper_describe'];
		$data['paper_category']= $post['paper_category'];
		$data['paper_type']= $post['paper_type'];
		$question_type= $post['question_type'];
		if($post['paper_id']){
			$data['paper_update_date'] = time();
			$result = M('ex_paper')->where('paper_id = '.$post['paper_id'])->data($data)->save();
		}else{
			$data['paper_insert_date'] = time();
			$data['paper_admin'] = $this->mid;
			$result = M('ex_paper')->data($data)->add();
			$question_count=$question_type ? D("ExPaper")->doPaperQuestion($question_type,$result) : 0 ;
		}
		if($result){
			unset($data);
			if($post['paper_id']){
				exit(json_encode(array('status'=>'1','info'=>'编辑成功')));
			} else {
				$question_count>0 ? exit(json_encode(array('status'=>'1','info'=>'添加成功,一共插入'.$question_count."道题！"))) : exit(json_encode(array('status'=>'1','info'=>'添加成功')));;
			}
		} else {
			exit(json_encode(array('status'=>'0','info'=>'系统繁忙，请稍后再试')));
		}
	}
	//添加试题
		public function addQuestion(){
		$this->_initExamListAdminMenu();
		$this->_initExamListAdminTitle();
		$this->pageTitle['addQuestion'] = '试题管理';
		$paper_id=intval($_GET["paper_id"]);
		$paper_info=M("ExPaper")->getPaperInfo($paper_id);
		$question_list=M("ExQuestion")->QuestionList();
		$paper_question=M("ExQuestion")->getPaperQuestion($paper_id);
		$this->assign('paper_info',$paper_info);
		$this->assign('question_list',$question_list);
		$this->assign('paper_question',$paper_question);
		$this->display();
	}
	//添加试题操作
    public function doAddQuestion(){
    	$id=intval($_POST["id"]);
    	$paper_question=explode(",",$_POST["paper_question"]);
    	$item=M("ex_paper_content")->where("paper_content_paperid=".$id)->field("paper_content_item")->order("paper_content_item desc")->find();
    	$num=$item ? $item["paper_content_item"] : 0 ;
    	$score=0;
    	$count=0;
    	$data['paper_content_admin']=$this->uid;
        $data['paper_content_update_date']=time();
        $data['paper_content_insert_date']=time();
        foreach ($paper_question as $vo) {
        	$num++;
        	$question=explode("-",$vo);
        	$result=M('ex_paper_content')->where("paper_content_paperid=".$id." and paper_content_questionid=".$question[0])->select();
        	if(!$result){
        		$data["paper_content_paperid"]=$id;
	            $data['paper_content_questionid'] = $question[0];
	            $data['paper_content_point']=$question[1];
	            $data['paper_content_item']=$num;
	            $res=M('ex_paper_content')->data($data)->add();
	            $score= $res ? $score+$question[1] : $score;
	            $count= $res ? $count+1 : $count;
        	}
        }
        if($score>0 && $count>0){
        	$data=array(
        	'paper_point'=>$score,
        	'paper_question_count' =>$count
        	);
        	M("ex_paper")->data($data)->where("paper_id=".$id)->save();
        }
        exit(json_encode(array('status'=>'1','info'=>'添加成功')));
    }
    /**
     * 删除试题
     * @return void
     */
    public function delquestion()
    {   
    	$data=array(
    		'paper_point' => $_POST["paper_point"],
    		'paper_question_count' => $_POST["paper_question_count"],
    		);
        $status=M("ex_paper_content")->delete($_POST["id"]);
        if($status){
        	M("ex_paper")->where("paper_id=".intval($_POST["paper_id"]))->data($data)->save();
            exit(json_encode(array('status'=>'1','info'=>'操作成功')));
        } else {
            exit(json_encode(array('status'=>'1','info'=>'操作失败')));
        }
    }
	/**
	 * 编辑试卷
	 */
	public function editPaper(){
		$this->_initexamListAdminMenu();
		$this->pageTab[] = array('title'=>'编辑试卷','tabHash'=>'editPaper','url'=>U('exam/AdminPaper/editPaper'));
		$this->pageTitle['editPaper'] = '编辑试卷';
	}
	public function update_paper_status(){
		$id=$_POST["id"];
		$status=$_POST["status"];
		$data["paper_status"]=$status;
		if($status==1){
			$result=M("ex_paper_content")->where("paper_content_paperid=".$id)->findALL();
			if($result){
				$res=M('ex_paper')->where("paper_id=".$id)->data($data)->save();
				if($res){
					exit(json_encode(array('status'=>1,'info'=>'操作成功')));
				} else {
					exit(json_encode(array('status'=>0,'info'=>'操作失败')));
				}
			}else{
				exit(json_encode(array('status'=>0,'info'=>'该试卷下没有试题,不能启用')));
			}
		}else{
			$res=M('ex_paper')->where("paper_id=".$id)->data($data)->save();
			if($res){
				exit(json_encode(array('status'=>1,'info'=>'操作成功')));
			} else {
				exit(json_encode(array('status'=>0,'info'=>'操作失败')));
			}
		}
	}
	//删除试卷(隐藏)
	public function delPaper(){
		if(!$_POST['id']){
			exit(json_encode(array('status'=>0,'info'=>'请选择要删除的对象!')));
		} 
		$map['paper_id'] = intval($_POST['id']);
		$data['paper_is_del'] = $_POST['is_del'] ? 0 : 1; //传入参数并设置相反的状态
		if(M('ex_paper')->where($map)->data($data)->save()){
			exit(json_encode(array('status'=>1,'info'=>'操作成功')));
		} else {
			exit(json_encode(array('status'=>0,'info'=>'操作失败')));
		}
	}

	//试卷回收站(被隐藏的试卷)
	public function recycle(){
		$this->_initexamListAdminMenu();
		$this->_initexamListAdminTitle();
		$this->pageKeyList = array('paper_id','uname','paper_status','paper_name','paper_describe','paper_category_name','paper_type','paper_point','paper_update_date','paper_insert_date','DOACTION');
		$this->pageButton[] = array('title'=>'清空回收站','onclick'=>"admin.mzPaperclear()");
		$listData = $this->_getData(20,1);
		$this->displayList($listData);
	}
	/**
     * 清空试卷回收站
     * @return void
     */
    public function delTable(){
        $result=M("ex_paper")->where(array('paper_is_del'=>1))->delete();
        if($result){
            exit(json_encode(array('status'=>'1','info'=>'已删除')));
        } else {
            exit(json_encode(array('status'=>'0','info'=> '操作繁忙,请稍后再试')));
        }
    }
	//获取试卷数据
	private function _getData($limit = 20, $is_del){
		if(isset($_POST)){
			$_POST['paper_id'] && $map['paper_id'] = intval($_POST['paper_id']);
			$_POST['paper_name'] && $map['paper_name'] = array('like', '%'.t($_POST['paper_name']).'%');
			$_POST['paper_category_name'] && $map['paper_category_id'] = intval($_POST['paper_category_name']);
		}
		$map['paper_is_del'] = $is_del; 
		$list = M('ex_paper p')->where($map)->join(C('DB_PREFIX').'ex_paper_category c ON p.paper_category = c.paper_category_id')->order('paper_insert_date desc')->findPage($limit);
		foreach ($list['data'] as &$value){
			$value['paper_describe'] = msubstr($value['paper_describe'],0,20);
			$value['paper_type'] = $value['paper_type'] == '0' ? '<span style="color:green">手动出卷</span>' : '<span style="color:#2E4C8C">自动出卷</span>';
			$value['paper_insert_date'] = date('Y-m-d H:i:s',$value['paper_insert_date']);
			$value['uname']=getUserName($value['paper_admin']);
			$value['DOACTION']=$value['paper_status'] == 1? '<a href="javascript:admin.updatePaperStatus('.$value['paper_id'].','.$value['paper_status'].');">禁用</a>' :'<a href="javascript:admin.updatePaperStatus('.$value['paper_id'].','.$value['paper_status'].');">启用</a>';
			$value['DOACTION'] .= $value['paper_is_del'] ? '<a onclick="admin.delObject('.$value['paper_id'].',\'Paper\','.$value['paper_is_del'].');" href="javascript:void(0)">恢复</a>' : '  | <a href="'.U('exam/AdminPaper/addQuestion',array('paper_id'=>$value['paper_id'],'tabHash'=>'addQuestion')).'">试题管理</a> | <a href="'.U('exam/AdminPaper/addPaper',array('paper_id'=>$value['paper_id'],'tabHash'=>'editPaper')).'">编辑</a> | <a onclick="admin.delObject('.$value['paper_id'].',\'Paper\','.$value['paper_is_del'].');" href="javascript:void(0)">删除(隐藏)</a> ';
			$value['paper_status'] = $value['paper_status'] == 1 ? '<span style="color:green">已启用</span>' : '<span style="color:red">禁用</span>';
		}
		return $list;
	}
	//获取试题数据
	public function getQuestionList($is_del){
		$map['question_is_del'] = $is_del;
		$questions = M('ex_question q')->join(C('DB_PREFIX').'ex_question_category c ON q.question_category = c.question_category_id')->join(C('DB_PREFIX').'ex_question_type t ON q.question_type = t.question_type_id')->where($map)->order('question_status = 1')->select();
		return $questions;
	}

	/**
	 * 试卷后台管理菜单
	 * @return void
	 */
	private function _initExamListAdminMenu(){
		$this->pageTab[] = array('title'=>'试卷列表','tabHash'=>'index','url'=>U('exam/AdminPaper/index'));
		$this->pageTab[] = array('title'=>'添加试卷','tabHash'=>'addPaper','url'=>U('exam/AdminPaper/addPaper'));
		$this->pageTab[] = array('title'=>'试卷回收站','tabHash'=>'recycle','url'=>U('exam/AdminPaper/recycle'));
	}

	/**
	 * 试卷后台的标题
	 */
	private function _initexamListAdminTitle(){
		$this->pageTitle['index'] = '试卷列表';
		$this->pageTitle['addPaper'] = '添加试卷';
		$this->pageTitle['recycle'] = '试卷回收站';
	}

}