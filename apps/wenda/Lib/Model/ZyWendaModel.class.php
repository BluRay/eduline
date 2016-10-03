<?php
/**
 *问答版块模型
 * User: Administrator
 * Date: 14-10-11
 * Time: 下午2:49
 */

class ZyWendaModel extends Model{

    /**获取推荐问答列表
     * @return 推荐列表
     */
    public function getRecommendList(){
        $map=array(
            'is_del'=>0,
            'recommend'=>1
        );
        $dataList=$this->where($map)->order('ctime DESC')->select();
        if(!empty($dataList)){

            foreach($dataList as &$val){
                $val['ctime']=getDateDiffer($val['ctime']);
                $val['tags']=$this->getWendaTags($val['tag_id']);
                $val['wd_description'] = t($val['wd_description']);
                $val['wd_comment']=D('ZyWendaComment')->getNowWenda($val['id'],2);
            }
        }
      /*  dump($dataList);
        die();*/
        return $dataList;
    }

    /**设置问答浏览量+1
     * @param $id
     */
    public function addBrowseCount($id){
        $id=intval($id);
        if(!empty($id)){
         //$this->where("id=$id")->setInc('wd_browse_count',1);
            $this->query('UPDATE `'.C('DB_PREFIX').'zy_wenda` SET `wd_browse_count`=`wd_browse_count`+1 WHERE `id`='.$id);
        }
    }

    /**设置问答评论量+1
     * @param $id
     */
    public function addCommentCount($id){
        $id=intval($id);
        if(!empty($id)){
            //$this->where("id=$id")->setInc('wd_browse_count',1);
            $this->query('UPDATE `'.C('DB_PREFIX').'zy_wenda` SET `wd_comment_count`=`wd_comment_count`+1 WHERE `id`='.$id);
        }
    }
    /**设置问答评论量-1
     * @param $id
     */
    public function reductionCommentCount($id){
        $id=intval($id);
        if(!empty($id)){
            //$this->where("id=$id")->setInc('wd_browse_count',1);
            $this->query('UPDATE `'.C('DB_PREFIX').'zy_wenda` SET `wd_comment_count`=`wd_comment_count`-1 WHERE `id`='.$id);
        }
    }
    /**根据标签
     * @param $ids
     * @return mixed|null
     */
    public function getWendaTags($ids){
        $tagsids=trim($ids,",");
        if(empty($tagsids)){
            return null;
        }
        $map=array(
            'tag_id'=>array("in",$tagsids)
        );
        //分割标签
        $tagList=M('tag')->where($map)->select();

        return $tagList;
    }
}


?>