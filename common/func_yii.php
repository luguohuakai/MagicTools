<?php

function yiiPost($key = false, $default = null)
{
    if ($key === false) return Yii::$app->request->post();
    return Yii::$app->request->post($key, $default);
}

function yiiGet($key = false, $default = null)
{
    if ($key === false) return Yii::$app->request->get();
    return Yii::$app->request->get($key, $default);
}

function yiiParams($key = false, $default = null)
{
    if ($key === false) return Yii::$app->params;
    $arr = explode('.', $key);
    $value = Yii::$app->params;
    if (count($arr) === 1) return $value[$key];
    foreach ($arr as $item) {
        $value = is_array($value) && isset($value[$item]) ? $value[$item] : null;
    }
    if ($value === null && $default !== null) return $default;
    return $value;
}