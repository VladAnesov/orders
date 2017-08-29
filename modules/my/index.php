<?php
/**
 * Created by PhpStorm.
 * User: HOME
 * Date: 20.08.2017
 * Time: 16:16
 */

if (!defined('CORE_INIT')) die('Core protection');

$title = "Мои заказы";
$auth = USERS_INIT();

if (isset($params['id']) && !empty($params['id'])) {
    $orderId = mysql_escape_string((int)$params['id']);
    $order_detail = PS_GetOrderDetail($orderId);
    $title = $order_detail['title'];
    $content = $order_detail['content'];
} else {
    if (isset($auth['user'])) {
        $deploy_cnt = PS_InDeploy($auth['user']['id']);
        if ($deploy_cnt["data"]["0"]["cnt"] > 0) {
            $filter = "`contractor` = '{$auth['user']['id']}' OR `owner` = '{$auth['user']['id']}'";
            $content = PS_GetList($filter, "my");
        } else {
            $content = 'Нет данных';
        }
    } else {
        $content = 'Ошибка доступа';
    }
}
