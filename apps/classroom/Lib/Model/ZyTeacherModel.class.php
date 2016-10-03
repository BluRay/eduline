<?php
/**
 * Created by Ashang.
 * 云课堂教师风采模型
 * Date: 14-10-7
 * Time: 下午3:40
 */

class ZyTeacherModel extends Model {

    /**根据讲师id获取讲师信息
     * @param $id讲师id
     * @return null
     */
    public function getTeacherInfo($id){
        if(intval($id)==0)return null;
        $teacher_info=$this->where(array('id'=>$id))->find();
        return $teacher_info;
    }
}

?>