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
    if (isset($data)) {
        foreach ($data as $k => $v) {
            $data[trim(mysql_escape_string($k))] = mysql_escape_string(trim($v));
        }
    }

    if (empty($data)) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'data is empty'
        );
    }
    if (!isset($data["name"]) || mb_strlen($data["name"], 'UTF-8') < 1) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'Поле "Название" пустое'
        );
    } else if (!isset($data["cost"]) || mb_strlen($data["cost"], 'UTF-8') < 1) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'Поле "Стоимость" пустое'
        );
    } else if (!isset($data["description"]) || mb_strlen($data["description"], 'UTF-8') < 1) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'Поле "Описание" пустое'
        );
    } else if (!isset($data["hash"]) || mb_strlen($data["hash"], 'UTF-8') < 1) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'Отсутствует хэш, обновите страницу.'
        );
    } else {
        $user = USERS_GET_USER();
        if ($user) {

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
                            'error_text' => "Минимальная сумма: " . $PaySystemConfig['min_price']
                        );
                    } else if ($data['cost'] > $PaySystemConfig['max_price']) {
                        $response = array(
                            'error' => 'yes',
                            'error_text' => "Максимальная сумма: " . $PaySystemConfig['max_price']
                        );
                    } else if (($user['balance'] - $data['cost']) < 0) {
                        $response = array(
                            'error' => 'yes',
                            'error_text' => "Недостаточно средств для создания заказа, пополните баланс. Ваш баланс: " . PS_Balance($user['balance'])
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

                            $e_price = PS_Price($data["cost"]);

                            $pay_level_1 = M_createTransaction("order_create", $e_price["price"], $user["id"], 0);
                            if ($pay_level_1["error"] == "no") {
                                $pay_level_2 = M_createTransaction("order_create_tax", $e_price["tax"], $user["id"], 0);
                                if ($pay_level_2["error"] == "no") {
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
                                            'title' => $data['name'],
                                            'e_hash' => $e_hash,
                                            'update' => array(
                                                BALANCE_CLASS => PS_Balance($pay_level_2['sender_balance'])
                                            )
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
                                        'error_text' => $pay_level_2["error_text"]
                                    );
                                }
                            } else {
                                $response = array(
                                    'error' => 'yes',
                                    'error_text' => $pay_level_1["error_text"]
                                );
                            }
                        } else {
                            $response = array(
                                'error' => 'yes',
                                'error_text' => "[001] You observed suspicious activity, please try again later"
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
    global $PaySystemConfig;
    $method = "ps_start_order";
    if (empty($data)) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'data is empty'
        );
    } else {
        $user = USERS_GET_USER();
        if ($user) {
            foreach ($data as $k => $v) {
                $data[intval($k)] = intval($v);
            }

            $orderId = intval($data['id']);
            $serverId = 1;
            $serverDb = 'test_db2';
            $new_status = 2;

            $check_array = array(
                'orders' => array(
                    'select' => "id",
                    'where' => "`contractor` = '{$user['id']}'"
                )
            );

            $check_rows = BD_select_rows($check_array, $serverId, $serverDb);
            if (isset($check_rows["response"]["0"]["rows"])) {
                $ucr = $check_rows["response"]["0"]["rows"];

                if ($ucr > 0) {
                    $response = array(
                        'error' => 'yes',
                        'error_text' => 'У Вас уже есть активные задачи, сначала закончите их.',
                    );
                } else {

                    $select_array = array(
                        'orders' => array(
                            'select' => "`status`, `name`, `price`",
                            'where' => "`id` = '{$orderId}'"
                        )
                    );

                    $query = BD_select($select_array, $serverId, $serverDb);

                    if (isset($query["response"]["0"]["data"])) {
                        $order_data = $query["response"]["0"]["data"]["0"];

                        $price_check = PS_Price($order_data['price']);
                        if (($user['balance'] + $price_check['price']) > $PaySystemConfig['max_price']) {
                            $response = array(
                                'error' => 'yes',
                                'error_text' => 'Вы не можете начать выполнять этот заказ, т.к. если вы выполните этот заказ, ваш лимит кошелька будет привышен.',
                            );
                        } else {
                            if ($order_data['status'] != $new_status) {
                                $hash_verify = PS_Hash($user['login'] . $orderId . $user["hash"] . $method);
                                if ($data['hash'] == $hash_verify) {
                                    $ha_activity = HA_Create($method, $data["hash"], $data);
                                    if ($ha_activity['error'] != "yes") {
                                        $updateOrder = array(
                                            'contractor' => $user['id']
                                        );
                                        $change_response = PS_ChangeStatus($orderId, $new_status, $updateOrder);
                                        if ($change_response["error"] == "no") {
                                            $action = PS_CreateOrderAction($orderId, $user['id']);
                                            $response = array(
                                                'error' => 'no',
                                                'status' => 'ok'
                                            );

                                            if ($action["error"] == "no") {
                                                $response['update'] = array(
                                                    ORDER_CLASS => PS_StyleStatus($new_status),
                                                    ORDER_ACTION_CLASS => $action['content']
                                                );
                                            } else {
                                                $response['update'] = array(
                                                    ORDER_CLASS => PS_StyleStatus($new_status)
                                                );
                                                $response['action_create_error'] = $action["error_text"];
                                            }

                                            $response['htmlText'] = 'Вы начали выполнять заказ <b>' . $order_data['name'] . '</b>';
                                        } else {
                                            $response = $change_response;
                                        }
                                    } else {
                                        $response = array(
                                            'error' => 'yes',
                                            'error_text' => "[002] You observed suspicious activity, please try again later"
                                        );
                                    }
                                } else {
                                    $response = array(
                                        'error' => 'yes',
                                        'error_text' => 'invalid hash',
                                    );
                                }
                            } else {
                                $response = array(
                                    'error' => 'yes',
                                    'error_text' => 'Заказ уже начал кто-то делать, к сожалению, вы опаздали.',
                                );
                            }
                        }
                    } else {
                        $response = array(
                            'error' => 'yes',
                            'error_text' => 'order #' . $orderId . ' not found',
                        );
                    }
                }
            }
        } else {
            $response = array(
                'error' => 'yes',
                'error_text' => 'Вы не авторизованы',
            );
        }
    }
    return $response;
}

