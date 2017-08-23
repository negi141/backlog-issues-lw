<?php
require __DIR__ . '/vendor/autoload.php';

require_once "lib/init.php";
require_once "lib/util.php";
require_once "lib/html.php";
require_once "lib/backlog.php";
require_once "lib/backlogCall.php";

main();

function main() {
    $span = (object)["start" => strtotime("-1 day"), "end" => strtotime("+20 day")];
    $issues = backlogCall::getIssues(getenv("BACKLOG_API_KEY"), $span);
    $content = formatIssues($issues);
    $content = createBody($content, $span);
    sendMail($content);
}

function formatIssues($issues) {
        
    $html = "";
    // Html生成
    foreach ($issues as $lwverKey => $lwver) {
        $html .= Html::tag('h2', $lwverKey, "border-bottom: 1px solid #cdf;");
        foreach ($lwver as $daysKey => $days) {
            $date = new DateTime($daysKey);
            $date->add(new DateInterval('P1D'));
            $now = new DateTime();
            $now->setTimezone(new DateTimeZone('Asia/Tokyo'));
            $interval = $date->diff($now);
            if ($now < $date) {
                $dateMessage = $interval->d . "日後";
            } else {
                $dateMessage = $interval->d . "日前";
            }
            if ($now < $date) {
                if ($lwver == "LW3") {
                    if ($interval->d == 15) $dateMessage .= "<br>★今日中にリリースノートを課題内に書いて下さい";
                    if ($interval->d == 14) $dateMessage .= "<br>★今日はリリース告知です";
                    if ($interval->d == 0) $dateMessage .= "<br>★今日はリリース日です";
                } else {
                    if ($interval->d == 8) $dateMessage .= "<br>★今日中にリリースノートを課題内に書いて下さい";
                    if ($interval->d == 7) $dateMessage .= "<br>★今日はリリース告知です";
                    if ($interval->d == 0) $dateMessage .= "<br>★今日はリリース日です";
                }
            }
            $html .= Html::tag('h3', substr($daysKey, 0, 10) . "　" . $dateMessage);
            foreach ($days as $issue) {
                $url = sprintf(BacklogApi::WEB_URL, 'esk-sys', $issue['issueKey']);
                $summary = preg_replace("/\[.+\]/", "", $issue['summary']);
                $summary = preg_replace("/【.+】/", "", $summary);
                $summary = Util::leftString($summary, 30);
                $statusColor = ["未対応" => "#ED8077", "処理中" => "#4488C5", "処理済み" => "#5EB5A6"];
                $stagusName = Html::tag("span", $issue['status']['name'], "style='color:" . $statusColor[$issue['status']['name']] . ";'");
                $summary = Html::tag('a', $summary, "href='$url' target='_blank'");
                $html .= Html::tag('li',
                        "{$summary} 　{$issue['assignee']['name']} 　{$stagusName}") . PHP_EOL;
            }
        }
    }
    return $html;
}

function createBody($content, $span) {
    $d1 = date("Y-m-d", $span->start);
    $d2 = date("Y-m-d", $span->end);

    $header = <<<HTML
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: helvetica, arial, 'lucida grande', 'hiragino kaku gothic pro', meiryo, 'ms pgothic', sans-serif;">
このメールは<a href="https://backlog-issues-lw.herokuapp.com/" target="_blank">backlog Checker for LW</a>から
自動で送られています。(一日一回)<br/>
抽出期間：$d1 ～ $d2
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