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

function yiiList($active_query, $page, $size, $where = null, $select = null, $order_by = 'id desc')
{
    if (!($active_query instanceof ActiveQuery)) return false;
    if (is_array($where) && !empty($where)) {
        if (is_array($where[0])) {
            foreach ($where as $v) {
                $active_query->andWhere($v);
            }
        } else {
            $active_query->andWhere($where);
        }
    }
    if ($select !== null) $active_query->select($select);
    $re['extra'] = $p = page($active_query->count(), $page, $size);
    $re['data'] = $active_query->offset($p->offset)->limit($p->limit)->orderBy($order_by)->asArray()->all();
    return $re['data'] ? $re : false;
}
