<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-10-16
 * Time: 下午7:10
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class AdminIndexAction extends AdministratorAction{
    /**
     * 初始化，初始化页面表头信息
     */
    public function _initialize() {
        parent::_initialize();
    }

    public function index(){
        // 页面具有的字段，可以移动到配置文件中！！！
        $this->pageKeyList = array( 'id','type','username','wd_description','wd_comment_count','wd_browse_count','DOACTION');
        $this->searchKey = array('id','uid','type','wd_description');
        $this->opt['type']    = array('0'=>'不限','1'=>'技术问答','2'=>'技术分享','3'=>'活动建议');
        $this->pageButton[] = array('title'=>'删除问答','onclick'=>"admin.delWendaAll('delWenda')");
        $this->pageButton[] = array('title'=>'搜索问答','onclick'=>"admin.fold('search_form')");
        $this->assign('pageTitle','问答管理');
        $map=array(
            'is_del'=>0,
        );
        $type=intval($_POST['type']);
        if(!empty($type)){
            $map['type']=$type;
        }
        $id=intval($_POST['id']);
        if(!empty($id)){
            $map['id'] =$id;
        }
        $wd_title=t($_POST['wd_description']);
        if(!empty($wd_title)){
            $map['wd_description']=array('like',"%{$_POST['wd_title']}%");
        }
        $uid=intval($_POST['uid']);
        if(!empty($uid)){
            $map['uid']=$uid;
        }
        $wdlist=D("ZyWenda")->where($map)->order("recommend DESC , ctime DESC")->findPage(20);
        //格式化数据
        foreach($wdlist['data'] as &$val){
            $val['type']=getWendaTypeName($val['type']);
            $val['username']=getUserName($val['uid']);
            $val['wd_description']="<a target='_blank' href='".U('wenda/Index/detail',array('id'=>$val['id']))."'>".getShort( t($val['wd_description']) ,50)."</a>";
            //判断是否是置顶内容
            if($val['recommend']!=1){
                $val['DOACTION'].="<a href='".U('wenda/AdminIndex/hotWenda',array('id'=>$val['id']))."'>置顶</a>";
            }else{
                $val['DOACTION'].="<a href='".U('wenda/AdminIndex/closeHot',array('id'=>$val['id']))."'>取消置顶</a>";
            }
            //添加删除按钮
            $val['DOACTION'].=" | <a href=javascript:admin.delWenda(".$val['id'].",'delWenda');>删除问答</a>";

        }
        $this->_listpk = 'id';
        $this->displayList($wdlist);
    }

    /**
     * 置顶
     */
    public function hotWenda(){
        $wid=intval($_GET['id']);
        if(empty($wid)){
            $this->error("此问答不存在！");
        }
        $wdinfo=D('ZyWenda')->where(array('id'=>$wid,'is_del'=>0))->find();
        if(!$wdinfo){
            $this->error("此问答不存在或已被删除！");
        }
        $data['recommend']=1;
        $res=D('ZyWenda')->where(array('id'=>$wid))->save($data);
        if($res!==false){
            $this->success("置顶成功!");
        }else{
            $this->error("置顶失败！");
        }


    }
    /**
     * 取消置顶
     */
    public function closeHot(){
        $wid=intval($_GET['id']);
        if(empty($wid)){
            $this->error("此问答不存在！");
        }
        $wdinfo=D('ZyWenda')->where(array('id'=>$wid,'is_del'=>0))->find();
        if(!$wdinfo){
            $this->error("此问答不存在或已被删除！");
        }
        $data['recommend']=0;
        $res=D('ZyWenda')->where(array('id'=>$wid))->save($data);
        if($res!==false){
            $this->success("取消置顶成功!");
        }else{
            $this->error("取消置顶失败！");
        }

    }

    /**
     * 删除问答
     */
    public function delWenda(){
        $ids=implode(",",$_POST['ids']);

        $ids=trim(t($ids),",");
        if($ids==""){
            $ids=intval($_POST['ids']);
        }
        $msg=array();
        $where=array(
            'id'=>array('in',$ids)
        );
        $data['is_del']=1;
        $res=D('ZyWenda')->where($where)->save($data);
        //echo D('ZyWenda')->getLastSql();
        if($res!==false){
            $msg['data']=L('PUBLIC_DELETE_SUCCESS');
            $msg['status']=1;
            echo json_encode($msg);
        }else{
            $msg['data']="删除失败!";
            echo json_encode($msg);
        }
    }




}


?>