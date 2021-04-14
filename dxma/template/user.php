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
<hr />
<h2>Uploaded missions</h2>
<?= fragment("list", $missions) ?>