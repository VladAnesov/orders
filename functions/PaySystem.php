<?php
/**
 * Created by PhpStorm.
 * User: Entero
 * Date: 04.08.2017
 * Time: 17:11
 */

require_once(PROJECT_LINK . "/config/PaySystem.php");
require_once(PROJECT_LINK . "/mysql/connect.php");

function CreateOrderDialog()
{

}

function PS_GetList()
{
    global $PaySystemConfig;
    $array = array(
        'test_db2' => array(
            'orders' => array(
                'map' => 't2',
                'search' => '*',
                'where' => array(
                    'test_db1' => array(
                        'users' => array(
                            'self_owner' => 'id',
                        )
                    ),
                    'test_db1' => array(
                        'users' => array(
                            'self_owner' => 'id',
                        )
                    )
                )
            )
        ),
        'test_db1' => array(
            'users' => array(
                'search' => 'name;img_50;login'
            )
        )
    );
    $query = BD_diff_select($array, 1);

    $html_output = '<table>';
    $html_output .= '<thead>';
    $html_output .= '<tr>';
    $html_output .= '<th>Проект</th>';
    $html_output .= '<th>Создал</th>';
    $html_output .= '<th>Статус</th>';
    $html_output .= '<th>Стоимость</th>';
    $html_output .= '</tr>';
    $html_output .= '</thead>';
    $html_output .= '<tr>';
    if (isset($query["state"]["response"]) && !empty($query["state"]["response"])) {
        foreach ($query["state"]["response"] as $order_key => $order_value) {
            $link = PROJECT_URL . "/orders/id/" . $order_value["id"];
            $link_js = showContent(PROJECT_URL . "/orders", '.a-body', '.loading', false);
            $price = PS_Price($order_value['price']);
            $html_output .= '<td>';
            $html_output .= '<a href="' . $link . '" onclick="' . $link_js . '"><b>' . $order_value["name"] . '</b></a>';
            $html_output .= '<br/><p>' . $order_value['description'] . '</p>';
            $html_output .= '</td>';
            $html_output .= '<td>';
            $html_output .= '<a href="https://vk.com/' . $order_value['t2_login'] . '" target="_blank">' . $order_value['t2_name'] . '</a>';
            $html_output .= '</td>';
            $html_output .= '<td>' . PS_GetStatus($order_value['status']) . '</td>';
            $html_output .= '<td>' . $price['price'] . ' ' . $PaySystemConfig['currency'] . '</td>';
        }
    }
    $html_output .= '</tr>';
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

    if ($price < $PaySystemConfig['min_price'])
        return $response['error'] = 'Min price: ' . $PaySystemConfig['min_price'];

    if ($price > $PaySystemConfig['max_price'])
        return $repsonse['error'] = 'Max price; ' . $PaySystemConfig['max_price'];

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