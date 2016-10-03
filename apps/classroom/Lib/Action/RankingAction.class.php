<?php

/**
 * 排行榜控制器
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH . '/classroom/Lib/Action/CommonAction.class.php');

class RankingAction extends CommonAction {

    public function _initialize() {
        //关注
        $fids = model('Follow')->field('fid')->where('uid=' . intval($this->mid))->select();
        $this->assign('fids', getSubByKey($fids, 'fid'));
    }

    /**
     * 榜单家族
     * Enter description here ...
     */
    public function index() {

        //课程热销排行
        $data['video'] = $this->getSingleList(true, 10, 'video', 1);
        //专辑热销排行
        $data['album'] = $this->getSingleList(true, 10, 'album', 1);
        //每日新上
        unset($_POST);
        $_POST['order'] = 'day_list';
        $data['day_list'] = $this->getList(true, 1);
        //原创榜
        unset($_POST);
        $_POST['order'] = 'yuanchuang';
        $data['yuanchuang'] = $this->getList(true, 1);
        //精选
        unset($_POST);
        $_POST['order'] = 'jingxuan';
        $data['jingxuan'] = $this->getList(true, 1);
        //学霸争霸赛
        $this->assign($data);
        $this->setTitle("榜单家族");
        $this->display();
    }

    /**
     * 课程榜单
     */
    public function video() {
        $this->display("list_sp");
    }

    /**
     * 专辑榜单
     */
    public function album() {
        $this->display("list_zj");
    }

    public function getSingleList($return = false, $limit = 10, $type = 'video') {
        if (isset($_POST['type'])) {
            $type = t($_POST['type']);
            $limit = intval($_POST['limit']);
            $return = false;
        }
        $data = getRanking($limit, $type);
        foreach ($data['data'] as $key => $value) {
            if ($type == 'video') {
                $data['data'][$key]['is_buy'] = isBuyVideo($this->mid, $value['id']);
                $data['data'][$key]['price'] = getPrice(D("ZyVideo")->getVideoById($value['id'], $this->mid));
            } else {
                $data['data'][$key]['is_buy'] = isBuyAlbum($this->mid, $value['id']);
            }
            $data['data'][$key]['cover'] = getAttachUrlByAttachId($value['middle_ids']);
            $data['data'][$key]['star_score'] = $value['score'] / 20;
        }
        if ($data['data']) {
            if ($type == 'video') {
                $this->assign('limit', $limit);
                $this->assign('data', $data);
                $vms = D('ZyVideoMerge')->getList($this->mid, session_id());
                $this->assign('vms', getSubByKey($vms, 'video_id'));
                $html = $this->fetch('video_list');
            } else if ($type == 'album') {
                $this->assign('limit', $limit);
                $this->assign('data', $data);
                $html = $this->fetch('album_list');
            }
        }
        if ($return) {
            return $data;
        } else {
            $data['_data'] = $data['data'];
            $data['data'] = $html;
            echo json_encode($data);
            exit;
        }
    }

    /**
     * 每日新上
     */
    public function day_list() {
        $this->display("list_bd");
    }

    /**
     * 精选
     */
    public function jingxuan() {
        $this->display("list_jx");
    }

    /**
     * 原创
     */
    public function yuanchuang() {
        $this->display("list_yc");
    }

    /**
     * 争霸赛榜单
     */
    public function zhengba() {
        $this->display("list_zb");
    }

    /**
     * 获取数据
     */
    public function getList($return = false, $page = 1) {
        $orders = array(
            'default' => ' ORDER BY `order_count` DESC',
            'yuanchuang' => ' ORDER BY `order_count` DESC',
            'jingxuan' => ' ORDER BY `be_sort` DESC',
            'day_list' => ' ORDER BY `ctime` DESC, `order_count` DESC',
        );
        $album_wheres = array(
            'default' => ' WHERE `is_del` = 0 AND (`uctime` > ' . time() . ' AND `listingtime` < ' . time() . ')',
            'yuanchuang' => ' WHERE `is_del` = 0 AND (`uctime` > ' . time() . ' AND `listingtime` < ' . time() . ')',
            'jingxuan' => '  WHERE `is_del` = 0 AND (`uctime` > ' . time() . ' AND `listingtime` < ' . time() . ') AND `best_recommend` = 1',
            'day_list' => ' WHERE `is_del` = 0 AND (`uctime` > ' . time() . ' AND `listingtime` < ' . time() . ')',
        );
        $video_wheres = array(
            'default' => ' WHERE `is_del` = 0 AND `is_activity` = 1 AND (`uctime` > ' . time() . ' AND listingtime < ' . time() . ')',
            'yuanchuang' => ' WHERE `is_del` = 0 AND `is_activity` = 1 AND (`uctime` > ' . time() . ' AND listingtime < ' . time() . ') AND `is_original` = 1',
            'jingxuan' => '  WHERE `is_del` = 0 AND `is_activity` = 1 AND (`uctime` > ' . time() . ' AND listingtime < ' . time() . ') AND `best_recommend` = 1',
            'day_list' => ' WHERE `is_del` = 0 AND `is_activity` = 1 AND (`uctime` > ' . time() . ' AND `listingtime` < ' . time() . ')',
        );
        $limit = 10;
        if (isset($_POST['p'])) {
            $page = intval($_POST['p']);
            $limit = intval($_POST['limit']);
            $return = false;
        }
        if (isset($orders[$_POST['order']])) {
            $order = $orders[$_POST['order']];
            $album_where = $album_wheres[$_POST['order']];
            $video_where = $video_wheres[$_POST['order']];
        } else {
            $order = $orders['default'];
            $album_where = $album_wheres['default'];
            $video_where = $video_wheres['default'];
        }
        $data = getRankingMix($limit, $order, $page, $album_where, $video_where);
        foreach ($data['data'] as $key => $value) {
            if ($value['type'] == 1) {
                $data['data'][$key]['is_buy'] = isBuyVideo($this->mid, $value['id']);
                $data['data'][$key]['price'] = getPrice(D("ZyVideo")->getVideoById($value['id'], $this->mid));
            } else {
                $data['data'][$key]['is_buy'] = isBuyAlbum($this->mid, $value['id']);
            }
            $data['data'][$key]['cover'] = getAttachUrlByAttachId($value['middle_ids']);
            $data['data'][$key]['star_score'] = $value['score'] / 20;
        }
        if ($data) {
            $this->assign('limit', $limit);
            $this->assign('data', $data);
            $vms = D('ZyVideoMerge')->getList($this->mid, session_id());
            $this->assign('vms', getSubByKey($vms, 'video_id'));
            $html = $this->fetch("fetch_list");
        }
        if ($return) {
            return $data;
        } else {
            $data['_data'] = $data['data'];
            $data['data'] = $html;
            echo json_encode($data);
            exit;
        }
    }

}
