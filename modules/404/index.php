<?php
/**
 * Created by PhpStorm.
 * User: HOME
 * Date: 02.08.2017
 * Time: 21:54
 */

if (!defined('CORE_INIT')) die('Core protection');

#Устанавливаем код 404 в header
http_response_code(404);

$title = "Страница не найдена";
$content = "Страница не найдена";
?>