<?php

echo dirname(__FILE__);
require_once 'api/aliyun/OssClient.php';

$ossClient = new OssClient('h0fQAaazdigLOQgZ', '9xujaAa49tkOPPXEz7L4173pauKEUD', 'oss-cn-hangzhou.aliyuncs.com', false);
$ossClient->uploadFile('wangjun1202', "c.file", 'chuyou81415085032.mp4');