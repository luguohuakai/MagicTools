<?php
/**
 * Created by PhpStorm.
 * User: DM
 * Date: 17/3/31
 * Time: 上午11:45
 */

// 导入当前路径下所有类似 func_* 的文件
$files = scandir(__DIR__);
foreach ($files as $file) {
    if (preg_match('/^(func_)\w+(.php)$/', $file)) {
        include_once __DIR__ . '/' . $file;
    }
}

if (!function_exists('dd')) {
    /**
     * 调试输出神器
     * @param string $var
     * @param bool $stop 是否截断(die)
     * @param bool $as_array
     */
    function dd($var = '', $stop = true, $as_array = false)
    {
        if (is_array($var) || is_object($var)) {
            echo '<pre>';
            if ($as_array) {
                var_export($var);
            } else {
                print_r($var);
            }
            if ($stop) {
                die('</pre>');
            } else {
                echo '</pre>';
            }
        } else {
            var_dump($var);
            if ($stop) die;
        }
    }
}

if (!function_exists('ww')) {
// 打印并换行
    function ww($var = '')
    {
        if ($var === false) echo 'bool false' . "\r\n";
        if ($var === null) echo 'null' . "\r\n";
        if (is_string($var) and trim($var) === '') echo 'string ""' . "\r\n";
        if (is_string($var)) {
            echo $var . "\r\n";
        } else {
            echo '<pre>';
            print_r($var);
            echo '</pre>';
            echo "\r\n";
        }
    }
}

if (!function_exists('logs')) {
    /**
     * @param string $filename 日志存放位置 默认 /tmp/dm-log/
     * @param mixed $data 日志内容
     * @param string $format 日志格式 human-readable:默认 json:JSON格式化 serialize:序列化
     * @param int $flags 默认:FILE_APPEND 追加
     * @param string $by 默认:month 日志文件按月生成
     */
    function logs($filename, $data, $format = 'human-readable', $flags = FILE_APPEND, $by = 'month')
    {
        if (strpos($filename, '/') === false) {
            switch (true) {
                case is_dir('/tmp/'):
                    $dir = '/tmp/dm-log/';
                    if (!is_dir($dir))
                        mkdir($dir);
                    break;
                case is_dir('/srun3/log/'):
                    $dir = '/srun3/log/dm-log/';
                    if (!is_dir($dir))
                        mkdir($dir);
                    break;
                case is_dir('/srun3/www/srun_mq/backend/runtime/logs/'):
                    $dir = '/srun3/www/srun_mq/backend/runtime/logs/dm-log/';
                    if (!is_dir($dir))
                        mkdir($dir);
                    break;
                case is_dir('/srun3/www/srun4-mgr/center/runtime/logs/'):
                    $dir = '/srun3/www/srun4-mgr/center/runtime/logs/dm-log/';
                    if (!is_dir($dir))
                        mkdir($dir);
                    break;
                default:
                    break;
            }
            if (isset($dir)) $filename = $dir . $filename;
        }
        $time = date('Y-m-d H:i:s', time());
        if ($by === 'month') $filename .= '_' . date('Ym', time());
        if ($by === 'day') $filename .= '_' . date('Ymd', time());
        if ($by === 'year') $filename .= '_' . date('Y', time());
        if ($by === 'hour') $filename .= '_' . date('YmdH', time());
        if ($by === 'minute') $filename .= '_' . date('YmdHi', time());
        if ($format === 'json') $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($format === 'serialize') $data = serialize($data);
        $filename .= '.log';
        if (!is_file($filename)) file_put_contents($filename, '', FILE_APPEND);
        chmod($filename, 0777);
        file_put_contents($filename, $time . ' ' . print_r($data, true) . "\r\n", $flags);
    }
}

if (!function_exists('wwLogs')) {
    function wwLogs($filename, $data, $format = 'human-readable', $flags = FILE_APPEND, $by = 'month')
    {
        ww($data);
        logs($filename, $data, $format, $flags, $by);
    }
}

if (!function_exists('alert')) {
    /**
     * 网页快速弹出调试
     * @param $var
     */
    function alert($var)
    {
        $str = (string)json_encode($var);
        echo "<script type='text/javascript'>alert('$str');</script>";
    }
}

if (!function_exists('rds')) {
    /**
     * 快速连接redis
     * @param int $index
     * @param int $port
     * @param string $host
     * @param string $pass
     * @return Redis
     */
    function rds($index = 0, $port = 6379, $host = 'localhost', $pass = null)
    {
        $rds = new Redis();
        $rds->connect($host, $port);
        if ($pass !== null) $rds->auth($pass);
        $rds->select($index);

        return $rds;
    }
}

