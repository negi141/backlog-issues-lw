<?php
require_once "lib/init.php";
require_once "lib/util.php";
require_once "lib/html.php";
require_once "lib/backlog.php";

mb_language("Japanese");
mb_internal_encoding("UTF-8");

$to      = getenv('MAIL_TO');
$subject = 'タイトル';
$message = '本文';
$headers = 'From: ' . 'tnegishi@pro-seeds.co.jp' . "\r\n";

mb_send_mail($to, $subject, $message, $headers);

