<?php
/**
 * Created by PhpStorm.
 * User: Entero
 * Date: 04.08.2017
 * Time: 17:21
 */

require_once(PROJECT_LINK . "/config/VK.php");
require_once(PROJECT_LINK . "/mysql/connect.php");

function USERS_INIT($ignore_f_c = false)
{
    $serverId = 1;
    $serverDb = 'test_db1';
    $response = array('auth' => false);
    if (empty($_COOKIE['va_hash']) || $ignore_f_c == true) {
        if (isset($_GET['code']) && !empty($_GET['code']) && ctype_alnum($_GET['code'])) {
            $token = VK_GetToken($_GET['code']);

            if (!isset($token['error'])) {
                $fields = 'photo_50,screen_name';
                $user_info = VK_GetUserInfo($token['user_id'], $fields, $token['access_token']);

                $escape_uid = mysql_escape_string($user_info['response']['0']['uid']);
                $select_array = array(
                    'users' => array(
                        'select' => "*",
                        'where' => "vk_uid = {$escape_uid}"
                    )
                );

                /* Проверка наличия привязки профиля */
                $S_Response = BD_select($select_array, $serverId, $serverDb);

                if (empty($S_Response['response'])) {
                    /* Пользователя нет, регаем */
                    $user_hash = USER_CreateHash($_SERVER['REMOTE_ADDR'] . $user_info['response']['0']['screen_name']);
                    $user_array = array(
                        'vk_uid' => $user_info['response']['0']['uid'],
                        'name' => $user_info['response']['0']['first_name'] . ' ' . $user_info['response']['0']['last_name'],
                        'img_50' => $user_info['response']['0']['photo_50'],
                        'login' => $user_info['response']['0']['screen_name'],
                        'balance' => '0',
                        'group' => '0',
                        'hash' => $user_hash,
                        'ip' => $_SERVER['REMOTE_ADDR']
                    );
                    $insert_query = BD_insert($user_array, 'users', $serverId, $serverDb);
                    if ($insert_query['status'] == "ok") {
                        setcookie("va_hash", $user_hash, time() + 86300, PROJECT_URL, "vladanesov.ru");
                        $response = array(
                            'auth' => true,
                            'user' => $user_info['response']['0']
                        );
                        header("Location: " . PROJECT_URL);
                    } else {
                        $response = array(
                            'auth' => false,
                            'authlink' => GenerateLinkAuth()
                        );
                    }
                } else {
                    /* Индентифицируем пользователя */
                    $user_hash = USER_CreateHash($_SERVER['REMOTE_ADDR'] . $user_info['response']['0']['screen_name']);

                    $update_fields = array(
                        'name' => $user_info['response']['0']['first_name'] . ' ' . $user_info['response']['0']['last_name'],
                        'img_50' => $user_info['response']['0']['photo_50'],
                        'login' => $user_info['response']['0']['screen_name'],
                        'hash' => $user_hash,
                        'ip' => $_SERVER['REMOTE_ADDR']
                    );

                    $update_filter = "`vk_uid` = '" . mysql_escape_string($user_info['response']['0']['uid']) . "'";

                    $update_query = BD_update('users', $update_fields, $update_filter, $serverId, $serverDb);
                    if ($update_query['status'] == "ok") {
                        setcookie("va_hash", $user_hash, time() + 86300, PROJECT_URL, "vladanesov.ru");
                        $response = array(
                            'auth' => true,
                            'user' => $user_info['response']['0']
                        );
                        header("Location: " . PROJECT_URL);
                    } else {
                        $response = array(
                            'auth' => false,
                            'authlink' => GenerateLinkAuth()
                        );
                    }
                }
            } else {
                $response = array(
                    'auth' => false,
                    'authlink' => GenerateLinkAuth()
                );
            }
        } else {
            $response = array(
                'auth' => false,
                'authlink' => GenerateLinkAuth()
            );
        }
    } else {
        // check your hash
        $mysql_hash = mysql_escape_string($_COOKIE['va_hash']);
        $select_array = array(
            'users' => array(
                'select' => "*",
                'where' => "(`hash`='{$mysql_hash}')"
            )
        );

        /* Проверка наличия привязки профиля */
        $S_Response = BD_select($select_array, $serverId, $serverDb);

        if (empty($S_Response['response'])) {
            if (isset($_COOKIE['va_hash'])) {
                if (USERS_LOGOUT()) {
                    $response = array(
                        'auth' => false,
                        'authlink' => GenerateLinkAuth()
                    );
                }
            } else {
                USERS_INIT(true);
            }
        } else {
            $user_data = $S_Response['response']['0']['data']['0'];

            if ($user_data['ip'] != $_SERVER['REMOTE_ADDR']) {
                if (USERS_LOGOUT()) {
                    $response = array(
                        'auth' => false,
                        'authlink' => GenerateLinkAuth()
                    );
                }
            } else if ($user_data['hash'] != $_COOKIE['va_hash']) {
                if (USERS_LOGOUT()) {
                    $response = array(
                        'auth' => false,
                        'authlink' => GenerateLinkAuth()
                    );
                }
            } else {
                $response = array(
                    'auth' => true,
                    'user' => $user_data
                );
            }
        }
    }

    return $response;
}