function PS_EndOrder($data)
{
    $method = "ps_end_order";
    if (empty($data)) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'data is empty'
        );
    } else {
        $user = USERS_GET_USER();
        if ($user) {
            foreach ($data as $k => $v) {
                $data[intval($k)] = intval($v);
            }
            $orderId = intval($data['id']);
            $new_status = 1;

            $updateOrder = array(
                'contractor' => 0
            );

            $change_status = PS_ChangeStatus($orderId, $new_status, $updateOrder);
            $response = array(
                'error' => 'yes',
                'error_text' => 'ТЕСТОВЫЙ РЕЖИМ: Задача вернута снова доступна для старта заказа',
                'test' => $change_status
            );
        }
    }
    return $response;
}

function PS_GetList($filter = false)
{
    $serverId = 1;
    $serverDb = 'test_db2';

    if (isset($filter) && !empty($filter)) {
        $select_array = array(
            'orders' => array(
                'select' => "*",
                'where' => $filter
            )
        );
    } else {
        $select_array = array(
            'orders' => array(
                'select' => "*",
                'where' => "`status` = '1' OR `status` = '0'"
            )
        );
    }

    /* Проверка наличия привязки профиля */
    $query = BD_select($select_array, $serverId, $serverDb);
    if (isset($query["response"]["0"]["data"])) {
        $data = $query["response"]["0"]["data"];
    }

    $html_output = '<table>';
    $html_output .= '<thead>';
    $html_output .= '<tr>';
    $html_output .= '<th>Заказ</th>';
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

            $class = null;
            if ($order_value["contractor"] == $user['id']) {
                $class = 'class="deploy"';
            }
            $html_output .= '<tr ' . $class . '>';

            $html_output .= '<td>';
            $html_output .= '<a href="' . $link . '" onclick="' . $link_js . '">';
            $html_output .= '<div class="clear_box">';
            if ($user['id'] == $order_value["owner"]) {
                $username = $owner['name'] . " (Вы)";
            } else {
                $username = $owner['name'];
            }

            $order_name = $order_value["name"];

            $html_output .= '<b>' . $order_name . '</b>';
            $html_output .= '</div>';
            $html_output .= '</a>';
            $html_output .= '</td>';

            $html_output .= '<td>';
            $html_output .= '<a href="https://vk.com/' . $owner['login'] . '" target="_blank">' . $username . '</a>';
            $html_output .= '</td>';
            $html_output .= '<td>' . PS_GetStatus($order_value['status']) . '</td>';
            $html_output .= '<td>' . PS_Balance($price['price'], true) . '</td>';
            $html_output .= '</tr>';
        }
    }
    $html_output .= '</table>';

    return $html_output;
}

