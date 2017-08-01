<script src="assets/js/main.js"></script>
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
require_once(PROJECT_LINK . "/functions/hash.php");

$serverId = 1;
$serverDb = 'test_db1';

$array = array(
    'test_db1' => array(
        'users' => array(
            'search' => 'id;login;password',
            'where' => array(
                'test_db2' => array(
                    'orders' => array(
                        'self_id' => 'owner',
                    )
                )
            )
        )
    ),
    'test_db2' => array(
        'orders' => array(
            'search' => 'owner;id'
        )
    )
);

#$query = BD_insert($array, 'users', $serverId, $serverDb);
$query = BD_diff_select($array, 1);
echo '<pre>';
var_dump($query);
echo '</pre>';
?>

<a href="test.php" onclick="<?= showContent('test.php', '.content', '.loading'); ?>">Открыть test.php</a>
<div class="loading" style="display: none;">Загрузка</div>
<div class="content"></div>
