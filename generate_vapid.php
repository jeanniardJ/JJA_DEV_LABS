<?php
require_once 'vendor/autoload.php';
use Minishlink\WebPush\VAPID;

$keys = VAPID::createVapidKeys();
echo "VAPID_PUBLIC_KEY=" . $keys['publicKey'] . "
";
echo "VAPID_PRIVATE_KEY=" . $keys['privateKey'] . "
";
