<?php
/**
 * 验证码短信发送模型
 * @author dengjb <dengjiebin@higher-edu.cn>
 * @version GJW2.0
 */
class MessagecodeModel extends Model {

	/**
	 * 发送验证码示例代码
	 */
	public function sendcode(){
		$phone = $_POST['mobile'];
		$code = yan_code(6);
		$code = implode("",$code);
		$con = iconv( "UTF-8", "gb2312//IGNORE" ,'您的注册验证码为:'.$code);
		$name = iconv( "UTF-8", "gb2312//IGNORE" ,'【Eduline】');
    	$res = $this->sendsms($phone,$con.' '.$name);
    	if($res > 0){
    		$_SESSION['code'] = $code;
    	}
    	
    	echo $res;
		
	}
    /**
     * 
     * 发送代码
     * @param string mobile string content
     * @return int res
     */
	public function sendsms($moblie,$content){
   		$sn =  C('SN'); //提供的帐号
   		$pw =  C('PWD'); //密码
   		$pwd= strtoupper(md5($sn.$pw));
  		$data = array(
      	'sn' => $sn, //提供的帐号
      	'pwd' =>$pwd, //此处密码需要加密 加密方式为 md5(sn+password) 32位大写
      	'mobile' => $moblie, //手机号 多个用英文的逗号隔开 post理论没有长度限制.推荐群发一次小于等于10000个手机号
      	'content' => $content, //短信内容
      	'ext' => '',
      	'stime' => '', //定时时间 格式为2011-6-29 11:09:21
      	'rrid' => '' //默认空 如果空返回系统生成的标识串 如果传值保证值唯一 成功则返回传入的值 
          );

     	$url = "http://117.79.237.29/webservice.asmx/mt?";

 
 
     $retult= $this->api_notice_increment($url,$data);
	 
     $retult=str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$retult);
     $retult=str_replace("<string xmlns=\"http://tempuri.org/\">","",$retult);
	 $retult=str_replace("</string>","",$retult);
	 
	 return $retult;
     /**if($retult>0)
			echo '发送成功返回值为:'.$retult;
			else
			echo '发送失败 返回值为:'.$retult;
			*/
	}

  	public function api_notice_increment($url, $data){
     
    	$curl = curl_init(); // 启动一个CURL会话
    	curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
    	curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
    	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    	curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    	curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    	$data = http_build_query($data);
    	curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
    	curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    	curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
     
     
     	$lst = curl_exec($curl);
      	if (curl_errno($curl)) {
       	echo 'Errno'.curl_error($curl);//捕抓异常
      	}
    	 curl_close($curl);
     	return $lst;
 	}
}