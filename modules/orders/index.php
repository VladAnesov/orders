<?php
/**
 * Created by PhpStorm.
 * User: HOME
 * Date: 02.08.2017
 * Time: 21:32
 */
if (!defined('CORE_INIT')) die('Core protection');

if (isset($params['id']) && !empty($params['id'])) {
    $orderId = mysql_escape_string((int)$params['id']);
    $order_detail = PS_GetOrderDetail($orderId);
    $title = $order_detail['title'];
    $content = $order_detail['content'];
} else {
    $title = "Заказы";
    $content = null;
    $user = USERS_GET_USER();
    if ($user) {
        $content .= '<div class="a-body_buttons">';
        $content .= '<div class="btn" onclick="orders.createOrder();">Создать заказ</div>';
        $content .= '</div>';
    }
    $content .= PS_GetList();
}
