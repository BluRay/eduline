<?php
/**
 * 管理中心api
 * utime : 2016-03-06
 */

class HomeApi extends Api{
	/**
    * 会员中心问题--异步处理
    * @return void
    */
	public function getwentilist(){
        $type  = t($this->data['type']);
		$zyQuestionMod   = D('ZyQuestion','classroom');
		$zyCollectionMod = D('ZyCollection','classroom');
		
		if($type == 'me'){
			$map['uid']       = intval($this->mid);
			$map['parent_id'] = 0;
			$order = 'ctime DESC';
			
			$data = $zyQuestionMod->where($map)->order($order)->limit($this->_limit())->select();
			foreach($data as &$value){
				$value['qst_title']       = msubstr($value['qst_title'],0,15);
				$value['qst_description'] = msubstr($value['qst_description'],0,153);
				$value['strtime']         = friendlyDate($value['ctime']);
				$value['qcount']          = $zyQuestionMod->where(array('parent_id'=>array('eq',$value['id'])))->count();
			}
		}else if ($type == 'question'){
			$thistable = C('DB_PREFIX').'zy_question';
			$uid = $this->mid;
			//找到所有的答案
			$sql  = "SELECT `id`,`parent_id` FROM {$thistable} WHERE `uid` = {$uid} and `parent_id` in(SELECT `id` FROM {$thistable} WHERE parent_id =0)";
			$data = M()->query($sql);
			$_myIds  = array();
			$_myPIds = array();
			foreach($data as $key=>$value){
				$_myIds[]  = $value['id'];
				$_myPIds[] = $value['parent_id'];
			}
 			
			$_myIds = array_unique($_myIds);
			$_myIds = $_myIds ? implode(',',$_myIds) : 0;
			
			$_myPIds = array_unique($_myPIds);
			$_myPIds = $_myPIds ? implode(',',$_myPIds) : 0;
			
			//找到所有的答案
			$data = M('ZyQuestion')->where(array('id'=>array('in',(string)$_myIds)))->order('ctime desc')->limit($this->_limit())->select();
			//把答案的问题
			$_data = M('ZyQuestion')->where(array('id'=>array('in',(string)$_myPIds)))->select();
			
			foreach($data as  &$value){
				$_value = array();
				foreach($_data as $k => $v){
					if($value['parent_id'] == $v['id']){
						$_value = $v;
						break;	
					}
				}
				$value['wenti'] = $_value;
			}
			foreach($data as &$value){
				$value['wenti']['qst_title'] = msubstr($value['wenti']['qst_title'],0,15);
				$value['wenti']['qst_description'] = msubstr($value['wenti']['qst_description'],0,149);
				$value['wenti']['strtime'] = friendlyDate($value['wenti']['ctime']);
				$value['wenti']['qcount']  = M('ZyQuestion')->where(array('parent_id'=>array('eq',$value['wenti']['id'])))->count();
				$value['qst_title'] = msubstr($value['qst_title'],0,15);
				$value['qst_description'] = msubstr($value['qst_description'],0,31);
				$value['qcount']  = M('ZyQuestion')->where(array('parent_id'=>array('eq',$value['id'])))->count();
			}
		}
		if($data){
			$this->exitJson($data);
		}else{
			$this->exitJson( array() ,10016,'你还没有回答!');
		}
	}
		
	//加载我的笔记
	public function getNoteList(){
		$type  = 'me';
		$zyNoteMod       = D('ZyNote','classroom');
		$zyCollectionMod = D('ZyCollection','classroom');
		if($type == 'me'){
			$map['uid']       = intval($_REQUEST['uid'])?intval($_REQUEST['uid']):$this->mid;
			$map['parent_id'] = 0;
			$order = 'ctime DESC';
			$data = $zyNoteMod->where($map)->order($order)->limit($this->_limit())->select();
			foreach($data as &$value){
				$value['userface']         = getUserFace($value['uid']);
				$value['uname']            = getUserName($value['uid']);
				$value['note_title']       = msubstr($value['note_title'],0,15);
				$value['note_description'] = msubstr($value['note_description'],0,150);
				$value['strtime']          = friendlyDate($value['ctime']);
				$value['qcount']           = $zyNoteMod->where(array('parent_id'=>array('eq',$value['id'])))->count();
				if($value['type'] == 1){//是课程
					$value['video_title'] = M('zy_video')->where('id='.$value['oid'].' and is_del=0')->getField('video_title');
				} else {
					$value['video_title'] = M('zy_album')->where('id='.$value['oid'].' and is_del=0')->getField('album_title');
				}
			}
		}
		$this->exitJson($data);
	}
		
	/**
    * 会员中心点评--异步处理
    * @return void
    */
	public function getReviewList(){
		$type        = 'me';	
		$zyReviewMod = D('ZyReview');	
		if($type == 'me'){
			$map['uid']       = intval($this->mid);
			$map['parent_id'] = 0;
			$order = 'ctime DESC';
			
			$data = $zyReviewMod->where($map)->order($order)->limit($this->_limit())->select();
			foreach($data as &$value){
				$value['star'] = $value['star']/20;
				$value['review_description'] = msubstr($value['review_description'],0,150);
				$value['strtime'] = friendlyDate($value['ctime']);
				$value['qcount']  = $zyReviewMod->where(array('parent_id'=>array('eq',$value['id'])))->count();
				$_map['id'] = array('eq',$value['oid']);
				//找到评论的内容
				if($value['type'] == 1){
					$value['title'] = M('ZyVideo')->where($_map)->getField('`video_title` as `title`');
				}else{
					$value['title'] = M('ZyAlbum')->where($_map)->getField('`album_title` as `title`');	
				}
				$value['title'] = msubstr($value['title'],0,18);
			}
		}
		$this->exitJson($data);
	}
	//系统消息	
	public function notify(){
		$list = D('notify_message','classroom')->where('uid='.$this->mid)->order('ctime desc')->limit($this->_limit())->select();
		foreach($list as &$v){
			if($appname !='public'){
				$v['app'] = model('App')->getAppByName($v['appname']);
			}
		}
		model('Notify')->setRead($this->mid);
		$this->exitJson($list);
   }
   
	//获取账户余额接口
	public function learnc(){
        $money=D('ZyLearnc','classroom')->getUser($this->mid);
        $this->exitJson($money);
    }

    //支付记录
    public function account_pay(){
        $map['uid'] = $this->mid;
        $st = t($this->data['st']);
        $et = t($this->data['et']);
        if(!$st) $st = '';
        if(!$et) $st= '';

        if($st){
            $map['ctime'] = array('gt', $st);
        }
        if($et){
            $map['ctime'] = array('lt', $et);
        }
        $data = D('ZyOrder','classroom')->where($map)->order('ctime DESC,id DESC')->limit($this->_limit())->select();
        foreach($data as &$val){
            $val['title'] = D('ZyVideo','classroom')->getVideoTitleById($val['video_id']);
        }
        $this->exitJson($data);
    }



		
}
	