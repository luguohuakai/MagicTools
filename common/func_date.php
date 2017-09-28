<?php
/**
 * Created by PhpStorm.
 * User: DM
 * Date: 2017/9/28
 * Time: 13:46
 */

function the_last_day_of_this_month(){
    return date('Y-m-d H:i:s',strtotime(date('Y-m',strtotime('+1 month'))) - 1);
}