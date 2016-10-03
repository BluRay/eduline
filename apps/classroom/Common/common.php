<?php

/**
 * 根据专辑ID获取名称
 */
function getAlbumNameForID($vid) {
    static $names = array();
    if (!isset($names[$vid])) {
        $names[$vid] = D('ZyAlbum')->where(array('id' => $vid))->getField('album_title');
    }
    return $names[$vid];
}

/**
 * 根据课程ID获取名称
 */
function getVideoNameForID($vid) {
    static $names = array();
    if (!isset($names[$vid])) {
        $names[$vid] = D('ZyVideo')->where(array('id' => $vid))->getField('video_title');
    }
    return $names[$vid];
}

/**
 * 取得课程或专辑的分类名字
 * @param $id
 * @return string
 */
function getCategoryName($id, $isTop = false) {
    static $names = array();
    if (!isset($names[$id])) {
        $names[$id] = model('VideoCategory')->getCategoryName($id, $isTop);
    }
    return $names[$id];
}

/**
 * 将附件|123|456 转为 123,456
 * @return [type] [description]
 */
function attachzh($ids = '') {
    if (!$ids)
        return '';
    return trim(str_replace('|', ',', $ids), ',');
}

function getAttachPathByAttachId($attachid) {
    if ($attachInfo = model('Attach')->getAttachById($attachid)) {
        return $attachInfo['save_path'] . $attachInfo['save_name'];
    } else {
        return false;
    }
}

/**
 * 格式化字符串时间
 * @param  [type] $time [description]
 * @return [type]       [description]
 */
function time_format($time) {      //-------------------by dengjb
    $now = time();
    $today = strtotime(date('y-m-d'));
    $zuotian = strtotime('-1 day', $today);

    $diff = $now - $time;

    $str = '';
    switch (true) {
        case $diff < 60 :
            $str = '刚刚';
            break;
        case $diff < 3600 :
            $str = floor($diff / 60) . '分钟前';
            break;
        case $diff < (3600 * 8) :
            $str = floor($diff / 3600) . '小时前';
            break;
        case $time > $today :
            $str = '今天' . date('H:i', $time);
            break;
        case $time > $zuotian :
            $str = '昨天' . date('H:i', $time);
            break;
        default :
            $str = date('Y-m-d H:i', $time);
    }
    return $str;
}

/**
 * 取得卖家/商家 根据uid进行判断
 * @param integer $id 卖家/商家uid
 * @param boolean $real 当值为true时返回真实的ID，为false时返回保存的ID
 * return integer 返回正确的卖家/商家uid;
 * 规则$real=true:如果uid大于0，则直接返回，否则取系统默认值
 * 规则$real=false:如果uid=master_uid(系统默认)，则返回0，否则直接返回uid
 */
function getSeller($uid = 0, $real = true) {
    exit('函数已经弃用');
    static $master_uid = null;
    if ($master_uid === null) {
        $master_uid = intval(getAppConfig('master_uid'));
    }

    if ($real) {
        return $uid > 0 ? $uid : $master_uid;
    } else {
        return $uid == $master_uid ? 0 : $uid;
    }
}

/**
 * 取得返回url
 * @param string $name 从GET或POST取时的名称
 * @param string $default 默认返回url
 * @param boolean $checkRef 是否检查 HTTP_REFERER
 */
function getBackUrl($name = 'backUrl', $default = null, $checkRef = true) {
    if (!empty($_GET[$name])) {
        return $_GET[$name];
    } elseif (!empty($_POST[$name])) {
        return $_POST[$name];
    } elseif ($checkRef && !empty($_SERVER['HTTP_REFERER'])) {
        return $_SERVER['HTTP_REFERER'];
    } else {
        return $default;
    }
}



function unique_arr($array2D,$stkeep=false,$ndformat=true)
{
    // 判断是否保留一级数组键 (一级数组键可以为非数字)
    if($stkeep) $stArr = array_keys($array2D);
    // 判断是否保留二级数组键 (所有二级数组键必须相同)
    if($ndformat) $ndArr = array_keys(end($array2D));
    //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
    foreach ($array2D as $v){
        $v = join(",",$v);
        $temp[] = $v;
    }
    //去掉重复的字符串,也就是重复的一维数组
    $temp = array_unique($temp);
    //再将拆开的数组重新组装
    foreach ($temp as $k => $v)
    {
        if($stkeep) $k = $stArr[$k];
        if($ndformat)
        {
            $tempArr = explode(",",$v);
            foreach($tempArr as $ndkey => $ndval) $output[$k][$ndArr[$ndkey]] = $ndval;
        }
        else $output[$k] = explode(",",$v);
    }
    return $output;
}


/**
 * 查询资源是否存在
 * @param int $type 2:专辑;1:课程;
 * @param int $resid 资源ID
 * @param array $resource 需要判断的资源数组
 * video    //高清视频课程
 * upload   //上传附件
 * note     //笔记
 * question //提问
 * @return mixed 返回判定结果
 */
