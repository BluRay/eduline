<?php
require_once dirname(__FILE__) . '/spark_config.php';?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $spark_config['charset']?>">
<title>API PHP demo 首页</title>
</head>
<!-- Spark Api Demo -->
<body>
	<h1>API PHP demo 首页</h1>
	<hr />
	<h4><a href="upload.php" target="_blank">视频上传</a></h4>
	<h4><a href="notify_info.php" target="_blank">视频回调</a></h4>
	<h4><a href="videolist.php" target="_blank">视频列表</a></h4>
	<h4><a href="userinfo.php" target="_blank">用户信息</a></h4>
</body>
</html>