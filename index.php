<?php

require_once "config/init.php";
require_once "lib/util.php";
require_once "lib/html.php";
require_once "lib/backlogApi.php";
require_once "lib/backlogControl.php";
require_once "lib/render.php";

// Main Logic
if (!isset($_POST['apiKey'])) {
    render("form");
} else {
    $span = (object)["start" => strtotime("-10 day"), "end" => strtotime("+40 day")];
    $issues = BacklogControl::getIssues($_POST['apiKey'], $span);
    render("issues", ["issues" => $issues, "span" => $span]);
}

function render($pageName, $params = [])
{
    $html = Render::layout("header", ["title" => "backlog Checker for LearningWare"]);
    try {
        $html .= Render::view($pageName, $params);
    } catch (\Exception $e) {
        $html .= Html::tag("p", $e->getmessage(), "style='color:red'");
    }
    $html .= Render::layout("footer");
    echo $html;
}