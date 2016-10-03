<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-10-11
 * Time: 下午3:48
 */

function getWendaTypeName($typeid){
    if($typeid==1){
        return "技术问答";
    }else if($typeid==2){
        return "技术分享";
    }else if($typeid==3){
        return "活动建议";
    }
}

?>