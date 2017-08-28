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
    /* пользователи */
    '1' => array(
        'user' => 'test_user1',
        'password' => 'Ub0kFDhfix'
    ),
    /* заказы */
    '2' => array(
        'user' => 'test_user1',
        'password' => 'Ub0kFDhfix'
    ),
    /* изменения статусов */
    '3' => array(
        'user' => 'test_user3',
        'password' => 'wuJg3QFT3i'
    ),
    /* Транзакции */
    '4' => array(
        'user' => 'test_user4',
        'password' => 'mSNJpI0Jld'
    ),
    /* хэш-активность */
    '5' => array(
        'user' => 'test_user5',
        'password' => 'f6QeilddM8'
    )
);

$bd_tables = array(
    'test_db1' => '1',
    'test_db2' => '2',
    'test_db3' => '3',
    'test_db4' => '4',
    'test_db5' => '5',
);

function BD_Connect($host, $user, $password, $db)
{
    $c = mysql_connect($host, $user, $password) or die('Не удалось соединиться: ' . mysql_error());
    mysql_select_db($db) or die('Не удалось выбрать базу данных' . mysql_error());
    mysql_set_charset("utf8");
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

function BD_insert($array, $filter, $serverid, $database)
{
    global $bd_array;
    global $bd_users;
    global $bd_tables;

    $response = array('status' => 'ok');
    $db = BD_Connect($bd_array[$serverid], $bd_users[$bd_tables[$database]]['user'], $bd_users[$bd_tables[$database]]['password'], $database);

    if (is_array($array)) {
        /* Прогоняем массив данных и чистим данные */
        foreach ($array as $k => $v) {
            $k = mysql_escape_string($k);
            $v = mysql_escape_string($v);
            $array[$k] = $v;
        }

        /* Ключи, которые заменяем */
        $keys = "(";
        $keys .= "`" . implode("`, `", array_keys($array)) . "`";
        $keys .= ")";

        /* Значения */
        $values = "VALUES (";
        $values .= "'" . implode("', '", $array) . "'";
        $values .= ")";

        /* Подготовка запроса */
        $sql = "INSERT INTO `{$filter}` {$keys} {$values}";

        /* Выполнение запроса */
        $query = mysql_query($sql) or $response['status'] = 'Запрос не удался: ' . mysql_error();
        if ($query) {
            $response['userId'] = mysql_insert_id();
        }
        $response['sql'] = $sql;
    } else {
        $response['status'] = 'Первый параметр должнен быть передан в виде массива.';
    }
    mysql_close($db);
    return $response;
}

/*
 * Позволяет выполнить запрос на выборку (возможно сделать несколько запросов подряд)
 *
 * $array - массив(
 *      'таблица' => массив(
 *          'select' => 'Значения, * или через запятую'
 *          'where' => 'условие, например, id = 5'
 *      )
 * )
 */
function BD_select($array, $serverid, $database)
{
    global $bd_array;
    global $bd_users;
    global $bd_tables;

    if (!is_array($array)) {
        return false;
    }

    $num = 0;
    //$db = null;
    //$response['status'] = 'ok';
    foreach ($array as $k_1 => $v_1) {
        $table = mysql_escape_string($k_1);
        $fields = mysql_escape_string($v_1['select']);
        $filter = $v_1['where'];
        $orderby = '';
        if (isset($v_1['sort']) && is_array($v_1['sort'])) {
            $orderby = "ORDER BY `{$table}`.`{$v_1['sort']['key']}` {$v_1['sort']['type']}";
            $query = "SELECT {$fields} FROM `{$table}` WHERE {$filter} {$orderby}";
        } else {
            $query = "SELECT {$fields} FROM `{$table}` WHERE {$filter}";
        }

        $response['response'][$num]['status'] = 'ok';
        $db = BD_Connect($bd_array[$serverid], $bd_users[$bd_tables[$database]]['user'], $bd_users[$bd_tables[$database]]['password'], $database);
        $result = mysql_query($query) or $response['response'][$num]['status'] = 'Запрос не удался: ' . mysql_error() . ' SQL:' . $query;
        while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $response['response'][$num]['data'][] = $line;
        }
        mysql_free_result($result);

        $num++;
    }

    mysql_close($db);
    return $response;
}

