<?php
/**
 * Created by PhpStorm.
 * User: Entero
 * Date: 04.08.2017
 * Time: 17:11
 */

if (!defined('CORE_INIT')) die('Core protection');

require_once(PROJECT_LINK . "/config/PaySystem.php");
require_once(PROJECT_LINK . "/mysql/connect.php");

function PS_Hash($data)
{
    return hash('sha512', $data);
}

function PS_CreateOrderDialog()
{
    global $PaySystemConfig;
    $user = USERS_GET_USER();
    if (isset($user["id"])) {
        $hash = PS_Hash($user['login'] . $user["hash"]);

        $data = '<div class="va__modal_iblock">';
        $data .= '<div class="va__modal_iblock-title">Название</div>';
        $data .= '<input class="va-input" name="name" placeholder="Название заказа" required/>';
        $data .= '</div>';

        $data .= '<div class="va__modal_iblock">';
        $data .= '<div class="va__modal_iblock-title">Стоимость</div>';
        $data .= '<input type="number" class="va-input" onkeyup="this.value=this.value.replace(/[^\d]/,\'\')" pattern="[0-9]{5}" oninput="orders.getPrice(this, \'.va__price\');" min="' . $PaySystemConfig["min_price"] . '" max="' . $PaySystemConfig["max_price"] . '" name="cost" placeholder="Сумма в рублях" required/>';
        $data .= '</div>';

        $data .= '<div class="va__modal_iblock">';
        $data .= '<div class="va__modal_iblock-title">Описание</div>';
        $data .= '<textarea class="va-textarea" name="description" placeholder="Кратко опишите что нужно сделать." required></textarea>';
        $data .= '</div>';
        $data .= '<input type="hidden" name="hash" value="' . $hash . '"/>';

        $response = array(
            'error' => 'no',
            'title' => 'Создание нового заказа',
            'sendtitle' => 'Создать заказ',
            'data' => $data
        );
    } else {
        $response = array(
            'error' => 'yes',
            'error_text' => 'Вы не авторизованы',
        );
    }
    return $response;
}

function PS_InDeploy($userId)
{
    if (ctype_digit($userId)) {
        $serverId = 1;
        $serverDb = 'test_db2';

        $select_array = array(
            'orders' => array(
                'select' => "COUNT(id) as cnt",
                'where' => "`owner` = '{$userId}' OR `contractor` = '{$userId}'"
            )
        );

        /* Проверка наличия привязки профиля */
        $query = BD_select($select_array, $serverId, $serverDb);
        if (isset($query["response"]["0"]["data"])) {
            $data = $query["response"]["0"]["data"];
            $response = array(
                'error' => 'no',
                'data' => $data
            );
        } else {
            $response = array(
                'error' => 'empty'
            );
        }
    } else {
        $response = array(
            'error' => 'yes',
            'error_text' => 'userid must be int value'
        );
    }
    return $response;
}

function PS_CreatedByUser($userId)
{
    if (ctype_digit($userId)) {
        $serverId = 1;
        $serverDb = 'test_db2';

        $select_array = array(
            'orders' => array(
                'select' => "COUNT(id) as cnt",
                'where' => "`owner` = '{$userId}'"
            )
        );

        /* Проверка наличия привязки профиля */
        $query = BD_select($select_array, $serverId, $serverDb);
        if (isset($query["response"]["0"]["data"])) {
            $data = $query["response"]["0"]["data"];
            $response = array(
                'error' => 'no',
                'data' => $data
            );
        } else {
            $response = array(
                'error' => 'empty'
            );
        }
    } else {
        $response = array(
            'error' => 'yes',
            'error_text' => 'userid must be int value'
        );
    }
    return $response;
}

function PS_CompletedByUser($userId)
{
    if (ctype_digit($userId)) {
        $serverId = 1;
        $serverDb = 'test_db2';

        $select_array = array(
            'orders' => array(
                'select' => "COUNT(id) as cnt",
                'where' => "`contractor` = '{$userId}' AND `status` = '4'"
            )
        );

        /* Проверка наличия привязки профиля */
        $query = BD_select($select_array, $serverId, $serverDb);
        if (isset($query["response"]["0"]["data"])) {
            $data = $query["response"]["0"]["data"];
            $response = array(
                'error' => 'no',
                'data' => $data
            );
        } else {
            $response = array(
                'error' => 'empty'
            );
        }
    } else {
        $response = array(
            'error' => 'yes',
            'error_text' => 'userid must be int value'
        );
    }
    return $response;
}

