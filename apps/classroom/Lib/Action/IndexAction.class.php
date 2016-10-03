<?php

/**
 * Eduline课堂首页控制器
 * @author Ashang <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH . '/classroom/Lib/Action/CommonAction.class.php');
class IndexAction extends CommonAction {
    /**
     * Eduline课堂首页方法
     * @return void
     */
    public function index() {
        //加载首页头部轮播广告位
        $ad_map = array(
            'is_active' => 1,
            'display_type' => 3,
            'place' => 15
        );
        $ad_list = M('ad')->where($ad_map)->order('display_order DESC')->find();
        //序列化广告内容
        $ad_list = unserialize($ad_list['content']);
        //获取精选专辑
        $best_recommend_list = D('ZyAlbum')->getBestRecommend();
        //格式化精选专辑 3条带封面 其余标题连接
        $br_list = array(
            'big_list' => array() ,
            'title_list' => array()
        );
        $br_le = 0;
        foreach ($best_recommend_list as $bl) {
            $br_le+= 1;
            if ($br_le <= 3) {
                array_push($br_list['big_list'], $bl);
            } else {
                array_push($br_list['title_list'], $bl);
            }
        }
        //获取畅销榜单
        $get_sell_well_list = D('ZyVideo')->getSellWell(10);
        foreach ($get_sell_well_list as & $value) {
            $value['imageurl'] = getAttachUrlByAttachId($value['cover']);
        }
        //购物车
        $vms = D('ZyVideoMerge')->getList($this->mid, session_id());
        $this->assign('vms', getSubByKey($vms, 'video_id'));
        //加载限时免费
        $map['is_del'] = '0';
        $map['is_activity'] = '1';
        $map['uctime'] = array(
            'GT',
            time()
        );
        $map['listingtime'] = array(
            'LT',
            time()
        );
        $map['limit_discount'] = 0.00;
        $map['is_tlimit'] = '1';
        $map['starttime'] = array(
            'LT',
            time()
        );
        $map['endtime'] = array(
            'GT',
            time()
        );
        $free_limit_list = M('zy_video')->where($map)->order('ctime DESC')->limit(5)->select();
        foreach ($free_limit_list as $key => &$val) {
            if ($val['uid'] == $GLOBALS['ts']['mid']) {
                $val['t_price'] = 0;
            }
            $val['mzprice'] = getPrice ( $val, $this->mid, true, true );
        }
        //加载热门问答
        $wenda = D('ZyWenda')->query("select * from ".C('DB_PREFIX')."zy_wenda  where DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= ctime and is_del=0 ORDER BY wd_comment_count DESC limit 6");
        foreach ($wenda as $key => &$val) {
            $val['ctime'] = getDateDiffer($val['ctime']); //格式化时间数据
            $val['tags'] = D('ZyWenda', 'wenda')->getWendaTags($val['tag_id']); //取出问答的标签
            //$val['wd_comment'] = D("ZyWendaComment", 'wenda')->getNowWenda($val['id'], 1); //取最新的一条评论
            $val['wd_description'] = t($val['wd_description']);
        }
        $time = time();
        //根据销售最佳读取最佳讲师等信息
        $beVideos = M()->query("SELECT zv.`id`, zv.`teacher_id`,zv.`video_title`,zt.`name`,zt.`inro`,zt.`head_id` FROM `".C('DB_PREFIX')."zy_video` zv,`".C('DB_PREFIX')."zy_teacher` zt WHERE zv.teacher_id=zt.id AND zt.id AND zt.is_del=0 and zv.`is_del`=0 AND `is_activity`=1 AND `uctime`>'$time' AND `listingtime`<'$time' and teacher_id >0 GROUP BY `teacher_id` ORDER BY `video_order_count` DESC ,`id` DESC  LIMIT 4");
        if(!$beVideos){
            $beVideos = M()->query("SELECT `id` as teacher_id,`name`,`inro`,`head_id` FROM `".C('DB_PREFIX')."zy_teacher` WHERE is_del=0 and course_count>0 order by `id` DESC  LIMIT 4");
        }
        $this->assign("wenda", $wenda);
        $this->assign("beTeacher", $beVideos);
        $this->assign("ad_list", $ad_list);
        $this->assign("br_list", $br_list);
        $this->assign("sw_list", $get_sell_well_list);
        $this->assign("free_limit_list", $free_limit_list);
        $this->assign('mid', $this->mid);
        $this->display();
    }
    /**
     * 最佳原创
     * @return void
     */
    public function get_original_recommend() {
        //数据分类 1:课程;2:专辑;
        $limit = 1;
        $zy_albumtable = C('DB_PREFIX') . 'zy_album';
        $zy_videotable = C('DB_PREFIX') . 'zy_video';
        //专辑
        $album_table = "SELECT `id`,`re_sort`,2 as `type`,`album_category` as `category`,`uid`,`cover`,`album_title` as `title`,`album_score` as `score`,`album_intro` as `intro` FROM `{$zy_albumtable}` WHERE `is_del`=0 AND `original_recommend`=1";
        echo $album_table . "<br/>";
        //课程
        $video_table = "SELECT `id`,`re_sort`,1 as `type`,`video_category` as `category`,`uid`,`cover`,`video_title` as `title`,`video_score` as `score`,`video_intro` as `intro` FROM `{$zy_videotable}` WHERE `is_del`=0 AND `original_recommend`=1 AND `is_activity`=1 ";
        echo $video_table . "<br/>";
        //拼接总的数据
        $sql = "SELECT * FROM ({$album_table} UNION {$video_table}) as `mysellwell` ORDER BY `re_sort` DESC  LIMIT 0,{$limit}";
        echo $sql;
        die();
        //处理和返回
        $this->dealAndReturn($sql);
    }
    /**
     * 编辑精选
     * @return void
     */
    public function get_best_recommend() {
        //数据分类 1:课程;2:专辑;
        $limit = intval($_POST['limit']);
        $zy_albumtable = C('DB_PREFIX') . 'zy_album';
        $zy_videotable = C('DB_PREFIX') . 'zy_video';
        //专辑
        $album_table = "SELECT `id`,`be_sort`,2 as `type`,`album_category` as `category`,`uid`,`is_offical`,`big_ids`,`middle_ids`,`small_ids`,`album_title` as `title`,`album_score` as `score`,`album_intro` as `intro` FROM `{$zy_albumtable}` WHERE `is_del`=0 AND `best_recommend`=1";
        echo $album_table . "<br/>";
        //课程
        $video_table = "SELECT `id`,`be_sort`,1 as `type`,`video_category` as `category`,`uid`,`is_offical`,`big_ids`,`middle_ids`,`small_ids`,`video_title` as `title`,`video_score` as `score`,`video_intro` as `intro` FROM `{$zy_videotable}` WHERE `is_del`=0 AND `best_recommend`=1 AND `is_activity`=1 ";
        echo $video_table . "<br/>";
        //拼接总的数据
        $sql = "SELECT * FROM ({$album_table} UNION {$video_table}) as `mysellwell` ORDER BY `be_sort` DESC  LIMIT 0,{$limit}";
        echo $sql;
        die();
        //处理和返回
        $this->dealAndReturn($sql);
    }
    /**
     * 为我推荐
     * @return void
     */
    public function get_recommend() {
        //数据分类 1:课程;2:专辑;
        $limit = intval($_POST['limit']);
        $uid = intval($this->mid);
        $zy_ordertable = C('DB_PREFIX') . 'zy_order';
        $zy_order_albumtable = C('DB_PREFIX') . 'zy_order_album';
        $zy_albumtable = C('DB_PREFIX') . 'zy_album';
        $zy_videotable = C('DB_PREFIX') . 'zy_video';
        //专辑
        $album_table = "SELECT `id`,2 as `type`,`album_category` as `category`,`uid`,`is_offical`,`big_ids`,`middle_ids`,`small_ids`,`album_title` as `title`,`album_score` as `score`,`album_intro` as `intro` FROM `{$zy_albumtable}` WHERE `is_del`=0 and `album_category` IN(SELECT `album_category` FROM `{$zy_albumtable}` WHERE `id` IN (SELECT `album_id` AS `rid` FROM `{$zy_order_albumtable}` WHERE `uid`={$uid})) AND `id` NOT IN (SELECT `album_id` AS `rid` FROM `{$zy_order_albumtable}` WHERE `uid`={$uid})";
        //课程
        $video_table = "SELECT `id`,1 as `type`,`video_category` as `category`,`uid`,`is_offical`,`big_ids`,`middle_ids`,`small_ids`,`video_title` as `title`,`video_score` as `score`,`video_intro` as `intro` FROM `{$zy_videotable}` WHERE `is_del`=0 and `video_category` IN(SELECT `video_category` FROM `{$zy_videotable}` WHERE `id` IN (SELECT `video_id` AS `rid` FROM `{$zy_ordertable}` WHERE `uid`={$uid})) AND `id` NOT IN (SELECT `video_id` AS `rid` FROM `{$zy_ordertable}` WHERE `uid`={$uid})";
        //拼接总的数据
        $sql = "SELECT * FROM ({$album_table} UNION {$video_table}) as `mysellwell`  LIMIT 0,{$limit}";
        //计算为我推荐总数
        $sql_count = "SELECT count(*) as `count` FROM ({$album_table} UNION {$video_table}) as `mysellwell` where 1;";
        //1:先找为我推荐的专辑或者课程---根据分类来找
        $count = M('')->query($sql_count);
        if (intval($count[0]['count'])) {
            //处理和返回
            $this->dealAndReturn($sql);
            exit;
        }
        //2:通过观看记录找为我推荐
        $session_data = session('watch_history');
        $session_data = $session_data ? implode(',', $session_data) : 0;
        $sql = "SELECT `id`,1 as `type`,`video_category` as `category`,`uid`,`is_offical`,`big_ids`,`middle_ids`,`small_ids`,`video_title` as `title`,`video_score` as `score`,`video_intro` as `intro` FROM {$zy_videotable} where `video_category` IN(SELECT `video_category` FROM {$zy_videotable} WHERE `id` IN ({$session_data})) and `is_del`=0 and `id` NOT IN({$session_data}) LIMIT 0,{$limit};";
        $sql_count = "SELECT count(*) as `count` FROM {$zy_videotable} where `video_category` IN(SELECT `video_category` FROM {$zy_videotable} WHERE `id` IN ({$session_data})) and `is_del`=0 and `id` NOT IN({$session_data});";
        $count = M('')->query($sql_count);
        if (intval($count[0]['count'])) {
            //处理和返回
            $this->dealAndReturn($sql);
            exit;
        }
        //3:如果发现没有-则找每日最新
        $this->get_today_new();
    }
    /**
     * 我的收藏
     * @return void
     */
    public function get_my_collect() {
        //数据分类 1:课程;2:专辑;
        $limit = intval($_POST['limit']);
        $uid = intval($this->mid);
        $zy_albumtable = C('DB_PREFIX') . 'zy_album';
        $zy_videotable = C('DB_PREFIX') . 'zy_video';
        $zy_collectiontable = C('DB_PREFIX') . 'zy_collection';
        //专辑
        $album_table = "SELECT `id`,2 as `type`,`album_category` as `category`,`uid`,`is_offical`,`big_ids`,`middle_ids`,`small_ids`,`album_title` as `title`,`album_score` as `score`,`album_intro` as `intro` from `{$zy_albumtable}` WHERE `id` in(SELECT `source_id` FROM `{$zy_collectiontable}` WHERE `uid`={$uid} and `source_table_name` = 'zy_album' ORDER BY `ctime` DESC)";
        //课程
        $video_table = "SELECT `id`,1 as `type`,`video_category` as `category`,`uid`,`is_offical`,`big_ids`,`middle_ids`,`small_ids`,`video_title` as `title`,`video_score` as `score`,`video_intro` as `intro` from `{$zy_videotable}` WHERE `id` in(SELECT `source_id` FROM `{$zy_collectiontable}` WHERE `uid`={$uid} and `source_table_name` = 'zy_video' ORDER BY `ctime` DESC)";
        //拼接总的数据
        $sql = "SELECT * FROM ({$album_table} UNION {$video_table}) as `mysellwell` ORDER BY `score` DESC LIMIT 0,{$limit}";
        //处理和返回
        $this->dealAndReturn($sql);
    }
    /**
     * 我的观看记录
     * @return void
     */
    public function get_watch_record() {
        $session_data = session('watch_history');
        $aids = implode(',', $session_data);
        //数据分类 1:课程;2:专辑;
        $limit = intval($_POST['limit']);
        $uid = intval($this->mid);
        $zy_videotable = C('DB_PREFIX') . 'zy_video';
        $sql = "SELECT `id`,1 as `type`,`big_ids`,`video_category` as `category`,`uid`,`is_offical`,`big_ids`,`middle_ids`,`small_ids`,`video_title` as `title`,`video_score` as `score`,`video_intro` as `intro` FROM `{$zy_videotable}` WHERE `id` in({$aids}) ORDER BY `ctime` DESC limit 0,{$limit}";
        //处理和返回
        $this->dealAndReturn($sql);
    }
    /**
     * 畅销排行榜
     * @return void
     */
    public function get_sell_well() {
        $limit = intval($_POST['limit']);
        $zy_albumtable = C('DB_PREFIX') . 'zy_album';
        $zy_videotable = C('DB_PREFIX') . 'zy_video';
        //数据分类 1:课程;2:专辑;
        //专辑
        $album_table = "SELECT `id`,2 as `type`,`big_ids`,`uid`,`is_offical`,`album_category` as `category`,`middle_ids`,`small_ids`,`album_title` as `title`,`album_order_count` as `number`,`album_score` as `score`,`album_intro` as `intro` from `{$zy_albumtable}` WHERE `is_del`=0";
        //课程
        $video_table = "SELECT `id`,1 as `type`,`big_ids`,`uid`,`is_offical`,`video_category` as `category`,`middle_ids`,`small_ids`,`video_title` as `title`,`video_order_count` as `number`,`video_score` as `score`,`video_intro` as `intro` from `{$zy_videotable}` WHERE `is_del`=0 AND `is_activity`=1";
        //拼接总的数据
        $sql = "SELECT * FROM ({$album_table} UNION {$video_table}) as `mysellwell` ORDER BY `number` DESC LIMIT 0,{$limit}";
        //处理和返回
        $this->dealAndReturn($sql);
    }
    /**
     * 每日上新
     * @return void
     */
    public function get_today_new() {
        $limit = intval($_POST['limit']);
        $zy_albumtable = C('DB_PREFIX') . 'zy_album';
        $zy_videotable = C('DB_PREFIX') . 'zy_video';
        //C('DB_PREFIX')
        //数据分类 1:课程;2:专辑;
        //专辑
        $album_table = "SELECT `id`,2 as `type`,`big_ids`,`uid`,`is_offical`,`album_category` as `category`,`middle_ids`,`small_ids`,`album_title` as `title`,`ctime`,`album_score` as `score`,`album_intro` as `intro` from `{$zy_albumtable}` WHERE `is_del`=0";
        //课程
        $video_table = "SELECT `id`,1 as `type`,`big_ids`,`uid`,`is_offical`,`video_category` as `category`,`middle_ids`,`small_ids`,`video_title` as `title`,`ctime`,`video_score` as `score`,`video_intro` as `intro` from `{$zy_videotable}` WHERE `is_del`=0 AND `is_activity`=1";
        //拼接总的数据
        $sql = "SELECT * FROM ({$album_table} UNION {$video_table}) as `mysellwell` ORDER BY `ctime` DESC LIMIT 0,{$limit}";
        //处理和返回
        $this->dealAndReturn($sql);
    }
    /**
     * 处理和返回
     * @return void
     */
    private function dealAndReturn($sql, $isRec = false) {
        //从数据库中取得
        $mylist = M('')->query($sql);
        //处理数据
        foreach ($mylist as & $value) {
            $value['score'] = floor(intval($value['score']) / 20);
            //$value['big_ids']  = getAttachUrlByAttachId($value['big_ids']);
            $value['category'] = getCategoryName($value['category'], true);
            $value['isGetResource'] = isGetResource($value['type'], $value['id'], array(
                'video',
                'upload',
                'note',
                'question'
            ));
            $value['title'] = msubstr($value['title'], 0, 21);
            $value['intro'] = msubstr($value['intro'], 0, 87);
            if ($value['type'] == 2) {
                $value['href'] = U('classroom/Album/view', 'id=' . $value['id']);
            } else {
                $value['href'] = U('classroom/Video/view', 'id=' . $value['id']);
            }
        }
        //关注
        $fids = model('Follow')->field('fid')->where('uid=' . intval($this->mid))->select();
        $this->assign('fids', getSubByKey($fids, 'fid'));
        //print_r($mylist);
        $this->assign('mylist', $mylist);
        //取得数据
        $content = $this->fetch('list');
        echo json_encode(array(
            'data' => $content
        ));
        exit;
    }
    /**
     * 专题列表
     * @return void
     */
    public function special() {
        //取得专题分类
        $scid = intval($_GET['scid']);
        if (!$scid) {
            $this->assign('isAdmin', 1);
            $this->error('您请求的专题列表不存在');
            return false;
        }
        $this->display();
    }
    /**
     * 获取分类数据列表
     */
    public function getCategoryData() {
    }
    /**
     * 投稿发布框
     * @return void
     */
    public function contributeBox() {
        $this->display();
    }
    /**
     * 提问/笔记/点评内容详情页
     * type 1:提问,2:点评,3:笔记
     * @return void
     */
    public function resource() {
        $types = array(
            1 => 'ZyQuestion',
            2 => 'ZyReview',
            3 => 'ZyNote',
        );
        $rid = intval($_GET['rid']);
        $type = intval($_GET['type']);
        $stable = $types[$type];
        if (!$stable) {
            $this->assign('isAdmin', 1);
            $this->error('参数错误');
        }
        $map['id'] = array(
            'eq',
            $rid
        );
        $data = D($stable)->where($map)->find();
        //print_r(D($stable)->getLastSql());
        if (!$data) {
            $this->assign('isAdmin', 1);
            $this->error('资源不存在');
        }
        $data['strtime'] = friendlyDate($data['ctime']);
        $data['username'] = getUserName($data['uid']);
        if ($type == 1) {
            $data['title'] = $data['qst_title'];
            $data['content'] = $data['qst_description'];
            $data['source'] = $data['qst_source'];
            $data['help_count'] = $data['qst_help_count'];
            $data['comment_count'] = $data['qst_comment_count'];
            $data['iscollect'] = D('ZyCollection')->isCollect($data['id'], 'zy_question', intval($this->mid));
        } else if ($type == 3) {
            $data['title'] = $data['note_title'];
            $data['content'] = $data['note_description'];
            $data['source'] = $data['note_source'];
            $data['help_count'] = $data['note_help_count'];
            $data['comment_count'] = $data['note_comment_count'];
            $data['iscollect'] = D('ZyCollection')->isCollect($data['id'], 'zy_note', intval($this->mid));
        }
        //返回专辑或者课程信息
        $this->getRInfo($data['oid'], $data['type']);
        $data['title'] = msubstr($data['title'], 0, 40);
        $this->assign('type', $type);
        $this->assign('data', $data);
        $this->display();
    }
    private function getRInfo($id, $type) {
        $map['id'] = array(
            'eq',
            $id
        );
        if ($type == 1) {
            //课程
            if (!$id) {
                $this->assign('isAdmin', 1);
                $this->error('课程不存在!');
            }
            $field = '`video_title` as `title`,`video_category` as `category`,`video_score` as `score`,`uid`,`id`,`ctime`,`video_comment_count` as `comment_count`';
            //取课程信息
            $data = M('ZyVideo')->where($map)->field($field)->find();
        } else if ($type == 2) {
            //专辑
            if (!$id) {
                $this->assign('isAdmin', 1);
                $this->error('专辑不存在!');
            }
            $field = '`album_title` as `title`,`album_category` as `category`,`album_score` as `score`,`uid`,`id`,`ctime`,`album_comment_count` as `comment_count`';
            //取专辑信息
            $data = M('ZyAlbum')->where($map)->field($field)->find();
        } else {
            $this->assign('isAdmin', 1);
            $this->error('参数错误!');
        }
        $data['score'] = floor($data['score'] / 20);
        //print_r($data);exit;
        $this->assign('datainfo', $data);
        $this->assign('id', $id);
        $this->assign('type', $type);
    }
    /**
     * 提问/笔记/点评内容详情页
     * type 1:提问,2:点评,3:笔记
     * @return void
     */
    public function getTopHot() {
        $zyVoteMod = D('ZyVote');
        $types = array(
            3 => 'ZyQuestion',
            4 => 'ZyNote',
        );
        $limit = intval($_POST['limit']);
        $pid = intval($_POST['pid']);
        $type = intval($_POST['type']);
        $stable = $types[$type];
        $order = $type == 3 ? 'qst_vote_count DESC' : 'note_vote_count DESC';
        $data = M($stable)->where(array(
            'parent_id' => array(
                'eq',
                $pid
            )
        ))->order($order)->findPage($limit);
        //处理数据
        foreach ($data['data'] as & $value) {
            $value['username'] = getUserName($value['uid']);
            $value['strtime'] = friendlyDate($value['ctime']);
            if ($type == 3) {
                $value['content'] = $value['qst_description'];
                $value['comment_count'] = $value['qst_comment_count'];
                $value['vote_count'] = $value['qst_vote_count'];
                $value['source'] = $value['qst_source'];
                $value['isvote'] = $zyVoteMod->isVote($value['id'], 'zy_question', $this->mid) ? 1 : 0;
            } else if ($type == 4) {
                $value['content'] = $value['note_description'];
                $value['comment_count'] = $value['note_comment_count'];
                $value['vote_count'] = $value['note_vote_count'];
                $value['source'] = $value['note_source'];
                $value['isvote'] = $zyVoteMod->isVote($value['id'], 'zy_note', $this->mid) ? 1 : 0;
            }
        }
        //处理数据
        echo json_encode($data);
        exit;
    }
    /**
     * 提问/笔记/点评内容详情页
     * type 1:提问,2:点评,3:笔记
     * @return void
     */
    public function getTopNew() {
        $zyVoteMod = D('ZyVote');
        $types = array(
            3 => 'ZyQuestion',
            4 => 'ZyNote',
        );
        $limit = intval($_POST['limit']);
        $pid = intval($_POST['pid']);
        $type = intval($_POST['type']);
        $stable = $types[$type];
        $order = 'ctime DESC';
        $data = M($stable)->where(array(
            'parent_id' => array(
                'eq',
                $pid
            )
        ))->order($order)->findPage($limit);
        //处理数据
        foreach ($data['data'] as & $value) {
            $value['username'] = getUserName($value['uid']);
            $value['strtime'] = friendlyDate($value['ctime']);
            if ($type == 3) {
                $value['content'] = $value['qst_description'];
                $value['comment_count'] = $value['qst_comment_count'];
                $value['vote_count'] = $value['qst_vote_count'];
                $value['source'] = $value['qst_source'];
                $value['isvote'] = $zyVoteMod->isVote($value['id'], 'zy_question', $this->mid) ? 1 : 0;
            } else if ($type == 4) {
                $value['content'] = $value['note_description'];
                $value['comment_count'] = $value['note_comment_count'];
                $value['vote_count'] = $value['note_vote_count'];
                $value['source'] = $value['note_source'];
                $value['isvote'] = $zyVoteMod->isVote($value['id'], 'zy_note', $this->mid) ? 1 : 0;
            }
        }
        echo json_encode($data);
        exit;
    }
    public function getListForId() {
        $types = array(
            3 => 'ZyQuestion',
            4 => 'ZyNote',
        );
        $limit = intval($_POST['limit']);
        $pid = intval($_POST['pid']);
        $type = intval($_POST['type']);
        $stable = $types[$type];
        $order = 'ctime desc';
        $data = M($stable)->where(array(
            'parent_id' => array(
                'eq',
                $pid
            )
        ))->order($order)->findPage($limit);
        //处理数据
        foreach ($data['data'] as & $value) {
            $value['username'] = getUserName($value['uid']);
            $value['strtime'] = friendlyDate($value['ctime']);
            if ($type == 3) {
                $value['content'] = $value['qst_description'];
            } else if ($type == 4) {
                $value['content'] = $value['note_description'];
            }
            $value['content'] = msubstr($value['content'], 0, 240);
        }
        echo json_encode($data);
        exit;
    }
    /**
     * 添加笔记、提问的评论
     * type 1:提问,2:点评,3:笔记
     * @return void
     */
    public function dowrite() {
        $types = array(
            1 => 'ZyQuestion',
            2 => 'ZyReview',
            3 => 'ZyNote',
        );
        if (!$this->mid) {
            //请不要重复刷新
            $this->mzError('请登录!');
        }
        $rid = intval($_POST['rid']);
        $reply_id = intval($_POST['rep_uid']);
        $type = intval($_POST['type']);
        $content = t($_POST['content']);
        $stable = $types[$type];
        //先找到之前的数据(评论的问题、点评、笔记的)
        $data = M($stable)->where(array(
            'id' => array(
                'eq',
                $rid
            )
        ))->find();
        if ($type == 1) {
            $data['parent_id'] = $rid;
            $data['uid'] = $this->mid;
            $data['qst_description'] = $content;
            $data['qst_help_count'] = 0;
            $data['qst_comment_count'] = 0;
            $data['qst_collect_count'] = 0;
            $data['qst_vote_count'] = 0;
            $data['qst_source'] = 'web网页';
            $data['ctime'] = time();
            unset($data['id'], $data['qst_title']);
        } else if ($type == 2) {
            $data['parent_id'] = $rid;
            $data['uid'] = $this->mid;
            $data['review_description'] = $content;
            $data['review_help_count'] = 0;
            $data['review_comment_count'] = 0;
            $data['review_collect_count'] = 0;
            $data['review_vote_count'] = 0;
            $data['review_source'] = 'web网页';
            $data['ctime'] = time();
            unset($data['id'], $data['review_title']);
        } else if ($type == 3) {
            $data['parent_id'] = $rid;
            $data['uid'] = $this->mid;
            $data['note_description'] = $content;
            $data['note_help_count'] = 0;
            $data['note_comment_count'] = 0;
            $data['note_collect_count'] = 0;
            $data['note_vote_count'] = 0;
            $data['note_source'] = 'web网页';
            $data['ctime'] = time();
            unset($data['id'], $data['note_title']);
        }
        if (session('mzaddQuestionandnote11' . $rid . $type) >= time()) {
            //请不要重复刷新
            $this->mzError('请不要重复添加,3分钟之后再试!');
        }
        $data['reply_uid'] = $reply_id;
        $i = M($stable)->add($data);
        if ($i) {
            $deinfo = "";
            if ($type == 1) {
                $_data['qst_comment_count'] = array(
                    'exp',
                    'qst_comment_count+1'
                );
            } else if ($type == 2) {
                $_data['review_comment_count'] = array(
                    'exp',
                    'review_comment_count+1'
                );
            } else if ($type == 3) {
                $_data['note_comment_count'] = array(
                    'exp',
                    'note_comment_count+1'
                );
            }
            M($stable)->where(array(
                'id' => array(
                    'eq',
                    $rid
                )
            ))->save($_data);
            session('mzaddQuestionandnote' . $rid . $type, time() + 180);
            $data['userface'] = getUserFace($this->mid, 's');
            $data['user_src'] = U('classroom/UserShow/index', 'uid=' . $this->mid);
            $data['username'] = getUserName($this->mid);
            $data['strtime'] = friendlyDate($data['ctime']);
            $data['description'] = $reply_id ? '回复<span class="user-reply">@' . getUserName($reply_id) . '</span>:' . $content : $content;
            $data['uid'] = $this->mid;
            //查出被评论人的uid和内容
            $finfo = M($stable)->where(array(
                'id' => array(
                    'eq',
                    $rid
                )
            ))->find();
            if (empty($reply_id)) {
                $fid = $finfo['uid'];
            } else {
                $fid = $reply_id;
            }
            if ($type == 1) {
                $deinfo = $finfo['qst_description'];
            } else if ($type == 3) {
                $deinfo = $finfo['note_description'];
            }
            model('Message')->doCommentmsg($this->mid, $fid, $finfo['oid'], $finfo['type'], 'zy_question', 0, limitNumber($deinfo, 30) , $content);
            $this->mzSuccess('添加成功', '', $data);
        } else {
            $this->mzError('添加失败');
        }
    }
    /**
     * 添加笔记、提问的评论
     * type 1:提问,2:点评,3:笔记
     * @return void
     */
    public function tongwen() {
        $types = array(
            1 => 'ZyQuestion',
            3 => 'ZyNote',
        );
        $rid = intval($_POST['rid']);
        $type = intval($_POST['type']);
        if (session('mzaddQuestionandnotetonwen' . $rid . $type) >= time()) {
            //请不要重复刷新
            $this->mzError('请不要重复点击哦');
        }
        $stable = $types[$type];
        if ($type == 1) {
            $data['qst_help_count'] = array(
                'exp',
                'qst_help_count+1'
            );
        } else {
            $data['note_help_count'] = array(
                'exp',
                'note_help_count+1'
            );
        }
        $i = M($stable)->where(array(
            'id' => array(
                'eq',
                $rid
            )
        ))->save($data);
        if ($i) {
            session('mzaddQuestionandnotetonwen' . $rid . $type, time() + 180);
            //查出被评论人的uid和内容
            $finfo = M($stable)->where(array(
                'id' => array(
                    'eq',
                    $rid
                )
            ))->find();
            if (empty($reply_id)) {
                $fid = $finfo['uid'];
            } else {
                $fid = $reply_id;
            }
            model('Message')->doCommentmsg($this->mid, $fid, $finfo['id'], 0, 'zy_question', 0, limitNumber($finfo['qst_description'], 30) , $content);
            $this->mzSuccess('添加成功');
        } else {
            $this->mzError('添加失败');
        }
    }
}

