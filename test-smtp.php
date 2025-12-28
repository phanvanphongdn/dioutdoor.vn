<?php
$smtp_server = "smtp.yandex.com";
$smtp_port = 465;
$timeout = 30;

$connection = fsockopen($smtp_server, $smtp_port, $errno, $errstr, $timeout);

if (!$connection) {
    echo "Connection failed: $errstr ($errno)\n";
} else {
    echo "Connection to SMTP server successful.\n";
    fclose($connection);
}
?>