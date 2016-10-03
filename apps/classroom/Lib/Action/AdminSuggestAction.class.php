<?php
/**
 * 意见反馈管理
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class AdminSuggestAction extends AdministratorAction{

    /**
     * 初始化，配置内容标题
     * @return void
     */
    public function _initialize(){
        parent::_initialize();
        $this->pageTitle['index'] = '意见反馈列表';
    }


    /**
     * 意见反馈列表
     * @return void
     */
    public function index(){

        //页面配置
        $this->pageKeyList = array('id','uid','content','ctime','DOACTION');

        $this->pageButton[] = array('title'=>'搜索','onclick'=>"admin.fold('search_form')");
        $this->pageButton[] = array('title'=>'删除意见反馈','onclick'=>"admin.delSuggest()");

        $this->searchKey = array('uid','content');

        $map = array();
        if(!empty($_POST['uid'])){
            $_POST['uid'] = t($_POST['uid']);
            $map['uid']   = array('in', $_POST['uid']);
        }
        if(!empty($_POST['content'])){
            $_POST['content'] = htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8');
            $map['content'] = array('like', "%{$_POST['content']}%");
        }

        //数据列表
        $listData = D('ZySuggest')->where($map)->order('ctime DESC,id DESC')->findPage();
        foreach($listData['data'] as $key=>$val){
            $val['ctime']    = friendlyDate($val['ctime']);
            $val['uid']      = $val['uid']?getUserSpace($val['uid'], null, '_blank'):'游客';
            $val['DOACTION'] = '<a href="javascript:;" onclick="admin.delSuggest('.$val['id'].');">删除</a>';
            $val['content']  = '<div style="width:500px">'.$val['content'].'</div>';
            $listData['data'][$key] = $val;
        }

        $this->displayList($listData);
    }

    /**
     * 删除意见反馈
     * @return void
     */
    public function del(){
        if(is_array($_POST['id'])){
            $_POST['id'] = implode(',', $_POST['id']);
        }
        $id = "'".str_replace(array("'", ','), array('', "','"), $_POST['id'])."'";
        if(D('ZySuggest')->where("id IN($id)")->delete()){
            $this->ajaxReturn('删除成功');
        }else{
            $this->ajaxReturn('删除失败');
        }
    }

}