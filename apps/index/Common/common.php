<?php

	//获取课程的评价份数
	function getNumByCid($cid){
	$cid = intval($cid);
	$num = M('g_cource_comment')->where('cid='.$cid)->count();
	return $num;
	}
	//获取课程的平均评价分
	function getPfenByCid($cid){
	$cid = intval($cid);
	$sum = M('g_cource_comment')->where('cid='.$cid)->sum('pinfen');
	$num = getNumByCid($cid);
	$res = ceil($sum/$num);
	return  $res;
	
	}
	
	
	//生成n位数验证码
	function yan_code($no){
		//range 是将1到10 列成一个数组 
		$numbers = range(0,9); 
		//shuffle 将数组顺序随即打乱 
		shuffle ($numbers); 
		//array_slice 取该数组中的某一段 
		
		$result = array_slice($numbers,0,$no);
		return $result;

	}
	
	
	
	
	
?>