<?php

class BacklogControl
{
    public static function getIssues($apiKey, $span)
    {
        $backlogApi = new BacklogApi($apiKey, 'esk-sys');
        $query = [
            'dueDateSince' => date("Y-m-d", $span->start),
            'dueDateUntil' => date("Y-m-d", $span->end),
            'count' => 100,
            'statusId' => [1, 2, 3],
            'categoryId' => [181620, 213143, 197771, 216136, 222754, 220959, 229849], // カテゴリー
            'versionId' => [121542, 121543, 121544, 135843], // 発生バージョン
            'sort' => 'dueDate',
            'order' => 'asc',
        ];
        $issues = $backlogApi->send("issues", $query);
        $versions = [
            'LW1' => 121544,
            'LW2' => 121543,
            'LW3' => 121542,
            'P-movie' => 135843,
        ];
        $formattedIssues = [];
        foreach ($issues as $issue) {
            foreach ($versions as $verName => $verId) {

                if (!isset($formattedIssues[$verName])) {
                    $formattedIssues[$verName] = [];
                }

                if ($issue['versions'][0] == $verId) {
                    $dueDate = $issue['dueDate'];
                }
                if (isset($dueDate)) {
                    $formattedIssues[$verName][$dueDate][] = $issue;
                }
            }
        }
        return $formattedIssues;
    }

    public static function formatIssues($issues)
    {
        $html = "";
        // Html生成
        foreach ($issues as $lwverKey => $lwver) {
            $html .= Html::tag('h2', $lwverKey, "border-bottom: 1px solid #cdf;");
            if (empty($lwver)) {
                $html .= Html::tag('div', 'ー');
            }
            foreach ($lwver as $daysKey => $days) {
                $date = new DateTime($daysKey);
                $date->add(new DateInterval('P1D'));
                $now = new DateTime();
                $now->setTimezone(new DateTimeZone('Asia/Tokyo'));
                $interval = $date->diff($now);
                if ($interval->days == 0) {
                    $dateMessage = "今日";
                } else {
                    if ($now < $date) {
                        $dateMessage = $interval->days . "日後";
                    } else {
                        $dateMessage = $interval->days . "日前";
                    }
                }
                if ($now < $date) {
                    if ($lwverKey == "LW3") {
                        if ($interval->days == 15) {
                            $dateMessage .= "<br>★今日中にリリースノートを課題内に書いて下さい";
                        }
                        if ($interval->days == 14) {
                            $dateMessage .= "<br>★今日はリリース告知です";
                        }
                        if ($interval->days == 0) {
                            $dateMessage .= "<br>★今日はリリース日です";
                        }
                    } else {
                        if ($interval->days == 8) {
                            $dateMessage .= "<br>★今日中にリリースノートを課題内に書いて下さい";
                        }
                        if ($interval->days == 7) {
                            $dateMessage .= "<br>★今日はリリース告知です";
                        }
                        if ($interval->days == 0) {
                            $dateMessage .= "<br>★今日はリリース日です";
                        }
                    }
                }
                $html .= Html::tag('h3', substr($daysKey, 0, 10) . "　" . $dateMessage);
                foreach ($days as $issue) {
                    $url = sprintf(BacklogApi::WEB_URL, 'esk-sys', $issue['issueKey']);
                    $summary = preg_replace("/\[.+\]/", "", $issue['summary']);
                    $summary = preg_replace("/【.+】/", "", $summary);
                    $summary = Util::leftString($summary, 30);
                    $statusColor = ["未対応" => "#ED8077", "処理中" => "#4488C5", "処理済み" => "#5EB5A6"];
                    $stagusName = Html::tag("span", $issue['status']['name'],
                        "style='color:" . $statusColor[$issue['status']['name']] . ";'");
                    $summary = Html::tag('a', $summary, "href='$url' target='_blank'");
                    $assigne = $issue['assignee']['name'];
                    $assigne = ($assigne == "実行くん") ? Html::tag("span", $assigne,
                        "style='color:red; text-decoration:underline'") : $assigne;
                    $html .= Html::tag('li',
                            "{$summary} 　{$assigne} 　{$stagusName}") . PHP_EOL;
                }
            }
        }
        return $html;
    }

    // 超暫定版
    public static function getWebUrl($span)
    {
        $d1enc = urlencode(date("Y/m/d", $span->start));
        $d2enc = urlencode(date("Y/m/d", $span->end));
        $cateIds = [181620, 213143, 197771, 216136, 222754, 220959, 229849];
        $cate = "";
        foreach ($cateIds as $id) {
            $cate .= "&condition.componentId=" . $id;
        }
        return "https://esk-sys.backlog.jp/find/LW3_SHUKAN?condition.projectId=73975&condition.issueTypeId={$cate}&condition.versionId=121542&condition.versionId=121543&condition.versionId=121544&condition.fixedVersionId=&condition.statusId=1&condition.statusId=2&condition.statusId=3&condition.priority=&condition.assignerId=&condition.createdUserId=&condition.resolutionId=&condition.file=&condition.parentChildIssue=&condition.limit=20&condition.offset=0&condition.query=&condition.sort=UPDATED&condition.order=false&condition.simpleSearch=false&condition.allOver=false&condition.createdRange.begin=&condition.createdRange.end=&condition.updatedRange.begin=&condition.updatedRange.end=&condition.startDateRange.begin=&condition.startDateRange.end=&condition.limitDateRange.begin={$d1enc}&condition.limitDateRange.end={$d2enc}";
    }
}