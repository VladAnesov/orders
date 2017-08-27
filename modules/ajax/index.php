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

        case "ps_start_order":
            $response = PS_StartOrder($_POST);
            break;

        case "ps_end_order":
            $response = PS_EndOrder($_POST);
            break;

        case "ps_get-price":
            $price = trim($_POST['price']);
            if (ctype_digit($price)) {
                $user = USERS_GET_USER();
                if ($user) {
                    global $PaySystemConfig;
                    $get_price = PS_Price($price);
                    if (isset($get_price["error"]) && !empty($get_price["error"])) {
                        $response = array(
                            'error' => 'no',
                            'data' => "<b>" . $get_price["error"] . "</b>"
                        );
                    } else {
                        $text = "Стоимость заказа: <b>" . PS_Balance($get_price["price"], true) . "</b> ";
                        $text .= "коммисия системы: <b>" . PS_Balance($get_price["tax"]) . "</b>";
                        switch ($PaySystemConfig['percent_type']) {
                            case 'P':
                                $percent = "";
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
                    }
                } else {
                    $response = array(
                        'error' => 'yes',
                        'error_text' => "401 Unauthorized"
                    );
                }
            } else {
                $response = array(
                    'error' => 'yes',
                    'error_text' => "price is not numeric"
                );
            }
            break;

        case "m_create":
            $response = M_createModalAdd();
            break;

        case "m_create_data":
            $response = M_add2user($_POST);
            break;

        default:
            $response = array(
                'error' => 'yes',
                'error_text' => 'type is empty'
            );
            break;
    }
} else {
    $response = array(
        'error' => 'yes',
        'error_text' => 'type not found'
    );
}

die(json_encode($response));