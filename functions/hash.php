<?php
/**
 * Created by PhpStorm.
 * User: Entero
 * Date: 31.07.2017
 * Time: 15:35
 */

/**
 * @param $page
 * @param $element
 * @param $loading_element
 * @param $set_class
 * @return string
 */
function showContent($page, $element, $loading_element, $set_class = true)
{
    $secret = "vk.com/dev/null";
    $hash = hash('sha256', $page . $secret);
    if ($set_class) {
        $cl_st = "true";
    } else {
        $cl_st = "false";
    }
    return "showContent(this, '$element', '$loading_element', '$hash', $cl_st)";
}

/**
 * @param $page
 * @param $element
 * @param $loading_element
 * @return hash
 */
function getHash($page)
{
    $secret = "vk.com/dev/null";
    $hash = hash('sha256', $page . $secret);
    return $hash;
}