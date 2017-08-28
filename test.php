<title>test</title>
<?php
/**
 * Created by PhpStorm.
 * User: Entero
 * Date: 31.07.2017
 * Time: 14:57
 */
ini_set('display_errors', 'on');

echo $_SERVER['REMOTE_ADDR'];
echo '<br>';
echo $_SERVER['HTTP_X_FORWARDED_FOR'];