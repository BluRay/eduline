<?php
class ExQuestionModel extends Model{
    var $tableName = 'ex_question q';
    public function QuestionList(){
    	$_data=$this->join(C('DB_PREFIX')."ex_question_category ec ON q.question_category=ec.question_category_id")->join(C('DB_PREFIX')."ex_question_type qt ON q.question_type=qt.question_type_id")->field("question_id,question_content,question_point,question_category_name,question_type_title")->findALL();
		return $_data;
    }
    public function getPaperQuestion($paper_id){
        $_data=M("ex_paper_content pc")->join(C('DB_PREFIX')."ex_question eq ON pc.paper_content_questionid=eq.question_id")->where("paper_content_paperid=".$paper_id)->field("paper_content_id,paper_content_paperid,question_id,question_content,question_category,question_type,paper_content_point")->order("paper_content_item asc")->findALL();
        foreach ($_data as $key => $value) {
            $category=M("ex_question_category")->where("question_category_id=".$value["question_category"])->find();
            $type=M("ex_question_type")->where("question_type_id=".$value["question_type"])->find();
            $_data[$key]["category_name"]=$category['question_category_name'];
            $_data[$key]["type_name"]=$type['question_type_title'];
            $value["question_content"]=msubstr($value['question_content'], 0, 50);
        }
        return $_data;
    }
    public function getQuestion_type(){
        $_data=M("ex_question_type")->findALL();
        foreach ($_data as $key => $value) {
            $question_list=M("ex_question")->where("question_type=".$value["question_type_id"]." and question_is_del=0")->findAll();
            $_data[$key]["question_count"]=count($question_list);
        }
        return $_data;
    }
}
?>