<?php
class TopicAction extends Action
{
	
	public function index() {
		$cate = model('Topics')->getCate();
		$this->assign('cate',$cate);
		$this->display();
	}
	
	public function getList(){
	    $_GET['cate']=$_GET['cate']?$_GET['cate']:0;
	    $data = model('Topics')->getTopic($_GET['type'],$_GET['cate']);
	    foreach($data['data'] as &$v){
		   $v['images'] = getCover($v['image'],222,145);
		}
        $this->assign('html',$data);
	    $html = $this->fetch('ajax_topic');
	    $data['data']=$html;
	    exit( json_encode($data) );
	}



	public function view(){
	    $id = $_GET['id'] ? intval($_GET['id']) : $this->error('参数错误');
		$data = model ('Topics')->getOnedata($id);
        //推荐阅读
        $map['id'] = array('neq',$id);
        $recData = M('zy_topic')->where($map)->order('readcount desc')->field('id,title,image')->limit(5)->select();
		if(!$data['id']){
			$this->error('不存在的数据');
		}else{
			//获取上一篇
			//model ('Topics')->addread($ids);
			//$down = model ('Topics')->downPage ($ids);
			//$up = model ('Topics')->upPage ($ids);
			//$this->assign('down',$down);
			//$this->assign('up',$up);
			$this->assign('data',$data);
            $this->assign('recData',$recData);
			$this->display();
		}
		
	}
	
}