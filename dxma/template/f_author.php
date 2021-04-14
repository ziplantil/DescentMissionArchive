<?php if (!defined('DXMA_VERSION')) {
    die();
} ?>
<?php if (!is_null($m["userid"])) : ?>
<?= fragment("user", [ $m["userid"], $m["name"] ]) ?>
<?php else : ?>
<a href="<?= route("", [ "author" => $m["name"] ]) ?>" class="authoralias"><?= htmlspecialchars($m["name"]) ?></a>
<?php endif; ?>