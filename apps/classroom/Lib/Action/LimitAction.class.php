<?php
	/**
	 * 限时免费控制器
	 */
	class LimitAction extends CommonAction{
		
		public function index(){
			$this->display();
		}
		
		/**
		 * 获取数据
		 */
		public function getList($return =false,$page = 1){
			$limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
			$return = false;
			$orders =' ORDER BY `ctime` DESC';
			$where = ' WHERE `is_del` = 0 AND `limit_discount`=0.00 AND `is_activity` = 1 AND (`uctime` > '.time().' AND listingtime < '.time().') AND `is_tlimit` = 1 AND (`starttime` < '.time().' AND `endtime` > '.time().')';
			$map['is_del'] = '0';
			$map['is_activity'] = '1';
			$map['uctime'] = array('GT',time());
			$map['listingtime'] = array('LT',time());
			$map['is_tlimit'] = '1';
			$map['limit_discount']=0.00;
			$map['starttime'] = array('LT',time());
			$map['endtime'] =array('GT',time());
			$sql = "SELECT * FROM ".C('DB_PREFIX').'zy_video'.$where.$orders;
			$count = count(M('')->query($sql));
			$data = M('')->findPageBySql($sql,$count,$limit);
			foreach($data['data'] as $key => $value){
				$data['data'][$key]['is_buy'] = isBuyVideo($this->mid, $value['id']);
				$data['data'][$key]['price'] = getPrice(D("ZyVideo")->getVideoById($value['id'], $this->mid));
				$data['data'][$key]['cover'] = getAttachUrlByAttachId($value['middle_ids']);
				$data['data'][$key]['star_score'] = $value['score'] / 20;
				$data['data'][$key]['isGetResource'] = isGetResource(1,$value['id'],array('video','upload','note','question'));
			}
			if($data){
				$this->assign('data',$data['data']);
				//关注
				$this->assign('limit',$limit);
				$fids = model('Follow')->field('fid')->where('uid='.intval($this->mid))->select();
	       		$this->assign('fids', getSubByKey($fids, 'fid'));
				$html = $this->fetch("fetch_list");
			}
			if($return){
				return $data;
			} else {
				$data['_data'] = $data['data'];
				$data['data'] = $html;


				echo json_encode($data);
				exit;
			}
		}
	}