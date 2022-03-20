<?php
require_once "Consumer.php";

$new_consumer = new Consumer('your_ip', 5672, 'your_user', 'your_pass', 'your_queue', 2, 'consumer.log');
$new_consumer->connecting();
$new_consumer->action();
$new_consumer->close();