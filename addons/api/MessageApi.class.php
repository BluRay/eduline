<?php
/**
 * 私信api
 * utime : 2016-03-06
 */

class MessageApi extends Api{


	/**
	 * 私信列表
	 * @return void
	 */
	public function index() {
		$dao = model('Message');
		$list = $this->getMessageListByUid($this->mid, 1);
		foreach ($list as &$val){
			$val['last_message']['content'] = parse_html($val['last_message']['content']);
			$val['last_message']['content'] = str_replace('__THEME__',SITE_URL.'/addons/theme/stv1/_static/',$val['last_message']['content']);
		}
		// 设置信息已读(在右上角提示去掉),
		model('Message')->setMessageIsRead( t( $this->data['id'] ) , $this->mid, 0);
		$userInfo = model('User')->getUserInfo( $this->mid );
		$this->exitJson($list);
	}

    /**
     * 获取私信列表 - 分页型
     * @param integer $uid 用户UID
     * @param integer $type 私信类型，1表示一对一私信，2表示多人聊天，默认为1
     * @return array 私信列表信息
     */
    public function getMessageListByUid($uid, $type = 1) {
        $uid  = intval($uid);
        $type = is_array($type) ? ' IN ('.implode(',', $type).')' : "={$type}";
        $list = M('message_member')->Table("`".C('DB_PREFIX')."message_member` AS `mb`")
            ->join("`".C('DB_PREFIX')."message_list` AS `li` ON `mb`.`list_id`=`li`.`list_id`")
            ->where("`mb`.`member_uid`={$uid} AND `li`.`type`{$type} AND `mb`.`is_del` = 0 AND mb.message_num > 0")
            ->order('`mb`.`new` DESC,`mb`.`list_ctime` DESC')
            ->limit($this->_limit())->select();
       model('Message')->_parseMessageList($list, $uid); // 引用
        return $list;
    }

    /**
     * 删除私信
     * @return integer 1=成功 0=失败
     */
    public function doDelete() {
        $res = model('Message')->deleteMessageByListId($this->mid, t($_REQUEST['ids']));
        if ($res) {
            $this->exitJson(true);
        }
        $this->exitJson( array() ,10036,"对不起，删除私信失败!");
    }

    /**
     * 私信详情
     * @return void
     */
    public function detail(){
        $messageid = intval( $this->data['mid'] );
        $message   = model('Message')->isMember($messageid, $this->mid, true);

        // 验证数据
        if(empty($message)) {
            $this->exitJson( array() ,10034,L('PUBLIC_PRI_MESSAGE_NOEXIST'));
        }
        $message['member'] = model('Message')->getMessageMembers($messageid, 'member_uid');
        $message['to'] = array();
        // 添加发送用户ID
        foreach($message['member'] as $v) {
            $this->mid != $v['member_uid'] && $message['to'][] = $v;
        }
        // 设置信息已读(私信列表页去掉new标识)
        model('Message')->setMessageIsRead($messageid, $this->mid, 0);
        $message['since_id'] = model('Message')->getSinceMessageId($message['list_id'],$message['message_num']);
        $this->exitJson($message);
    }
	
