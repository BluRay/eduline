<?php

/**
 * 订单管理
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH . '/admin/Lib/Action/AdministratorAction.class.php');

class AdminOrderAction extends AdministratorAction {

    //课程订单模型对象
    protected $order = null;
    //专辑订单模型对象
    protected $orderAlbum = null;
    //约课订单模型对象
    protected $orderCourse = null;
    /**
     * 初始化，配置页面标题；创建模型对象
     * @return void
     */
    public function _initialize() {
        parent::_initialize();
        $this->pageTab[] = array('title' => '课程订单', 'tabHash' => 'index', 'url' => U('classroom/AdminOrder/index'));
        $this->pageTab[] = array('title' => '专辑订单', 'tabHash' => 'album', 'url' => U('classroom/AdminOrder/album'));
        $this->pageTab[] = array('title' => '约课订单', 'tabHash' => 'course', 'url' => U('classroom/AdminOrder/course'));
        $this->pageTitle['index'] = '课程订单 - 交易记录';
        $this->pageTitle['album'] = '专辑订单 - 交易记录';
        $this->pageTitle['course'] = '约课订单 - 交易记录';
        //默认搜索提交地址
        $this->searchPostUrl = U(APP_NAME . '/' . MODULE_NAME . '/' . ACTION_NAME, array('tabHash' => ACTION_NAME));
        //实例化模型
        $this->orderAlbum = D('ZyOrderAlbum');
        $this->order = D('ZyOrder');
        $this->orderCourse = D('ZyOrderCourse');
    }
    /**
     * 约课订单列表
     */
    public function course(){
        //显示字段
        $this->pageKeyList = array('id', 'uname', 'teacher_name','course_name', 'course_price','teach_way','is_del','ctime');
        //页面按钮
        $this->pageButton[] = array('title' => '搜索记录', 'onclick' => "admin.fold('search_form')");
        //搜索字段
        $this->searchKey = array('id', 'uid', 'course_name','teacher_name');
        $this->searchPostUrl = U('classroom/AdminOrder/course');
        $map = array();
        if (!empty($_POST['id'])) {
            $map['id'] = $_POST['id'];
        }else{
            //根据用户查找
            if (!empty($_POST['uid'])) {
                $_POST['uid'] = t($_POST['uid']);
                $map['uid'] = array('in', $_POST['uid']);
            }
            //课程ID
            if (!empty($_POST['course_name'])) {
                $course=M("zy_teacher_course")->where("course_name like"."'%".$_POST["course_name"]."%'")->field("course_id")->find();
                $map['course_id'] = $course["course_id"];
            }
            //教师ID
            if (!empty($_POST['teacher_name'])) {
                $tecaher=M("zy_teacher")->where("name like"."'%".$_POST["teacher_name"]."%'")->field("id")->find();
                $map['teacher_id'] = $tecaher["id"];
            }
        }
        //取得数据列表
        $listData = $this->orderCourse->where($map)->order('ctime DESC')->findPage();
        //整理数据列表
        foreach ($listData['data'] as $key => $val) {
            $listData['data'][$key]["uname"]=getUserSpace($val['uid'], null, '_blank');
            $listData['data'][$key]["teach_way"]=$this->orderCourse->teacherWay($val["teach_way"]);
            $listData['data'][$key]["teacher_name"]=$this->orderCourse->teacherId($val["teacher_id"]);
            $listData['data'][$key]["course_name"]=$this->orderCourse->courseId($val["course_id"]);
            $listData['data'][$key]["ctime"]=date("Y-m-d H:i:s",$val['ctime']);
            $listData['data'][$key]["is_del"]=$val["is_del"]==0 ? "<span style='color:green'>未删除</span>" : "<span style='color:red'>已删除</span>";
            $listData['data'][$key]["course_price"] = '<span style="color:green">'.$val['course_price'].'</span>&nbsp;元';
        }
        $this->displayList($listData);
    }
    /**
     * 课程订单列表
     */
    public function index() {
        //显示字段
        $this->pageKeyList = array(
            'id', 'uid', 'muid', 'video_id', 'old_price', 'discount',
            'discount_type', 'price', 'album_title', 'percent',
            'user_num', 'master_num', 'learn_status', 'ctime', 'DOACTION'
        );
        //页面按钮
        $this->pageButton[] = array('title' => '搜索记录', 'onclick' => "admin.fold('search_form')");
        //搜索字段
        $this->searchKey = array('id', 'uid', 'muid', 'video_id', 'order_album_id', 'startTime', 'endTime');

        //where
        $map = array();
        if (!empty($_POST['id'])) {
            $map['id'] = $_POST['id'];
        } else {
            //根据用户查找
            if (!empty($_POST['uid'])) {
                $_POST['uid'] = t($_POST['uid']);
                $map['uid'] = array('in', $_POST['uid']);
            }
            //根据商家查找
            if (!empty($_POST['muid'])) {
                $_POST['muid'] = t($_POST['muid']);
                $map['muid'] = array('in', $_POST['muid']);
            }
            //课程ID
            if (!empty($_POST['video_id'])) {
                $map['video_id'] = $_POST['video_id'];
            }
            //专辑订单ID
            if (!empty($_POST['order_album_id'])) {
                $map['order_album_id'] = $_POST['order_album_id'];
            }
            //开始时间
            if (!empty($_POST['startTime'])) {
                $map['ctime'][] = array('gt', strtotime($_POST['startTime']));
            }
            //结束时间
            if (!empty($_POST['endTime'])) {
                $map['ctime'][] = array('lt', strtotime($_POST['endTime']));
            }
        }
        //取得数据列表
        $listData = $this->order->where($map)->order('ctime DESC,id DESC')->findPage();
        //整理数据列表
        foreach ($listData['data'] as $key => $val) {
            $val = $this->formatData($val);
            $val['DOACTION'] = '<a href="' . U(APP_NAME . '/' . MODULE_NAME . '/viewOrder', array('id' => $val['id'], 'tabHash' => 'viewOrder')) . '">查看详细</a>';
            $listData['data'][$key] = $val;
        }
        $this->displayList($listData);
    }

    /**
     * 专辑订单列表
     * @return void
     */
    public function album() {
        //显示字段
        $this->pageKeyList = array(
            'id', 'uid', 'cuid', 'album_id', 'price', 'ctime', 'DOACTION'
        );
        //页面按钮
        $this->pageButton[] = array('title' => '搜索记录', 'onclick' => "admin.fold('search_form')");
        //搜索字段
        $this->searchKey = array('id', 'uid', 'cuid', 'album_id', 'startTime', 'endTime');

        //where
        $map = array();
        if (!empty($_POST['id'])) {
            $map['id'] = $_POST['id'];
        } else {
            //根据用户查找
            if (!empty($_POST['uid'])) {
                $_POST['uid'] = t($_POST['uid']);
                $map['uid'] = array('in', $_POST['uid']);
            }
            //根据商家查找
            if (!empty($_POST['cuid'])) {
                $_POST['cuid'] = t($_POST['cuid']);
                $map['cuid'] = array('in', $_POST['cuid']);
            }
            //专辑ID
            if (!empty($_POST['album_id'])) {
                $map['album_id'] = $_POST['album_id'];
            }
            //开始时间
            if (!empty($_POST['startTime'])) {
                $map['ctime'][] = array('gt', strtotime($_POST['startTime']));
            }
            //结束时间
            if (!empty($_POST['endTime'])) {
                $map['ctime'][] = array('lt', strtotime($_POST['endTime']));
            }
        }

        //查询数据列表
        $listData = $this->orderAlbum->where($map)->order('ctime DESC,id DESC')->findPage();
        //整理数据列表
        foreach ($listData['data'] as $key => $val) {

            $val['ctime'] = friendlyDate($val['ctime']);
            $val['uid'] = getUserSpace($val['uid'], null, '_blank');
            $val['cuid'] = getUserSpace($val['cuid'], null, '_blank');
            $val['album_id'] = getAlbumNameForID($val['album_id']);
            $val['price'] = '<span style="color:red">￥' . $val['price'] . '</span>';
            $val['DOACTION'] = '<a href="' . U(APP_NAME . '/' . MODULE_NAME . '/albumOrderList', array('id' => $val['id'], 'tabHash' => 'albumOrderList')) . '">查看课程订单</a>';

            $listData['data'][$key] = $val;
        }
        $this->displayList($listData);
    }

    /**
     * 专辑的课程订单列表
     * @return void
     */
    public function albumOrderList() {
        //显示字段
        $this->pageKeyList = array(
            'id', 'uid', 'muid', 'video_id', 'old_price', 'discount',
            'discount_type', 'price', 'album_title', 'percent',
            'user_num', 'master_num', 'learn_status', 'ctime', 'DOACTION'
        );

        $_GET['id'] = intval($_GET['id']);

        $this->pageTab[] = array('title' => '查看课程订单-专辑订单ID:' . $_GET['id'], 'tabHash' => 'albumOrderList', 'url' => U('classroom/AdminOrder/albumOrderList', array('id' => $_GET['id'])));
        //页面按钮
        $this->pageButton[] = array('title' => '&lt;&lt;&nbsp;返回来源页', 'onclick' => "admin.zyPageBack()");
        $this->pageTitle['albumOrderList'] = '专辑订单 - 查看课程订单';
        //取得专辑ID
        $albumId = $this->orderAlbum->getAlbumIdById($_GET['id']);
        $vl = D('ZyAlbum')->getVideoId($albumId); //取得专辑的课程IDList
        $rows = $this->order->getAlbumOrderList($_GET['id'], $vl);
        foreach ($rows as $key => $val) {
            $val = $this->formatData($val);
            $val['DOACTION'] = '<a href="' . U(APP_NAME . '/' . MODULE_NAME . '/viewOrder', array('id' => $val['id'], 'tabHash' => 'viewOrder')) . '">查看详细</a>';
            $rows[$key] = $val;
        }
        $data['count'] = intval(count($rows));
        $data['totalPages'] = 1;
        $data['totalRows'] = $data['count'];
        $data['nowPage'] = $data['nowPage'];
        $data['html'] = '';
        $data['data'] = $rows;
        $this->displayList($data);
    }

    /**
     * 查看课程订单
     * @return void
     */
    public function viewOrder() {
        //不允许更改
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $url = U(APP_NAME . '/' . MODULE_NAME . '/index');
            $this->redirect($url);
            exit;
        }

        $_GET['id'] = intval($_GET['id']);

        $this->pageTab[] = array('title' => '查看课程订单-ID:' . $_GET['id'], 'tabHash' => 'viewOrder', 'url' => U('classroom/AdminOrder/viewOrder', array('id' => $_GET['id'])));
        //显示字段
        $this->pageKeyList = array(
            'id', 'ctime', 'uid', 'muid', 'video_id', 'old_price', 'discount',
            'discount_type', 'price', 'album_title', 'percent',
            'user_num', 'master_num', 'learn_status'
        );
        //点击按钮返回来源页面
        $this->submitAlias = '返 回';
        $this->onsubmit = 'admin.zyPageBack()';
        $this->pageTitle['viewOrder'] = '课程订单  - 查看详细';
        $this->savePostUrl = U(APP_NAME . '/' . MODULE_NAME . '/' . ACTION_NAME);

        $data = $this->order->find($_GET['id']);
        if (!$data)
            $this->error('没有找到对应的订单记录');

        $data = $this->formatData($data);
        $this->displayConfig($data);
    }

    /**
     * 数据显示格式化
     * @param $val 一个结果集数组
     * @return array
     */
    protected function formatData($val) {
        //学习状态
        $learn_status = array('未开始', '学习中', '已完成');
        //折扣类型
        $discount_type = array('<span style="color:gray">无折扣</span>', '会员折扣', '限时优惠');
        //取得专辑订单的专辑ID
        if ($val['order_album_id'] > 0) {
            $albumId = $this->orderAlbum->getAlbumIdById($val['order_album_id']);
            $val['album_title'] = getAlbumNameForID($albumId);
        } else {
            $val['album_title'] = ACTION_NAME == 'albumOrderList' ? '<span style=color:gray>单独购买</span>' : '-';
        }
        //购买用户
        $val['uid'] = getUserSpace($val['uid'], null, '_blank');
        //课程卖家|商家
        $val['muid'] = getUserSpace($val['muid'], null, '_blank');
        //课程学习状态
        $val['learn_status'] = $learn_status[$val['learn_status']];
        //取得课程名称
        $val['video_id'] = '<div style="width:300px;">' . getVideoNameForID($val['video_id']) . '</div>';

        //价格和折扣
        $val['old_price'] = '<span style="text-decoration:line-through;">￥' . $val['old_price'] . '</span>';
        $val['price'] = '<span style="color:red">￥' . $val['price'] . '</span>';
        $val['discount_type'] = $discount_type[$val['discount_type']];
        if ($val['discount_type'] > 0) {
            $val['discount'] = $val['discount'] . '折';
        } else {
            $val['discount'] = '-';
        }
        //返佣分成
        $val['percent'] = $val['percent'] ? $val['percent'] . '%' : '-';
        $val['user_num'] = $val['user_num'] ? '￥' . $val['user_num'] : 0;
        $val['master_num'] = $val['master_num'] ? '￥' . $val['master_num'] : 0;

        //购买时间
        $val['ctime'] = ACTION_NAME == 'viewOrder' ? date('Y-m-d H:i:s') : friendlyDate($val['ctime']);

        return $val;
    }
}
