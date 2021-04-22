<?php
/**
 * Created by PhpStorm.
 * User: DM
 * Date: 2017/9/28
 * Time: 13:46
 */

if (!function_exists('humanityTime')) {
    /**
     * @param int $timestamp 时间戳
     * @return string 返回更人性化时间
     */
    function humanityTime($timestamp)
    {
        $now = time();
        $diff = $now - $timestamp;
        $day = floor($diff / (24 * 60 * 60));
        switch (true) {
            case $diff > 365 * 24 * 60 * 60:
                return date('Y-m-d H:i:s');
            case $diff > 1 * 60 * 60:
                if ($day >= 3) {
                    return $day . '天前';
                } elseif ($day >= 2) {
                    return '前天' . date('H:i', $timestamp);
                } elseif ($day >= 1) {
                    return '昨天' . date('H:i', $timestamp);
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

if (!function_exists('dateSimple')) {
    /**
     * @param bool $timestamp
     * @param bool $short 是否包含时分秒
     * @return false|string
     */
    function dateSimple($timestamp = false, $short = false)
    {
        if ($timestamp === false) $timeStamp = time();
        if ($short) {
            return date('Y-m-d', $timestamp);
        } else {
            return date('Y-m-d H:i:s', $timestamp);
        }
    }
}

if (!function_exists('second2Humanity')) {
    function seconds2Humanity($seconds, $simple = false)
    {
        $day = floor($seconds / 86400);
        $hour = floor($seconds % 86400 / 3600);
        $minute = floor($seconds % 86400 % 3600 / 60);
        $second = $seconds % 86400 % 3600 % 60;
        $str = '';
        if ($simple) {
            if ($second) $str = $second . '秒';
            if ($minute) $str = $minute . '分钟';
            if ($hour) $str = $hour . '小时';
            if ($day) $str = $day . '天';
        } else {
            if ($day) $str .= $day . '天';
            if ($hour) $str .= $hour . '小时';
            if ($minute) $str .= $minute . '分钟';
            if ($second) $str .= $second . '秒';
        }
        return $str;
    }
}