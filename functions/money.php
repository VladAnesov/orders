<?php
/**
 * Created by PhpStorm.
 * User: Entero
 * Date: 21.08.2017
 * Time: 15:41
 */

if (!defined('CORE_INIT')) die('Core protection');

require_once(PROJECT_LINK . "/config/PaySystem.php");
require_once(PROJECT_LINK . "/mysql/connect.php");

function M_createModalAdd()
{
    global $PaySystemConfig;
    $user = USERS_GET_USER();
    if (isset($user["id"])) {
        $hash = PS_Hash($user['login'] . $user["hash"] . "m_add");

        $data = '<div class="va__modal_iblock">';
        $data .= '<div class="va__modal_iblock-title">Сумма</div>';
        $data .= '<input autocomplete="off" type="number" class="va-input" onkeypress=\'return event.charCode >= 48 && event.charCode <= 57\' pattern="[0-9]{5}" oninput="orders.minMax(this, ' . $PaySystemConfig["min_price"] . ', ' . $PaySystemConfig["max_price"] . ');" min="' . $PaySystemConfig["min_price"] . '" max="' . $PaySystemConfig["max_price"] . '" name="sum" placeholder="Сумма в рублях" required/>';
        $data .= '</div>';

        $data .= '<input type="hidden" name="hash" value="' . $hash . '"/>';

        $response = array(
            'error' => 'no',
            'title' => 'Пополнить баланс',
            'sendtitle' => 'Пополнить',
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

function M_add2user($data)
{
    global $PaySystemConfig;
    if (empty($data)) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'data is empty'
        );
        return $response;
    }

    if (!isset($data['sum']) && empty($data['sum'])) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'sum is not valid'
        );
        return $response;
    }

    $user = USERS_GET_USER();
    if (isset($user["id"])) {
        foreach ($data as $k => $v) {
            $data[trim(mysql_escape_string($k))] = mysql_escape_string(trim($v));
        }

        $hash = PS_Hash($user['login'] . $user["hash"] . "m_add");
        if ($hash == $data["hash"]) {
            $ha_activity = HA_Create("m_create_data", $data["hash"], $data);
            if ($ha_activity['error'] != "yes") {
                if (isset($data['sum']) && !ctype_digit($data['sum'])) {
                    $response = array(
                        'error' => 'yes',
                        'error_text' => "price is not numeric"
                    );
                } else {
                    $data['sum'] = floor($data['sum']);
                    $balance_add = ($user['balance'] + $data['sum']);
                    if ($data['sum'] < $PaySystemConfig['min_price']) {
                        $response = array(
                            'error' => 'yes',
                            'error_text' => "min price: " . PS_Balance($PaySystemConfig['min_price'])
                        );
                    } else if ($data['sum'] > $PaySystemConfig['max_price']) {
                        $response = array(
                            'error' => 'yes',
                            'error_text' => "max price: " . PS_Balance($PaySystemConfig['max_price'])
                        );
                    } else if ($balance_add > $PaySystemConfig['max_price']) {
                        $canPay = $PaySystemConfig['max_price'] - $user['balance'];
                        $response = array(
                            'error' => 'yes',
                            'error_text' => "Вы можете пополнить баланс на сумму " . PS_Balance($canPay) . " Лимит кошелька: " . PS_Balance($PaySystemConfig['max_price'])
                        );
                    } else {
                        $transactionStart = M_createTransaction("add", $data['sum'], 0, $user["id"]);
                        if ($transactionStart["error"] == "no") {
                            $sum = PS_Balance($data['sum']);
                            $user_balance = PS_Balance($transactionStart["payee_balance"]);
                            $response = array(
                                'error' => 'no',
                                'update' => array(
                                    '.a-main__balance-value' => $user_balance
                                ),
                                'htmlText' => 'Вы добавили на свой аккаунт <b>' . $sum . '</b><br>Теперь ваш баланс: <b>' . $user_balance . '</b>'
                            );
                        } else {
                            $response = array(
                                'error' => 'yes',
                                'error_text' => $transactionStart["error_text"]
                            );
                        }
                    }
                }
            } else {
                $response = array(
                    'error' => 'yes',
                    'error_text' => "[008] You observed suspicious activity, please try again later"
                );
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
            'error_text' => 'Вы не авторизованы',
        );
    }
    return $response;
}

