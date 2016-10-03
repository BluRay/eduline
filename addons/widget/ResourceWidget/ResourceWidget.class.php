<?php
/**
 * 提问/点评/笔记/插件
 * @example W('Resource',array('kztype'=>1,'kzid'=>1,'type'=>1,'template'=>'album_question'))
 * @author ashangmanage
 * @version CY1.0
 */
class ResourceWidget extends Widget {
	private $tableList = array(
		1=>'zy_question',2=>'zy_review',3=>'zy_note'
	);
	
	
	
	/**
	 * @param integer kztype //数据分类 1:课程;2:专辑;
	 * @param integer kzid //课程或者专辑ID
	 * @param integer type //分类类型 1:提问,2:点评,3:笔记
	 * @param string template 模板名称
	 */
	public function render($data) {
		$var = array();
		$var['kztype']      = 1;//数据分类 1:课程;2:专辑;
		$var['kzid']        = 0;//课程或者专辑ID
        $var['type']        = 1;//分类类型 1:提问,2:点评,3:笔记
		$var['ispage']      = true;//是否分页
		$var['template']    = 'album_question';//模板名称
		$var['ispage']      = $data['ispage']?'true':'false';//是否分页
		//是否取信息
		$var['isdata']      = $data['isdata']?true:false;
		
        is_array($data) && $var = array_merge($var,$data);
		//获得模板名称
		$template = $var['template'].'.html';
		
		$mulus = array();
		
		if($var['isdata']){
			if($var['kztype'] == 2){
				//序列化字段---让专辑和课程的字段看起来一样
				$field = '`id`,`album_title` as `title`,`uid`,`album_score` as `score`,`cover`,`album_video`,`album_comment_count` as `comment_count`';
				//取得专辑信息
				$baseInfo = M('ZyAlbum')->where(array('id'=>array('eq',intval($var['kzid']))))->field($field)->find();
				
				$videoids = trim( D('Album','classroom')->getVideoId($baseInfo['id']) , ',');
				//取目录信息并根据后台添加的id顺序排序
//				$mulus    = M('ZyVideo')->where(array('id'=>array('in',(string)$videoids)))->field('*')->select();
				$sql = 'SELECT * FROM ' .C("DB_PREFIX").'zy_video WHERE `id` IN ('.(string)$videoids.') ORDER BY find_in_set(id,"'.(string)$videoids.'")';
				$result = M('')->query($sql);
				$mulus = $result;
			}else{
				//序列化字段---让专辑和课程的字段看起来一样
				$field = '`id`,`video_title` as `title`,`uid`,`video_id`,`video_score` as `score`,`cover`,`video_comment_count` as `comment_count`';
				$field = '*';
				//取得课程信息
				$baseInfo   = M('ZyVideo')->where(array('id'=>array('eq',intval($var['kzid']))))->field($field)->find();
				//取目录信息
				$mulus[0] = array(
					'id' => $baseInfo['id'],'video_id' => $baseInfo['video_id'],'video_title' => $baseInfo['title'],
				);
				$mulus[0] = $baseInfo;
			}
			//基础数据
			$baseInfo['username'] = getUserName($baseInfo['uid']);
			$baseInfo['score']    = floor(intval($baseInfo['score'])/20);
			$baseInfo['title']    = msubstr($baseInfo['title'],0,20);
			//目录
			foreach($mulus as &$value){
				$value['video_title'] = msubstr($value['video_title'],0,20);
				
				$value['mzprice']      = getPrice($value,$this->mid,true,true);
				
				$isok = M('ZyService')->checkVideoAccess(intval($this->mid),$value['id']);
				if($isok){
					$value['isBuyVideo']   = 1;
				}else{
					$value['isBuyVideo']   = isBuyVideo($this->mid,$value['id'])?1:0;	
				}
			}
			//转换数据
			$var['baseInfo'] = $baseInfo;
			$var['mulus']    = $mulus;
		
		}
		$var['username'] = getUserName($this->mid);
		$var['userface'] = $this->mid ? getUserFace($this->mid, 'm') : THEME_URL.'/_static/image/noavatar/middle.jpg';
		$var['user_src'] = U('classroom/UserShow/index','uid='.$this->mid);
		$var['mid'] = $this->mid;
		//print_r($var);
		//渲染模版
        $content = $this->renderFile(dirname(__FILE__)."/".$template,$var);
        unset($var,$data);
        //输出数据
        return $content;
	}
	
