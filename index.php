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
require_once(PROJECT_LINK . "/mysql/connect.php");
require_once(PROJECT_LINK . "/functions/hash.php");

$serverId = 1;
$serverDb = 'test_db1';

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

if (isset($_GET['module']) && !empty($_GET['module'])) {
    if (file_exists(PROJECT_LINK . "/modules/" . $_GET['module'] . "/index.php")) {
        $module = $_GET['module'];
    } else {
        $module = "404";
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