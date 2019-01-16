<?php
/**
 * Created by PhpStorm.
 * User: DM
 * Date: 2017/9/28
 * Time: 13:46
 */

if (!function_exists('humanity_time')) {
    /**
     * @param int $timestamp 时间戳
     * @return string 返回更人性化时间
     */
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

if (!function_exists('magic_time')) {
    /**
     * // 时间函数封装
     * // 举例
     * magicTime('-30_days_begin') 30天前的凌晨
     * magicTime('-30_days_end') 30天前的午夜
     * magicTime('-30_months_begin') 30月前的凌晨
     * magicTime('-30_months_end') 30月前的午夜
     * 'yesterday_begin', 'yesterday_end', 'today_begin',
     * 'now', 'today_end', 'tomorrow_begin',
     * 'tomorrow_end', 'this_week_begin', 'this_week_end',
     * 'this_month_begin', 'this_month_end', 'this_year_begin',
     * 'this_year_end',
     * @param string $what 要转换的时间字符串
     * @return bool|false|int
     * @throws Exception
     */
    function magic_time($what)
    {
        $arr = explode('_', $what);
        if (is_numeric($arr[0])) {
            if ($arr[1] === 'days') {
                switch ($arr[2]) {
                    case 'begin':
                        return strtotime(date('Y-m-d', strtotime($arr[0] . ' days')));
                    case 'end':
                        return strtotime(date('Y-m-d', strtotime($arr[0] + 1 . ' days'))) - 1;
                    default:
                        return false;
                }
            } elseif ($arr[0] === 'months') {
                switch ($arr[2]) {
                    case 'begin':
                        return strtotime(date('Y-m', strtotime($arr[0] . ' months')));
                    case 'end':
                        return strtotime(date('Y-m', strtotime($arr[0] + 1 . ' months'))) - 1;
                    default:
                        return false;
                }
            }
        }
        $whats = [
            'yesterday_begin', 'yesterday_end', 'today_begin',
            'now', 'today_end', 'tomorrow_begin',
            'tomorrow_end', 'this_week_begin', 'this_week_end',
            'this_month_begin', 'this_month_end', 'this_year_begin',
            'this_year_end',
        ];
        if (!in_array($what, $whats)) return false;
        switch ($what) {
            case 'tomorrow_end':
                return strtotime(date('Ymd', strtotime('+2 days'))) - 1;
            case 'tomorrow_begin':
                return strtotime(date('Ymd', strtotime('+1 day')));
            case 'today_end':
                return strtotime(date('Ymd', strtotime('+1 day'))) - 1;
            case 'now':
                return time();
            case 'today_begin':
                return strtotime(date('Ymd'));
            case 'yesterday_end':
                return strtotime(date('Ymd')) - 1;
            case 'yesterday_begin':
                return strtotime(date('Ymd', strtotime('-1 day')));
            case 'this_week_begin':
                return strtotime((new DateTime)->modify('this week')->format('Ymd'));
            case 'this_week_end':
                return strtotime((new DateTime)->modify('this week + 7 days')->format('Ymd')) - 1;
            case 'this_month_begin':
                return strtotime(date('Ym01', strtotime(date("Ymd"))));
            case 'this_month_end':
                $m = date('m');
                $y = date('Y');
                if ($m == 12) {
                    $next_m = 1;
                    $next_y = $y + 1;
                } else {
                    $next_m = $m + 1;
                    $next_y = $y;
                }
                return strtotime($next_y . $next_m . '01') - 1;
            case 'this_year_begin':
                return strtotime(date('Y0101'));
            case 'this_year_end':
                return strtotime(date(date('Y') + 1 . '0101')) - 1;
            default:
                return false;
        }
    }
}

if (!function_exists('date_simple')) {
    /**
     * @param int|bool $timeStamp 默认当前时间
     * @param bool $short 是否包含时分秒
     * @return false|string
     */
    function date_simple($timeStamp = false, $short = false)
    {
        if ($timeStamp === false) $timeStamp = time();
        if ($short) {
            return date('Y-m-d', $timeStamp);
        } else {
            return date('Y-m-d H:i:s', $timeStamp);
        }
    }
}