<?php
/**
 * Created by PhpStorm.
 * User: HOME
 * Date: 14.08.2017
 * Time: 23:01
 */

if (isset($params['type'])) {
    switch ($params['type']) {
        case "ps_create":
            $response = PS_CreateOrderDialog();
            break;

        case "ps_create_data":
            $response = PS_CreateOrder($_POST);
            break;

        case "ps_get-price":
            $price = trim($_POST['price']);
            if (ctype_digit($price)) {
                global $PaySystemConfig;
                $get_price = PS_Price($price);
                $text = "Исполнитель заплатит: <b>{$get_price["price"]} {$PaySystemConfig['currency']}</b> ";
                $text .= "коммисия системы: <b>{$get_price["tax"]} {$PaySystemConfig['currency']}</b>";
                switch ($PaySystemConfig['percent_type']) {
                    case 'P':
                        $percent = " ({$PaySystemConfig["percent_cost"]}%)";
                        break;

                    case 'S':
                        $percent = " ({$PaySystemConfig["percent_cost"]} {$PaySystemConfig['currency']})";
                        break;
                }
                $text .= $percent;
                $response = array(
                    'error' => 'no',
                    'data' => $text
                );
            } else {
                $response = array(
                    'error' => 'yes',
                    'error_text' => "price is not numeric"
                );
            }
            break;

        default:
            $response = array(
                'status' => 'error',
                'text' => 'type is empty'
            );
            break;
    }
} else {
    $response = array(
        'status' => 'error',
        'text' => 'type not found'
    );
}

die(json_encode($response));