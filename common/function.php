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
     */
    function dd($var = '', $stop = true)
    {
        if ($var === false) {
            if ($stop) {
                die('bool false');
            } else {
                echo 'bool false';
            }
        }
        if ($var === null) {
            if ($stop) {
                die('null');
            } else {
                echo 'null';
            }
        }
        if (is_string($var) and trim($var) === '') {
            if ($stop) {
                die('string ""');
            } else {
                echo 'string ""';
            }
        }
        if ($stop) {
            echo '<pre>';
            print_r($var);
            die('</pre>');
        } else {
            echo '<pre>';
            print_r($var);
            echo '</pre>';
        }
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
    function rds($index = 0, $port = 6379, $host = 'localhost', $pass = '')
    {
        $rds = new Redis();
        $rds->connect($host, $port);
        if ($pass) $rds->auth($pass);
        $rds->select($index);

        return $rds;
    }
}

if (!function_exists('export_as_csv')) {
    /**
     * @param $data
     * @param string $filename
     */
    function export_as_csv($data, $filename = '')
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

if (!function_exists('get')) {
    /**
     * @param $url
     * @param array $get_data
     * @param array $header
     * @return mixed
     */
    function get($url, $get_data = [], $header = [])
    {
        if (!empty($get_data)) {
            $url = join_params($url, $get_data);
        }
        //初始化
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// SSL证书认证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);// SSL证书认证
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        //执行并获取HTML文档内容
        $output = curl_exec($ch);
        //释放curl句柄
        curl_close($ch);

        return $output;
    }

    function join_params($path, $params)
    {
        $url = $path;
        if (count($params) > 0) {
            $url = $url . "?";
            foreach ($params as $key => $value) {
                $url = $url . $key . "=" . $value . "&";
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
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // post的变量
//        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data)); // 模拟表单提交
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }
}

if (!function_exists('get_real_client_ip')) {
    /**
     * 获取客户端IP地址 摘自TP框架
     * @param integer $type 返回类型 0:返回IP地址 1:返回IPV4地址数字
     * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    function get_real_client_ip($type = 0, $adv = true)
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

if (!function_exists('generate_unique_id')) {
    /**
     * 生成唯一ID
     * @param string $prefix
     * @param string $uid
     * @param string $suffix
     * @return string
     */
    function generate_unique_id($prefix = 'mg_', $uid = '', $suffix = '')
    {
        return
            uniqid($prefix . $uid . '_', true)
            . '_' . mt_rand(1, 999999999)
            . $suffix;
    }
}

if (!function_exists('base64_encode_image')) {
    /**
     * 将图片转化为Base64
     * @param $image_file
     * @return string
     */
    function base64_encode_image($image_file)
    {
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));

        return $base64_image;
    }
}

if (!function_exists('lat_lng_to_address')) {
    /**
     * 把传入的经纬度转换为位置 腾讯地图api 取第一个位置
     * @param $lat
     * @param $lng
     * @return string
     */
    function lat_lng_to_address($lat, $lng)
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
        if ($data !== false) {
            $re['data'] = $data;
        }
        $re['msg'] = $msg;
        $re['status'] = $status;
        $re['code'] = $code;

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

        return json_encode($re);
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
        if ($data !== false) {
            $re['data'] = $data;
        }
        $re['msg'] = $msg;
        $re['status'] = $status;
        $re['code'] = $code;

        // 设置json头

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

        return json_encode($re);
    }
}

if (!function_exists('ED')) {
    /**
     * @param string $string 需要加密解密的字符串
     * @param string $operation 判断是加密还是解密，E表示加密，D表示解密
     * @param string $key 密匙
     * @return bool|mixed|string
     */
    function ED($string, $operation, $key = 'www.srun.com')
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

if (!function_exists('format_distance')) {
    /**
     * @param $size
     * @param bool $chinese 是否汉化
     * @return string
     */
    function format_distance($size, $chinese = false)
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

if (!function_exists('is_email')) {
    function is_email($input)
    {
        return filter_var($input, FILTER_VALIDATE_EMAIL) ? true : false;
    }
}

if (!function_exists('is_ip')) {
    function is_ip($input)
    {
        return filter_var($input, FILTER_VALIDATE_IP) ? true : false;
    }
}

if (!function_exists('is_ipv4')) {
    function is_ipv4($input)
    {
        return filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? true : false;
    }
}

if (!function_exists('is_ipv6')) {
    function is_ipv6($input)
    {
        return filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? true : false;
    }
}

if (!function_exists('is_mac')) {
    function is_mac($input)
    {
        return filter_var($input, FILTER_VALIDATE_MAC) ? true : false;
    }
}

if (!function_exists('is_domain')) {
    function is_domain($input)
    {
        return filter_var($input, FILTER_VALIDATE_DOMAIN) ? true : false;
    }
}

if (!function_exists('is_url')) {
    function is_url($input)
    {
        return filter_var($input, FILTER_VALIDATE_URL) ? true : false;
    }
}

if (!function_exists('is_mobile_phone')) {
    function is_mobile_phone($input)
    {
        return preg_match('/^1\d{10}$/', $input) ? true : false;
    }
}

if (!function_exists('file_size_format')) {
    /**
     * 存储容量转化 B - KB - MB - GB - TB - PB
     * @param int $size B
     * @param int $dec 保留小数位数
     * @return string
     */
    function file_size_format($size = 0, $dec = 2)
    {
        $unit = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        $pos = 0;
        while ($size >= 1024) {
            $size /= 1024;
            $pos++;
        }
        $result['size'] = round($size, $dec);
        $result['unit'] = $unit[$pos];
        return $result['size'] . $result['unit'];
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