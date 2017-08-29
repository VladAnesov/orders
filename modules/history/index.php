<?php
/**
 * Created by PhpStorm.
 * User: HOME
 * Date: 27.08.2017
 * Time: 23:10
 */

if (!defined('CORE_INIT')) die('Core protection');

$title = "История транзакций";
$user = USERS_GET_USER();
if ($user) {
    $content = '<h4>История транзакций</h4>';
    $transaction_list = M_GetTransactionList($user['id']);
    if (isset($transaction_list["response"]["0"]["data"])) {
        $transaction_list = $transaction_list["response"]["0"]["data"];
        $word_transaction_array = array('транзакция', 'транзакции', 'транзакций');
        $transaction_count = TEXT_declinationOfNum(count($transaction_list), $word_transaction_array);
        $content .= '<p>В списке ' . $transaction_count . '</p>';

        $content .= '<table>';
        $content .= '<thead>';
        $content .= '<tr>';
        $content .= '<th></th>';
        $content .= '<th>Дата</th>';
        $content .= '<th>Тип транзакции</th>';
        $content .= '<th>Сумма</th>';
        $content .= '<tr>';
        $content .= '</thead>';

        foreach ($transaction_list as $k => $v) {
            $transaction_type = M_transactionType($v['type'], false);
            $content .= '<tr>';
            $content .= '<td class="table_min_number">#' . $v["id"] . '</td>';
            $content .= '<td>' . date('d.m.Y H:i:s', $v['timestamp']) . '</td>';
            $content .= '<td>' . $transaction_type["data"] . '</td>';
            $content .= '<td>' . PS_Balance($v['sum']) . '</td>';
            $content .= '</tr>';
        }
        $content .= '</table>';
    } else {
        $content .= "Нет данных";
    }
} else {
    $content = 'Ошибка доступа';
}