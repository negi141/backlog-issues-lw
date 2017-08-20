<?php
require_once "lib/init.php";
require_once "lib/util.php";
require_once "lib/backlog.php";

require __DIR__ . '/vendor/autoload.php'; // path to vendor/

main();
function main(){
    $span = (object)["start" => strtotime("-10 day"), "end" => strtotime("+20 day")];
    $issues = getIssues(getenv("BACKLOG_API_KEY"), $span);
    sendMail($issues);
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

function sendMail($issues) {
    $request_body = json_decode('{
    "personalizations": [
        {
        "to": [
            {
            "email": "'.getenv("MAIL_TO").'"
            }
        ],
        "subject": "backlog issues for lw"
        }
    ],
    "from": {
        "email": "'.getenv("MAIL_FROM").'"
    },
    "content": [
        {
        "type": "text/plain",
        "value": "'.$issues.'"
        }
    ]
    }');

    $apiKey = getenv('SENDGRID_API_KEY');
    $sg = new \SendGrid($apiKey);

    $response = $sg->client->mail()->send()->post($request_body);
    echo $response->statusCode();
    echo $response->body();
    echo $response->headers();
}