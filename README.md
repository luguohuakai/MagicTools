# MagicTools 常用php功能函数
###使用方法非常简单
如果能找到对应框架可加载对应框架的工具函数
```
include_once __DIR__ . '/yii2_func.php';
```
如果没有对应框架可以加载通用工具函数
```
include_once __DIR__ . '/common/function.php';(推荐)
```
如果只想加载某一个类型的函数可以这样加载
```
include_once __DIR__ . '/common/func_date.php';
```
使用 如:
```
echo the_last_day_of_this_month();
```
