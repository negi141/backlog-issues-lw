<?php
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
echo $html;


$d1 = date("Y-m-d", $span->start);
$d2 = date("Y-m-d", $span->end);
$d1enc = urlencode(date("Y/m/d", $span->start));
$d2enc = urlencode(date("Y/m/d", $span->end));
$url = "https://esk-sys.backlog.jp/find/LW3_SHUKAN?condition.projectId=73975&condition.issueTypeId=&condition.componentId=213143&condition.componentId=197771&condition.versionId=121542&condition.versionId=121543&condition.versionId=121544&condition.fixedVersionId=&condition.statusId=1&condition.statusId=2&condition.statusId=3&condition.priority=&condition.assignerId=&condition.createdUserId=&condition.resolutionId=&condition.file=&condition.parentChildIssue=&condition.limit=20&condition.offset=0&condition.query=&condition.sort=UPDATED&condition.order=false&condition.simpleSearch=false&condition.allOver=false&condition.createdRange.begin=&condition.createdRange.end=&condition.updatedRange.begin=&condition.updatedRange.end=&condition.startDateRange.begin=&condition.startDateRange.end=&condition.limitDateRange.begin={$d1enc}&condition.limitDateRange.end={$d2enc}";
?>
<br>
<div class="note">
    検索条件 (<a href="<?= $url ?>" target="_blank">backlogで検索</a>)<br/>
    ・[状態] 未対応 / 処理中 / 処理済み<br/>
    ・[カテゴリー] 開発_2017/06～11_機能追加 / 開発_2017/06～11_保守/バージョンアップ<br/>
    ・[期限日] <?= "$d1 ～ $d2" ?><br/>
    ・[発生バージョン] LW3ライト / LW2ライト / LW1ライト<br/>
    分類<br/>
    ・タイトルに入っている"LW1"とかで分けて、次に期限日で分けています<br/>
</div>