function PS_GetOrderDetail($orderId)
{
    $content = null;
    $order = PS_GetOrderByID($orderId);

    if (isset($order["response"]["0"]["data"]) && !empty($order["response"]["0"]["data"])) {
        $data = $order["response"]["0"]["data"]["0"];
        $price = PS_Price($data["price"]);
        $owner = USER_GetByID($data["owner"]);
        $owner = $owner["response"]["0"]["data"]["0"];

        $status = PS_StyleStatus($data['status']);

        if ($data["contractor"] > 0) {
            $contractor = USER_GetByID($data["contractor"]);
            if (isset($contractor["response"]) && !empty($contractor["response"])) {
                $contractor = $contractor["response"]["0"]["data"]["0"];
            }
        }

        $title = "Заказы > {$data["name"]}";

        $content = "<h1>{$data["name"]}</h1>";
        $content .= '<div class="a-main__order-panel">';
        $content .= '<div class="a-main__order-user">';

        $content .= '<div class="a-main__order-img"><a href="https://vk.com/' . $owner["login"] . '" target="_blank">';
        $content .= '<img src="' . $owner["img_50"] . '" alt="' . $owner["name"] . '"/>';
        $content .= '</a></div>';

        $content .= '<div class="a-main__order-text">';
        $content .= '<a href="https://vk.com/' . $owner["login"] . '" target="_blank">' . $owner["name"] . '</a>';
        $content .= '<p>Платит <b>' . PS_Balance($price["price"]) . '</b> за выполнение задания</p>';
        $content .= "<p class='va__orderStatus'>{$status}</p>";
        $content .= '</div>';

        $content .= '</div>';

        $user = USERS_GET_USER();

        if ($user) {
            $content .= '<div class="va__orderAction">';
            $order_action = PS_CreateOrderAction($data['id'], $user['id']);
            if ($order_action['error'] == "no") {
                $content .= $order_action['content'];
            }
            $content .= '</div>';
        }

        $content .= '</div>';
        $description = str_replace(PHP_EOL, "<br/>", $data["description"]);
        $description = str_replace('\r', "<br/>", $description);
        $description = str_replace('\n', "<br/>", $description);
        $content .= "<p>{$description}</p>";
        /* Если заказ выполен, то пишем об этом */
        if ($data["contractor"] > 0 && isset($contractor)) {
            if ($data["status"] == 4) {
                $user_image = '<img src="' . $contractor["img_50"] . '" alt="' . $contractor["name"] . '"/>';
                $content .= '<div class="a-main__order-contractor">';
                $content .= '<div class="user">';
                $content .= $user_image;
                $content .= '</div>';
                $content .= '<div class="info">';
                $content .= '<a href="https://vk.com/' . $contractor["login"] . '">' . $contractor["name"] . '</a>';
                $content .= '<p>Выполнил ваш заказ</p>';
                $content .= '</div>';
                $content .= '</div>';
            } else if ($data["status"] == 2 && $data["owner"] == $user['id']) {
                $user_image = '<img src="' . $contractor["img_50"] . '" alt="' . $contractor["name"] . '"/>';
                $content .= '<div class="a-main__order-contractor">';
                $content .= '<div class="user">';
                $content .= $user_image;
                $content .= '</div>';
                $content .= '<div class="info">';
                $content .= '<a href="https://vk.com/' . $contractor["login"] . '">' . $contractor["name"] . '</a>';
                $content .= '<p>Выполнят ваш заказ</p>';
                $content .= '</div>';
                $content .= '</div>';
            }
        }
    } else {
        $title = "Заказы";
        $content = "<h1>Заказ не найден</h1>";
    }

    $response = array(
        'title' => $title,
        'content' => $content
    );
    return $response;
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
            'error' => 'Минимальная сумма: ' . PS_Balance($PaySystemConfig['min_price'])
        );
    } else if ($price > $PaySystemConfig['max_price']) {
        $response = array(
            'error' => 'Максимальная сумма: ' . PS_Balance($PaySystemConfig['max_price'])
        );
    } else {

        switch ($PaySystemConfig['percent_type']) {
            case 'P':
                $percent_cost = ($price * ($PaySystemConfig["percent_cost"] / 100));
                $price_final = floor($price - $percent_cost);
                $tax_final = ceil($percent_cost);
                $response = array(
                    'price' => $price_final,
                    'tax' => $tax_final
                );
                break;

            case 'S':
                $price_final = floor($price - $PaySystemConfig["percent_cost"]);
                $tax_final = ceil($PaySystemConfig["percent_cost"]);
                $response = array(
                    'price' => $price_final,
                    'tax' => $tax_final
                );
                break;

            default:
                $response['error'] = 'Settings error, set percent_type';
                break;
        }
    }

    return $response;
}

