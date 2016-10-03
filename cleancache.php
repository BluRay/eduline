<?php
header("Content-type: text/html; charset=utf-8"); 
//缓存清理权限
$pwd = 'eduline2016';
//清文件缓存
$dirs	=	array('./_runtime/');

//清理缓存
if( !empty( $_GET['pwd'] ) && trim( $_GET['pwd'] ) == $pwd ) {
	foreach($dirs as $value) {
		rmdirr($value);
		echo "<div style='border:2px solid green; background:#f1f1f1; padding:20px;margin:20px;width:800px;font-weight:bold;color:green;text-align:center;margin: 0 auto;'>\"".$value."\" 成功清除缓存！ </div> <br /><br />";
	}
} else {
	echo "<div style='border:2px solid green; background:#f1f1f1; padding:20px;margin:20px;width:800px;font-weight:bold;color:green;text-align:center;margin: 0 auto;'>哥，别乱来哦！</div>";
}

@mkdir('_runtime',0777,true);

function rmdirr($dirname) {
	if (!file_exists($dirname)) {
		return false;
	}
	if (is_file($dirname) || is_link($dirname)) {
		return unlink($dirname);
	}
	$dir = dir($dirname);
	if($dir){
		while (false !== $entry = $dir->read()) {
			if ($entry == '.' || $entry == '..') {
				continue;
			}
			rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
		}
	}
	$dir->close();
	return rmdir($dirname);
}
function U(){
	return false;
}
?>