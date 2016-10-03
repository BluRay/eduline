<?php
/**
 * 讲师api
 * utime : 2016-03-06
 */

class TeacherApi extends Api{
     private $teacher;

    public function __construct(){
        parent::__construct();
        $this->teacher = M('ZyTeacher');

    }
    /**
     * Eduline获取讲师列表接口
     * 参数：
     * page 页数
     * count 每页条数
     * return   用户数据或者登录错误提示
     */
    public function getTeacherList(){
		$teacherData = $this->teacher->where(array('is_del'=>0))->order('ctime DESC')->limit($this->_limit())->select();
			if( !$teacherData ) {
      		$this->exitJson( array() );
		}
        //计算每个教师的课程总数
		foreach($teacherData as &$val){
			$time  = time();
			$where = "is_del=0 AND is_activity=1 AND uctime>$time AND listingtime<$time AND teacher_id= ".$val['id'];
			$val['video_cont'] = D('ZyVideo')->where($where)->count();
			$val['headimg']    = getCover($val['head_id'] , 150 , 150 );
		}
		$this->exitJson($teacherData);
    }
    
    /**
     * 搜索教师
     */
    public function searchTeacher(){
    	$map['name']   = array('like' , '%'.t($this->data['name']).'%');
    	$map['is_del'] = 0;
    	$teacherData = $this->teacher->where($map)->order('ctime DESC')->limit($this->_limit())->select();
    	if( !$teacherData ) {
			$this->exitJson( array() );
    	}
    	//计算每个教师的课程总数
    	foreach($teacherData as &$val){
    		$time  = time();
    		$where = "is_del=0 AND is_activity=1 AND uctime>$time AND listingtime<$time AND teacher_id= ".$val['id'];
    		$val['video_cont']   = D('ZyVideo')->where($where)->count();
    		$val['headimg']      = getCover($val['head_id']);
    	}
    	$this->exitJson($teacherData);
    }

    /**
     * Eduline获取讲师详情
     * teacher_id 讲师id
     * return   讲师的详情
     */
    public function  getTeacher(){
        $teacherId = intval($this->data['teacher_id']);//获取讲师id
        if(!$teacherId){
            $this->exitJson( array() ,10005,"没有讲师id");
        }
        $teacherInfo = $this->teacher->where(array('id'=>$teacherId,'is_del'=>0))->find();
        $teacherInfo['headimg'] = getCover($teacherInfo['head_id'] , 150 , 150);
        $teacherInfo['follow_state'] = model('Follow')->getFollowState($this->mid , $teacherInfo['uid']);
        $this->exitJson($teacherInfo);
    }

    /**
     * 获取讲师相关课程列表
     * 参数：
     * teacher_id 讲师id
     * return   讲师相关课程列表详情
     */
    public function teacherVideoList(){
        $teacher_id = intval( $this->data['teacher_id'] );//获取讲师id
        if(!$teacher_id){
            $this->exitJson( array() ,10005,"没有讲师id");
        }
        $order="id DESC";
        $time  = time();
        $where = "is_del=0 AND is_activity=1 AND uctime>$time AND listingtime<$time AND teacher_id= $teacher_id";
        $data = D('ZyVideo')->where($where)->order($order)->limit($this->_limit())->select();
        if( !$data ) {
        	$this->exitJson( array() );
        }
		foreach($data as $key => &$val){
            $val['cover']   = getCover( $val['cover'] , 280 , 160);
            $val['mzprice'] = getPrice ( $val, $this->mid, true, true );
        }
        $this->exitJson($data);
    }
    
    //获取专辑讲师列表
    public function groupTeacherList(){
        $albumid = intval($this->data['id']);//获取课程/专辑的id
        //查询专辑中的课程
        $videoStr = trim( D('ZyAlbum', 'classroom')->getVideoId( $albumid ) , ',');
        $where['id']         = array('in',$videoStr);
        $where['teacher_id'] = array("gt",0);
        $videoList = M('ZyVideo')->where($where)->field("teacher_id")->select();
		foreach($videoList as $key=>$val){
			$videoList[$key] = $val['teacher_id'];
        }
      	$videoList = array_flip(array_flip($videoList));//去掉重复讲师id
      	$videoList = implode(',' , $videoList);
        $str = trim($videoList, ',');
        $teacherList = M('ZyTeacher')->where(array("id"=>array('in',trim($str,','))))->select();
        foreach($teacherList as &$val){
            $val['headimg'] = getCover($val['head_id'] , 150 , 150);
        }
        $this->exitJson($teacherList);
    }


}








?> 