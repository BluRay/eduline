<?php
/**
 * 用户
 * @author ashangmanage <arsom@qq.com>
 * @version CY1.0
 */
tsload(APPS_PATH . '/classroom/Lib/Action/CommonAction.class.php');
class UserShowAction extends CommonAction{
    protected $userid;

    public function _initialize() {
        $this->userid=intval($_GET['uid']);
        $username=getUserName($this->userid);
        if(empty($username)){//用户不存在自动跳转课程页面
            $this->redirect(U("classroom/Video/index"));

        }
        if($this->mid!=0 && $this->mid!=$this->userid){
            //查询最近访问是否有自己的uid
            $map=array(
                'uid'=>$this->mid,
                'fuid'=>$this->userid,
            );
            $res=M("ZyUserVisitor")->where($map)->find();
            $data['ctime']=time();
            if($res){
                M("ZyUserVisitor")->where($map)->save($data);
            }else{
                $map['ctime']=$data['ctime'];
                M("ZyUserVisitor")->add($map);
            }
        }
        //判断用户是不是讲师
        $res=D('ZyTeacher')->where(array('uid'=>$this->userid))->find();
        if($res){
          $this->assign("isteacher",true);
          $this->assign("teacherid",$res['id']);
        }
        $vrlist=M("ZyUserVisitor")->where(array('fuid'=>$this->userid))->order("ctime DESC")->limit(6)->select();
        foreach ($vrlist as &$vr){
        	$vr['userinfo']=model('User')->getUserInfo($vr['uid']);
        }
        $this->assign('vrlist',$vrlist);
        $this->twcont=D("ZyQuestion")->where(array('uid'=>$this->userid))->count();//加载提问数量
        $this->videocont=D("ZyOrder")->where(array('uid'=>$this->userid,'is_del'=>0))->count();//加载我的课程总数
        $this->commcont=M("ZyWendaComment")->where(array('uid'=>$this->userid,'is_del'=>0))->count();//加载我的评论
        $this->wdcont=M('ZyWenda')->where(array('uid'=>$this->userid,'is_del'=>0))->count();//加载我的问答数量
        $this->note=M('ZyNote')->where(array('uid'=>$this->userid))->count();
        $this->assign("userid", $this->userid);
    }
    /**
     * 用户资料显示Index页面
     */
    public function index(){
        $limit      = intval($_POST['limit']);
        $uid        = intval($this->userid);
        $limit      = 9;
        //拼接两个表名
        $vtablename = C('DB_PREFIX').'zy_video';
        $otablename = C('DB_PREFIX').'zy_order';

        //拼接字段
        $fields     = '';
        $fields .= "{$otablename}.`learn_status`,{$otablename}.`uid`,{$otablename}.`id` as `oid`,";
        $fields .= "{$vtablename}.`video_title`,{$vtablename}.`video_category`,{$vtablename}.`id`,{$vtablename}.`video_intro`,";
        $fields .= "{$vtablename}.`cover`,{$vtablename}.`video_order_count`,{$vtablename}.`ctime`";
        //不是通过专辑购买的
        //$where     = "{$otablename}.`is_del`=0 and {$otablename}.`order_album_id`=0 and {$otablename}.`uid`={$uid}";
        $where     = "{$otablename}.`is_del`=0 and {$otablename}.`uid`={$uid}";
        $data = M('ZyOrder')->join("{$vtablename} on {$otablename}.`video_id`={$vtablename}.`id`")->where($where)->field($fields)->findPage($limit);
        //获取用户关注状态
        $fstatus=M('UserFollow')->where(array('uid'=>$this->mid,'fid'=>$this->userid))->find();
        if($fstatus){
            $this->assign("isfollow",true);
        }
        $vms = D('ZyVideoMerge')->getList(intval($this->mid), session_id());


        $this->assign('vms', getSubByKey($vms, 'video_id'));

        $this->assign('data',$data);
        $this->display();
    }


    /**
     * 加载用户问答
     */
    public function wenda(){
     $map=array(
         'uid'=>$this->userid,
         'is_del'=>0
     );
      $wdlist=D("ZyWenda")->where($map)->order("ctime DESC")->findPage(20);
      $this->assign("wdlist",$wdlist);
      $this->display();
    }

    /**
     * 加载评论
     */
    public function wdcomm(){
        $map=array(
            'uid'=>$this->userid,
            'is_del'=>0,
            'parent_id'=>0,
        );
        $idstr="";
        $wendaIds=D("ZyWendaComment")->where($map)->field("wid")->order("ctime DESC")->select();
        $wendaIds= unique_arr($wendaIds,true);//去掉重复数据
        foreach($wendaIds as &$val){//拼装id
            $idstr .=$val['wid'].",";

        }
        $where['id']=array('in',trim($idstr,","));
        $where['is_del']=0;
        $where['uid']= array("neq",$this->userid);
        $wendaList=D("ZyWenda")->where($where)->order("ctime DESC")->findPage(3);
        foreach($wendaList['data'] as &$val){
            $val['commt']=D('ZyWendaComment')->where(array('uid'=>$this->userid,'wid'=>$val['id']))->order("ctime DESC")->find();
        }
       /* dump($wendaList);*/

        $this->assign("wdlist",$wendaList);
        $this->display();


    }

    /**
     * 加载笔记
     */
    public function note(){
        $map['uid']       = intval($this->userid);
        $map['parent_id'] = 0;
        $order = 'ctime DESC';
        $data = D("zyNote")->where($map)->order($order)->findPage(20);
       /* dump($data['data']);*/
        $this->assign("data",$data);
        $this->display();
    }

    //关注/粉丝
    public function fans(){
        $uid=intval($_GET['uid']);
        $follow = model('Follow');
        $user = model('User');
        $this->mid=$uid;

        $count = $follow->getFollowCount(array($this->mid));
        $count = $count[$this->mid];
        $type  = t($_GET['type']);
        if($type != 'follower'){
            $data = $follow->getFollowingList($this->mid, null, 5);
        }else{
            $data = $follow->getFollowerList($this->mid, 5);
        }
        foreach($data['data'] as &$rs){
            $rs['user'] = $user->getUserInfo($rs['fid']);
        }
        $fids = getSubByKey($data['data'], 'fid');
        $followState = $follow->getFollowStateByFids($this->mid, $fids);
        //print_r($followState);
        $this->assign('followState', $followState);
        $this->assign('data', $data);
        $this->assign('type', $type);
        $this->assign('count', $count);
        $this->display();
    }




}


?>