function PS_getDeploysOnUser($userId)
{
    if (ctype_digit($userId)) {
        $serverId = 1;
        $serverDb = 'test_db2';

        $select_array = array(
            'orders' => array(
                'select' => "COUNT(id), `status` as cnt",
                'where' => "`contractor` = '{$userId}'"
            )
        );

        /* Проверка наличия привязки профиля */
        $query = BD_select($select_array, $serverId, $serverDb);
        if (isset($query["response"]["0"]["data"])) {
            $data = $query["response"]["0"]["data"];
            $response = array(
                'error' => 'no',
                'data' => $data['0']
            );
        } else {
            $response = array(
                'error' => 'empty'
            );
        }
    } else {
        $response = array(
            'error' => 'yes',
            'error_text' => 'userid must be int value'
        );
    }
    return $response;
}

function PS_CreateOrder($data)
{
    if ((!isset($data["name"]) || empty($data["name"])) ||
        (!isset($data["description"]) || empty($data["description"])) ||
        (!isset($data["cost"]) || empty($data["cost"])) ||
        (!isset($data["hash"]) || empty($data["hash"])) ||
        empty($data)) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'data is empty'
        );
    } else {
        $user = USERS_GET_USER();
        if ($user) {
            foreach ($data as $k => $v) {
                $data[trim(mysql_escape_string($k))] = mysql_escape_string(trim($v));
            }

            $hash = PS_Hash($user['login'] . $user["hash"]);
            if ($hash == $data["hash"]) {
                if (isset($data['cost']) && !ctype_digit($data['cost'])) {
                    $response = array(
                        'error' => 'yes',
                        'error_text' => "price is not numeric"
                    );
                } else {
                    global $PaySystemConfig;
                    if ($data['cost'] < $PaySystemConfig['min_price']) {
                        $response = array(
                            'error' => 'yes',
                            'error_text' => "min price: " . $PaySystemConfig['min_price']
                        );
                    } else if ($data['cost'] > $PaySystemConfig['max_price']) {
                        $response = array(
                            'error' => 'yes',
                            'error_text' => "max price: " . $PaySystemConfig['max_price']
                        );
                    } else if (($user['balance'] - $data['cost']) <= 0) {
                        $response = array(
                            'error' => 'yes',
                            'error_text' => "Недостаточно средств для создания заказа, пополните баланс."
                        );
                    } else {
                        $ha_activity = HA_Create("ps_create", $data["hash"], $data);
                        if ($ha_activity['error'] != "yes") {
                            $order_array = array(
                                'name' => htmlspecialchars($data["name"]),
                                'description' => htmlspecialchars($data['description']),
                                'price' => $data['cost'],
                                'owner' => $user['id'],
                                'status' => '1',
                                'stimestamp' => time()
                            );
                            $serverId = "1";
                            $serverDb = "test_db2";
                            $insert_query = BD_insert($order_array, 'orders', $serverId, $serverDb);
                            if ($insert_query['status'] == "ok") {
                                $e_url = PROJECT_URL . "/orders/id/" . $insert_query['userId'];
                                $e_hash = getHash(PROJECT_URL . "/orders");
                                $response = array(
                                    'error' => 'no',
                                    'status' => 'ok',
                                    'url' => $e_url,
                                    'e_hash' => $e_hash
                                );
                            } else {
                                $response = array(
                                    'error' => 'yes',
                                    'error_text' => $insert_query['status']
                                );
                            }
                        } else {
                            $response = array(
                                'error' => 'yes',
                                'error_text' => "You observed suspicious activity, please try again later"
                            );
                        }
                    }
                }
            } else {
                $response = array(
                    'error' => 'yes',
                    'error_text' => 'Invalid hash'
                );
            }
        } else {
            $response = array(
                'error' => 'yes',
                'error_text' => '401 Unauthorized'
            );
        }
    }

    return $response;
}

function PS_StartOrder($data)
{
    if (empty($data)) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'data is empty'
        );
    } else {

    }
}

