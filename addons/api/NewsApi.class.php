<?php
/**
 * 资讯api
 * utime : 2016-03-06
 */

class NewsApi extends Api{

	//获取资讯分类
	function getCate(){
		$all  = array(0=>array( 'topic_category_id'=>'0','title'=>'全部') );
		$list = M('zy_topic_category')->field('topic_category_id,title')->order('sort asc')->findAll();
		$list = array_merge($all , $list);
		$list ? $this->exitJson($list , '1') : $this->exitJson( array() );
	}

	//获取资讯列表
	function getList(){
		$cid = intval( $this->data['cid'] );
		$order_type = intval( $this->data['order'] ) ? intval( $this->data['order'] ) : 1;//1最新 2最热
		if( $cid > 0 ) {
			$map['cate'] = $cid;
		}
		if( $order_type == 1 ) {
			$order = 'dateline desc';
		} else {
			$order = 'readcount desc';
		}
		$map['is_del'] = 0;
		$list = M('zy_topic')->where($map)->field('`id`,`title`,`desc`,`readcount`,`dateline`,`image`')->order($order)->limit( $this->_limit() )->findAll();
		foreach($list as &$val){
			$val['image'] = getCover($val['image'],280,145);
			$val['desc']  = getShort($val['desc'],200);
			$val['dateline']  = friendlyDate($val['dateline']);
		}
		$list ? $this->exitJson($list  , '1') : $this->exitJson( array() );
	}

	//获取资讯详情
	function getInfo(){
		$id = $this->data['id'];
		$map['id'] = $id;
		$map['is_del'] = 0;
		$info = M('zy_topic')->where($map)->find();
		$info['dateline'] = friendlyDate($info['dateline']);
		//阅读量+1
		M('zy_topic')->where('id='.$id)->setInc('readcount');
		$info ? $this->exitJson($info  , '1') : $this->exitJson( array() );
	}


	
	
		
		
		
}
	