function isGetResource($type, $resid, $resource = array()) {
    $_resource = array();
    foreach ($resource as $value) {
        switch ($value) {
            case 'video':
                if ($type == 1) {
                    $album_video = M('ZyAlbum')->where(array('id' => intval($resid)))->getField('album_video');
                    $count = M('ZyVideo')->where(array('video_id' => array('NEQ', ''), 'id' => array('in', (string) $album_video)))->count();
                    $_resource[$value] = $count ? true : false;
                } else {
                    $count = M('ZyVideo')->where(array('video_id' => array('NEQ', ''), 'id' => array('eq', $resid)))->count();
                    $_resource[$value] = $count ? true : false;
                }
                break;
            case 'upload':
                if ($type == 1) {
                    $album_video = M('ZyAlbum')->where(array('id' => intval($resid)))->getField('album_video');
                    $count = M('ZyVideo')->where(array('videofile_ids' => array('NEQ', ''), 'id' => array('in', (string) $album_video)))->count();
                    $_resource[$value] = $count ? true : false;
                } else {
                    $count = M('ZyVideo')->where(array('videofile_ids' => array('NEQ', ''), 'id' => array('eq', $resid)))->count();
                    $_resource[$value] = $count ? true : false;
                }
                break;
            case 'note':
                $count = M('ZyNote')->where(array('oid' => array('eq', $resid), 'type' => array('eq', $type)))->count();
                $_resource[$value] = $count ? true : false;
                break;
            case 'question':
                $count = M('ZyQuestion')->where(array('oid' => array('eq', $resid), 'type' => array('eq', $type)))->count();
                $_resource[$value] = $count ? true : false;
                break;
            default:
                return null;
                break;
        }
    }
    return $_resource;
}





/**
 * 取得一个用户当前的课程组合列表数量
 * @param $uid
 * @param $tmp_id
 * @return integer
 */
function getVideoMergeNum($uid, $tmp_id) {
    return D('ZyVideoMerge')->getNum($uid, $tmp_id);
}

/**
 * 取得用户认证信息，如果有则返回数组，否则返回false
 * @param $uid 用户UID
 * return array|false
 */
function getUserAuthInfo($uid) {
    static $userAuthInfo = null;
    if (null === $userAuthInfo) {
        $userAuthInfo = D('user_verified')->where('verified=1 AND uid=' . $uid)->find();
        $userAuthInfo = $userAuthInfo ? $userAuthInfo : false;
    }
    return $userAuthInfo;
}

function unLast0($decimal) {
    $parts = explode('.', $decimal);
    $decimal = $parts[0];
    if (isset($parts[1])) {
        $parts[1] = rtrim($parts[1], '0');
        if ($parts[1] && $parts[1] > 0) {
            $decimal .= '.' . $parts[1];
        }
    }
    return $decimal;
}

/**
 * 友好的时间
 * @param int    $time 待显示的时间
 * @return string
 */
function friendlyTime($time) {
    $time = intval($time);

    $days = 0;
    $hours = 0;
    $minutes = 0;
    $seconds = 0;

    //算天
    if ($time > 86400) {
        $days = intval($time / 86400);
        $time = $time - $days * 86400;
    }
    //算小时
    if ($time > 3600) {
        $hours = intval($time / 3600);
        $time = $time - $hours * 3600;
    }
    //算分钟
    if ($time > 60) {
        $minutes = intval($time / 60);
        $time = $time - $minutes * 60;
    }
    //算秒
    $seconds = intval($time % 60);

    $hours = $hours <= 9 ? '0' . $hours : $hours;
    $minutes = $minutes <= 9 ? '0' . $minutes : $minutes;
    $seconds = $seconds <= 9 ? '0' . $seconds : $seconds;

    $strtime = '';
    if ($days > 0) {
        return $days . '天' . $hours . '小时' . $minutes . '分钟' . $seconds . '秒';
    } else {
        if (intval($hours) > 0) {
            return $hours . ':' . $minutes . ':' . $seconds;
        } else {
            return $minutes . ':' . $seconds;
        }
    }
}

/**
 * 取得用户的学币数量
 * @param integer $uid 用户ID
 * @return integer
 */
function getUserLearncoin($uid) {
    $user = D('ZyLearnc')->getUser($uid);
    if ($user) {
        return $user['balance'];
    } else {
        return 0;
    }
}

/**
 * 取得用户的关注数和粉丝数
 * @param array $uids array(1)
 * @return array
 */
function getFollowCount($uids) {
    return model('Follow')->getFollowCount($uids);
}

/**
 * 获取用户积分
 *
 * 返回积分值的数据结构
 * <code>
 * array(
 * 'score' =>array(
 * 'credit'=>'1',
 * 'alias' =>'积分',
 * ),
 * 'experience'=>array(
 * 'credit'=>'2',
 * 'alias' =>'经验',
 * ),
 * '类型' =>array(
 * 'credit'=>'值',
 * 'alias' =>'名称',
 * ),
 * )
 * </code>
 *
 * @param int $uid          
 * @return boolean array
 */
