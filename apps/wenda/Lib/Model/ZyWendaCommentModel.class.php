<?php
/**
 * 问答评论模型
 * By:Ashang
 * Date: 14-10-13
 * Time: 下午8:43
 */
class ZyWendaCommentModel extends Model{

    /**根据type取最新或者最赞的一条回答
     * @param $id
     * @param $type
     */
    public function getNowWenda($id,$type){
        $id=intval($id);//问答id
        $type=intval($type);//类型 1,最新的回答 2最赞的回答
        $where=array(
            'is_del'=>0,
            'wid'=>$id
        );
        if($type==1){
          $order="ctime DESC";
        }else{
          $orde="help_count DESC";
        }
        $nowd=$this->where($where)->order($order)->find();
        return $nowd;
    }

    /**设置回复评论量+1
     * @param $id
     */
    public function addCommentCount($id){
        $id=intval($id);
        if(!empty($id)){
            //$this->where("id=$id")->setInc('wd_browse_count',1);
            $this->query('UPDATE `'.C('DB_PREFIX').'zy_wenda_comment` SET `comment_count`=`comment_count`+1 WHERE `id`='.$id);
        }
    }
     /**设置回复评论量+1
     * @param $id
     */
    public function reductionCommentCount($id){
        $id=intval($id);
        if(!empty($id)){
            //$this->where("id=$id")->setInc('wd_browse_count',1);
            $this->query('UPDATE `'.C('DB_PREFIX').'zy_wenda_comment` SET `comment_count`=`comment_count`-1 WHERE `id`='.$id);
        }
    }
    /**设置回复赞+1
     * @param $id
     */
    public function addCommentZan($id){
        $id=intval($id);
        if(!empty($id)){
            //$this->where("id=$id")->setInc('wd_browse_count',1);
            $this->query('UPDATE `'.C('DB_PREFIX').'zy_wenda_comment` SET `help_count`=`help_count`+1 WHERE `id`='.$id);
        }
    }

}


?>