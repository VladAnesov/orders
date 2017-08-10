<?php
/**
 * Created by PhpStorm.
 * User: HOME
 * Date: 26.07.2017
 * Time: 21:24
 */

ini_set('display_errors', 'on');
define('CORE_INIT', true);
define('PROJECT_URL', '/projects/project_1635');
define('PROJECT_LINK', $_SERVER['DOCUMENT_ROOT'] . PROJECT_URL);

$serverId = 1;
$serverDb = 'test_db1';

require_once(PROJECT_LINK . "/functions/hash.php");
require_once(PROJECT_LINK . "/functions/PaySystem.php");
require_once(PROJECT_LINK . "/functions/users.php");

//$array = array(
//    'test_db1' => array(
//        'users' => array(
//            'search' => 'id;login;password',
//            'where' => array(
//                'test_db2' => array(
//                    'orders' => array(
//                        'self_id' => 'owner',
//                    )
//                )
//            )
//        )
//    ),
//    'test_db2' => array(
//        'orders' => array(
//            'search' => 'owner;id'
//        )
//    )
//);
//
//$query = BD_insert($array, 'users', $serverId, $serverDb);
//$query = BD_diff_select($array, 1);


$module = "orders";

$url_parts = ltrim(str_ireplace(PROJECT_URL . "/", "", strtok($_SERVER['REQUEST_URI'], '?')));
$url_parts = explode("/", $url_parts);

if (isset($url_parts[0]) && !empty($url_parts[0])) {
    if (ctype_alnum($url_parts[0])) {
        $file_name = array_shift($url_parts);
        if (file_exists(PROJECT_LINK . "/modules/" . $file_name . "/index.php")) {
            $module = $file_name;
        } else {
            $module = "404";
        }
    } else {
        $module = "404";
    }
}

/*
 * Надо переназначить элементы массива на x = x+1
 */
$params = null;
for ($i = 0; $i < count($url_parts); $i++) {
    if (!empty($url_parts[$i]) && !empty($url_parts[($i + 1)])) {
        $params[trim($url_parts[$i])] = trim($url_parts[($i + 1)]);
    }
}

$h_a_f = true;
$current_hash = getHash(PROJECT_URL . "/" . $module);
if (isset($_POST['VAHash']) && $_POST['VAHash'] == $current_hash) {
    $h_a_f = false;
}

if ($h_a_f) {
    require_once(PROJECT_LINK . "/tpl/header.php");
    require_once(PROJECT_LINK . "/modules/" . $module . "/index.php");
    require_once(PROJECT_LINK . "/tpl/footer.php");
} else {
    require_once(PROJECT_LINK . "/modules/" . $module . "/index.php");
}
?>