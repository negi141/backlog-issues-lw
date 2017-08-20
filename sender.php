<?php
require __DIR__ . '/vendor/autoload.php'; // path to vendor/

$sendgrid = new SendGrid(getenv('SENDGRID_USERNAME'), getenv('SENDGRID_PASSWORD'));
$email = new SendGrid\Email();
$email->addTo(getenv("MAIL_TO"))->
    setFrom(getenv("MAIL_FROM"))->
    setSubject('件名')->
    setText('こんにちは！');

$sendgrid->send($email);