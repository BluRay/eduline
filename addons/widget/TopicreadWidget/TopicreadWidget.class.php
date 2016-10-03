<?php

class TopicreadWidget extends Widget {
	
	
	public function render($data) {
		
		$template = 'read.html';
		$var = model('Topics')->getTjlist($data['limit']);
		//渲染模版
        $content = $this->renderFile(dirname(__FILE__)."/".$template,$var);
        unset($var,$data);
        //输出数据
        return $content;
	}
	
	
	
	
}