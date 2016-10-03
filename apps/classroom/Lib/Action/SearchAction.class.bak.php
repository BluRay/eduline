<?php
require(ADDON_PATH.'/library/sphinxapi.class.php');
class SearchAction extends Action{
	
	public function index(){
		$sphinx = new SphinxClient();
		$sphinx->SetServer("127.0.0.1",3308);
		$sphinx->SetArrayResult(true);
		$sphinx->SetFieldWeights(array('title' => 16, 'intro' => 10, 'tag' => 1));
		$sphinx->SetSortMode( SPH_SORT_EXTENDED, "@weight DESC");
		$page = isset($_GET['p']) ? intval($_GET['p']) : 1;
		$perNum = 10;
		$offset = ($page - 1) * $perNum;
		$sphinx->setLimits ($offset,$perNum);
		$keyword=t($_GET['key']);
		$this->assign("search_key",$keyword);
		$result=$sphinx->Query($keyword,"*");
		$this->assign('result_count',$result['total']);
		if($result['total']%10 == 0){
			$total_page = intval($result['total']/10);	
		} else {
			$total_page = intval($result['total']/10) + 1;
		}
		if($result['total'] && $page > $total_page){
			$this->assign('isAdmin',1);
			$this->error("输入的分页参数错误");
		}
		foreach($result['matches'] as $key => $doc){
			$rs[$key]['id'] = $doc[id];
			$rs[$key]['type'] = $doc['attrs']['type'];
			$rs[$key]['weight'] = $doc['weight'];
		}
//		$aRes = array_slice($rs,$offset,10);
		foreach($rs as $key=> $vo){
			if($vo['type'] == '1'){
				$resT['data'][$key]= M('zy_video')->where('id = '.$vo['id'])->field('video_title as title,video_category as category,video_intro as intro,video_collect_count as collect,uid,id,video_score as score,middle_ids')->find();
				$resT['data'][$key]['collection'] =  M('zy_collection')->where('source_id='.$vo['id'].' and source_table_name="zy_video"')->count();
				$resT['data'][$key]['type'] = '1';
				$resT['data'][$key]['weight'] = $vo['weight'];
				$resT['data'][$key]['isGetResource'] =  isGetResource(1,$vo['id'],array('video','upload','note','question'));
			} elseif($vo['type'] == '2'){
				$resT['data'][$key] = M('zy_album')->where('id = '.$vo['id'])->field('album_title as title,album_category as category,album_intro as intro,album_collect_count as collect,uid,id,album_score as score,middle_ids')->find();
				$resT['data'][$key]['collection'] =  M('zy_collection')->where('source_id='.$vo['id'].' and source_table_name="zy_album"')->count();
				$resT['data'][$key]['type'] = '2';
				$resT['data'][$key]['weight'] = $vo['weight'];
				$resT['data'][$key]['isGetResource'] =  isGetResource(2,$vo['id'],array('video','upload','note','question'));
			}
		}
		if($_GET['ajax']){
			$this->assign('search_result',$resT['data']);
			$html = $this->fetch('result_list');
			$data['data'] = $html;
			$data['totalPages'] = $total_page;
			$data['totalRows'] = $result['total'];
			$data['nowPage'] = $page;
			$this->ajaxReturn($data);
			exit;
		}
		//热门标签
		$tag_order = new Model();
		$tag_ids = $tag_order->query("select `tag_id` from ".C('DB_PREFIX')."app_tag where app='classroom' group by `tag_id` order by count(tag_id) desc");
		foreach($tag_ids as $key => $tag){
			$tag_ids[$key]['tag_name'] = model("Tag")->getTagNames($tag['tag_id']);
		}
		$this->assign('hot_tag',$tag_ids);
		$resT['nowPage'] = $page;
		$resT['totalPages'] = $total_page;
		$resT['totalRows'] = $result['total'];
		$this->assign('rest',$resT);
		if(!$resT['totalRows']){
			$this->display('no_result');
		} else {
			$this->display();
		}
	}
}