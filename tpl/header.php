<?php
/**
 * Created by PhpStorm.
 * User: HOME
 * Date: 02.08.2017
 * Time: 21:34
 */
if (!defined('CORE_INIT')) die('Core protection');

$menu = array(
    'orders' => array(
        'name' => 'Заказы',
        'url' => PROJECT_URL . "/orders",
    ),
    'users' => array(
        'name' => 'Исполнители',
        'url' => PROJECT_URL . "/users",
    )
);

$auth = USERS_INIT();

if (isset($auth['user'])) {
    $deploy_cnt = PS_InDeploy($auth['user']['id']);
    if ($deploy_cnt["data"]["0"]["cnt"] > 0) {
        $menu['my'] = array(
            'name' => 'Мои заказы',
            'url' => PROJECT_URL . "/my"
        );
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= (isset($title)) ? $title : $menu[$module]["name"]; ?></title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300&amp;subset=cyrillic" rel="stylesheet">
    <link rel="stylesheet" href="<?= PROJECT_URL ?>/assets/css/style.css">
    <script src="<?= PROJECT_URL ?>/assets/js/main.js"></script>
</head>
<body>
<div class="a-main">
    <div class="loading" style="display: none;">
        <div class="loader_bg">
            <div class="Page__loader">
                <div class="loader"></div>
            </div>
        </div>
    </div>
    <div class="a-header a-wrapper">
        <div class="a-main__logo">
            Orders
        </div>

        <div class="a-main__menu">
            <ul>
                <? foreach ($menu as $k => $v): ?>

                    <li class="menu-item <?= ($module == $k) ? 'active' : ''; ?>" data-item="<?= $k ?>">
                        <a href="<?= $v['url'] ?>"
                           onclick="<?= showContent($v['url'], '.a-body', '.loading'); ?>"
                        >
                            <?= $v['name'] ?>
                        </a>
                    </li>
                <? endforeach; ?>
            </ul>
        </div>

        <div class="a-main__user">
            <?php

            if (isset($auth['authlink']) && !empty($auth['authlink'])) {
                echo '<a class="auth" href="' . $auth['authlink'] . '">Авторизоваться через ВКонтакте</a>';
            } else {
                global $PaySystemConfig;
                echo '<div class="a-main__user">';
                echo '<div class="a-main__user-block">';
                echo '<img src="' . $auth['user']['img_50'] . '" alt="' . $auth['user']['name'] . '" />';
                echo '<div class="a-main__balance">';
                echo '<p>' . $auth['user']['name'] . '</p>';
                echo '<div class="a-main__balance-wrap">';
                echo '<span class="a-main__balance-value">' . PS_Balance($auth['user']['balance']) . '</span>';
                echo '<div class="a-main__balance_add" onclick="orders.createModalAddBalance();"></div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '<a href="' . PROJECT_URL . '/auth?logout=yes">Выйти</a>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
    <div class="a-body a-wrapper">