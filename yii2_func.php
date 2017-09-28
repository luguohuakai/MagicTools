<?php
/**
 * Created by PhpStorm.
 * User: DM
 * Date: 17/3/31
 * Time: 上午11:45
 */

// 导入公共函数
require_once __DIR__ . '/common/function.php';

if (!function_exists('L')) {
    /**
     * 需要配置路径 不具有通用性
     * @param $msg
     * @param $file_name
     */
    function L($msg, $file_name)
    {
        $msg = date('Y-m-d H:i:s')
            . "\r\n" . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
            . "\r\n" . Yii::$app->controller->module->id . '/' . Yii::$app->controller->id . '/' . Yii::$app->controller->action->id
            . "\r\n输出信息:" . $msg
            . "\r\n\r\n";
        error_log($msg, 3, __DIR__ . '/../../console/runtime/logs/' . $file_name . '_' . date('Y-m-d') . '.txt');
    }
}

if (!function_exists('C')) {
    /**
     * 快捷获取配置信息
     * @param $str
     * @return mixed
     */
    function C($str)
    {
        return Yii::$app->params[$str];
    }
}