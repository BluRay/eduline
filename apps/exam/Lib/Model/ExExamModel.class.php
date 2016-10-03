<?php
class ExExamModel extends Model
{
	var $tableName = 'ex_exam'; 
	public function getExam($id,$paper_id) {
	   	$map['exam_id'] = $id;
		$data = $this->join(C('DB_PREFIX')."ex_paper ON paper_id=".$paper_id)->where($map)->find();
		return $data;
    }
    public function getTime($starttime,$endtime){
    	//计算天数
        $timediff = $endtime-$starttime;
        $days = intval($timediff/86400);
        //计算小时数
        $remain = $timediff%86400;
        $hours = intval($remain/3600);
        //计算分钟数
        $remain = $remain%3600;
        $mins = intval($remain/60);
        //计算秒数
        $secs = $remain%60;
        $time = $hours."时".$mins."分".$secs."秒";
        return $time;
    }
}
?>