function PS_GetList()
{
    global $PaySystemConfig;

    $serverId = 1;
    $serverDb = 'test_db2';

    $select_array = array(
        'orders' => array(
            'select' => "*",
            'where' => "`status` != '4'"
        )
    );

    /* Проверка наличия привязки профиля */
    $query = BD_select($select_array, $serverId, $serverDb);
    if (isset($query["response"]["0"]["data"])) {
        $data = $query["response"]["0"]["data"];
    }

    $html_output = '<table>';
    $html_output .= '<thead>';
    $html_output .= '<tr>';
    $html_output .= '<th>Задача</th>';
    $html_output .= '<th>Создал</th>';
    $html_output .= '<th>Статус</th>';
    $html_output .= '<th>Стоимость</th>';
    $html_output .= '</tr>';
    $html_output .= '</thead>';
    if (isset($data) && !empty($data)) {
        foreach ($data as $order_key => $order_value) {
            $link = PROJECT_URL . "/orders/id/" . $order_value["id"];
            $link_js = showContent(PROJECT_URL . "/orders", '.a-body', '.loading', false);
            $price = PS_Price($order_value['price']);

            $user_owner = USER_GetByID($order_value["owner"]);
            $user = USERS_GET_USER();
            $owner = $user_owner["response"]["0"]["data"]["0"];

            $html_output .= '<tr>';
            $html_output .= '<td>';
            $html_output .= '<div class="clear_box">';
            if ($user['id'] == $order_value["owner"]) {
                $username = $owner['name'] . " (Вы)";
            } else {
                $username = $owner['name'];
            }

            $html_output .= '<a href="' . $link . '" onclick="' . $link_js . '"><b>' . $order_value["name"] . '</b></a>';
//            $description = str_replace(PHP_EOL, " ", $order_value["description"]);
//            $description = str_replace('\r', " ", $description);
//            $description = str_replace('\n', " ", $description);
//            $html_output .= '<br/><p>' . $description . '</p>';
            $html_output .= '</div>';
            $html_output .= '</td>';
            $html_output .= '<td>';
            $html_output .= '<a href="https://vk.com/' . $owner['login'] . '" target="_blank">' . $username . '</a>';
            $html_output .= '</td>';
            $html_output .= '<td>' . PS_GetStatus($order_value['status']) . '</td>';
            $html_output .= '<td>' . $price['price'] . ' ' . $PaySystemConfig['currency'] . '</td>';
            $html_output .= '</tr>';
        }
    }
    $html_output .= '</table>';

    return $html_output;
}

function PS_GetOrderByID($id)
{
    if (empty($id))
        return false;

    $serverId = 1;
    $serverDb = 'test_db2';

    $id_sql = mysql_escape_string($id);

    $select_array = array(
        'orders' => array(
            'select' => "*",
            'where' => "(`id`='{$id_sql}')"
        )
    );

    /* Проверка наличия привязки профиля */
    $S_Response = BD_select($select_array, $serverId, $serverDb);

    return $S_Response;
}

function PS_Price($price)
{
    global $PaySystemConfig;

    if ($price < $PaySystemConfig['min_price']) {
        $response = array(
            'error' => 'Минимальная сумма: ' . $PaySystemConfig['min_price'] . " " . $PaySystemConfig['currency']
        );
    } else if ($price > $PaySystemConfig['max_price']) {
        $response = array(
            'error' => 'Максимальная сумма: ' . $PaySystemConfig['max_price'] . " " . $PaySystemConfig['currency']
        );
    } else {

        switch ($PaySystemConfig['percent_type']) {
            case 'P':
                $percent_cost = ($price * ($PaySystemConfig["percent_cost"] / 100));
                $response = array(
                    'price' => ($price - $percent_cost),
                    'tax' => $percent_cost
                );
                break;

            case 'S':
                $response = array(
                    'price' => ($price - $PaySystemConfig["percent_cost"]),
                    'tax' => $PaySystemConfig["percent_cost"]
                );
                break;

            default:
                $response['error'] = 'Settings error, set percent_type';
                break;
        }
    }

    return $response;
}

function PS_GetStatus($status)
{
    $status_values = array(
        '0' => 'Заказ создан',
        '1' => 'Открыто',
        '2' => 'Выполняется',
        '3' => 'Ждет одобрение заказчика',
        '4' => 'Выполнено',
        '5' => 'Не выполнено'
    );

    return $status_values[$status];
}