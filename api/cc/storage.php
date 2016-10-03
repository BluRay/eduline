<?php
/**
 * 类名：storage
 * 功能：数据存储操作类
 * 详细：本类封装了数据存储相关操作
 * 版本：1.0
 * 修改日期：2010-12-28
 * 作者：Eachcan, Kelystor
 * 说明：以下代码只是为了方便用户测试而提供的样例代码，用户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 */
class storage {
	var $videos_file = 'videos.txt';
	var $urls_file = 'urls.txt';
	
	 function __construct() {
	 	if (!file_exists($this->videos_file)) {
	 		@touch($this->videos_file);
	 	}
	 	if (!file_exists($this->urls_file)) {
	 		@touch($this->urls_file);
	 	}
	}
	
	function get_videos() {
		$contents = file_get_contents($this->videos_file);
		$videos = unserialize($contents);
		
		return is_array($videos) ? $videos : array();
	}
	
	function save_videos($videos) {
		$contents = serialize($videos);
		file_put_contents($this->videos_file, $contents);
	}
	
	function get_notice_urls() {
		return file($this->urls_file);
	}
	
	function save_url($url) {
		return file_put_contents($this->urls_file, $url . "\r\n", FILE_APPEND);
	}
}