function getUserCredit($uid) {
    return model('Credit')->getUserCredit($uid);
}

//取得用户的营收比
function getUserIncomePercent($uid) {
    $credits = getUserCredit($uid);
    $score = intval($credits['credit']['score']['value']);
    $learnc = D('ZyOrder')->where(array('muid' => $uid))->sum('user_num');
    if ($score > 5000) {
        $percent = 0.3;
    } elseif ($score < 3000) {
        $percent = 0.2;
    } else {
        $percent = 0.25;
    }
    if ($learnc > 5000) {
        $percent += 0.4;
    } elseif ($learnc < 3000) {
        $percent += 0.3;
    } else {
        $percent += 0.35;
    }
    return $percent;
}

/**
 * 发送短信
 */
function sendSms_classroom($phone, $content) {
    $sms = M('system_data')->where("`list`='admin_config' AND `key`='sms'")->field('value')->find();
    $sms = unserialize($sms['value']);
    $sn = $sms['sms_uid']; //提供的帐号
    $pw = $sms['sms_pwd']; //密码
    $pwd = strtoupper(md5($sn . $pw));
    $data = array(
        'sn' => $sn, //提供的帐号
        'pwd' => $pwd, //此处密码需要加密 加密方式为 md5(sn+password) 32位大写
        'mobile' => mb_convert_encoding($phone, 'GB2312', 'UTF-8'), //手机号 多个用英文的逗号隔开 post理论没有长度限制.推荐群发一次小于等于10000个手机号
        'content' => mb_convert_encoding($content . '【Eduline】', 'GB2312', 'UTF-8'), //短信内容
        'ext' => '',
        'stime' => '', //定时时间 格式为2011-6-29 11:09:21
        'rrid' => '' //默认空 如果空返回系统生成的标识串 如果传值保证值唯一 成功则返回传入的值 
    );
    $url = "http://117.79.237.29/webservice.asmx/mt?";

    $ch = curl_init(); // 启动一个CURL会话
    curl_setopt($ch, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    curl_setopt($ch, CURLOPT_POST, 1); // 发送一个常规的Post请求
    $data = http_build_query($data);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($ch, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    $result = curl_exec($ch);
    curl_close($ch);
    if (!$result || curl_errno($ch)) {
        return false;
    }
    if (json_encode($result) === false) {
        $result = mb_convert_encoding($result, 'UTF-8', 'GBK');
    }
    $result = strip_tags($result);
    return trim($result);
}

/**
 * 获取榜单数据
 * @param $limit 数量
 * @param $type 类型
 */
function getRanking($limit = 10, $type = 'video') {
    $table = M("");
    $where = ' WHERE `is_del` = 0 AND (`uctime` > ' . time() . ' AND `listingtime` < ' . time() . ')';
    if ($type == "video") {
        $where .= " AND `is_activity` = 1";
    }
    $order = ' ORDER BY `' . $type . '_order_count` DESC';
    $sql = "SELECT * FROM " . C('DB_PREFIX') . "zy_" . $type . $where . $order;
    $count = count($table->query($sql));
    $data = $table->findPageBySql($sql, $count, $limit);
    return $data;
}

/**
 * 混合查询专辑和课程
 */
function getRankingMix($limit = 10, $order = '', $page = 1, $album_where = "", $video_where = "") {
    $zy_albumtable = C('DB_PREFIX') . 'zy_album';
    $zy_videotable = C('DB_PREFIX') . 'zy_video';
    //专辑
    $album_table = "SELECT `id`,`re_sort`,`be_sort`,2 as `type`,`album_category` as `category`,`uid`,`cover`,`album_title` as `title`,`album_score` as `score`,`album_order_count` as `order_count`,`album_intro` as `intro`,`ctime`,`is_offical` FROM `{$zy_albumtable}` " . $album_where;
    //课程
    $video_table = "SELECT `id`,`re_sort`,`be_sort`,1 as `type`,`video_category` as `category`,`uid`,`cover`,`video_title` as `title`,`video_score` as `score`,`video_order_count` as `order_count`,`video_intro` as `intro`,`ctime`,`is_offical` FROM `{$zy_videotable}` " . $video_where;
    //拼接总的数据
    //$sql_count ="SELECT COUNT(*) AS `count` FROM ({$album_table} UNION {$video_table}) as `mysellwell`".$order;
//		$sql = "SELECT * FROM ({$album_table} UNION {$video_table}) as `mysellwell`".$order." LIMIT ".($page-1) * $limit .",".$page * $limit ;
    $sql = "SELECT * FROM ({$album_table} UNION {$video_table}) as `mysellwell`" . $order;
    $obj = M("");
    $count = count($obj->query($sql));
//		$totle = M('')->query($sql_count);
//		$data['totle'] = $totle[0]['count'];
//		$data['page'] = ($data['totle']%$limit) == 0 ? intval($data['totle'] / $limit) : intval($data['totle']/$limit) + 1;
    $data = $obj->findPageBySql($sql, $count, $limit);
    return $data;
}
