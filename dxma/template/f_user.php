<?php if (!defined('DXMA_VERSION')) {
    die();
} ?>
<?php if (!is_null($m["0"])) : ?>
<a class="userlink" href="<?= route("user", [ "u" => $m[0] ]) ?>"><?= htmlspecialchars($m[1]) ?></a>
<?php else : ?>
<span class="userlink"><?= htmlspecialchars($m[1]) ?></span>
<?php endif; ?>