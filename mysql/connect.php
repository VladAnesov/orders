<?php
/*
 * Полевые условия, пишем код полностью без ООП
 * Значит будем использовать стандартные mysql методы
 * Никакого mysqli, только mysql, только хардкор
 * Буду разделять названия функций по классам
 * BD_ префикс работы с баззой данных
 */

if (!defined('CORE_INIT')) die('Core protection');

$bd_array = array(
    '1' => '178.170.189.34',
);

$bd_users = array(
    '1' => array(
        'user' => 'test_user1',
        'password' => 'Ub0kFDhfix'
    ),
    '2' => array(
        'user' => 'test_user2',
        'password' => 'iIT3fwJ0Hr'
    )
);

$bd_tables = array(
    'test_db1' => '1',
    'test_db2' => '2'
);

function BD_Connect($host, $user, $password, $db)
{
    $c = mysql_connect($host, $user, $password) or die('Не удалось соединиться: ' . mysql_error());
    mysql_select_db($db) or die('Не удалось выбрать базу данных' . mysql_error());
    return $c;
}

function BD_query($query, $serverid, $database)
{
    global $bd_array;
    global $bd_users;
    global $bd_tables;

    $response = array('status' => 'ok');
    $db = BD_Connect($bd_array[$serverid], $bd_users[$bd_tables[$database]]['user'], $bd_users[$bd_tables[$database]]['password'], $database);
    $result = mysql_query($query) or $response['status'] = 'Запрос не удался: ' . mysql_error();
    while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $response['response'][] = $line;
    }
    mysql_free_result($result);
    mysql_close($db);
    return $response;
}