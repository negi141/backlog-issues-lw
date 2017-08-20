<?php
require_once "lib/init.php";
require_once "lib/util.php";
require_once "lib/html.php";
require_once "lib/backlog.php";

require __DIR__ . '/vendor/autoload.php'; // path to vendor/

main();

function main(){
    $span = (object)["start" => strtotime("-10 day"), "end" => strtotime("+20 day")];
    $issues = getIssues(getenv("BACKLOG_API_KEY"), $span);
    $content = formatIssues($issues);
    sendMail($content);
}

function getIssues($apiKey, $span)
{
    $backlogApi = new BacklogApi($apiKey, 'esk-sys');
    $query = [
        'dueDateSince' => date("Y-m-d", $span->start),
        'dueDateUntil' => date("Y-m-d", $span->end),
        'count' => 100,
        'statusId' => [1, 2, 3],
        'categoryId' => [213143, 197771],
        'versionId' => [121542, 121543, 121544],
        'sort' => 'dueDate',
        'order' => 'asc',
    ];
    $issues = $backlogApi->send("issues", $query);
    $lwvers = ['LW1', 'LW2', 'LW3'];
    $formattedIssues = [];
    foreach ($issues as $issue) {
        foreach ($lwvers as $lwver) {

            if (!isset($formattedIssues[$lwver])) {
                $formattedIssues[$lwver] = [];
            }

            if (preg_match("/" . $lwver . "/", $issue['summary'])) {
                $dueDate = $issue['dueDate'];
                if (isset($dueDate)) {
                    $formattedIssues[$lwver][$dueDate][] = $issue;
                }
            }
        }
    }

    return $formattedIssues;
}

function formatIssues($issues) {
        
    $html = "";
    // Html生成
    foreach ($issues as $lwverKey => $lwver) {
        $html .= Html::tag('h2', $lwverKey);
        foreach ($lwver as $daysKey => $days) {
            $date = new DateTime($daysKey);
            $date->add(new DateInterval('P1D'));
            $now = new DateTime();
            $interval = $date->diff($now);
            if ($now < $date) {
                $dateMessage = $interval->d . "日後";
            } else {
                $dateMessage = $interval->d . "日前";
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
$header = <<<HTML
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
このメールは<a href="https://backlog-issues-lw.herokuapp.com/" target="_blank">backlog Checker for LW</a>から
自動で送られています。(一日一回)
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
    return $header . $html . $footer;
}

function sendMail($mailBody) {
    $from = new SendGrid\Email(null, getenv("MAIL_FROM"));
    $subject = "backlog issues for lw";
    $to = new SendGrid\Email(null, getenv("MAIL_TO"));
    $content = new SendGrid\Content("text/html", $mailBody);
    $mail = new SendGrid\Mail($from, $subject, $to, $content);

    $apiKey = getenv('SENDGRID_API_KEY');
    $sg = new \SendGrid($apiKey);

    $response = $sg->client->mail()->send()->post($mail);

    echo $response->statusCode();
    echo $response->body();
}