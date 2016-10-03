<?php
class ExUserExamModel extends Model{
    var $tableName = 'ex_user_exam';
    //获取用户考试记录
    public function getUserExam($exam_id,$user_id){
        $_data=$this->where(array('user_exam' => $_GET['id'],'user_id'=>$user_id))->count();
        return $_data;
    }
    public function getUserExamList($user_id){
        $_data=M("ex_user_exam")->join(C('DB_PREFIX')."ex_exam ON exam_id=user_exam")->where(array('user_id'=>$user_id))->order("user_exam_id desc")->field('user_exam,exam_publish_result_flg,user_paper,user_exam_number,exam_name,exam_categoryid,exam_describe')->select();
        foreach ($_data as $key=>$v) {
            $where=array(
                'user_exam'=>$v["user_exam"],
                'user_paper'=>$v["user_paper"],
                'user_exam_number'=>$v["user_exam_number"]
            );
            $category=M("ex_exam_category")->where("exam_category_id=".$v["exam_categoryid"])->find();
            $exam_sum=M("ex_user_exam")->where($where)->order("user_exam_score desc")->select();
            foreach ($exam_sum as $k=>$exam) {
                if($exam["user_id"]==$user_id){
                    $_data[$key]["user_rank"]=$k+1;
                }
            }
            $_data[$key]["user_sum"]=count($exam_sum);
            $_data[$key]["category_name"]=$category["exam_category_name"];
        }
        return $_data;
    }
    public function getUserExamCount($exam_id,$paper_id,$user_id,$user_exam_number){
        $map=array(
            'user_exam' =>$exam_id,
            'user_paper' =>$paper_id,
            'user_id' =>$user_id,
        );
        if($user_exam_number){
            $map["user_exam_number"]=$user_exam_number;
        }
        $_data=$this->where($map)->order('user_exam_id desc')->find();
        return $_data;
    }
}
?>