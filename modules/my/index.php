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

if (isset($auth['user'])) {
    $deploy_cnt = PS_InDeploy($auth['user']['id']);
    if ($deploy_cnt["data"]["0"]["cnt"] > 0) {
        $content = PS_GetList("`contractor` = '{$auth['user']['id']}' OR `owner` = '{$auth['user']['id']}'");
    } else {
        $content = 'Ошибка доступа';
    }
} else {
    $content = 'Ошибка доступа';
}
