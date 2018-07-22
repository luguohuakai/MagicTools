<?php
/**
 * Created by PhpStorm.
 * User: DM
 * Date: 2017/9/28
 * Time: 13:46
 */

function the_last_day_of_this_month(){
    return date('Y-m-d H:i:s',strtotime(date('Y-m',strtotime('+1 month'))) - 1);
}

if (!function_exists('humanity_time')) {
    function humanity_time($timestamp)
    {
        $now = time();
        $diff = $now - $timestamp;
        switch (true) {
            case $diff > 24 * 60 * 60:
                return date('Y-m-d H:i:s');
            case $diff > 1 * 60 * 60:
                if (date('d', $timestamp) + 1 == date('d')) {
                    return '昨天' . date('H:i:s', $timestamp);
                } else {
                    return floor($diff / (60 * 60)) . '小时前';
                }
            case $diff > 60:
                return floor($diff / 60) . '分钟前';
            case $diff > 0:
                return '刚刚';
            case $diff == 0:
                return '此时';
            case $diff < 0:
                return '未来';
            default:
                return '参数错误';
        }
    }
}