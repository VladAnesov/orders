<?php
/**
 * Created by PhpStorm.
 * User: HOME
 * Date: 26.07.2017
 * Time: 21:24
 */

ini_set('display_errors', 'on');
define('CORE_INIT', true);
define('PROJECT_LINK', $_SERVER['DOCUMENT_ROOT'] . '/projects/project_1635');
require_once(PROJECT_LINK . "/mysql/connect.php");

$query = BD_query("select * from `users` where 1", 1, 'test_db1');
echo '<pre>';
var_dump($query);
echo '</pre>';