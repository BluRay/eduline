<?php

	//生成n位数验证码
	function yan_code($no){
		//range 是将0到9 列成一个数组 
		$numbers = range(0,9); 
		//shuffle 将数组顺序随即打乱 
		shuffle ($numbers); 
		//array_slice 取该数组中的某一段 
		
		$result = array_slice($numbers,0,$no);
		return $result;

	}
	/**
	 * 发送短信
	 */
	function sendSms_home($phone, $content){
		$sn  =  C('SN'); //提供的帐号
		$pw  =  C('PWD'); //密码
		$pwd = strtoupper(md5($sn.$pw));
		$data = array(
				'sn'   => $sn, //提供的帐号
				'pwd'  => $pwd, //此处密码需要加密 加密方式为 md5(sn+password) 32位大写
				'mobile'  => mb_convert_encoding($phone, 'GB2312', 'UTF-8'), //手机号 多个用英文的逗号隔开 post理论没有长度限制.推荐群发一次小于等于10000个手机号
				'content' => mb_convert_encoding($content.'【高教网】', 'GB2312', 'UTF-8'), //短信内容
				'ext'   => '',
				'stime' => '', //定时时间 格式为2011-6-29 11:09:21
				'rrid'  => '' //默认空 如果空返回系统生成的标识串 如果传值保证值唯一 成功则返回传入的值
		);
		$url = "http://117.79.237.29/webservice.asmx/mt?";
	
		$ch = curl_init(); // 启动一个CURL会话
		curl_setopt($ch, CURLOPT_URL, $url); // 要访问的地址
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
		curl_setopt($ch, CURLOPT_POST, 1); // 发送一个常规的Post请求
		$data = http_build_query($data);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
		curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
		curl_setopt($ch, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
		$result = curl_exec($ch);
		curl_close($curl);
		if (!$result || curl_errno($ch)) {
			return false;
		}
		if(json_encode($result) === false){
			$result = mb_convert_encoding($result, 'UTF-8', 'GBK');
		}
		$result = strip_tags($result);
		return trim($result);
	}