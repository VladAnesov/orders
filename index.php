<?php
/**
 * Created by PhpStorm.
 * User: HOME
 * Date: 26.07.2017
 * Time: 21:24
 */

ini_set('display_errors', 'on');

/* костыль, потому что у меня не получилось нормально настроить VestaCP */
$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];

define('CORE_INIT', true);
define('PROJECT_URL', '/projects/project_1635');
define('PROJECT_LINK', $_SERVER['DOCUMENT_ROOT'] . PROJECT_URL);
define('BALANCE_CLASS', '.a-main__balance-value');
define('ORDER_CLASS', '.va__orderStatus');
define('ORDER_ACTION_CLASS', '.va__orderAction');
define('ORDER_COMMENT', '.va__orderComment');
define('VERSION', 'Beta 2.2');

$serverId = 1;
$serverDb = 'test_db1';

require_once(PROJECT_LINK . "/functions/text.php");
require_once(PROJECT_LINK . "/functions/hash.php");
require_once(PROJECT_LINK . "/functions/PaySystem.php");
require_once(PROJECT_LINK . "/functions/money.php");
require_once(PROJECT_LINK . "/functions/users.php");
require_once(PROJECT_LINK . "/functions/hash-activity.php");

$module = "orders";

$headers = getallheaders();

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

if (isset($headers['VAAjax']) && $headers['VAAjax'] == 'yes') {
    $h_a_f = false;
}

if ($h_a_f) {
    require_once(PROJECT_LINK . "/modules/" . $module . "/index.php");
    require_once(PROJECT_LINK . "/tpl/header.php");
    echo $content;
    require_once(PROJECT_LINK . "/tpl/footer.php");
} else {
    require_once(PROJECT_LINK . "/modules/" . $module . "/index.php");
    echo $content;
}
?>