<?php
/**
 * 云课堂搜索模块
 * Created by Ashang.
 * Email: ashangmanage@phpzsm.com
 * Date: 14-10-21
 * Time: 下午2:50
 */

class SearchAction extends Action{
    protected $pagesize=null;//分页大小
    protected $key=null;//搜索内容
    protected $tagid=null;
    protected $video_where=null;//课程搜索条件
    protected $wenda_where=null;//问答搜索条件
    protected $wdvideo_where=null;//课程问答搜索条件
    public function _initialize(){
        $this->key=trim(t($_GET['searchkey']));//获取搜索的内容
        $this->tagid=intval($_GET['searchtid']);//获取标签
        if(empty($this->tagid)){//当标签id为空
            if(empty($this->key)){//未输入搜索内容
                $this->redirect("classroom/Home/index");
            }
            $this->pagesize=20;
            //课程查询条件
            $this->video_where=array(
                'video_title'=>array("like","%$this->key%"),
                'video_intro'=>array("like","%$this->key%"),
                'is_del'=>0
            );
            //开始统计课程数量
            $video_count=D("ZyVideo")->where($this->video_where)->count();
            //问答查询条件
            $this->wenda_where=array(
                'is_del'=>0,
                'wd_description'=>array("like","%$this->key%")
            );
            //技术问答
            $this->wenda_where['type']=1;
            $js_count=D("ZyWenda")->where($this->wenda_where)->count();
            //技术分享
            $this->wenda_where['type']=2;
            $fx_count=D("ZyWenda")->where($this->wenda_where)->count();
            //活动建议
            $this->wenda_where['type']=3;
            $jy_count=D("ZyWenda")->where($this->wenda_where)->count();
            //课程问答
            $this->wdvideo_where=array(
                'parent_id'=>0,
                'qst_title'=>array("like","%$this->key%"),
            );
            $wdviode_count=D("ZyQuestion")->where($this->wdvideo_where)->count();

        }else{
            //问答查询条件
            //根据id取标签内容
            $this->key=M('tag')->where(array('tag_id'=>$this->tagid))->getField('name');
            $this->wenda_where=array(
                'is_del'=>0,
                'tag_id'=>array("like","%$this->tagid%")
            );
            //技术问答
            $this->wenda_where['type']=1;
            $js_count=D("ZyWenda")->where($this->wenda_where)->count();
            //技术分享
            $this->wenda_where['type']=2;
            $fx_count=D("ZyWenda")->where($this->wenda_where)->count();
            //活动建议
            $this->wenda_where['type']=3;
            $jy_count=D("ZyWenda")->where($this->wenda_where)->count();
        }

        $this->assign("searchkey",$this->key);//搜索内容
        $this->assign("searchtid",$this->tagid);//搜索内容
        $this->assign("video_count",$video_count);//课程数量
        $this->assign("js_count",$js_count);//技术问答数量
        $this->assign("fx_count",$fx_count);//技术分享数量
        $this->assign("jy_count",$jy_count);//活动建议数量
        $this->assign("wdviode_count",$wdviode_count);//课程问答数量

    }
    /**
     * 课程搜索结果
     */
    public function index(){

     //课程查询条件
     $videolist=D("ZyVideo")->where($this->video_where)->findPage($this->pagesize);
     //循环计算课程的价格

     foreach($videolist as $val){
         $val['t_price']=getPrice($val,$this->mid);
         $val['video_score']=$val['video_score']/20;
     }
    /* dump($videolist);
     die();*/
     $this->assign("data",$videolist);
     $this->display();
    }

    /**
     * 问答搜索结果
     */

    public function wenda(){
        $type=intval($_GET['type']);//获取问答类型
        $this->wenda_where['type']=$type;
        $wendalist=D('ZyWenda')->where( $this->wenda_where)->findPage($this->pagesize);
        foreach($wendalist['data'] as &$val){
            $val['ctime']=getDateDiffer($val['ctime']);//格式化时间数据
            $val['tags']=D('ZyWenda','wenda')->getWendaTags($val['tag_id']);//取出问答的标签
            $val['wd_comment']=D('ZyWendaComment','wenda')->getNowWenda($val['id'],1);//取最新的一条评论
        }
       /* dump($wendalist);
        die();*/
        $this->assign("data",$wendalist);
        $this->display("index");

    }

    /**
     *课程问答搜索
     */
    public function videowd(){
        $videowd_list=D('ZyQuestion')->where($this->wdvideo_where)->findPage($this->pagesize);
        //格式化数据
        foreach($videowd_list['data'] as &$val){
            $val['ctime']=getDateDiffer($val['ctime']);//格式化时间数据
            if($val['type']==1){
                $tablename="ZyVideo";
                $val['href']=U('classroom/Video/view',array('id'=>$val['oid']));//生成来源url
            }else{
                $tablename="ZyAlbum";
                $val['href']=U('classroom/Album/view',array('id'=>$val['oid']));//生成来源url
            }
            $map['id']=$val['oid'];
            $val['videoinfo']=M($tablename)->where($map)->find();

            if($val['type']==1){
                $val['video_title']= $val['videoinfo']['video_title'];
            }else{
                $val['video_title']= $val['videoinfo']['album_title'];
            }
        }
        $this->assign("data",$videowd_list);
        $this->display("index");
    }


}


?>