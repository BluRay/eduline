<?php
/**
 * 专辑订单模型
 * @author xiewei <master@xiew.net>
 * @version 1.0
 */
class ZyOrderCourseModel extends Model{
    /**
     * 通过教师id获取教师姓名
     */
    public function teacherId($id){
        $teacher_info=M("zy_teacher")->where("id=".$id)->find();
        return $teacher_info["name"];
    }
    public function courseId($id){
        $course_info=M("zy_teacher_course")->where("course_id=".$id)->find();
        return $course_info["course_name"];
    }
    public function teacherWay($id){
        if($id==1){
            return "线上授课";
        }else if($id==2){
            return "线下授课";
        }else{
            return "线上/线下均可";
        }
    }
}