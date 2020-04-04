<?php

use IDCT\Wykop\SimpleWykop;

include "vendor/autoload.php";

ini_set('xdebug.var_display_max_depth', '10');
ini_set('xdebug.var_display_max_children', '256');
ini_set('xdebug.var_display_max_data', '1024');

$appKey = '';
$appSecret = '';
$connectionKey = '';
$login = '';

$wykop = new SimpleWykop($appKey, $appSecret, $connectionKey);
$wykop->login($login);
$notifications = $wykop->retrieveHashtagNotifications();
var_dump($notifications->getData());
foreach ($notifications as $notification) {
    var_dump($notification->getUrl());
}
