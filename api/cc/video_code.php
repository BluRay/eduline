<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
require_once dirname(__FILE__) . '/spark_config.php';
require_once dirname(__FILE__) . '/class/spark_function.php';
$time = time();
$info = array();
$action = $_GET['action'];
if($action == 'update') {
	$info['videoid'] = $_POST['videoid'];
	$info['player_width'] = $_POST['width'];
	$info['player_height'] = $_POST['height'];
	$info['auto_play'] = $_POST['autoplay'];	
} else {
	$info['videoid'] = $_GET['videoid'];
}
$videoid = $info['videoid'];
$key = $spark_config['key'];
$url = $spark_config['api_playcode'] . '?' . spark_function::get_hashed_query_string($info, $time, $key);
$result_xml = spark_function::url_get_xml($url);
$result = spark_function::parse_videos_xml($result_xml);
$playcode = $result['video']['playcode'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $spark_config['charset']?>">
<title>playcode info</title>
</head>
<body>
	<h1>API php demo 获取视频播放代码</h1>
	<hr />
	<a href='index.php'>返回首页</a>
	<hr />
	<br />
	
	<div style="float: left;">
		默认JavaScript代码：<br><textarea cols="50px" rows="5px" id="play_code"><?php echo $playcode; ?></textarea>
		<form action="video_code.php?action=update" method='post'>
			<input type="hidden" name="videoid" value="<?php echo $videoid;?>">
			播放器宽度：<input id="width" name="width" type="text" size="5px"/>px<br>
			播放器高度：<input id="height" name="height" type="text" size="5px"/>px<br>
			是否自动播放：<input name="autoplay" type="radio" value="true"
				id="autoplay1" />True<input name="autoplay" type="radio"
				value="false" id="autoplay1" />False <br> <input type="submit"
				value="设置"">
		</form>
	</div>
	<div style="float: right; padding-right: 25%">
		视频展示<?php echo $playcode; ?></div>

</body>
</html>