if (!function_exists('get')) {
    /**
     * @param $url
     * @param array $get_data
     * @param array $header
     * @return mixed
     */
    function get($url, $get_data = [], $header = [])
    {
        if (!empty($get_data)) $url = join_params($url, $get_data);
        // 初始化
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// SSL证书认证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);// SSL证书认证
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        // 设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        // 执行并获取HTML文档内容
        $output = curl_exec($ch);
        $_err = curl_error($ch);
        if ($_err) return json_decode(json_encode(['_err' => $_err]));
        // 释放curl句柄
        curl_close($ch);

        return $output;
    }

    function join_params($path, $params)
    {
        $url = $path;
        $parse_rs = parse_url($url);
        $query = isset($parse_rs['query']) ? $parse_rs['query'] : '';
        if (count($params) > 0) {
            $url = $query ? $url . '&' : $url . '?';
            foreach ($params as $key => $value) {
                $url = $url . $key . '=' . $value . '&';
            }
            $length = mb_strlen($url);
            if ($url[$length - 1] == '&') {
                $url = substr($url, 0, $length - 1);
            }
        }
        return $url;
    }
}

if (!function_exists('post')) {
    /**
     * @param $url
     * @param $post_data
     * @param array $header
     * @return mixed
     */
    function post($url, $post_data, $header = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// SSL证书认证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);// SSL证书认证
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // 模拟表单提交
//        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        // json方式提交
//        $_header = array("Content-Type: application/json; charset=utf-8", "Content-Length:" . strlen(json_encode($post_data)));
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $_header);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        if (!empty($header)) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $output = curl_exec($ch);
        $_err = curl_error($ch);
        if ($_err) return json_decode(json_encode(['_err' => $_err]));
        curl_close($ch);

        return $output;
    }
}

if (!function_exists('formatReturnData2Json')) {
    /**
     * @param false $data 返回数据
     * @param string $msg
     * @param int $status
     * @param int $code
     * @return false|string
     */
    function formatReturnData2Json($data = false, $msg = '成功', $status = 1, $code = 200)
    {
        $re['code'] = $code;
        $re['message'] = $msg;
        $re['status'] = $status;
        if ($data !== false) {
            if (is_array($data) && isset($data['extra'])) {
                $re['extra'] = $data['extra'];
            }
            if (is_array($data) && isset($data['data'])) {
                $re['data'] = $data['data'];
            } else {
                $re['data'] = $data;
            }
        }
        // 支持跨域
        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST,PUT,GET,DELETE,OPTIONS',
            'Access-Control-Allow-Headers' => 'ApiAuth, token, User-Agent, Keep-Alive, Origin, No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With',
            'Access-Control-Allow-Credentials' => 'true'
        ];
        $headers['Content-Type'] = 'application/json; charset=utf-8';
        $headers_str = '';
        foreach ($headers as $k => $header) {
            $headers_str .= $k . ': ' . $header . '; ';
        }
        $headers_str = substr($headers_str, 0, -2);
        header($headers_str);
        return json_encode($re, JSON_UNESCAPED_UNICODE);
    }
}

if (!function_exists('success')) {
    /**
     * json返回 成功
     * @param bool $data false代表不返回data字段
     * @param string $msg
     * @param int $status 1:成功 0:失败
     * @param int $code 大于100时为http状态码
     * @return string json
     */
    function success($data = false, $msg = '成功', $status = 1, $code = 200)
    {
        return formatReturnData2Json($data, $msg, $status, $code);
    }
}

if (!function_exists('fail')) {
    /**
     * json返回 失败
     * @param bool $data false代表不返回data字段
     * @param string $msg
     * @param int $status
     * @param int $code 大于100时为http状态码
     * @return string json
     */
    function fail($data = false, $msg = '失败', $status = 0, $code = 200)
    {
        return formatReturnData2Json($data, $msg, $status, $code);
    }
}

if (!function_exists('exitSuccess')) {
    function exitSuccess($data, $msg = '成功')
    {
        header('Content-Type:application/json; charset=utf-8');
        exit(success($data, $msg));
    }
}

if (!function_exists('exitFail')) {
    function exitFail($data = '', $msg = '失败')
    {
        header('Content-Type:application/json; charset=utf-8');
        exit(fail($data, $msg));
    }
}

if (!function_exists('page')) {
    /**
     * 分页函数
     * @param int $count 总条目数
     * @param int $page 当前页码
     * @param int $size 当前没有条目数
     * @return object mixed
     */
    function page($count, $page, $size)
    {
        return json_decode(json_encode([
            'page' => $page,
            'size' => $size,
            'total_pages' => ceil($count / $size),
            'total_items' => $count,
            'limit' => $size,
            'offset' => $size * ($page - 1),
        ]));
    }
}

