<?php if (!defined('DXMA_VERSION')) die(); ?>
<h2>Your favorites (<?= $total ?>)</h2>
<?= fragment("list", $missions) ?>
<br />
<?= fragment("pages", [ "num" => $pageNum, "count" => $pageCount ]) ?>
