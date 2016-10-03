<?php

/**
 * Eduline考试系统首页控制器
 * @author Ashang <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
class IndexAction extends Action {
    /**
     * Eduline考试系统首页方法
     * @return void
     */ 
    public function index() {
        $tp = C('DB_PREFIX');
        $str='<img class="tkimg" alt="2000" /><img class="tkimg" alt="20" /><img class="tkimg" alt="2" />';
        if(preg_match_all("/<[a-z]+ [a-z]+=\"[a-z]+\" [a-z]+=\"[0-9]+\" \/>/",$str,$match)) { 
        }
        $result = M('')->query('SELECT `exam_category_id`,`exam_category_name` FROM '.$tp.'ex_exam_category ORDER BY exam_category_insert_date');
        $data=M("ExUserExam")->getUserExamList($this->uid);
        $this->assign('selCate',$result);
        $this->assign('data',$data);
        $this->display();
    }
    /**
     * 取得考试分类
     * @param boolean $return 是否返回数据，如果不是返回，则会直接输出Ajax JSON数据
     * @return void|array
     */
    public function getList($return = false) {
        $tp = C('DB_PREFIX');
        //排序
        $order = 'exam_begin_time DESC';
        $time = time();
        $where="";
        $cateId=$_GET["cateId"];
        if ($cateId> 0) {
            $where= " exam_categoryid=$cateId and";
        }
        $where .= " exam_is_del=0 AND exam_begin_time<$time and exam_end_time>$time";
        $data = M("ex_exam_category ec")->join("`{$tp}ex_exam` e ON ec.exam_category_id=e.exam_categoryid")->where($where)->order($order)->findPage(10);
        foreach ($data['data'] as $key=> $vo) {
            $data['data'][$key]["exam_begin_time"]=date("Y-m-d H:i:s",$vo["exam_begin_time"]);
            $data['data'][$key]["exam_end_time"]=date("Y-m-d H:i:s",$vo["exam_end_time"]);
            if($vo["exam_total_time"]==0){
                $data['data'][$key]["exam_total_time"]="不限制时长";
            }else{
                $data['data'][$key]["exam_total_time"]=$vo["exam_total_time"]."分钟";
            }
        }
        if ($data['data']) {
            $this->assign('listData', $data['data']);
            $this->assign('where', $where);
            $this->assign('cateId',$_GET['cateId']);//定义分类
            $html = $this->fetch('index_list');
        } else {
            $html = $this->fetch('index_list');
        }
        $data['data'] = $html;
        if ($return) {
            return $data;
        } else {
            echo json_encode($data);
            exit();
        }
    }
    /**
     * 取得逐题考试数据
     * @param boolean $return 是否返回数据，如果不是返回，则会直接输出Ajax JSON数据
     * @return void|array
     */
    public function getOneExam($return = false) {
        $tp = C('DB_PREFIX');
        $paper_id=$_GET["paper_id"];
        $subscript=array("A","B","C","D","E","F","G","H","I","J","K");
        $data = D("ExPaper")->join("{$tp}ex_question q ON pc.paper_content_questionid=q.question_id")->where("pc.paper_content_paperid=".$paper_id)->order("paper_content_item")->findPage(1);
        foreach ($data['data'] as $key=> $vo) {
            $$data['data'][$key]["question_content"]=preg_replace("/<[a-z]+ [a-z]+=\"[a-z]+\" [a-z]+=\"[0-9]+\" \/>/", '______', $vo["question_content"]);
            $question_answer="";
            $option_list = M('ex_option' )->where('option_question='.$vo["question_id"])->order('option_item_id')->findAll();
            foreach ($option_list as $k =>$answer) {
                if($vo["question_type"]==3){
                    $question_answer.=$answer["option_content"].","; 
                }else{
                     if($answer["is_right"]==1){
                       $question_answer.=$subscript[$answer["option_item_id"]-1].",";
                    }
                }
            }
            $data['data'][$key]["question_answer"] = $question_answer{strlen($question_answer)-1} == ',' ? substr($question_answer, 0, -1) : $question_answer;  
            $data['data'][$key]["option_list"]=$option_list;
        }
        if($data['data']) {
            $this->assign('exam_info', $exam_info);
            $html = $this->fetch('one_exam');
        }
        if($return) {
            return $data;
        }else{
            echo json_encode($data);
            exit();
        }
    }
    public function exam(){
        $tp = C('DB_PREFIX');
        $exam_id=intval($_GET["id"]);
        if($exam_id==0){
            $this->error('参数错误');
        }
        $paper_list=M("ex_exam_paper")->where("exam_paper_exam=".$exam_id)->findALl();
        $paper_id=0;
        if(count($paper_list)==1){
            $paper_id=$paper_list[0]["exam_paper_paper"];
        }else{
            $list="";
            foreach ($paper_list as $v) {
                $list[]=$v["exam_paper_paper"];
            }
            $paper_id=$list[array_rand($list)];
        }
        $exam_info=D('ExExam')->getExam($exam_id,$paper_id);
        $user_exam_time= M("ExUserExam")->getUserExam($exam_id,$this->uid);
        if($user_exam_time>=$exam_info["exam_times_mode"] &&  $exam_info["exam_times_mode"]!=0){
            $this->error('考试次数已达上限');
        }
        $data=M('ExPaper')->getPaper($paper_id);
        if(count($data["question_list"])==0){
            $this->error('该试卷暂未抽选出题!');
        }
        $question_type=M('')->query('SELECT question_type_id,question_type_title,COUNT(paper_content_paperid) AS sum, Sum(paper_content_point) as score FROM '.$tp.'ex_paper_content pc,'.$tp.'ex_question q,'.$tp.'ex_question_type qt WHERE pc.paper_content_questionid=q.question_id AND q.question_type=qt.question_type_id AND pc.paper_content_paperid='.$paper_id.' GROUP  BY question_type_id');
        $this->assign('exam_info',$exam_info);
        $this->assign('data',$data);
        $this->assign('exam_id',$exam_id);
        $this->assign('subscript',array("A","B","C","D","E","F","G","H","I","J","K"));
        $this->assign('question_type',$question_type);
        $this->assign('begin_time',time());
        $this->assign('sum',count($data["question_list"]));
         $this->display();
    }
    public function doExam(){
        $user_id=$this->uid;
        $data["user_id"]=$user_id;
        $data["user_exam"]=$_POST["exam_id"];
        $data["user_paper"]=$_POST["paper_id"];
        $data["user_exam_time"]=time();
        $data["user_exam_score"]=$_POST["user_score"];
        $data["user_total_date"]=D("ExExam")->getTime($_POST["begin_time"],time());
        $data["user_right_count"]=$_POST["rightcount"];
        $data["user_error_count"]=$_POST["errorcount"];
        $user_exam_number=1;
        $count=M("ExUserExam")->getUserExamCount($_POST["exam_id"],$_POST["paper_id"],$user_id);
        if($count){
            $user_exam_number=$count["user_exam_number"]+1;
        }
        $data["user_exam_number"]=$user_exam_number;
        $exam = M('ex_user_exam')->data($data)->add();
        if($exam){
            $question_list=$_POST["question_list"];
            $question_list=explode("+",$question_list);
            foreach ($question_list as $vo) {
                $vo=explode("-",$vo);
                $data["user_id"]=$user_id;
                $data['user_exam_id'] = $_POST["exam_id"];
                $data['user_paper_id'] = $_POST["paper_id"];
                $data['user_question_id'] = $vo[0];
                $data['user_exam_time'] =$user_exam_number;
                $data['user_question_answer'] = $vo[1];
                if($vo[1]=="null"){
                    $data['user_question_answer'] ="未填";
                }
                M('ex_user_answer')->data($data)->add();
            }
            exit(json_encode(array('status'=>'1','user_exam_number'=>$user_exam_number)));
        }else {
            exit(json_encode(array('status'=>'0')));
        }
    }   
}

