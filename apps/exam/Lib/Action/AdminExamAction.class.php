<?php
/**
 * 考试系统后台配置
 * 1.考试管理
 * @author 陈韵
 * @version CY1.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');

class AdminExamAction extends AdministratorAction
{
    /**
     * 初始化，配置内容标题
     * @return void
     */
    public function _initialize()
    {
        parent::_initialize();
    }
    //考试管理回收站(被隐藏的考试信息)
    public function postRecycle(){
        $this->_initExamListAdminMenu();
        $this->_initExamListAdminTitle();
        $this->pageKeyList = array('exam_id','exam_name','exam_category','exam_begin_time','exam_end_time','exam_passing_grade','exam_publish_result_flg','exam_times_mode','exam_admin','DOACTION');
        $this->pageButton[] = array('title'=>'清空回收站','onclick'=>"admin.mzTableclear()");
        $listData = $this->_getData(20,1);
        $this->displayList($listData);
    }
    //考试列表
    public function index(){
        $this->_initExamListAdminMenu();
        $this->_initExamListAdminTitle();
        $this->pageKeyList = array('exam_id','exam_name','exam_count','exam_status','exam_category','exam_begin_time','exam_end_time','exam_passing_grade','exam_publish_result_flg','exam_times_mode','exam_admin','DOACTION');
        $this->pageButton[] =  array('title'=>'搜索考试管理','onclick'=>"admin.fold('search_form')");
        $this->searchKey = array('exam_id','exam_name','exam_begin_time','exam_end_time');
        $this->searchPostUrl = U('exam/AdminExam/index');
        $listData = $this->_getData(20,0);
        $this->displayList($listData);
    }
    //编辑、添加考试信息
    public function addExam(){
        $this->_initExamListAdminMenu();
        $this->_initExamListAdminTitle();
        $tp = C('DB_PREFIX');
        if($_GET["id"]){
            $this->pageTitle['addExam'] = '编辑考试信息';
            $exam=M("ex_exam")->where(array('exam_id'=>$_GET["id"]))->find();
            $exam_paper=M("ex_exam_paper")->where(array('exam_paper_exam'=>$_GET["id"]))->findAll();
            $this->assign("exam",$exam);
            $this->assign("exam_paper",$exam_paper);
        }else{
            $this->pageTitle['addExam'] = '添加考试信息';
        }
        $exam_category = M('ex_exam_category')->findAll();
        $paper_list=M("ex_paper p")->join("`{$tp}ex_paper_category` c ON p.paper_category = c.paper_category_id and p.paper_status=1")->findAll();
        $this->assign("exam_category",$exam_category);
        $this->assign("paper_list",$paper_list);
        $this->display();
    }
    //添加课程操作
    public function doAddExam(){
        $post = $_POST;
        $data["exam_name"]=$post["exam_name"];
        $data["exam_describe"]=$post["exam_describe"];
        $data["exam_categoryid"]=$post["exam_categoryid"];
        $data["exam_status"]=$post["exam_status"];
        $data["exam_publish_result_flg"]=$post["exam_publish_result_flg"];
        $data["exam_passing_again_flg"]=$post["exam_passing_again_flg"];
        $data["exam_publish_result_tm_mode"]=$post["exam_publish_result_tm_mode"];
        $data["exam_user_signup_flg"]=$post["exam_user_signup_flg"];
        $data["exam_passing_grade"]=$post["exam_passing_grade"];
        $data["exam_total_time"]=$post["exam_total_time"];
        $data["exam_times_mode"]=$post["exam_times_mode"];
        $data['exam_begin_time']= $post['exam_begin_time'] ? strtotime($post['exam_begin_time']) : null; //考试开始时间
        $data['exam_end_time']= $post['exam_end_time'] ? strtotime($post['exam_end_time']) : null; //考试结束时间
        $data['exam_publish_result_tm']= $post['exam_publish_result_tm'] ? strtotime($post['exam_publish_result_tm']) : null; //成绩发布时间
        $data['exam_user_signup_time']= $post['exam_user_signup_time'] ? strtotime($post['exam_user_signup_time']) : null; //报名考试开始时间
        $data['exam_user_signup_end']= $post['exam_user_signup_end'] ? strtotime($post['exam_user_signup_end']) : null; //考试报名结束时间
        if($data['exam_begin_time'] >$data['exam_end_time'] || $data['exam_user_signup_end'] < $data['exam_user_signup_time']){
            exit(json_encode(array('status'=>'0','info'=>'结束时间不能小于开始时间')));
        }
        if($data['exam_publish_result_tm']!=0){
            if($data['exam_end_time']>$data['exam_publish_result_tm']){
                exit(json_encode(array('status'=>'0','info'=>'发布时间不能小于考试结束时间')));
            }
        }
        if($post['exam_id']){
            $data['exam_update_date'] = time();
            $exam = M('ex_exam')->where('exam_id = '.$post['exam_id'])->data($data)->save();
        }else{
            $data["exam_admin"]=$this->mid;
            $data['exam_update_date'] = time();
            $data['exam_insert_date'] = time();
            $exam= M('ex_exam')->data($data)->add();
        }
        if($exam){
            unset($data);
            if($post['exam_id']){
                exit(json_encode(array('status'=>'1','info'=>'编辑成功')));
            }else{
                foreach ($paper_id as $vo) {
                    $data["exam_paper_paper"]=$vo;
                    $data['exam_paper_exam'] = $exam;
                    $result=M('ex_exam_paper')->where($data)->find();
                    if(!$result){
                        $result = M('ex_exam_paper')->data($data)->add();
                        if($result){
                            $status++;
                        }
                    }
                }
                exit(json_encode(array('status'=>'1','info'=>'添加成功')));
            }
        } else {
            exit(json_encode(array('status'=>'0','info'=>'系统繁忙，请稍后再试')));
        }
    }
    //获取考试相关数据
    private function _getData($limit = 20,$is_del){
        $tp = C('DB_PREFIX');
        if(isset($_POST)){
            if($_POST['exam_id']){
               $_POST['exam_id'] && $map['exam_id'] = intval($_POST['exam_id']); 
            }
            if($_POST['exam_name']){
               $_POST['exam_name'] && $map['exam_name'] = array('like', '%'.t($_POST['exam_name']).'%');
            }
            if($_POST['exam_begin_time']){
               $map['exam_begin_time'] = array(array('EGT',strtotime($_POST['exam_begin_time']))); 
            }
            if($_POST['exam_end_time']){
               $map['exam_end_time'] = array(array('ELT',strtotime($_POST['exam_end_time'])));
            }
        }
        $map['exam_is_del']=$is_del;
        $list = M("ex_exam e")->join("`{$tp}ex_exam_category` c ON e.exam_categoryid = c.exam_category_id")->where($map)->order('exam_update_date desc')->field('exam_id,c.exam_category_name,exam_name,exam_status,exam_begin_time,exam_is_del,exam_end_time,exam_passing_grade,exam_publish_result_flg,exam_times_mode,exam_admin,exam_is_del')->findPage($limit);
        foreach ($list['data'] as $key => $value){
            $user_exam=M("ex_user_exam")->where("user_exam=".$value["exam_id"])->field("user_exam_id")->findALL();
            $list['data'][$key]['exam_count'] = count($user_exam)."&nbsp;人";
            $list['data'][$key]['exam_name'] = msubstr($value['exam_name'],0,20);
            $list['data'][$key]['exam_status'] = $value['exam_status'] == 1 ? '<span style="color:green">启用</span>' : '<span style="color:red">未启用</span>';
            $list['data'][$key]['exam_category'] = $value['exam_category_name'];
            $list['data'][$key]['exam_begin_time'] =date("Y-m-d H:i:s",$value['exam_begin_time']);
            $list['data'][$key]['exam_end_time'] = date("Y-m-d H:i:s",$value['exam_end_time']);
            $list['data'][$key]['exam_admin']= getUserName($value['exam_admin']);
            $list['data'][$key]['exam_times_mode'] = $value['exam_times_mode'] == 0 ? "不限" : $value['exam_times_mode'];
            $list['data'][$key]['exam_publish_answer_flg'] = $value['exam_publish_answer_flg'] == 1 ? '<span style="color:green">允许</span>' : '<span style="color:green">不允许</span>';
            $list['data'][$key]['exam_publish_result_flg'] = $value['exam_publish_result_flg'] == 1 ? '<span style="color:green">允许</span>' : '<span style="color:green">不允许</span>';
            $list['data'][$key]['DOACTION'] .= $value['exam_is_del']==1 ? '<a href="javascript:admin.mzExamEdit('.$value['exam_id'].','.$value['exam_is_del'].',\'delExam\',\'恢复\',\'考试信息\');">恢复</a>' : '<a href="'.U('exam/AdminExam/addExam',array('id'=>$value['exam_id'],'tabHash'=>'addExam')).'">编辑</a> | <a href="javascript:admin.mzExamEdit('.$value['exam_id'].','.$value['exam_is_del'].',\'delExam\',\'删除(隐藏)\',\'考试信息\');">删除(隐藏)</a> | <a href="'.U('exam/AdminExam/ExamDetil',array('id'=>$value['exam_id'],'tabHash'=>'ExamDetil')).'">查看考试信息</a>';
        }
        return $list;
    }
    /**
     * 软删除考试信息
     * @return void
     */
    public function delExam(){
        $data["exam_is_del"]=$_POST["is_del"];
        $result = M('ex_exam')->where('exam_id = '.$_POST['id'])->data($data)->save();
        if($result){
            exit(json_encode(array('status'=>'1','info'=>'已删除')));
        } else {
            exit(json_encode(array('status'=>'0','info'=> '操作繁忙,请稍后再试')));
        }
    }
    /**
     * 清空回收站
     * @return void
     */
    public function delTable(){
        $result=M("ex_exam")->where(array('exam_is_del'=>1))->delete();
        if($result){
            exit(json_encode(array('status'=>'1','info'=>'已删除')));
        } else {
            exit(json_encode(array('status'=>'0','info'=> '操作繁忙,请稍后再试')));
        }
    }
     /**
     * 查询考试信息
     * @return void
     */
    public function selectpaper()
    {    
        $result=M('ex_exam_paper')->where('exam_paper_exam='.$_POST['id'])->findAll();
        if($result){
           exit(json_encode(array('status'=>'1','info'=>'')));
        }else{
            exit(json_encode(array('status'=>'0','info'=>'请至少选择一张试卷')));
        }
    }
    /**
     * 添加考试信息
     * @return void
     */
    public function addpaper()
    {    
        $exam_paper_ids=explode(",",$_POST["exam_paper_ids"]);
        $status=0;
        foreach ($exam_paper_ids as $vo) {
            $data["exam_paper_paper"]=$vo;
            $data['exam_paper_exam'] = $_POST["id"];
            $result=M('ex_exam_paper')->where($data)->find();
            if(!$result){
                $result = M('ex_exam_paper')->data($data)->add();
                if($result){
                    $status++;
                }
            }
        }
        if($status>0){
            exit(json_encode(array('status'=>'1','info'=>'添加成功')));
        } else {
            exit(json_encode(array('status'=>'1','info'=>'添加失败')));
        }
    }
    /**
     * 删除考试信息
     * @return void
     */
    public function delpaper()
    {   
        $m=M("ex_exam_paper");
        $status=$m->delete($_POST["id"]);
        if($status){
            exit(json_encode(array('status'=>'1','info'=>'操作成功')));
        } else {
            exit(json_encode(array('status'=>'1','info'=>'操作失败')));
        }
    }
    /**
     * 考试后台管理菜单
     * @return void
     */
    private function _initExamListAdminMenu(){
        $this->pageTab[] = array('title'=>'考试列表','tabHash'=>'index','url'=>U('exam/AdminExam/index'));
        $this->pageTab[] = array('title'=>'添加考试','tabHash'=>'addExam','url'=>U('exam/AdminExam/addExam'));
        $this->pageTab[] = array('title'=>'考试管理回收站','tabHash'=>'postRecycle','url'=>U('exam/AdminExam/postRecycle'));
    }
    /**
     * 考试后台的标题
     */
    private function _initExamListAdminTitle(){
        $this->pageTitle['index'] = '考试列表';
        $this->pageTitle['addExam'] = '添加考试';
        $this->pageTitle['postRecycle'] = '考试管理回收站';
    }
    //考试信息
    public function ExamDetil(){
        $this->_initExamListAdminMenu();
        $this->_initExamListAdminTitle();
        $id=intval($_GET["id"]);
        $this->pageTitle['ExamDetil'] = '考试信息';
        $this->pageButton[] = array('title'=>'导出考试信息','onclick'=>"admin.mzExport(".$id.")");
        $this->pageKeyList = array('user_exam_id','user_name','user_exam_number','user_exam_time','user_exam_score','user_total_date','user_right_count','user_error_count');
        $user_exam=M("ex_user_exam")->where(array('user_exam'=>$id." and user_exam_is_del=0"))->order('user_exam_id desc')->field('user_exam_id,user_id,user_exam_number,user_exam_time,user_exam_score,user_total_date,user_right_count,user_error_count')->findPage(30);
        foreach ($user_exam['data'] as $key => $value) {
            $user_exam['data'][$key]['user_name']= getUserName($value['user_id']);
            $user_exam['data'][$key]['user_exam_number']= "第&nbsp;".$value['user_exam_number']."&nbsp;次";
            $user_exam['data'][$key]['user_exam_time']= date("Y-m-d H:i:s",$value['user_exam_time']);
            $user_exam['data'][$key]['user_exam_score']= $value['user_exam_score']."&nbsp;分";
            $user_exam['data'][$key]['user_right_count']= $value['user_right_count']."&nbsp;个";
            $user_exam['data'][$key]['user_error_count']= $value['user_error_count']."&nbsp;个";
        }
        $this->displayList($user_exam);
    }
    /**
     * 试题模板导出
     * @return void
     */
    public function doExport(){
        $id=$_GET["id"];
        $exam_info=M("ex_exam")->where("exam_id=".$id)->find();
        $user_exam=M("ex_user_exam")->where(array('user_exam'=>$id." and user_exam_is_del=0"))->order('user_exam_id')->field('user_exam_id,user_id,user_exam_number,user_exam_time,user_exam_score,user_total_date,user_right_count,user_error_count')->findAll();
        $this->error("没有考试相关的数据！");die;
        foreach ($user_exam as $key => $value) {
            $user_exam[$key]['user_name']= getUserName($value['user_id']);
            $user_exam[$key]['user_exam_number']= "第".$value['user_exam_number']."次";
            $user_exam[$key]['user_exam_time']= date("Y-m-d H:i:s",$value['user_exam_time']);
            $user_exam[$key]['user_exam_score']= $value['user_exam_score']."分";
            $user_exam[$key]['user_right_count']= $value['user_right_count']."个";
            $user_exam[$key]['user_error_count']= $value['user_error_count']."个";
        }
        require_once 'PHPExcel/PHPExcel.php';
        require_once 'PHPExcel/PHPExcel/Writer/Excel5.php';
        require_once 'PHPExcel/PHPExcel/Writer/Excel2007.php';
        $objPHPExcel = new PHPExcel();
        /* 设置输出的excel文件为2007兼容格式 */
        //$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter=new PHPExcel_Writer_Excel5($objPHPExcel);
        /* 设置当前的sheet */
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        /* sheet标题 */
        $objActSheet->setTitle($exam_info["exam_name"]."-考试信息导出");
        //合并单元格
        $objActSheet->mergeCells('A1:G1');
        $objActSheet->setCellValue('A1',$exam_info["exam_name"]."-考试信息导出");
        $objStyleA1 =$objPHPExcel->getActiveSheet()->getStyle('A1');
        $objActSheet->setCellValue("A2","用户名");
        $objActSheet->setCellValue("B2","考试次数");
        $objActSheet->setCellValue("C2","交卷时间");
        $objActSheet->setCellValue("D2","考试所花时间");
        $objActSheet->setCellValue("E2","正确个数");
        $objActSheet->setCellValue("F2","错误个数");
        $objActSheet->setCellValue("G2","得分");
        $objStyleA1->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//设置垂直居中
        $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置横向居中
        //颜色填充
        $objStyleA1->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objStyleA1->getFill()->getStartColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW);
        //字体设置
        $objStyleA1->getFont()->setName('Candara');
        $objStyleA1->getFont()->setSize(16);
        $objStyleA1->getFont()->setBold(true);
        $objStyleA1->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
        $objStyleA1->getFont()->setBold(true);                      
        $objActSheet->getColumnDimension('A')->setWidth(20);
        $objActSheet->getColumnDimension('B')->setWidth(20);
        $objActSheet->getColumnDimension('C')->setWidth(20);
        $objActSheet->getColumnDimension('D')->setWidth(20);
        $objActSheet->getColumnDimension('E')->setWidth(20);
        $objActSheet->getColumnDimension('F')->setWidth(20);
        $objActSheet->getColumnDimension('G')->setWidth(20);
        for ($i=0; $i <count($user_exam); $i++) {
            $k=$i+3; 
            $user_name=$user_exam[$i]['user_name'];
            $user_exam_number=$user_exam[$i]['user_exam_number'];
            $user_exam_time=$user_exam[$i]['user_exam_time'];
            $user_total_date=$user_exam[$i]['user_total_date'];
            $user_right_count=$user_exam[$i]['user_right_count'];
            $user_error_count=$user_exam[$i]['user_error_count'];
            $user_exam_score=$user_exam[$i]['user_exam_score'];
            //设置值
            $objActSheet->setCellValue("A$k","$user_name");
            $objActSheet->setCellValue("B$k","$user_exam_number");
            $objActSheet->setCellValue("C$k","$user_exam_time");
            $objActSheet->setCellValue("D$k","$user_total_date");
            $objActSheet->setCellValue("E$k","$user_right_count");
            $objActSheet->setCellValue("F$k","$user_error_count");
            $objActSheet->setCellValue("G$k","$user_exam_score");
        }
        /* 生成到浏览器，提供下载 */
        ob_end_clean();  //清空缓存
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=test.xls");
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }
}