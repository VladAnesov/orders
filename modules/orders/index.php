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
    $order = PS_GetOrderByID($orderId);

    if (isset($order["response"]["0"]["data"]) && !empty($order["response"]["0"]["data"])) {
        global $PaySystemConfig;
        $data = $order["response"]["0"]["data"]["0"];
        $price = PS_Price($data["price"]);
        $currency = $PaySystemConfig["currency"];
        $status = PS_GetStatus($data["status"]);
        $owner = USER_GetByID($data["owner"]);
        $owner = $owner["response"]["0"]["data"]["0"];

        switch ($data["status"]) {
            case 4:
                $status = '<span class="status_active">' . $status . '</span>';
                break;
            case 5:
                $status = '<span class="status_reserve">' . $status . '</span>';
                break;
            default:
                $status = '<span class="status_block">' . $status . '</span>';
                break;
        }

        if ($data["contractor"] > 0) {
            $contractor = USER_GetByID($data["contractor"]);
            if (isset($contractor["response"]) && !empty($contractor["response"])) {
                $contractor = $contractor["response"]["0"]["data"]["0"];
            }
        }

        $content = "<h1>{$data["name"]}</h1>";
        $content .= '<div class="a-main__order-user">';

        $content .= '<div class="a-main__order-img"><a href="https://vk.com/' . $owner["login"] . '" target="_blank">';
        $content .= '<img src="' . $owner["img_50"] . '" alt="' . $owner["name"] . '"/>';
        $content .= '</a></div>';

        $content .= '<div class="a-main__order-text">';
        $content .= '<a href="https://vk.com/' . $owner["login"] . '" target="_blank">' . $owner["name"] . '</a>';
        $content .= '<p>Платит <b>' . $price["price"] . ' ' . $currency . '</b> за выполнение задания</p>';
        $content .= "<p>Статус: {$status}</p>";
        $content .= '</div>';

        $content .= '</div>';
        $content .= "<p>{$data["description"]}</p>";
        /* Если заказ выполен, то пишем об этом */
        if ($data["status"] == 4 && $data["contractor"] > 0) {
            $content .= '<div class="a-main__order-contractor">';
            $content .= "<p>Заказ выполнил: {$contractor["name"]}</p>";
            $content .= '</div>';
        }
    } else {
        $content = "<h1>Заказ не найден</h1>";
    }
    echo $content;
} else {
    $test = PS_GetList();
    echo $test;
}
