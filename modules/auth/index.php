<?php
/**
 * Created by PhpStorm.
 * User: Entero
 * Date: 04.08.2017
 * Time: 20:19
 */

if (!defined('CORE_INIT')) die('Core protection');

if ($_GET['logout'] == "yes") {
    USERS_LOGOUT();
}
header("Location: " . PROJECT_URL);