	/**
	 * 获取指定私信列表中的私信内容
	 * @return void
	 */
	public function loadMessage() {
		$message = model('Message')->getMessageByListId(intval($_REQUEST['list_id']), $this->mid, intval($_REQUEST['since_id']), intval($_REQUEST['max_id']));
		// 临时解决方案
		foreach ($message['data'] as &$value) {
			if ($value['content'] == t($value['content'])) {
				$value['content'] = parse_html($value['content']);
				$value['content'] = str_replace('__THEME__',SITE_URL.'/addons/theme/stv1/_static/',$value['content']);
			}
		}
		$this->exitJson($message['data']);
	}
	
	
	/**
	 * 回复私信
	 * @return void
	 */
	public function doReply() {
		$UserPrivacy = model('UserPrivacy')->getPrivacy($this->mid, intval($this->data['to']));
		$_POST['reply_content'] = t($this->data['reply_content']);
		$_POST['id']  			= intval($this->data['id'] );

		if ( !$_POST['id'] || empty($_POST['reply_content']) ) {
			 $this->exitJson( array() ,10036,L('PUBLIC_COMMENT_MAIL_REQUIRED'));
		}
		// 图片附件信息
		if (trim(t($_POST['attach_ids'])) != '') {
			$_POST['attach_ids'] = explode('|', $_POST['attach_ids']);
			$_POST['attach_ids'] = array_filter($_POST['attach_ids']);
			$_POST['attach_ids'] = array_unique($_POST['attach_ids']);
		}

		$res = model('Message')->replyMessage( $_POST['id'], $_POST['reply_content'], $this->mid, $_POST['attach_ids']);
		if ($res) {
			 $this->exitJson(true);
		}else {
			 $this->exitJson( array() ,10038,L('PUBLIC_PRIVATE_MESSAGE_SEND_FAIL'));
		}
	}
	
	/**
	 * 发送私信
	 * @return void
	 */
	public function doPost() {
		if ( empty($this->data['to']) || !CheckPermission('core_normal','send_message') ) {
			$this->exitJson( array() ,10038,L('PUBLIC_SYSTEM_MAIL_ISNOT'));
		}
		if( trim( t( $this->data['content'] ) ) == ''){
			$this->exitJson( array() ,10038,L('PUBLIC_COMMENT_MAIL_REQUIRED'));
		}
		$_POST['to'] = trim(t($this->data['to']),',');
		$to_num = explode(',', $_POST['to']);
		!in_array($_POST['type'], array(MessageModel::ONE_ON_ONE_CHAT, MessageModel::MULTIPLAYER_CHAT)) && $_POST['type'] =  array() ;
		$_POST['content'] = h($_REQUEST['content']);
		// 图片附件信息
		if (trim(t($_POST['attach_ids'])) != '') {
			$_POST['attach_ids'] = explode('|', $_POST['attach_ids']);
			$_POST['attach_ids'] = array_filter($_POST['attach_ids']);
			$_POST['attach_ids'] = array_unique($_POST['attach_ids']);
		}
		$res = model('Message')->postMessage($_POST, $this->mid);
		if ($res) {
			 $this->exitJson(true);
		}else {
			 $this->exitJson( array() ,10038,model('Message')->getError());
		}
	}

    //加载评论消息
    public function comment(){
        $comsg=M('ZyComment')->where(array('fid'=>$this->mid,'is_del'=>0))->order("ctime DESC")->limit($this->_limit())->select();
        //循环格式化数据
        $ids="";
        foreach($comsg as &$val){
            $ids=$ids.$val['id'].",";
            $val['uidinfo']=model('User')->getUserInfo($val['uid']);
            $val['fidinfo']=model('User')->getUserInfo($val['fid']);
            //问答方法
            if($val['app_table']=="wenda"){
                if($val['fid']!=$val['app_uid']){
                    $val['app_name']="评论";
                }else{
                    $val['app_name']="问答";
                }

            }else{
                //判断评论的是专辑还是课程
                if($val['app_uid']==1){
                    $app="video";
                    $val['app_name']="课程";
                }else{
                    $val['app_name']="专辑";
                    $app="album";
                }
            }
        }
        //设置为已读系统消息
        $ids = trim($ids,",");
        $where['id'] = array('in',$ids);
        $data['is_read'] = 1;
        D('ZyComment','classroom')->where($where)->save($data);
        $this->exitJson($comsg);
    }

    //删除我是收到的评论接口
    public function delComsg(){
        $id = intval($this->data['id']);
        $data['is_del'] = 1;
        $res = M('ZyComment','classroom')->where(array('id'=>$id))->save($data);
        if($res!==false){
        	$this->exitJson(true);
        }else{
        	$this->exitJson( array() ,10045,"对不起，删除失败！");
        }
    }

}








?> 