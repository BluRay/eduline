<?php

@set_time_limit(1000);
if (phpversion() <= '5.3.0')
    set_magic_quotes_runtime(0);
if ('5.2.0' > phpversion())
    exit('您的php版本过低，不能安装本软件，请升级到5.2.0或更高版本再安装，谢谢！');

date_default_timezone_set('PRC');
error_reporting(E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=UTF-8');
define('SITEDIR', _dir_path(substr(dirname(__FILE__), 0, -8)));
define("VERSION", 'Version 1.1');
$sqlFile = 'install.sql';

if(file_exists("./install.lock")){
	exit('你已经安装过此网站，若需重新安装请手动删除网站目录下install/install.lock文件!');
}

if (!is_readable('./' . $sqlFile)) {
    echo '数据库文件无法读取，请检查install/install.sql是否存在!';
    exit;
}


$Title = "Eduline在线教育系统";
$Powered = "Powered by Eduline";
$steps = array(
    '1' => '安装许可协议',
    '2' => '运行环境检测',
    '3' => '安装参数设置',
    '4' => '安装详细过程',
    '5' => '安装完成',
);
$step = isset($_GET['step']) ? $_GET['step'] : 1;

//地址
$scriptName = !empty($_SERVER["REQUEST_URI"]) ? $scriptName = $_SERVER["REQUEST_URI"] : $scriptName = $_SERVER["PHP_SELF"];
$rootpath = @preg_replace("/\/(I|i)nstall\/index\.php(.*)$/", "", $scriptName);
$domain = empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
if ((int) $_SERVER['SERVER_PORT'] != 80) {
    $domain .= ":" . $_SERVER['SERVER_PORT'];
}
$domain = $domain . $rootpath;

switch ($step) {

    case '1':
        include_once ("./templates/s1.php");
        exit();

    case '2':

        if (phpversion() < 5) {
            die('本系统需要PHP5+MYSQL >=4.1环境，当前PHP版本为：' . phpversion());
        }

        $phpv = @ phpversion();
        $os = PHP_OS;
        $os = php_uname();
        $tmp = function_exists('gd_info') ? gd_info() : array();
        $server = $_SERVER["SERVER_SOFTWARE"];
        $host = (empty($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_HOST"] : $_SERVER["SERVER_ADDR"]);
        $name = $_SERVER["SERVER_NAME"];
        $max_execution_time = ini_get('max_execution_time');
        $allow_reference = (ini_get('allow_call_time_pass_reference') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
        $allow_url_fopen = (ini_get('allow_url_fopen') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
        $safe_mode = (ini_get('safe_mode') ? '<font color=red>[×]On</font>' : '<font color=green>[√]Off</font>');

        $err = 0;
        if (empty($tmp['GD Version'])) {
            $gd = '<font color=red>[×]Off</font>';
            $err++;
        } else {
            $gd = '<font color=green>[√]On</font> ' . $tmp['GD Version'];
        }
        if (function_exists('mysql_connect')) {
            $mysql = '<span class="correct_span">&radic;</span> 已安装';
        } else {
            $mysql = '<span class="correct_span error_span">&radic;</span> 出现错误';
            $err++;
        }
        if (ini_get('file_uploads')) {
            $uploadSize = '<span class="correct_span">&radic;</span> ' . ini_get('upload_max_filesize');
        } else {
            $uploadSize = '<span class="correct_span error_span">&radic;</span>禁止上传';
        }
        if (function_exists('session_start')) {
            $session = '<span class="correct_span">&radic;</span> 支持';
        } else {
            $session = '<span class="correct_span error_span">&radic;</span> 不支持';
            $err++;
        }
        $folder = array('_runtime/', 'config/', 'data/','install/' );
        include_once ("./templates/s2.php");
        exit();

    case '3':

        if ($_GET['testdbpwd']) {
            $dbHost = $_POST['dbHost'] . ':' . $_POST['dbPort'];
            $conn = @mysql_connect($dbHost, $_POST['dbUser'], $_POST['dbPwd']);
            die($conn ? "1" : "");
        }
        include_once ("./templates/s3.php");
        exit();


    case '4':
        if (intval($_GET['install'])) {
            $n = intval($_GET['n']);
            $arr = array();

            $dbHost = trim($_POST['dbhost']);
            $dbPort = trim($_POST['dbport']);
            $dbName = trim($_POST['dbname']);
            $dbHost = empty($dbPort) || $dbPort == 3306 ? $dbHost : $dbHost . ':' . $dbPort;
            $dbUser = trim($_POST['dbuser']);
            $dbPwd = trim($_POST['dbpw']);
            $dbPrefix = empty($_POST['dbprefix']) ? 'el_' : trim($_POST['dbprefix']);
            $uname = trim($_POST['manager_email']);
            $password = trim($_POST['manager_pwd']);
			$randkey = uniqid(rand());
			$config = array(
				// 数据库常用配置
				'DB_TYPE'			=>	'mysql',		// 数据库类型

				'DB_HOST'			=>	 $dbHost,		// 数据库服务器地址
				'DB_NAME'			=>	 $dbName,		// 数据库名
				'DB_USER'			=>	 $dbUser,		// 数据库用户名
				'DB_PWD'			=>	 $dbPwd,		// 数据库密码

				'DB_PORT'			=>	 $dbPort,		// 数据库端口
				'DB_PREFIX'			=>	 $dbPrefix,		// 数据库表前缀（因为漫游的原因，数据库表前缀必须写在本文件）
				'DB_CHARSET'		=>	 'utf8',		// 数据库编码
				'SECURE_CODE'		=>	 $randkey,	    // 数据加密密钥
				'COOKIE_PREFIX'		=>	 'el_',	    // 数据加密密钥
			);

            $conn = @ mysql_connect($dbHost, $dbUser, $dbPwd);
            if (!$conn) {
                $arr['msg'] = "连接数据库失败!";
                die(json_encode($arr));
            }
            mysql_query("SET NAMES 'UTF8'");
            $version = mysql_get_server_info($conn);
            if ($version < 4.1) {
                $arr['msg'] = '数据库版本太低!';
                die(json_encode($arr));
            }

            if (!mysql_select_db($dbName, $conn)) {
                //创建数据时同时设置编码
                if (!mysql_query("CREATE DATABASE IF NOT EXISTS `" . $dbName . "` DEFAULT CHARACTER SET UTF8;", $conn)) {
                    $arr['msg'] = '数据库 ' . $dbName . ' 不存在，也没权限创建新的数据库！';
                    die(json_encode($arr));
                }
                if (empty($n)) {
                    $arr['n'] = 1;
                    $arr['msg'] = "成功创建数据库:{$dbName}<br>";
                    die(json_encode($arr));
                }
                mysql_select_db($dbName, $conn);
            }

            //读取数据文件
            $sqldata = file_get_contents('./' . $sqlFile);
            $sqlFormat = sql_split($sqldata, $dbPrefix);

            //执行SQL语句
            $counts = count($sqlFormat);

            for ($i = $n; $i < $counts; $i++) {
                $sql = trim($sqlFormat[$i]);

                if (strstr($sql, 'CREATE TABLE')) {
                    preg_match('/CREATE TABLE `([^ ]*)`/', $sql, $matches);
                    mysql_query("DROP TABLE IF EXISTS `$matches[1]");
                    $ret = mysql_query($sql);
                    if ($ret) {
                        $message = '<li><span class="correct_span">&radic;</span>创建数据表' . $matches[1] . '，完成</li> ';
                    } else {
                        $message = '<li><span class="correct_span error_span">&radic;</span>创建数据表' . $matches[1] . '，失败</li>';
                    }
                    $i++;
                    $arr = array('n' => $i, 'msg' => $message);
                    die(json_encode($arr));
                } else {
                    $ret = mysql_query($sql);
                    $message = '';
                    $arr = array('n' => $i, 'msg' => $message);
                    //echo json_encode($arr); exit;
                }
            }

            if ($i == 999999) exit;
            //插入管理员
			$password = md5(md5($password).'11111');
			$query = "INSERT INTO `{$dbPrefix}user`  VALUES (1, '{$uname}', '{$password}', '11111', '管理员', '{$uname}', '', 1, '北京市 北京市 海淀区', 1, 1, 1, ".time().", 1, '', '', 110000, 110100, 110108, '1', 'zh-cn', 'PRC', 0, 'G', '', '', ".time().", 4, ".time().", '管理员 guanliyuan', '', ".time().", 0, 0, 0, 0, 0, 0, 0, 0);";
			mysql_query($query);
			//将管理员加入“管理员”用户组
			$sql_user_group	= "INSERT INTO `{$dbPrefix}user_group_link` (`id`,`uid`,`user_group_id`) VALUES ('1', '1','1');";
			if( mysql_query($sql_user_group) ){
				$quit = true;
			} else {
				$quit =	false;
			}
            if( $quit ) {
				$message = '成功添加管理员<br />成功写入配置文件<br>安装完成．';
			} else {
				$message = '添加管理员失败，请重新安装！';
			}
			
            file_put_contents("../config/config.inc.php", ("<?php \r\n return " . var_export($config, true) . "; \r\n ?>") );
            $arr = array('n' => 999999, 'msg' => $message);
            die(json_encode($arr));
        }

        include_once ("./templates/s4.php");
        exit;

    case '5':
    	file_get_contents('../cleancache.php');
        include_once ("./templates/s5.php");
        @touch('./install.lock');
        exit;
}

function testwrite($d) {
    $tfile = "_test.txt";
    $fp = @fopen($d . "/" . $tfile, "w");
    if (!$fp) {
        return false;
    }
    fclose($fp);
    $rs = @unlink($d . "/" . $tfile);
    if ($rs) {
        return true;
    }
    return false;
}

function sql_execute($sql, $tablepre) {
    $sqls = sql_split($sql, $tablepre);
    if (is_array($sqls)) {
        foreach ($sqls as $sql) {
            if (trim($sql) != '') {
                mysql_query($sql);
            }
        }
    } else {
        mysql_query($sqls);
    }
    return true;
}

function sql_split($sql, $tablepre) {
	//此处的前缀必须和sql文件的数据表前缀一致
    if ($tablepre != 'el_')
        $sql = str_replace('el_', $tablepre, $sql);
    $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql);

    if ($r_tablepre != $s_tablepre)
        $sql = str_replace($s_tablepre, $r_tablepre, $sql);
    $sql = str_replace("\r", "\n", $sql);
    $ret = array();
    $num = 0;
    $queriesarray = explode(";\n", trim($sql));
    unset($sql);
    foreach ($queriesarray as $query) {
        $ret[$num] = '';
        $queries = explode("\n", trim($query));
        $queries = array_filter($queries);
        foreach ($queries as $query) {
            $str1 = substr($query, 0, 1);
            if ($str1 != '#' && $str1 != '-')
                $ret[$num] .= $query;
        }
        $num++;
    }
    return $ret;
}

function _dir_path($path) {
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/')
        $path = $path . '/';
    return $path;
}

function dir_create($path, $mode = 0777) {
    if (is_dir($path))
        return TRUE;
    $ftp_enable = 0;
    $path = dir_path($path);
    $temp = explode('/', $path);
    $cur_dir = '';
    $max = count($temp) - 1;
    for ($i = 0; $i < $max; $i++) {
        $cur_dir .= $temp[$i] . '/';
        if (@is_dir($cur_dir))
            continue;
        @mkdir($cur_dir, 0777, true);
        @chmod($cur_dir, 0777);
    }
    return is_dir($path);
}

function dir_path($path) {
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/')
        $path = $path . '/';
    return $path;
}

/**
  +----------------------------------------------------------
 * 生成随机字符串
  +----------------------------------------------------------
 * @param int       $length  要生成的随机字符串长度
 * @param string    $type    随机码类型：0，数字+大写字母；1，数字；2，小写字母；3，大写字母；4，特殊字符；-1，数字+大小写字母+特殊字符
  +----------------------------------------------------------
 * @return string
  +----------------------------------------------------------
 */
function randCode($length = 5, $type = 0) {
    $arr = array(1 => "0123456789", 2 => "abcdefghijklmnopqrstuvwxyz", 3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4 => "~@#$%^&*(){}[]|");
    if ($type == 0) {
        array_pop($arr);
        $string = implode("", $arr);
    } else if ($type == "-1") {
        $string = implode("", $arr);
    } else {
        $string = $arr[$type];
    }
    $count = strlen($string) - 1;
    for ($i = 0; $i < $length; $i++) {
        $str[$i] = $string[rand(0, $count)];
        $code .= $str[$i];
    }
    return $code;
}

?>