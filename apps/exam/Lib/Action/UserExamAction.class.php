<?php

/**
 * Eduline考试系统首页控制器
 * @author Ashang <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
class UserExamAction extends Action {
    /**
     * Eduline考试系统首页方法
     * @return void
     */ 
    public function index() {
        $this->display();
    }
    /**
     * 取得考试分类
     * @param boolean $return 是否返回数据，如果不是返回，则会直接输出Ajax JSON数据
     * @return void|array
     */
    public function getList($return = false) {
        $user_id=$this->uid;
        //排序
        $order = 'user_exam_id DESC';
        $time = time();
        $where .= "user_exam_is_del=0 and user_id=$user_id";
        $data = M("ex_user_exam")->join(C('DB_PREFIX')."ex_exam ON exam_id=user_exam")->where($where)->order($order)->findPage(10);
        foreach ($data['data'] as $key=> $v) {
           $where=array(
                'user_exam'=>$v["user_exam"],
                'user_paper'=>$v["user_paper"],
                'user_exam_number'=>$v["user_exam_number"]
            );
            $paper=M("ex_paper")->where("paper_id=".$v["user_paper"])->find();
            $category=M("ex_exam_category")->where("exam_category_id=".$v["exam_categoryid"])->find();
            $exam_sum=M("ex_user_exam")->where($where)->order("user_exam_score desc")->select();
            foreach ($exam_sum as $k=>$exam) {
                if($exam["user_id"]==$user_id){
                    $data['data'][$key]["user_rank"]=$k+1;
                }
            }
            $data['data'][$key]["user_sum"]=count($exam_sum);
            $data['data'][$key]["paper_point"]=$paper["paper_point"];
            $data['data'][$key]["category_name"]=$category["exam_category_name"];
        }
        if ($data['data']) {
            $this->assign('listData', $data['data']);
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
    public function exam_info(){
        $user_id=$this->uid;
        $exam_id=intval($_GET["exam_id"]);
        $paper_id=intval($_GET["paper_id"]);
        $user_exam_number=intval($_GET["user_exam_number"]);
        $user_exam=M("ExUserExam")->getUserExamCount($exam_id,$paper_id,$user_id,$user_exam_number);
        $where=array(
            'user_id'=>$user_id,
            'user_exam_id'=>$exam_id,
            'user_paper_id'=>$paper_id,
            'user_exam_time'=>$user_exam["user_exam_number"]
            );
        $user_answer=M("ex_user_answer")->where($where)->field('user_question_answer,user_question_id')->select();
        $exam_info=D('ExExam')->getExam($exam_id,$paper_id);
        $question_type=M('')->query('SELECT question_type_id,question_type_title,COUNT(paper_content_paperid) AS sum, Sum(paper_content_point) as score FROM '.C('DB_PREFIX').'ex_paper_content pc,'.C('DB_PREFIX').'ex_question q,'.C('DB_PREFIX').'ex_question_type qt WHERE pc.paper_content_questionid=q.question_id AND q.question_type=qt.question_type_id AND pc.paper_content_paperid='.$paper_id.' GROUP  BY question_type_id');
        $data=M('ExPaper')->getPaper($paper_id);
        $this->assign('user_exam',$user_exam);
        $this->assign('exam_info',$exam_info);
        $this->assign('user_answer',$user_answer);
        $this->assign('data',$data);
        $this->assign('subscript',array("A","B","C","D","E","F","G","H","I","J","K"));
        $this->assign('question_type',$question_type);
        $this->display();
    }  
}

