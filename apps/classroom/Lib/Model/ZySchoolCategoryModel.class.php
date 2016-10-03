<?php
class ZySchoolCategoryModel extends Model {

    public function getParentIdList($id){
        $list = '';
        while ($id > 0){
            $data = $this->field('zy_school_category_id,pid')->find($id);
            if($data){
                $list = $data['zy_school_category_id'].','.$list;
                $id = $data['pid'];
            }else{
                break;
            }
        }
        return trim($list, ',');
    }

}