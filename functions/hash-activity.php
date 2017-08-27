<?php
/**
 * Created by PhpStorm.
 * User: HOME
 * Date: 20.08.2017
 * Time: 1:32
 */

if (!defined('CORE_INIT')) die('Core protection');

function HA_Create($method, $hash, $data)
{
    if (empty($method) && empty($hash) && empty($data)) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'inputs empty'
        );
        return $response;
    }

    if (!is_array($data)) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'data must be array'
        );
        return $response;
    }

    $order_array = array(
        'method' => mysql_escape_string($method),
        'hash' => mysql_escape_string($hash),
        'data' => serialize($data)
    );
    $serverId = "1";
    $serverDb = "test_db5";

    $sql = "SELECT COUNT(id) as cnt FROM `hash_activity` WHERE 
    (`timestamp` >= (NOW() - INTERVAL 20 SECOND)
    AND `method` = '{$order_array["method"]}'
    AND `hash` = '{$order_array["hash"]}'
    AND `data` = '{$order_array["data"]}')";

    $query = BD_query($sql, $serverId, $serverDb);
    if ($query["response"]["0"]["cnt"] > 5) {
        $response = array(
            'error' => 'yes'
        );
    } else {
        $sql = "SELECT COUNT(id) as cnt FROM `hash_activity` WHERE 
        (`timestamp` >= (NOW() - INTERVAL 60 SECOND)
        AND `method` = '{$order_array["method"]}'
        AND `hash` = '{$order_array["hash"]}')";

        $query = BD_query($sql, $serverId, $serverDb);
        if ($query["response"]["0"]["cnt"] > 30) {
            $response = array(
                'error' => 'yes'
            );
        } else {
            $insert_query = BD_insert($order_array, 'hash_activity', $serverId, $serverDb);
            if ($insert_query['status'] == "ok") {
                $response = array(
                    'error' => 'no',
                    'error_text' => $insert_query['status']
                );
            } else {
                $response = array(
                    'error' => 'yes',
                    'error_text' => $insert_query['status']
                );
            }
        }
    }
    return $response;
}