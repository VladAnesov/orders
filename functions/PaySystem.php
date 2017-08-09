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
            $html_output .= '<td><b>' . $order_value["name"] . '</b><br/><p>' . $order_value['description'] . '</p></td>';
            $html_output .= '<td>';
            $html_output .= '<a href="https://vk.com/' . $order_value['t2_login'] . '" target="_blank">' . $order_value['t2_name'] . '</a>';
            $html_output .= '</td>';
            $html_output .= '<td>' . PS_GetStatus($order_value['status']) . '</td>';
            $html_output .= '<td>' . $order_value['price'] . ' </td>';
        }
    }
    $html_output .= '</tr>';
    $html_output .= '</table>';
    return $html_output;
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
        '1' => 'Открыто',
        '2' => 'Выполняется',
        '3' => 'Выполнено',
        '4' => 'Не выполнено'
    );

    return $status_values[$status];
}