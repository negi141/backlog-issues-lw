<?php
class BacklogCall
{
    public static function getIssues($apiKey, $span)
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
}