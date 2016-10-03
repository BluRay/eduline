<?php 
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
require_once dirname(__FILE__) . '/spark_config.php';
require_once dirname(__FILE__) . '/storage.php';

/**
 * 功能：列出视频
 * 版本：2.0
 * 日期：2012-07-30
 * 作者：Eachcan, Kelystor,baker95935
 */
$storage = new storage();
$videos = $storage->get_videos();
$video_count = count($videos);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $spark_config['charset']?>">
<title>API PHP demo 视频列表</title>
</head>
<body>
<h1>API PHP demo 视频列表</h1>
<hr />
<a href='index.php'>返回首页</a>
<hr />
<a href="videosync.php?videoidFrom=&videoidTo=" target="_blank">同步视频</a>
<hr>
<div style="color: red;" id="delete_result"></div>
<br />
视频总数：<?php echo $video_count;?> 个<br />
<table border="1">
	<tr>
		<th align="center">视频标题</th>
		<th align="center">图片</th>
		<th align="center">操作</th>
		<th align="center">是否可以播放</th>
		<th align="center">点击播放</th>
		<th align="center">查看代码</th>
	</tr>
<?php foreach ($videos as $video) {?>
<tr>
<td align="center"><?php echo $video['title']?></td>
<td align="center"><img alt="<?php echo $video['title']?>" src="<?php echo $video['image']?>" /></td>
<td align="center"><a href="video_edit.php?userid=<?php echo $spark_config['user_id']?>&videoid=<?php echo $video['id']?>"" target="_blank">编辑</a>  <a
				onclick="deletevideo(this.id);" href="#" id="<?php echo $video['id']?>">删除</a></td>
<?php 
		if ($video['status'] == "OK") {
			//可播放
?>
<td align="center">
可播放
</td>
<td align="center">
<a href="play.php?userid=<?php echo $spark_config['user_id']?>&videoid=<?php echo $video['id']?>"" target="_blank">点击播放</a>
</td>
<?php 
		} else {
			//不可播放
?>
<td align="center">
不可播放
</td>
<td align="center">

</td>
<?php 
		}?>
<td><a href="video_code.php?userid=<?php echo $spark_config['user_id']?>&videoid=<?php echo $video['id']?>"" target="_blank">查看代码</a></td>
<?php 		
		echo '</tr>';
} ?>
</table>
	<script type="text/javascript">
		function deletevideo(videoId) {
			var url = "video_del.php?videoid=" + videoId;
			var req = getAjax();
			req.open("GET", url, true);
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					if (req.status == 200) {
						var re = req.responseText;//获取返回的内容
						document.getElementById("delete_result").innerHTML = re;
					}
				}
			};
			req.send(null);
		}
		function getAjax() {
			var oHttpReq = null;

			if (window.XMLHttpRequest) {
				oHttpReq = new XMLHttpRequest;
				if (oHttpReq.overrideMimeType) {
					oHttpReq.overrideMimeType("text/xml");
				}
			} else if (window.ActiveXObject) {
				try {
					oHttpReq = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					oHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
				}
			} else if (window.createRequest) {
				oHttpReq = window.createRequest();
			} else {
				oHttpReq = new XMLHttpRequest();
			}

			return oHttpReq;
		}
	</script>
</body>
</html>