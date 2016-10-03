<?php 
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
require_once dirname(__FILE__) . '/spark_config.php';
require_once dirname(__FILE__) . '/class/spark_function.php';
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $spark_config['charset']?>">
<title>API PHP demo 用户信息</title>
</head>
<body>
<h1>API PHP demo 用户信息</h1>
<hr />
<a href='index.php'>返回首页</a>
<hr />
<br />
<?php 
/**
 * 功能：用户信息接口示例
 * 版本：1.0
 * 日期：2010-12-28
 * 作者：Eachcan
 **/
$info = array();
$info['userid'] = $spark_config['user_id'];
$time = time();
$salt = $spark_config['key'];

$request_url = spark_function::get_hashed_query_string($info, $time, $salt);
$url = $spark_config['api_user'] . "?" . $request_url;
$response = spark_function::url_get_xml($url);
$info = spark_function::parse_videos_xml($response);
$info = $info['user'];
$info['version'] = spark_function::convert($info['version'], 'Utf-8', $spark_config['charset']);
?>
账户：<?php echo $info['account'];?><br />
版本：<?php echo $info['version'];?><br />
到期：<?php echo $info['expired'];?><br />
空间：总：<?php echo $info['space']['total'];?>&nbsp;&nbsp;&nbsp;&nbsp;剩余：<?php echo $info['space']['remain'];?><br />
流量：总：<?php echo $info['traffic']['total'];?>&nbsp;&nbsp;&nbsp;&nbsp;已用：<?php echo $info['traffic']['used'];?><br />
</body>
</html>