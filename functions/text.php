<?php
/**
 * Created by PhpStorm.
 * User: HOME
 * Date: 27.08.2017
 * Time: 18:57
 */

if (!defined('CORE_INIT')) die('Core protection');

function Text_Convert2user($text)
{
    $data = trim($text);
    $data = str_replace(PHP_EOL, "<br/>", $data);
    $data = str_replace('\r', "<br/>", $data);
    $data = str_replace('\n', "<br/>", $data);

    /* Set URLs */
    $data = preg_replace_callback('/((http|https|ftp|ftps):\/\/[^\s<]+[^\s\.,\!\?:;)<\]])/u', 'Text_SURL', $data);

    return $data;
}

function Text_SURL($arrayText)
{
    $url = null;
    $name = null;
    if (is_array($arrayText)) {
        $url = $arrayText[1];
        $name = $url;
        if (strlen($name) > 70) {
            $t = strpos($name, '/', 8) + 1;
            if ($t === false) $t = 50;
            $rest = substr($name, $t);
            $name = substr($name, 0, $t) . '...';
            if (preg_match_all('/[^a-zA-Z0-9]+[a-zA-Z0-9]+/u', $rest, $rest_a)) {
                $rest_a = array_reverse($rest_a[0]);
                $rest_str = array();
                $restr_cnt = 0;
                foreach ($rest_a as $item) {
                    $t = strlen($item);
                    if ($restr_cnt + $t > 60) {
                        if ($restr_cnt < 15)
                            $rest_str[] = substr($item, $restr_cnt - 60);
                        break;
                    }
                    $restr_cnt += $t;
                    $rest_str[] = $item;
                }
                $name .= implode('', array_reverse($rest_str));
            } else {
                $name .= substr($rest, $t - 70);
            }
        }
    }

    return '<a href="' . $url . '">' . $name . '</a>';
}

function TEXT_declinationOfNum($number, $titles)
{
    $cases = array(2, 0, 1, 1, 1, 2);
    return $number . " " . $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
}