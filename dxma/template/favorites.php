<?php if (!defined('DXMA_VERSION')) {
    die();
}
if (!$logged_in) {
    redirect(route());
} ?>
<h2>Your favorites (<?= $total ?>)</h2>
<?= fragment("list", $missions) ?>
<br />
<?= fragment("pages", [ "num" => $pageNum, "count" => $pageCount ]) ?>
