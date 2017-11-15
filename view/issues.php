<?php

echo BacklogControl::formatIssues($issues);

$d1 = date("Y-m-d", $span->start);
$d2 = date("Y-m-d", $span->end);
$url = BacklogControl::getWebUrl($span);
?>
<br>
<div class="note">
抽出期間 <?= "$d1 ～ $d2" ?><br/>
    <a href="<?= $url ?>" target="_blank">backlogで検索</a>
</div>