if (!function_exists('ed')) {
    /**
     * @param string $string 需要加密解密的字符串
     * @param string $operation 判断是加密还是解密，E表示加密，D表示解密
     * @param string $key 密匙
     * @return array|false|string|string[]
     */
    function ed($string = '', $operation = 'E', $key = 'www.srun.com')
    {
        $key = md5($key);
        $key_length = strlen($key);
        $string = $operation == 'D' ? base64_decode($string) : substr(md5($string . $key), 0, 8) . $string;
        $string_length = strlen($string);
        $rndkey = $box = array();
        $result = '';
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($key[$i % $key_length]);
            $box[$i] = $i;
        }
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'D') {
            if (substr($result, 0, 8) == substr(md5(substr($result, 8) . $key), 0, 8)) {
                return substr($result, 8);
            } else {
                return '';
            }
        } else {
            return str_replace('=', '', base64_encode($result));
        }
    }
}

if (!function_exists('exportAsCsv')) {
    /**
     * @param $data
     * @param string $filename
     */
    function exportAsCsv($data, $filename = '')
    {
        if (!$filename) $filename = date('YmdHis') . '.csv';
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $str = '';
        $keys = array_keys($data[0]);
        for ($i = 0; $i < count($keys); $i++) {
            if ($i != count($keys) - 1) {
                $str .= $keys[$i] . ',';
            } else {
                $str .= $keys[$i] . "\r\n";
            }
        }
        foreach ($data as $vv) {
            $k = 0;
            foreach ($vv as $vvv) {
                if ($k != count($vv) - 1) {
                    $str .= $vvv . ',';
                } else {
                    $str .= $vvv . "\r\n";
                }
                $k++;
            }
        }
        $str = iconv('utf-8', 'gb2312', $str);
        exit($str);
    }
}

if (!function_exists('getRealClientIp')) {
    /**
     * 获取客户端IP地址 摘自TP框架
     * @param integer $type 返回类型 0:返回IP地址 1:返回IPV4地址数字
     * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    function getRealClientIp($type = 0, $adv = true)
    {
        $type = $type ? 1 : 0;
        static $ip = null;
        if (null !== $ip) {
            return $ip[$type];
        }
        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? [$ip, $long] : ['0.0.0.0', 0];

        return $ip[$type];
    }
}

if (!function_exists('generateUniqueId')) {
    /**
     * 生成唯一ID
     * @param string $prefix
     * @param string $uid
     * @param string $suffix
     * @return string
     */
    function generateUniqueId($prefix = 'mg_', $uid = '', $suffix = '')
    {
        return
            uniqid($prefix . $uid . '_', true)
            . '_' . mt_rand(1, 999999999)
            . $suffix;
    }
}

if (!function_exists('base64EncodeImage')) {
    /**
     * 将图片转化为Base64
     * @param $image_file
     * @return string
     */
    function base64EncodeImage($image_file)
    {
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        return 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    }
}

if (!function_exists('latLngToAddress')) {
    /**
     * 把传入的经纬度转换为位置 腾讯地图api 取第一个位置
     * @param $lat
     * @param $lng
     * @return string
     */
    function latLngToAddress($lat, $lng)
    {
        // 不要频繁调用腾讯的接口
        sleep(1);
        $url = 'https://apis.map.qq.com/ws/geocoder/v1/';
        $data['location'] = $lat . ',' . $lng;
        $data['key'] = 'SNCBZ-IAIKX-VD74W-ZBGFH-DBNSQ-UXBE2';

        $rs = get($url, $data);
        $address = '';
        $rs = json_decode($rs, true);
        if ($rs['status'] === 0) {
            $address = $rs['result']['address'];
        }

        return $address;
    }
}

if (!function_exists('formatDistance')) {
    /**
     * @param $size
     * @param bool $chinese 是否汉化
     * @return string
     */
    function formatDistance($size, $chinese = false)
    {
        if ($chinese) {
            $units = ['里', '公里'];
        } else {
            $units = ['m', 'km'];
        }
        for ($i = 0; $size >= 1000 && $i < 1; $i++) {
            $size /= 1000;
        }

        return round($size, 2) . $units[$i];
    }
}

if (!function_exists('isEmail')) {
    function isEmail($input)
    {
        return (bool)filter_var($input, FILTER_VALIDATE_EMAIL);
    }
}

if (!function_exists('isIp')) {
    function isIp($input)
    {
        return (bool)filter_var($input, FILTER_VALIDATE_IP);
    }
}

if (!function_exists('isIpv4')) {
    function isIpv4($input)
    {
        return (bool)filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }
}

if (!function_exists('isIpv6')) {
    function isIpv6($input)
    {
        return (bool)filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }
}