function BD_select_rows($array, $serverid, $database)
{
    global $bd_array;
    global $bd_users;
    global $bd_tables;

    if (!is_array($array)) {
        return false;
    }

    $num = 0;
    $db = null;
    $response['status'] = 'ok';
    foreach ($array as $k_1 => $v_1) {
        $table = mysql_escape_string($k_1);
        $fields = mysql_escape_string($v_1['select']);
        $filter = $v_1['where'];

        $query = "SELECT {$fields} FROM `{$table}` WHERE {$filter}";

        $response["response"][$num]['status'] = 'ok';
        $db = BD_Connect($bd_array[$serverid], $bd_users[$bd_tables[$database]]['user'], $bd_users[$bd_tables[$database]]['password'], $database);
        $result = mysql_query($query) or $response["response"][$num]['status'] = 'Запрос не удался: ' . mysql_error() . ' SQL:' . $query;
        if ($response["response"][$num]['status'] == 'ok') {
            $response["response"][$num]['rows'] = mysql_num_rows($result);
        }
        mysql_free_result($result);
        $num++;
    }

    mysql_close($db);
    return $response;
}

function BD_update($table, $fields, $where, $serverid, $database)
{
    global $bd_array;
    global $bd_users;
    global $bd_tables;

    $table = mysql_escape_string($table);
    $query = "UPDATE `{$table}` SET " . BD_array2update($fields) . " WHERE {$where}";

    $response = array('status' => 'ok');
    $db = BD_Connect($bd_array[$serverid], $bd_users[$bd_tables[$database]]['user'], $bd_users[$bd_tables[$database]]['password'], $database);
    $result = mysql_query($query) or $response['status'] = 'Запрос не удался: ' . mysql_error();
    mysql_close($db);
    return $response;
}

function BD_array2update($input)
{
    $output = implode(', ', array_map(
        function ($v, $k) {
            return sprintf("`%s`='%s'", mysql_escape_string($k), mysql_escape_string($v));
        },
        $input,
        array_keys($input)
    ));

    return $output;
}


/*
 * Заброшенных хлам и помойка
 */


/*
 * Функция забракована и более не актуальна
 *
 * Функция сложной выборки данных
 * @filter array
 * @serverId string
 *
 * Структура массива $filter
 * 'база_данных' => массив( #1 LEVEL
 *      'таблица' => массив( #2 LEVEL
 *          'map' => 'ключ'
 *          'search' => 'выборка полей (через точку с запятой)', #3 level (параметры выборки)
 *          'where' => массив( #3 level
 *              'база_данных' => массив( #4 level
 *                  'таблица' => массив( #5 level
 *                      можно использовать префикс self_ для обозначения данных из родительской таблицы
 *                      'параметр' => 'значение' #6 level
 *                  )
 *              )
 *          )
 *      )
 * )
 *
 * массив для упрощённой работы создания вложенных запросов
 * чтобы сделать выборку значений из второй таблицы, нужно дублировать первый уровень массива, и сделать выборку search
 * Значения where должны быть пустым.
 */