function USERS_LOGOUT()
{
    if (isset($_COOKIE['va_hash'])) {
        if (setcookie("va_hash", '', time() - 86300, PROJECT_URL, "vladanesov.ru")) {
            return true;
        } else {
            return false;
        }
    }
}

function GenerateLinkAuth()
{
    global $VKAuthData;
    $url = 'http://oauth.vk.com/authorize';
    $params = array(
        'client_id' => $VKAuthData['app_id'],
        'redirect_uri' => $VKAuthData['redirect_url'],
        'response_type' => 'code'
    );
    $link = $url . '?' . urldecode(http_build_query($params));

    return $link;

}

function VK_GetToken($code, $debug = false)
{
    if (empty($code)) {
        if ($debug) {
            return "'code' is empty in VK_GetToken";
        } else {
            return false;
        }
    }

    global $VKAuthData;
    $params = array(
        'client_id' => $VKAuthData['app_id'],
        'client_secret' => $VKAuthData['app_secret'],
        'code' => htmlspecialchars($code),
        'redirect_uri' => $VKAuthData['redirect_url'],
    );

    $link = 'https://oauth.vk.com/access_token' . '?' . urldecode(http_build_query($params));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $link);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $VKResponse = curl_exec($ch);
    curl_close($ch);

    if ($VKResponse === FALSE) {
        if ($debug) {
            return "cURL Error: " . curl_error($ch);
        } else {
            return false;
        }
    } else {
        $token = json_decode($VKResponse, true);
        return $token;
    }
}

function VK_GetUserInfo($user_ids, $fields, $access_token)
{
    if (empty($user_ids) || empty($fields) || empty($access_token)) {
        return false;
    }

    global $VKAuthData;

    $params = array(
        'user_ids' => $user_ids,
        'fields' => $fields,
        'access_token' => $access_token
    );

    $link = 'https://api.vk.com/method/users.get' . '?' . urldecode(http_build_query($params));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $link);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $VKResponse = curl_exec($ch);
    curl_close($ch);

    if ($VKResponse === FALSE) {
        return false;
    }

    $response = json_decode($VKResponse, true);
    return $response;
}

function USER_CreateHash($login)
{
    return hash('sha512', $login . date("d_m_Y"));
}

function USER_GetList()
{
    $array = array(
        'test_db1' => array(
            'users' => array(
                'map' => 't2',
                'search' => '*',
                'where' => array(
                    'test_db2' => array(
                        'orders' => array(
                            'self_id' => 'contractor',
                            'value_status' => '3'
                        )
                    )
                )
            )
        ),
        'test_db2' => array(
            'orders' => array(
                'search' => 'id'
            )
        )
    );
    $query = BD_diff_select($array, 1);

    $html_output = '<table>';
    $html_output .= '<tr>';
    $html_output .= '<th>Исполнитель</th>';
    $html_output .= '<th>Выполнил работ</th>';
    $html_output .= '</tr>';
    $html_output .= '<tr>';
    if (isset($query["state"]["response"]) && !empty($query["state"]["response"])) {
        foreach ($query["state"]["response"] as $order_key => $order_value) {
            $html_output .= '<td>';
            $html_output .= '<a href="https://vk.com/' . $order_value['login'] . '" target="_blank">' . $order_value['name'] . '</a>';
            $html_output .= '</td>';
            $html_output .= '<td>' . $order_value["t2_count"] . '</td>';
        }
    }
    $html_output .= '</tr>';
    $html_output .= '</table>';
    return $html_output;
}