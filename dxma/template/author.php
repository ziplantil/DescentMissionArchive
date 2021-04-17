<?php if (!defined('DXMA_VERSION')) {
    die();
} ?>
<h1>Author: <?= htmlspecialchars($author["name"]) ?></h1>
<?php if (!empty($missions)) : ?>
<hr />
<p>
<h2 class="resultcountontheright">Authored missions</h2>
<i class="resultcount"><?= $total ?> mission(s)</i>
</p>
<?= fragment("list", $missions) ?>
<br />
<?= fragment("pages", [ "num" => $pageNum, "count" => $pageCount, "param" => "upage" ]) ?>
<br />
<?php endif; ?>