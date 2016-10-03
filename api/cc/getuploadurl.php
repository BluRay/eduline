<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
require_once dirname(__FILE__) . '/spark_config.php';
require_once dirname(__FILE__) . '/class/spark_function.php';

/**
 * 功能：取得请求上传的URL参数
 * 版本：1.0
 * 日期：2014-04-15
 * 作者：dengjb
 */
$info = array();

$info['title'] = trim($_GET['title']);
$info['tag'] = trim($_GET['tag']);
$info['description'] = trim($_GET['description']);
$info['userid'] = $spark_config['user_id'];

$time = time();
$salt = $spark_config['key'];

$info['title'] = spark_function::convert($info['title'], $spark_config['charset'], 'Utf-8');
$info['tag'] = spark_function::convert($info['tag'], $spark_config['charset'], 'utf-8');
$info['description'] = spark_function::convert($info['description'], $spark_config['charset'], 'Utf-8');
$request_url = spark_function::get_hashed_query_string($info, $time, $salt);
echo $request_url;