function BD_diff_select($filter, $serverid)
{
    $response = array('status' => 'ok');

    if (is_array($filter)) {
        /*
         * Здесь начинаем шаманить с массивом для подготовки запроса.
         * Для удобства работы и чтения кода, все foreach я раскидываю по уровням ($k_1) ключ первого уровня
         * см структуру массива.
         */

        /* Разные переменные и массивы в дефолтном значении */
        $va_bases = array();
        $select = null;
        $from = null;
        $inner_join = "INNER JOIN ";
        $inner_join_array = array();
        $sl = null;
        $f_m = null;
        $extra_key = null;

        foreach ($filter as $k_1 => $v_1) {
            /* Установка маппинга */
            foreach ($v_1 as $k_2 => $v_2) {
                if (isset($v_2["map"])) {
                    $extra_key = $v_2["map"];
                }
            }

            /* 2 level */
            foreach ($v_1 as $k_2 => $v_2) {
                /*
                 * Записываем:
                 * ключ - названеие базы
                 * значение - таблица
                 * результат выборки: FROM `база_данных`.`таблица`
                 */
                $va_bases[$k_1] = $k_2;
                if (isset($v_2['search']) && !empty($v_2['search'])) {
                    if (stripos($v_2['search'], ";") !== false) {
                        $v_2_explode = explode(";", $v_2['search']);
                        foreach ($v_2_explode as $exp_k => $exp_v) {
                            $exp_sql = "`$k_1`.`$k_2`.`" . $exp_v . "` as `" . $extra_key . "_" . $exp_v . "`";
                            $sl[] = $exp_sql;
                        }
                    } else {
                        if ($v_2['search'] == "*") {
                            $sl[] = "`$k_1`.`$k_2`." . $v_2['search'];
                        } else {
                            $sl[] = "`$k_1`.`$k_2`.`" . $v_2['search'] . "` as `" . $extra_key . "_" . $v_2['search'] . "`";
                            $sl[] = "COUNT(`$k_1`.`$k_2`.`" . $v_2['search'] . "`) as `" . $extra_key . "_count`";

                        }
                    }
                } else {
                    $response['status'] = "Параметр `search` пуст. Трассировка массива: " . $k_1 . ">" . $k_2 . "[" . $k_2 . "]";
                }

                if (isset($v_2['where']) && !empty($v_2['where']) && is_array($v_2['where'])) {
                    // 3 level
                    foreach ($v_2['where'] as $k_3 => $v_3) {
                        /* 4 level */
                        foreach ($v_3 as $k_4 => $v_4) {
                            /* 5 level */
                            /* Начинаем крутить бубен, в силу идет INNER JOIN */
                            $i_j = "`$k_3`.`$k_4`";
                            foreach ($v_4 as $k_5 => $v_5) {
                                if (stripos($k_5, "self_") !== false) {
                                    $k_5 = str_replace(array('self_'), '', $k_5);
                                    $inner_join_array[] = "`$k_1`.`$k_2`.`" . $k_5 . "` = " . $i_j . ".`" . $v_5 . "`";
                                } else if (stripos($k_5, "value_") !== false) {
                                    $k_5 = str_replace(array('value_'), '', $k_5);
                                    $inner_join_array[] = $i_j . ".`" . $k_5 . "` = '" . mysql_escape_string($v_5) . "'";
                                } else {
                                    $inner_join_array[] = $i_j . ".`" . $k_5 . "` = " . $i_j . ".`" . $v_5 . "`";
                                }
                            }

                            /* Inner join */
                            $inner_join .= $i_j . " ON (" . implode(' AND ', $inner_join_array) . ")";
                        }
                    }
                }
            }
        }

        /* Установка Select */
        $select = implode(", ", $sl);

        /* Обработка результата выборки FROM */
        $from = null;
        /* Default Base */
        $d_base = null;
        foreach ($va_bases as $k_1 => $v_1) {
            $f_m[] = "`$k_1`.`$v_1`";
            $d_base = $k_1;
            break;
        }
        $from = implode(', ', $f_m);

        $sql = "SELECT " . $select . " FROM (" . $from . ") " . $inner_join;

        $query = BD_query($sql, $serverid, $d_base);
        $response['state'] = $query;

        $response['debug'] = array(
            'filter' => $filter,
            'sql' => $sql,
        );
    } else {
        $response['status'] = 'Первые два парамтера должны быть переданы в виде массива.';
    }
    return $response;
}