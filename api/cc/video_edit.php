<?php 
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
require_once dirname(__FILE__) . '/spark_config.php';
require_once dirname(__FILE__) . '/class/spark_function.php';
$time = time();
$info = array();
$info['videoid'] = $_GET['videoid'];
$info['userid'] = $_GET['userid'];
$key = $spark_config['key'];
$url = $spark_config['api_video'] . '?' . spark_function::get_hashed_query_string($info, $time, $key);
$result_xml = spark_function::url_get_xml($url);
$result = spark_function::parse_videos_xml($result_xml);
$video_info = $result['video'];
$video_info = spark_function::convert($video_info, 'Utf-8', $spark_config['charset']);

$video_img = $video_info['image-alternate'];
$category_info = array();
$category_info['userid'] = $_GET['userid'];
$url_c = $spark_config['api_category'] . '?' . spark_function::get_hashed_query_string($category_info, $time, $key);
$result_xml_c = spark_function::url_get_xml($url_c);
$category = spark_function::parse_videos_xml($result_xml_c);
$category_top = array();
$category_top = spark_function::convert($category['video']['category'],'Utf-8', $spark_config['charset']);
 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $spark_config['charset']?>">
<title>视频编辑</title>
</head>
<body>
	<hr />
	<br />
	
	<div id="edit_result"></div>
	<input type="hidden" id="videoid" value="<?php echo $video_info['id'];?>"> 视频标题：
	<input id="title" value="<?php echo $video_info['title'];?>" />
	<br /> 视频标签：
	<input id="tag" value="<?php echo trim($video_info['tags']);?>" />
	<br /> 视频描述：
	<input id="desp" value="<?php echo $video_info['desp'];?>" />
	<br /> 视频分类：
	
	<select id="supercategory" name="supercategory" onchange="show();">
		<?php 
		if(!empty($category_top)) {
			foreach($category_top as $category_top_value) {
			?>
			<option value="<?php echo $category_top_value['id'];?>" <?php if($category_top_value['id']==$video_info['top_category']) echo "selected";?>><?php echo $category_top_value['name'];?></option>
			<?php 
			}
		}
		?>
	</select>
		<?php 
		if(!empty($category_top)) {
			foreach($category_top as $category_top_value) {
			?>
			<select id="sub_<?php echo $category_top_value['id'];?>" style="display: none;" name="sub_category">
				<?php
				//只有1个分类的 数据格式要处理一下
				 if(array_key_exists('id',$category_top_value['sub-category'])) {
				 	$category_sub_tmp = array();
				 	$category_sub_tmp[]= $category_top_value['sub-category'];
				 	$category_top_value['sub-category'] = array();
				 	$category_top_value['sub-category'] = $category_sub_tmp;
				 }
				if(!empty($category_top_value['sub-category'])) {
					foreach($category_top_value['sub-category'] as $category_sub_value) {
					?>
					<option value="<?php echo $category_sub_value['id'];?>" <?php if($category_sub_value['id'] == $video_info['category']) echo "selected";?>><?php echo $category_sub_value['name'];?></option>
					<?php 
					 }
				}
				?>
			</select>
		<?php 
			}
		}
		?>
	
	<br> 选择视频截图：
	<br>
	<?php 
	if(!empty($video_img)) {
		foreach($video_img as $img) {
	?>
	<img src="<?php echo $img['url'];?>" alt="暂无截图"
		style="width: 9%;" />
	<input type="radio" name="video_img"
		value="<?php echo $img['index'];?>" />
	<?php 
		}
	}
	?>
	
	<br>
	<br>
	<input type="button" id="submit" onclick="submitVideo();" value="提交" />

	<script type="text/javascript">
		//控制视频分类显示
		showSub();
		function show() {
			subCategorys = document.getElementsByName("sub_category");
			for ( var i = 0; i < document.getElementsByName("sub_category").length; i++) {
				subCategorys[i].style.display = 'none';
			}
			showSub();
		}
	
		function showSub() {
			var superCategory = document.getElementById("supercategory").value;
			var subCategory = document.getElementById("sub_" + superCategory);
			if (subCategory.value != '') {
				subCategory.style.display = 'inline';
			}
		}
		//控制视频编辑
		function submitVideo() {
			var videoId = document.getElementById("videoid").value;
			var title = encodeURIComponent(
					document.getElementById("title").value, "utf-8");
			var tag = encodeURIComponent(document.getElementById("tag").value,
					"utf-8");
			var description = encodeURIComponent(document
					.getElementById("desp").value, "utf-8");
			var superCategory = encodeURIComponent(document
					.getElementById("supercategory").value, "utf-8");
			var subCategory = document.getElementById("sub_" + superCategory).value;
			var editUrl = "video_edit_ajax.php?videoid=" + videoId + "&title="
					+ title + "&tag=" + tag + "&description=" + description;
			if (document.getElementById("supercategory") != null
					&& subCategory == ''){
				document.getElementById("edit_result").innerHTML = "<div style='color:red;'>一级分类不能添加视频，请重新选择</div>";
				return;
			}
			if (subCategory != null) {
				editUrl = editUrl + "&categoryid=" + subCategory;
			}
			var images = document.getElementsByName("video_img");
			for ( var i = 0; i < document.getElementsByName("video_img").length; i++) {
				if (images[i].checked) {
					editUrl = editUrl + "&imageindex=" + images[i].value;
				}
			}
			var req = getAjax();
			req.open("GET", editUrl, true);
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					if (req.status == 200) {
						var re = req.responseText;//获取返回的内容
						document.getElementById("edit_result").innerHTML = re;
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
