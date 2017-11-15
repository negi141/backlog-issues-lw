<?php
require __DIR__ . '/vendor/autoload.php';

require_once "config/init.php";
require_once "lib/util.php";
require_once "lib/html.php";
require_once "lib/backlogApi.php";
require_once "lib/backlogControl.php";

main();

function main() {
    $span = (object)["start" => strtotime("-1 day"), "end" => strtotime("+20 day")];
    $issues = BacklogControl::getIssues(getenv("BACKLOG_API_KEY"), $span);
    $content = BacklogControl::formatIssues($issues);
    $content = createBody($content, $span);
    sendMail($content);
}

function createBody($content, $span) {
    $d1 = date("Y-m-d", $span->start);
    $d2 = date("Y-m-d", $span->end);
    $url = BacklogControl::getWebUrl($span);

    $header = <<<HTML
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: helvetica, arial, 'lucida grande', 'hiragino kaku gothic pro', meiryo, 'ms pgothic', sans-serif;">
このメールは<a href="https://backlog-issues-lw.herokuapp.com/" target="_blank">backlog Checker for LW</a>から
自動で送られています。(一日一回)<br>
抽出期間：$d1 ～ $d2<br>
<a href="$url" target="_blank">backlogで検索</a>
HTML;

    $footer = <<<HTML
    <hr>
    <footer>
        Created by Negishi. 
        <a href="https://github.com/negi141/backlog-issues-lw" target="_blank">Repository</a>
    </footer>
    </body>
</html>
HTML;
    return $header . $content . $footer;
}

function sendMail($mailBody) {
    $from = new SendGrid\Email(null, getenv("MAIL_FROM"));
    $subject = "LWのリリース予定状況の確認";
    $content = new SendGrid\Content("text/html", $mailBody);

    $apiKey = getenv('SENDGRID_API_KEY');
    $sg = new \SendGrid($apiKey);

    $tos = explode(",", getenv("MAIL_TO"));
    foreach ($tos as $to) {
        $mailTo = new SendGrid\Email(null, $to);
        $mail = new SendGrid\Mail($from, $subject, $mailTo, $content);
        $response = $sg->client->mail()->send()->post($mail);
        echo $response->statusCode();
    }
}