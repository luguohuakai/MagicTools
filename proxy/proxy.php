<?php
/**
 * Created by PhpStorm.
 * User: DM
 * Date: 2016/6/5
 * Time: 9:01
 */
// 简单请求方式（类似get请求）
//$url = $_POST['url'];
//$content = file_get_contents($url);
//exit($content);

$count = count($_POST);
if ($count > 1 && isset($_POST['url'])) {
    $url = $_POST['url'];
    unset($_POST['url']);
    $para = $_POST;
    getHttpResponsePOST($url, $para);
} elseif (($count == 1 && isset($_POST['url'])) || isset($_GET['url'])) {
//    isset($_POST['url']) ? $url = $_POST['url'] : $url = urldecode(str_replace('url=','',$_SERVER['QUERY_STRING']));
    if (isset($_POST['url'])) {
        $url = $_POST['url'];
    } elseif (isset($_GET['url'])) {
        $url = $_GET['url'];
        unset($_GET['url']);
        $i = 0;
        foreach ($_GET as $k => $v) {
            if ($i == 0) {
                $url .= '?' . $k . '=' . $v;
                $i++;
            } else {
                $url .= '&' . $k . '=' . $v;
            }
        }
    }
    getHttpResponseGET($url);
} else {
    $re['msg'] = '请求错误（未传递任何参数或参数不正确）';
    $re['code'] = 400;
    exit(json_encode($re));
}

// post请求（curl()函数）
function getHttpResponsePOST($url, $para, $cacert_url = '', $input_charset = '')
{
    $c = curl_init();
//    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
//    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);//SSL证书认证
//    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
//    curl_setopt($c, CURLOPT_CAINFO,$cacert_url);//证书地址
    curl_setopt($c, CURLOPT_HEADER, 0); // 过滤HTTP头
    curl_setopt($c, CURLOPT_URL, $url);   // 设置要访问的url地址
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);   // ???? 显示输出结果
    curl_setopt($c, CURLOPT_POST, 1);   // 设置post
    curl_setopt($c, CURLOPT_POSTFIELDS, $para);   // 要post发送的数据
    $out_put = curl_exec($c);
//    var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
    curl_close($c);
    exit($out_put);
}

// get请求 （curl()函数）
function getHttpResponseGET($url, $cacert_url = '')
{
    $c = curl_init();
//    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
//    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);//SSL证书认证
//    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
//    curl_setopt($c, CURLOPT_CAINFO,$cacert_url);//证书地址
    curl_setopt($c, CURLOPT_HEADER, 0); // 过滤HTTP头
    curl_setopt($c, CURLOPT_URL, $url);   // 设置要访问的url地址
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);   // ???? 显示输出结果
    $out_put = curl_exec($c);
//    var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
    curl_close($c);
    exit($out_put);
}