function PS_Balance($balance, $text = false)
{
    global $PaySystemConfig;
    switch ($balance) {
        default:
            if ($text) {
                $data = ($balance > 0) ? ($balance . " " . $PaySystemConfig['currency']) : "бесплатно";
            } else {
                $data = $balance . " " . $PaySystemConfig['currency'];
            }
            break;
    }
    return $data;
}

function PS_ChangeStatus($orderId, $newStatus, $data = false)
{
    if (is_int($orderId) && is_int($newStatus)) {
        $serverId = "1";
        $serverDb = "test_db2";
        if ($data && is_array($data)) {
            $update_array = $data;
            $update_array['status'] = $newStatus;
        } else {
            $update_array = array(
                'status' => $newStatus
            );
        }
        $where = "`id` = '{$orderId}'";
        $order_query = BD_update('orders', $update_array, $where, $serverId, $serverDb);
        if ($order_query['status'] == "ok") {
            $history_serverId = "1";
            $history_serverDb = "test_db3";
            $order_array = array(
                'timestamp' => time(),
                'order_id' => $orderId,
                'new_status' => $newStatus
            );
            $query = BD_insert($order_array, 'orders_history', $history_serverId, $history_serverDb);
            if ($query['status'] == "ok") {
                $response = array(
                    'error' => 'no',
                    'id' => $query['userId']
                );
            } else {
                $response = array(
                    'error' => 'yes',
                    'error_text' => $query['status']
                );
            }
        } else {
            $response = array(
                'error' => 'yes',
                'error_text' => $order_query['status']
            );
        }
    } else {
        ob_start();
        var_dump($orderId);
        var_dump($newStatus);
        $result = ob_get_clean();
        $response = array(
            'error' => 'yes',
            'error_text' => 'orderId and newStatus must be a numeric',
            'test' => $result
        );
    }
    return $response;
}

function PS_CreateOrderAction($orderId, $userId)
{
    $serverId = "1";
    $serverDb = "test_db2";

    $select_array = array(
        'orders' => array(
            'select' => "`id`, `owner`, `contractor`, `status`",
            'where' => "`id` = '{$orderId}'"
        )
    );

    $user = USER_GetByID($userId);
    $user = $user["response"]["0"]["data"]["0"];

    if ($user) {
        /* Проверка наличия привязки профиля */
        $query = BD_select($select_array, $serverId, $serverDb);

        if (isset($query["response"]["0"]["data"])) {
            $order_data = $query["response"]["0"]["data"]["0"];

            if ($order_data['owner'] != $userId) {
                if ($order_data["contractor"] == 0 && in_array($order_data['status'], array('0', '1'))) {
                    $hash = PS_Hash($user['login'] . $order_data["id"] . $user["hash"] . "ps_start_order");
                    $content = '<div class="a-main__order-start btn" onclick="orders.startOrder(this)" data-orderId="' . $order_data["id"] . '" data-hash="' . $hash . '">Начать выполнение задания</div>';
                    $response = array(
                        'error' => 'no',
                        'content' => $content
                    );
                } else {
                    if ($order_data["contractor"] == $user['id'] && $order_data['status'] == 2) {
                        $hash = PS_Hash($user['login'] . $order_data["id"] . $user["hash"] . "ps_end_order");
                        $content = '<div class="a-main__order-start btn" onclick="orders.endOrder(this)" data-orderId="' . $order_data["id"] . '" data-hash="' . $hash . '">Закончить выполнение задания</div>';
                        $response = array(
                            'error' => 'no',
                            'content' => $content
                        );
                    } else {
                        $response = array(
                            'error' => 'no',
                            'content' => ''
                        );
                    }
                }
            } else {
                $response = array(
                    'error' => 'yes',
                    'error_text' => '[OCA1]: Пользователь не найден'
                );
            }
        } else {
            $response = array(
                'error' => 'yes',
                'error_text' => '[OCA2]: Заказ не найден'
            );
        }
    } else {
        $response = array(
            'error' => 'yes',
            'error_text' => '[OCA1]: Пользователь не найден'
        );
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

function PS_StyleStatus($data)
{
    if ($data) {
        $status = PS_GetStatus($data);
        switch ($data) {
            case 4:
                $response = 'Статус: <span class="status_active">' . $status . '</span>';
                break;
            case 5:
                $response = 'Статус: <span class="status_reserve">' . $status . '</span>';
                break;
            default:
                $response = 'Статус: <span class="status_block">' . $status . '</span>';
                break;
        }
        return $response;
    } else {
        return false;
    }
}