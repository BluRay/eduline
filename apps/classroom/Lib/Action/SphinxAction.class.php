<?php
require(ADDON_PATH.'/library/sphinxapi.class.php');
class SphinxAction extends Action{
	
	public function index(){
		$sphinx = new SphinxClient();
		$sphinx->SetServer("127.0.0.1",3308);
		$sphinx->SetFieldWeights(array('title' => 16, 'intro' => 10, 'tag' => 1));
		$sphinx->SetSortMode( SPH_SORT_EXTENDED, "@weight DESC");
		$keyword=t($_GET['key']);
		$result=$sphinx->Query($keyword,"*");
		print_r($result);
		$this->assign('result_count',$result['total']);
		
		$page = isset($_GET['p']) ? intval($_GET['p']) : 1;
		$total_page = intval($result['total']/3) + 1;
		if($page > $total_page){
			$this->assign('isAdmin',1);
			$this->error("输入的分页参数错误");
		}
		$aRes = array_slice($result['matches'], ($page-1) * 3, 3);
		foreach($aRes as $key=> $vo){
			if($vo['type'] == '1'){
				$resT['data'][$key] = M('zy_video')->where('id = '.$vo['id'])->field('video_title as title,video_category as category,video_intro as intro,video_collect_count as collect,uid,id,video_score as score,middle_ids')->find();
				$resT['data'][$key]['type'] = '1';
				$resT['data'][$key]['weight'] = $vo['weight'];
			} elseif($vo['type'] == '2'){
				$resT['data'][$key] = M('zy_album')->where('id = '.$vo['id'])->field('album_title as title,album_category as category,album_intro as intro,album_collect_count as collect,uid,id,album_score as score,middle_ids')->find();
				$resT['data'][$key]['type'] = '2';
				$resT['data'][$key]['weight'] = $vo['weight'];
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