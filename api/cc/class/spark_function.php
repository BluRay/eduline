<?php 
require_once dirname(__FILE__) . '/charset.php';
require_once dirname(__FILE__) . '/xml2array.php';

/**
 * 类名：spark_function
 * 功能：Spark Api 接口公用函数
 * 详细：该页面是请求接口生成参数字符串的公用函数处理文件，不需要修改
 * 版本：1.0
 * 修改日期：2010-12-27
 * 作者：Eachcan
 * 说明：以下代码只是为了方便用户测试而提供的样例代码，用户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 */
class spark_function {
	/**
	 * 生成可供Spark使用的URL参数
	 * @param Array $info 待发送的数组
	 * @param int $time 请求时间
	 * @param String $salt KEY
	 */
	function get_hashed_query_string($info, $time, $salt) {
		$qs = spark_function::get_query_string($info);
		$hash = spark_function::get_hashed_value($qs, $time, $salt);
		$htqs = $qs . "&time=$time&hash=$hash";
		
		return $htqs;
	}
	
	function get_info_hash($info, $time, $salt) {
		$qs = spark_function::get_query_string($info);
		return spark_function::get_hashed_value($qs, $time, $salt);
	}
	
	/**
	 * 生成THQS算法的hash值
	 *
	 */
	function get_hashed_value($qs, $time, $salt) {
		return strtoupper(md5($qs . "&time=$time&salt=$salt"));
	}
	
	/**
	 * 生成THQS算法的信息查询串（Query string）
	 *
	 */
	function get_query_string($info) {
		ksort($info);
		return spark_function::http_build_query($info);
	}
	
	/**
	 * 根据数组生成HTTP请求URL参数
	 * PHP4 版本
	 * @param unknown_type $array
	 */
	function http_build_query($array) {
		$query = '';
		foreach ($array as $key => $value) {
			$key = self::urlencode($key);
			$value = self::urlencode($value);
			$query .= "$key=$value&";
		}
		
		if (strlen($query)) {
			$query = substr($query, 0, -1);
		}
		return $query;
	}
	
	/**
	 * *不要被转义了。
	 * @param $string
	 */
	function urlencode($string) {
		$string = str_replace('*', '-tSl2nWmMsagD-gEr', $string);
		$string = urlencode($string);
		return str_replace('-tSl2nWmMsagD-gEr', '*', $string);
	}
	
	/**
	 * 转换编码
	 * @param int $origen 原始字符串
	 * @param String $from_charset 原始编码 Big5, GBK, Utf-8, ISO-8851-1 ...
	 * @param String $to_charset 目标编码
	 */
	function convert($origen, $from_charset, $to_charset) {
		return Charset::instance($from_charset, $to_charset)->Convert($origen);
	}
	
	function url_get_xml($url, $time_out = "60") {
		$urlarr     = parse_url($url);
		$errno      = "";
		$errstr     = "";
		$prefix = '';
		$port = 80;
		
		if ($urlarr['scheme'] == 'https') {
			$prefix = 'ssl://';
			$port = 443;
		}

		$fp = fsockopen($prefix . $urlarr['host'], $port, $errno, $errstr, $time_out);
		
		if(!$fp) {
			die("ERROR: $errno - $errstr<br />\n");
		} else {
			fwrite($fp, "GET " . $urlarr["path"] . '?' . $urlarr["query"] . " HTTP/1.1\r\n");
			fwrite($fp, "Host: ".$urlarr["host"]."\r\n");
			fwrite($fp, "Connection: close\r\n\r\n");
			while(!feof($fp)) {
			    $info[]=@fread($fp, 1024);
			}
			fclose($fp);
			
			$info = implode("",$info);
			$pos = strpos($info, '<?xml');
			$pos2= strrpos($info, '>');
			$info = substr($info, $pos, $pos2 - $pos + 1);
			return $info;
		}
	}
	
	function parse_videos_xml($string) {
		$xml_parser = new xml2array($string);
		$xml = $xml_parser->parse();
		return $xml;
	}
	
 }