function M_GetByID($id)
{
    if ((int)$id >= 0) {

        $serverId = 1;
        $serverDb = 'test_db4';

        $id_sql = mysql_escape_string($id);

        $select_array = array(
            'transaction' => array(
                'select' => "*",
                'where' => "(`id`='{$id_sql}')"
            )
        );

        $S_Response = BD_select($select_array, $serverId, $serverDb);

        return $S_Response;
    } else {
        return false;
    }
}

function M_GetTransactionList($userId)
{
    $serverId = 1;
    $serverDb = 'test_db4';

    $id_sql = mysql_escape_string($userId);

    $select_array = array(
        'transaction' => array(
            'select' => "*",
            'where' => "(`sender`='{$id_sql}' OR `payee` = '{$id_sql}')",
            'sort' => array(
                'key' => 'id',
                'type' => 'desc'
            )
        )
    );

    $S_Response = BD_select($select_array, $serverId, $serverDb);
    return $S_Response;
}

function M_transactionUpdate($id, $data, $ignore = false)
{
    $serverId = 1;
    $serverDb = 'test_db4';

    if ((int)$id < 0) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'id is empty'
        );
    } else if (empty($data)) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'data is empty'
        );
    } else if (!is_array($data)) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'data must be array'
        );
    } else {
        $user = M_GetByID($id);
        $user_data = $user["response"]["0"]["data"]["0"];
        if (isset($user_data["id"])) {
            $update_fields = null;
            if ($ignore == false) {
                foreach ($data as $key => $value) {
                    $update_fields[mysql_escape_string($key)] = mysql_escape_string($value);
                }
            }

            $update_filter = "`id` = '" . $user_data['id'] . "'";
            $update_query = BD_update('transaction', $update_fields, $update_filter, $serverId, $serverDb);

            if ($update_query['status'] == "ok") {
                $response = array(
                    'error' => 'no',
                    'data' => $update_query
                );
            } else {
                /* Повтор запроса, в случае, если сервер mysql не отвечает, но не забываем игнорировать уже установку идов*/
                $data = M_transactionUpdate($id, $data, true);
                $response = $data;
                $response['try'][] = $data;
            }
        } else {
            $response = array(
                'error' => 'yes',
                'error_text' => 'transaction not found'
            );
        }
    }
    return $response;
}

