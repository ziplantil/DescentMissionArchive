<?php if (!defined('DXMA_VERSION')) {
    die();
} ?>
<h1>User: <?= fragment("user", [null, $u["username"]]) ?></h1>
<table>
    <?php if (!empty($u["realname"])) : ?>
    <tr>
        <th>Real Name</th>
        <td><?= htmlspecialchars($u["realname"]) ?></td>
    </tr>
    <?php endif; ?>
    <?php if (!empty($u["website"])) : ?>
    <tr>
        <th>Website</th>
        <td><a href="<?= htmlspecialchars($u["website"]) ?>"><?= htmlspecialchars($u["website"]) ?></a></td>
    </tr>
    <?php endif; ?>
    <?php if (!empty($u["description"])) : ?>
    <tr>
        <th>Description</th>
        <td><pre><?= htmlspecialchars($u["description"]) ?></pre></td>
    </tr>
    <?php endif; ?>
</table>
<?php if (!empty($authoredMissions)) : ?>
<hr />
<p>
<h2 class="resultcountontheright">Authored missions</h2>
<i class="resultcount"><?= $authoredTotal ?> mission(s)</i>
</p>
<?= fragment("list", $authoredMissions) ?>
<br />
<?= fragment("pages", [ "num" => $authoredPageNum, "count" => $authoredPageCount ]) ?>
<br />
<?php endif; ?>
<?php if (!empty($missions)) : ?>
<hr />
<p>
<h2 class="resultcountontheright">Uploaded missions</h2>
<i class="resultcount"><?= $total ?> mission(s)</i>
</p>
<?= fragment("list", $missions) ?>
<br />
<?= fragment("pages", [ "num" => $pageNum, "count" => $pageCount, "param" => "upage" ]) ?>
<br />
<?php endif; ?>