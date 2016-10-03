<?php
/**
 * Created by Ashang.
 * 云课堂教师风采控制器
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH . '/classroom/Lib/Action/CommonAction.class.php');
class TeacherAction extends CommonAction{
    protected $teacher = null;//讲师模型对象
    protected $passport=null;
    public function _initialize(){
        $this->teacher=D('ZyTeacher');
        $this->passport = model('Passport');
    }
    /**
     * 教师首页显示方法
     */
    public function index(){
        $subject_category=M("zy_subject_category")->findALL();
        $teacher_schedule=M("zy_teacher_schedule")->where("pid=0")->findALL();
        $this->assign("subject_category",$subject_category);
        $this->assign("teacher_schedule",$teacher_schedule);
        $this->display();
    }
    /**
     * 获取讲师列表方法
     */
    public function getList(){
        $where="t.is_del=0 ";
        $order="reservation_count desc";
        $subject_category= intval($_GET['subject_category']);
        $sort_type= intval($_GET['sort_type']);
        $reservation= $_GET['reservation'];
        $sex= intval($_GET['sex']);
        if($subject_category>0){
            $where.=" and subject_category=$subject_category";
        }
        if($sort_type>0){
            switch ($sort_type) {
                case 1:
                    $order="teacher_age desc";
                    break;
                case 2:
                    $order="review_count desc";
                    break;
            }
        }
        if($sex>0){
            $InSql="";
            $user = M("zy_teacher a ")->join(C('DB_PREFIX')."user u on a.uid=u.uid and u.uid")->where("sex=".$sex)->findALL();
            foreach ($user as $key => $value) {
                if(!strstr($InSql,$value["uid"])){
                    $InSql.=$value["uid"].",";
                }
            }
            $InSql=substr_replace($InSql, '', -1);
            $where .= " AND u.uid IN (". (string) $InSql . " )";

        }
        if($reservation>0){
            $InSql="";
            $result=M("zy_teacher_schedule")->where(array('id'=>array('IN',$reservation)))->findALL();
            foreach ($result as $key => $value) {
                $str=" teacher_schedule like '%".$value["id"]."%' and is_del=0 ";
                $tacher=M('zy_teacher')->where($str)->select();
                foreach ($tacher as $val) {
                   if(!strstr($InSql,$val["id"])){
                        $InSql.=$val["id"].",";
                    }
                }
            }
            $InSql=substr_replace($InSql, '', -1);
            $where .= " AND id IN (". (string) $InSql . " )";
        }
        $size=12;
        $data=M('zy_teacher t')->join(C('DB_PREFIX')."user u ON  u.uid=t.uid ")->where($where)->order($order)->findPage($size);
        if ($data['data']) {
             foreach ($data['data'] as $key => &$value) {
                $max_price=M("zy_teacher_course")->where("course_teacher=".$value["id"])->order("course_price desc")->field("course_price")->find();
                $min_price=M("zy_teacher_course")->where("course_teacher=".$value["id"])->order("course_price")->field("course_price")->find();
                $value["max_price"]=$max_price ? $max_price["course_price"]: 0;
                $value["min_price"]=$min_price ? $min_price["course_price"]: 0;
                $value["video"] = M('zy_video')->where('is_del=0 and teacher_id='.$value['id'])->order('video_order_count desc')->field('id,video_title')->find();
            }
            $this->assign('listData', $data['data']);
            $this->assign('subject_category', $subject_category);
            $this->assign('sex', $sex);
            $this->assign('reservation', $reservation);
            $this->assign('sort_type', $sort_type);
            $html = $this->fetch('index_list');
        }else{
            $html="<div style=\"margin-top:20px;\">对不起，没有找到符合条件的教师T_T</div>";
        }
        $data['data'] = $html;
        echo json_encode($data);
        exit;
    }
    /**
     * 讲师详情页面
     */
    public function view(){
        $id   = intval($_GET['id']);
        $data = $this->teacher->getTeacherInfo($id);
        $data["teacher_schedule"] = explode(",",$data["teacher_schedule"]);
        //教师课程
        $teacher_course=M("zy_teacher_course")->where("course_teacher=".$data['uid'])->findALL();
        $data["course_count"] = count($teacher_course);
        foreach ($teacher_course as $key => $value) {
            $teacher_review=M('zy_teacher_review')->where("course_id=".$value["course_id"])->field('star')->findAll();
            if($teacher_review){
                $review= $teacher_review;
            }
        }
        if($review){
            $data["star"]=intval(array_sum(getSubByKey($review,'star'))/count($review));
        }else{
            $data["star"]=0;
        }
        //课程安排表
        $teacher_schedule=M("zy_teacher_schedule")->where("pid=0")->findALL();
        $teacher_level=array();
        for ($i=0; $i <3 ; $i++) { 
            foreach ($teacher_schedule as $key => $value) {
                $level=M("zy_teacher_schedule")->where("pid=".$value["id"])->findALL();
                $teacher_level[$i][]=$level[$i];
            }
        }
        //可支配余额
        $data['balance'] = D("zyLearnc")->getUser($this->mid);
        
        //教师视频
        $video_count=M("zy_video")->where("teacher_id=".$id)->findALL();
        $data["video_count"]=count($video_count);
        //获取讲师推荐
        $recTeacher = M("zy_teacher")->where(array('id'=>array('neq',$id)))->order('reservation_count DESC')->limit(4)->select();
        //获取推荐的课程
        $recClass=M("zy_video")->field('id,teacher_id,video_title,cover')->order('video_order_count desc')->where('is_del=0')->limit(3)->select();
        //获取讲师名
        $name = M('zy_teacher')->where(array('uid'=>array('in',array_unique(getSubBykey($recClass,'teacher_id')))))->field('head_id','uid,name')->findAll();
        foreach($recClass as $k=>&$value){
            for($i=0;$i<count($name);$i++){
                if($value['teacher_id'] == $name[$i]['uid']){
                    $value['name'] = $name[$i]['name'];
                    $value['head_id'] = $name[$i]['head_id'];
                }
            }
        }
        $this->assign('teacher_level',$teacher_level);
        $this->assign('teacher_course',$teacher_course);
        $this->assign('recTeacher',$recTeacher);
        $this->assign('recClass',$recClass);
        $this->assign("data",$data);
        $this->assign("teacher_schedule",$teacher_schedule);
        $this->display();
    }
    /**
     * 获取讲师课程方法
     */
    public function getVideoList(){
        $teacher_id=intval($_GET['tid']);
        $order="id DESC";
        $time  = time();
        $where = "is_del=0 AND is_activity=1 AND uctime>$time AND listingtime<$time AND teacher_id= $teacher_id";
        $size=12;
        $data = D('ZyVideo')->where($where)->order($order)->findPage($size);
        foreach($data['data'] as &$val){
            $val['imageurl']=getAttachUrlByAttachId($val['cover']);

        }
        if ($data['data']) {
            $this->assign('listData', $data['data']);
            $html = $this->fetch('video_list');
        }else{
            $html="";
        }
        $data['data'] = $html;
        echo json_encode($data);
        exit;
    }
    public function getTeachNote(){
        $where="is_del=0";
        $order="ctime desc";
        $course_teacher= intval($_GET['id']);
        $inSql = "SELECT course_id FROM ".C('DB_PREFIX')."zy_teacher_course WHERE course_teacher=$course_teacher";
        $where .= " AND course_id IN($inSql)";
        $data=M("zy_teacher_review")->where($where)->order($order)->findPage(10);
        if($data['data']) {
            foreach ($data['data'] as $key => $value) {
                $data['data'][$key]["course_info"]=M("zy_teacher_course")->where("course_id=".$value["course_id"])->find();
                $data['data'][$key]["user_info"]=M("user")->where('uid='.$value["uid"])->field('uname')->find();
            }
            
            $this->assign('listData', $data['data']);
            $this->assign('course_teacher', $course_teacher);
            $this->assign('uid', $this->mid);
            $html = $this->fetch('teacher_note');
        }else{
            $html="<div style=\"margin-top:20px;\">对不起，暂无评论信息T_T</div>";
        }
        $data['data'] = $html;
        echo json_encode($data);
        exit;
    }
    /**
     * 讲师详情页面(老版)
     */
    public function viewdeatil(){
        $id=intval($_GET['id']);
        $teacher_info=$this->teacher->getTeacherInfo($id);
        if(!empty($teacher_info['uid'])){
         $this->assign("is_user",true);
        }
        //查询讲师最近课程
        $videoinfo=D('ZyVideo')->where(array('teacher_id'=>$id,'is_del'=>0))->field('id,video_title')->order('ctime DESC')->find();
        //查询相关课程个数
        $time  = time();
        $where = "is_del=0 AND is_activity=1 AND uctime>$time AND listingtime<$time AND teacher_id= $id";
        $count=D('ZyVideo')->where($where)->count();
        $this->assign('videoinfo',$videoinfo);
        $this->assign('count',$count);
        $this->assign("data",$teacher_info);
        $this->display();
    }
    public function addreview(){
        //要添加的数据
        $map=array(
        'course_id'=>intval($_POST['id']),
        'description'=>t($_POST['description']),
        'skill'=>t($_POST['skill']),
        'ctime'=>time(),
        'Professional'=>t($_POST['Professional']),
        'attitude'=>t($_POST['attitude']),
        'star'=>t($_POST['star']),
        'uid'=>$this->mid
        );
        $res=M("zy_teacher_review")->data($map)->add();
        if($res){
            $_data['review_count'] = array('exp','`review_count` + 1');
                //专辑
            M('zy_teacher')->where(array('id'=>array('eq',intval($_POST["teacher_id"]))))->save($_data);
            exit(json_encode(array('status'=>'1','info'=>'评论成功')));
        }else{
            exit(json_encode(array('status'=>'0','info'=>'评论失败')));
        }
    }
    public function delreview(){
        $id=intval($_GET["id"]);
        $res=M("zy_teacher_review")->where("id=".$id)->data(array("is_del"=>1))->save();
        if($res){
            $_data['review_count'] = array('exp','`review_count` - 1');
                //专辑
            M('zy_teacher')->where(array('id'=>array('eq',intval($_GET["teacher_id"]))))->save($_data);
            exit(json_encode(array('status'=>'1','info'=>'删除成功')));
        }else{
            exit(json_encode(array('status'=>'0','info'=>'删除失败')));
        }
    }
    public function buyCourse(){
        $map=array(
            'uid'=>$this->mid,
            'course_id'=>intval($_POST["course_id"]),
            'course_price'=>$_POST["course_price"],
            'teacher_id'=>intval($_POST["teacher_id"]),
            'teach_way'=>intval($_POST["teach_way"]),
            'ctime'=>time()
            );
        if(!$this->mid){
            $this->ajaxReturn('', '请先登录', '1');
        }
        if (!$_POST['course_id']) {
            $this->ajaxReturn('', '没有选择专辑', '1');
        }
        if (M("zy_order_course")->where(array("uid"=>$this->mid,"course_id"=>intval($_POST['course_id'])))->find()) {
            $this->ajaxReturn('', '您已预约此课程', '0');
        }
        if (!D('ZyLearnc')->isSufficient($this->mid, $_POST["course_price"], 'balance')) {
            $this->ajaxReturn('', '可支配的学币不足', '3');
        }
        $res=M("zy_order_course")->add($map);
        if($res){ 
            M()->query("UPDATE `".C('DB_PREFIX')."zy_teacher` SET `reservation_count`=`reservation_count`+1 WHERE `id`=".intval($_POST["teacher_id"]));
            //发送系统消息
            $teacher_name = M('teacher')->where('id='.$_POST['teacher_id'].' and is_del=0')->getField('name');
            $s['uid']   = $this->mid;
            $s['title'] = "恭喜您约课成功";
            $s['body']  = "恭喜您成功约课".$teacher_name."老师的课";
            $s['ctime'] = time();
            model('Notify')->sendMessage($s);
            exit(json_encode(array('status'=>'1','info'=>'约课成功')));
        }else{
            exit(json_encode(array('status'=>'0','info'=>'约课失败')));
        }
    }
    public function doLogin(){
        $login 		= addslashes($_POST['login_email']);
        $password 	= trim($_POST['login_password']);
        $remember	= intval($_POST['login_remember']);
        $result 	= $this->passport->loginLocal($login,$password,$remember);
        if(!$result){
            $status = 0;
            $info	= $this->passport->getError();
            $data 	= 0;
        }else{
            $this->redirect('/');
        }
    }
}

?>