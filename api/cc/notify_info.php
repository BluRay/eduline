<?php 
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
require_once dirname(__FILE__) . '/spark_config.php';
require_once dirname(__FILE__) . '/storage.php';
require_once dirname(__FILE__) . '/class/spark_function.php';
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $spark_config['charset']?>">
<title>API PHP demo 视频回调</title>
<script type="text/javascript" src="js/swfobject.js"></script>
</head>
<body>
<h1>API PHP demo 视频回调</h1>
<hr />
<a href='index.php'>返回首页</a>
<hr />
<?php 
$storage = new storage();
$urls = $storage->get_notice_urls();
foreach ($urls as $url) { ?>
	url:<?php echo $url;?><br />
<?php } ?>
</body>
</html>