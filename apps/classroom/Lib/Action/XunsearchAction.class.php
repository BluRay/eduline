<?php
require('/web/server/xunsearch/sdk/php/lib/XS.php');
class XunsearchAction extends Action{
	public function index(){

		try
		{
			$gaojiao  = new XS("/web/server/xunsearch/sdk/php/app/gaojiao.ini");
			$gaojiao_album = new XS("/web/server/xunsearch/sdk/php/app/gaojiao_album.ini");
			$search = $gaojiao->search;
			$search_album = $gaojiao_album->search;
			$keyword = t($_GET['key']);
			$search->setQuery($keyword);
			$search_album->setQuery($keyword);
			$search->addWeight('title','intro','tag');
			$search_album->addWeight('title','intro','tag');
			$search->setLimit(5,0);
			$search_album->setLimit(5,0);
			$result = $search->setFuzzy('yes')->search();
			$count = $search->setFuzzy('yes')->count();
			$result_album = $search_album->setFuzzy('yes')->search();
			$count_album = $search_album->setFuzzy('yes')->count();
			$count = $count + $count_album;
			$result = array_merge($result,$result_album);
			foreach($result as $key => $doc){
				$data[$key]['title'] = $search->highlight($doc->title);
				$data[$key]['intro'] = $search->highlight($doc->intro);
				$data[$key]['tag'] = $search->highlight($doc->tag);
				$data[$key]['id'] = $doc->id;
				$data[$key]['type'] = $doc->type;
				$data[$key]['weight'] = $doc->weight();
			}
			$data = $this->arraySortByKey($data, 'weight',false);
			print_r($data);
		} catch(XSException $e){
			echo $e;
		}
	}
	
/**
* 根据数组中的某个键值大小进行排序，仅支持二维数组
*
* @param array $array 排序数组
* @param string $key 键值
* @param bool $asc 默认正序
* @return array 排序后数组
*/
private function arraySortByKey(array $array, $key, $asc = true)
{
    $result = array();
    // 整理出准备排序的数组
    foreach ( $array as $k => &$v ) {
        $values[$k] = isset($v[$key]) ? $v[$key] : '';
    }
    unset($v);
    // 对需要排序键值进行排序
    $asc ? asort($values) : arsort($values);
    // 重新排列原有数组
    foreach ( $values as $k => $v ) {
        $result[$k] = $array[$k];
    }

    return $result;
}
}