function M_createTransaction($type, $sum, $sender, $payee)
{
    global $PaySystemConfig;

    $serverId = 1;
    $serverDb = "test_db4";

    if (!isset($type) || empty($type)) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'Транзакции: указан не тот тип перевода',
        );
    } else if (!isset($sum) || $sum < 0) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'Транзакции: неверная сумма перевода',
        );
    } else if (!isset($sender) || $sender < 0) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'Транзакции: не выбран отправитель',
        );
    } else if (!isset($payee) || $payee < 0) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'Транзакции: не выбран получатель',
        );
    } else if ($sum < 0) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'Транзакции: сумма перевода должна сстоять из цифр',
        );
    } else if ($sum > $PaySystemConfig['max_price']) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'Транзакции: максимальная сумма для перевода: ' . PS_Balance($PaySystemConfig['max_price']) . ' у вас' . $sum,
        );
    } else if ($sum < 0) {
        $response = array(
            'error' => 'yes',
            'error_text' => 'Транзакции: минимальная сумма для перевода: ' . PS_Balance(0),
        );
    } else {
        $transaction_type = M_transactionType($type, true);
        if ($transaction_type["data"] == true) {
            if ($sender >= 0) {
                /* проверка отправителя */
                $sender_select = USER_GetByID($sender);
                $sender_data = $sender_select["response"]["0"]["data"]["0"];
                if (isset($sender_data["id"])) {
                    /* проверка получателя */
                    if ($payee >= 0) {
                        $payee_select = USER_GetByID($payee);
                        $payee_data = $payee_select["response"]["0"]["data"]["0"];

                        if (isset($payee_data["id"])) {
                            $sender_new = $sender_data['balance'] - $sum;
                            $payee_new = $payee_data['balance'] + $sum;


                            if ($sender_new < 0 && $sender_data["id"] != "0") {
                                $response = array(
                                    'error' => 'yes',
                                    'error_text' => 'for sender min balance value: 0'
                                );
                            } else if ($payee_new > $PaySystemConfig['max_price'] && $payee_data["id"] != "0") {
                                $response = array(
                                    'error' => 'yes',
                                    'error_text' => 'for payee max balance value: ' . PS_Balance($PaySystemConfig['max_price'])
                                );
                            } else {
                                /* запись в историю транзакции */
                                $user_array = array(
                                    'timestamp' => time(),
                                    'type' => $type,
                                    'sum' => $sum,
                                    'sender_old' => $sender_data["balance"],
                                    'sender_new' => $sender_new,
                                    'sender' => $sender_data["id"],
                                    'payee_old' => $payee_data["balance"],
                                    'payee_new' => $payee_new,
                                    'payee' => $payee_data["id"]
                                );
                                $insert_query = BD_insert($user_array, 'transaction', $serverId, $serverDb);
                                if ($insert_query['status'] == "ok") {
                                    if ($type == "add" && $sender_data["id"] == 0) {
                                        $sender_array = array(
                                            'balance' => $sender_data['balance']
                                        );
                                    } else {
                                        $sender_array = array(
                                            'balance' => $sender_new
                                        );
                                    }
                                    $update_sender = USER_UPDATE($sender_data['id'], $sender_array);

                                    if ($update_sender["error"] == "no") {
                                        $payee_array = array(
                                            'balance' => $payee_new
                                        );
                                        $update_payee = USER_UPDATE($payee_data['id'], $payee_array);
                                        if ($update_payee["error"] == "no") {
                                            if (isset($insert_query['userId'])) {
                                                $m_transactionArray = array(
                                                    'status' => 1
                                                );
                                                $m_transactionUpdate = M_transactionUpdate($insert_query['userId'], $m_transactionArray);
                                                if ($m_transactionUpdate["error"] = "no") {
                                                    $response = array(
                                                        'error' => 'no',
                                                        'sender_balance' => ($sender_data["id"] == 0) ? null : $sender_new,
                                                        'payee_balance' => ($payee_data["id"] == 0) ? null : $payee_new,
                                                        'transactionId' => $insert_query['userId']
                                                    );
                                                } else {
                                                    $response = array(
                                                        'error' => 'yes',
                                                        'error_text' => $m_transactionUpdate["error_text"]
                                                    );
                                                }
                                            } else {
                                                $m_try = M_createTransaction($type, $sum, $sender, $payee);
                                                $response = $m_try;
                                                $response['try'][] = $m_try;
                                            }

                                        } else {
                                            $response = array(
                                                'error' => 'yes',
                                                'error_text' => $update_payee["error_text"],
                                            );
                                        }
                                    } else {
                                        $response = array(
                                            'error' => 'yes',
                                            'error_text' => $update_sender["error_text"],
                                        );
                                    }
                                } else {
                                    $response = array(
                                        'error' => 'yes',
                                        'error_text' => $insert_query['status'],
                                    );
                                }
                            }
                        } else {
                            $response = array(
                                'error' => 'yes',
                                'error_text' => 'payee user not found',
                            );
                        }
                    } else {
                        $response = array(
                            'error' => 'yes',
                            'error_text' => 'sender invalid',
                        );
                    }
                } else {
                    $response = array(
                        'error' => 'yes',
                        'error_text' => 'sender user not found',
                    );
                }
            } else {
                $response = array(
                    'error' => 'yes',
                    'error_text' => 'invalid sender',
                );
            }
        } else {
            $response = array(
                'error' => 'yes',
                'error_text' => 'transaction type not found',
            );
        }
    }
    return $response;
}

function M_transactionType($type, $check)
{
    $types = array(
        'add' => 'Пополнение',
        'order_create' => 'Оплата создания заказа',
        'order_create_tax' => 'Оплата комиссии системы за создание заказа',
        'order_payee' => 'Пополнение за выполнение задания',
        'transfer_send' => 'Перевод денег другому пользователю',
        'transfer_payee' => 'Получение денег от другого пользователя',
        'order_refund' => 'Возврат денежных средств',
        'order_refund_tax' => 'Отмена уплаты комиссии (возврат средств)'
    );
    if ($check == true) {
        $response = array(
            'error' => 'no',
            'data' => (array_key_exists($type, $types)) ? true : false
        );
    } else {
        $response = array(
            'error' => 'no',
            'data' => (array_key_exists($type, $types)) ? $types[$type] : false
        );
    }
    return $response;
}