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
     * @param $var
     */
    function dd($var = '')
    {
        if ($var === false) die('bool false');
        if ($var === true) die('bool true');
        if ($var === null) die('null');
        if ($var === 0) die('int 0');
        if ($var === 0.0) die('float 0.0');
        if (is_string($var) and trim($var) === '') die('string ""');
        echo '<pre>';
        print_r($var);
        die('</pre>');
    }
}

if (!function_exists('Rds')) {
    /**
     * 快速连接redis
     * @param int $index
     * @param int $port
     * @param string $host
     * @param string $pass
     * @return Redis
     */
    function Rds($index = 0, $port = 6379, $host = 'localhost', $pass = '')
    {
        $rds = new \Redis();
        $rds->connect($host, $port);
        if ($pass) $rds->auth($pass);
        $rds->select($index);

        return $rds;
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

if (!function_exists('export_csv')) {
    /**
     * @param $data
     * @param string $filename
     */
    function export_csv($data, $filename = '')
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
     * 不同环境下获取真实的IP
     * @return array|false|string
     */
    function get_real_client_ip()
    {
        //判断服务器是否允许$_SERVER
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $real_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $real_ip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $real_ip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            //不允许就使用getenv获取
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $real_ip = getenv("HTTP_X_FORWARDED_FOR");
            } elseif (getenv("HTTP_CLIENT_IP")) {
                $real_ip = getenv("HTTP_CLIENT_IP");
            } else {
                $real_ip = getenv("REMOTE_ADDR");
            }
        }

        return $real_ip;
    }
}

if (!function_exists('generate_unique_id')) {
    /**
     * 生成唯一ID
     * @param string $uid
     * @return string
     */
    function generate_unique_id($uid = '')
    {
        return uniqid('wx_' . $uid . '_', true) . '_' . mt_rand(1, 999999999);
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

