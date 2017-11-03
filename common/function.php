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
    if(preg_match('/^(func_)\w+(.php)$/',$file)){
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
        if ($var === null) die('null');
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
     * @return Redis
     */
    function Rds($index = 0, $port = 6379, $host = 'localhost')
    {
        $rds = new \Redis();
        $rds->connect($host, $port);
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
     * @return mixed
     */
    function get($url)
    {
        //初始化
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// SSL证书认证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);// SSL证书认证
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //执行并获取HTML文档内容
        $output = curl_exec($ch);
        //释放curl句柄
        curl_close($ch);

        return $output;
    }
}

if (!function_exists('post')) {
    /**
     * @param $url
     * @param $post_data
     * @return mixed
     */
    function post($url, $post_data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// SSL证书认证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);// SSL证书认证
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }
}
