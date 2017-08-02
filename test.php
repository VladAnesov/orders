<title>test</title>
<?php
/**
 * Created by PhpStorm.
 * User: Entero
 * Date: 31.07.2017
 * Time: 14:57
 */
ini_set('display_errors', 'on');
define('CORE_INIT', true);
define('PROJECT_LINK', $_SERVER['DOCUMENT_ROOT'] . '/projects/project_1635');
require_once(PROJECT_LINK . "/functions/hash.php");
$va_headers = getallheaders();
$current_hash = getHash('test.php');
if (isset($va_headers['VAHash']) && $va_headers['VAHash'] == $current_hash) {
    echo 'detected ajax';
} else {
    echo 'default page without ajax detection';
}