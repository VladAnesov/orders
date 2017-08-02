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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= (isset($menu[$module]["name"])) ? $menu[$module]["name"] : 'Orders'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/main.js"></script>
</head>
<body>
<div class="a-main">
    <div class="loading" style="display: none;">Загрузка</div>
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
    </div>
    <div class="a-body a-wrapper">