	/**
	 * 获取提问列表
	 * @param integer kztype //数据分类 1:课程;2:专辑;
	 * @param integer kzid //课程或者专辑ID
	 * @param integer type //分类类型 1:提问,2:点评,3:笔记
	 * @param string template 模板名称
	 */
	public function getList(){
		$kztype = intval($_POST['kztype']);
		$kzid   = intval($_POST['kzid']);
		$type   = intval($_POST['type']);
		$limit  = intval($_POST['limit']);
		
		$stable = parse_name($this->tableList[$type],1);
		//如果是课程的话就是=，专辑就是in
		$map['oid']        = $kzid;
		$map['parent_id']  = 0;
		
		//如果是专辑的话、、需要把下面的所有的
		if($kztype == 2){
			$vids = M('ZyAlbum')->where(array('id'=>array('eq',$kzid)))->getField('album_video');
			$vids = getCsvInt($vids);
			$vids = $vids?$vids:'0';
			if($type!=2){
				$map['oid']        = array('in',(string)$kzid.','.$vids);
			}
		}else{
			$map['type']       = $kztype;
		}
		if($type == 3){
			//复合查询--如果是他本人就连带私密的也查出来
			$where['uid']      = array('eq', $this->mid);
			$where['is_open']  = array('eq',1);
			$where['_logic']   = 'or';
			
			$map['_complex'] = $where;
		}
		$data = M($stable)->where($map)->order('`ctime` DESC')->findPage($limit);
		$data['userface'] = $this->mid ? getUserFace($this->mid, 's') : THEME_URL.'/_static/image/noavatar/small.jpg';
		$data['user_src'] = U('classroom/UserShow/index','uid='.$this->mid);
		$data['username'] = getUserName($this->mid);
		$zyVoteMod = D('ZyVote','classroom');
		foreach($data['data'] as $key => &$value){
			$value['strtime']  = friendlyDate($value['ctime']);
			$value['uid']=$value['uid'];
			$value['username'] = getUserName($value['uid']);
			$value['userface'] = getUserFace($value['uid'],'m');
			$value['user_src'] = U('classroom/UserShow/index','uid='.$value['uid']);
			$value['count']    = $this->getListCount($type,$value['id']);
			if($type == 1){
				$value['qst_src']  = U('classroom/Index/resource','rid='.$value['id'].'&type=3');
			}else if($type == 2){
				$value['star']     = $value['star']/20;
				$value['sname']    = $this->reSName($value['uid'],$value['oid'],$kztype);
				//判断时候已经投票了
				$value['isvote']   = $zyVoteMod->isVote($value['id'],'zy_review',$this->mid)?1:0;
				$value['username'] = intval($value['is_secret'])?'*****':$value['username'];
			}else if($type == 3){
				$value['note_src']  = U('classroom/Index/resource','rid='.$value['id'].'&type=4');
				$value['note_description']  = msubstr($value['note_description'],0,44);
				$value['strtime']  = msubstr($value['strtime'],0,6,'utf-8',false);;
			}
			$value['username'] = msubstr($value['username'],0,8);
		}
		
		echo json_encode($data?$data:array());exit;
	}
	
	
	private function getListCount($type,$id){
		$stable = parse_name($this->tableList[$type],1);
		$map['parent_id'] = array('eq',$id);
		$count = M($stable)->where($map)->order('`ctime` DESC')->count();
		return $count;
	}
	
	/**
	 * 根据ID获取评论信息【提问/点评/笔记】
	 * @param integer id   //提问/点评/笔记   表里面的ID
	 * @param integer type //分类类型 1:提问,2:点评,3:笔记
	 */
	public function getListForId(){
		$type = intval($_POST['type']);
		$id   = intval($_POST['id']);
		$limit  = intval($_POST['limit']);
		
		
		$stable = parse_name($this->tableList[$type],1);
		$map['parent_id'] = array('eq',$id);
		$data = M($stable)->where($map)->order('`ctime` DESC')->findPage($limit);
		foreach($data['data'] as $key =>&$value){
			$value['strtime']  = friendlyDate($value['ctime']);
			$value['username'] = getUserName($value['uid']);
			$value['userface'] = getUserFace($value['uid'], 's');
			$value['user_src'] = U('classroom/UserShow/index','uid='.$value['uid']);
			$value['reply_user'] = $value['reply_uid'] ? getUserName($value['reply_uid']) : '';
			// $value['content'] = $type == 1 ? $value['qst_description'] : $type == 2 ? $value['review_description'] : $value['note_description'];
			if($type == 1){
				$value['content'] = $value['qst_description'];
			}else if($type == 2){
				$value['content'] = $value['review_description'];
			}else{
				$value['content'] = $value['note_description'];
			}
		}
		echo json_encode($data?$data:array());exit;
	}
	
	
	
	
	
	
	
	
	
	private function reSName($uid,$oid,$kztype){
		$status = D('ZyOrder','classroom')->getLearnStatus($uid,$oid,$kztype);
		if($status == 0){
			return '待学习';
		}else if($status == 1){
			return '在上课';
		}else if($status == 2){
			return '已学完';
		}
	}
	
	
	
	
	
}