if (!function_exists('isMac')) {
    function isMac($input)
    {
        return (bool)filter_var($input, FILTER_VALIDATE_MAC);
    }
}

if (!function_exists('isDomain')) {
    function isDomain($input)
    {
        return (bool)filter_var($input, FILTER_VALIDATE_DOMAIN);
    }
}

if (!function_exists('isUrl')) {
    function isUrl($input)
    {
        return (bool)filter_var($input, FILTER_VALIDATE_URL);
    }
}

if (!function_exists('isMobilePhone')) {
    function isMobilePhone($input)
    {
        return (bool)preg_match('/^1\d{10}$/', $input);
    }
}

if (!function_exists('dataSizeFormat')) {
    /**
     * 存储容量转化 B - KB - MB - GB - TB - PB - ...
     * @param int $size B
     * @param int $dec 保留小数位数
     * @return string
     */
    function dataSizeFormat($size = 0, $dec = 2)
    {
        if (!is_numeric($size) || $size < 0) return false;
        $unit = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB', 'BB', 'NB', 'DB', 'CB', 'XB'];
        $pos = 0;
        while ($size >= 1024) {
            $size /= 1024;
            $pos++;
        }
        $result['size'] = round($size, $dec);
        $result['unit'] = isset($unit[$pos]) ? $unit[$pos] : '--';
        return $result['size'] . $result['unit'];
    }
}

if (!function_exists('magicTime')) {
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
     * @return bool|int
     * @throws Exception
     */
    function magicTime($what)
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
                return strtotime(date('Ym01'));
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

if (!function_exists('jsonDecodePlus')) {
    /**
     * json解析key不含双引号的字符串
     * @param $str
     * @param bool $mode
     * @return mixed
     */
    function jsonDecodePlus($str, $mode = false)
    {
        if (preg_match('/\w:/', $str)) {
            $str = preg_replace('/(\w+):/is', '"$1":', $str);
        }
        return json_decode($str, $mode);
    }
}

if (!function_exists('tree')) {
    /**
     * @param array $items 形如: [['id'=>1, 'pid'=>0, ...], ...]
     * @return array|mixed
     */
    function tree($items)
    {
        foreach ($items as $item)
            $items[$item['pid']]['son'][$item['id']] = &$items[$item['id']];
        return isset($items[0]['son']) ? $items[0]['son'] : [];
    }
}

if (!function_exists('replaceWith')) {
    /**
     * 将字符串的一部分字符替换为指定字符
     * @param string $str 要替换的字符串
     * @param string $position 要替换的位置 left:从左边开始替换 middle:默认从中间开始替换 right:从右边开始替换
     * @param string $replace 要替换成的字符串 默认 *
     * @return string
     */
    function replaceWith($str, $position = 'middle', $replace = '*')
    {
        // 替换字符串长度均为原字符串一半 向下取整
        $str_len = mb_strlen($str);
        $replace_len = ceil($str_len / 3);
        $replace_str = str_repeat($replace, $replace_len);
        $offset = 0;
        if ($position === 'middle') {
            if ($str_len <= 1) {
                $offset = 0;
            } elseif ($str_len <= 5) {
                $offset = 1;
            } else {
                $offset = ceil($str_len / 4);
            }
        }
        if ($position === 'right') $offset = -$replace_len;
        return substr_replace($str, $replace_str, $offset, $replace_len);
    }
}

if (!function_exists('dt')) {
    /**
     * 快速格式化时间戳
     * @param false $timestamp
     * @param string $delimiter
     * @param false $short 是否包含时分秒
     * @return false|string
     */
    function dt($timestamp = false, $delimiter = '-', $short = false)
    {
        if ($timestamp === false) $timestamp = time();
        if ($short) {
            return date("Y{$delimiter}m{$delimiter}d", $timestamp);
        } else {
            return date("Y{$delimiter}m{$delimiter}d H:i:s", $timestamp);
        }
    }
}

if (!function_exists('rateLimit')) {
    /**
     * 请求速率限制
     * @param int $seconds 秒数
     * @param int $times 最大请求次数
     * @return bool|string
     */
    function rateLimit($seconds = 10, $times = 1)
    {
        $key = 'rate_limit:' . md5($_SERVER['REMOTE_ADDR'] . $_SERVER['REQUEST_URI'] . json_encode($_GET) . json_encode($_POST));
        $rds = Rds();
        $v = $rds->get($key);
        if ($v) {
            $arr = explode(',', $v);
            if ($arr[1] < $times) {
                $rds->set($key, $arr[0] . ',' . ($arr[1] + 1), time() - $arr[0]);
                return true;
            } else {
                return "请求太频繁,请稍后再试[$seconds,$times]";
            }
        } else {
            $rds->set($key, time() . ',1', $seconds);
            return true;
        }
    }
}