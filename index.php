<?php
require_once "lib/init.php";
require_once "lib/util.php";
require_once "lib/html.php";
require_once "lib/backlog.php";
require_once "lib/render.php";

// Main Logic
if (!isset($_POST['apiKey'])) {
    render("form");
} else {
    $span = (object)["start" => strtotime("-10 day"), "end" => strtotime("+20 day")];
    $issues = getIssues($_POST['apiKey'], $span);
    render("issues", ["issues" => $issues, "span" => $span]);
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

function render($pageName, $params = [])
{
    $html = Render::layout("header", ["title" => "LW Release - backlog API"]);
    try {
        $html .= Render::view($pageName, $params);
    } catch (\Exception $e) {
        $html .= Html::tag("p", $e->getmessage(), "style='color:red'");
    }
    $html .= Render::layout("footer");
    echo $html;
}