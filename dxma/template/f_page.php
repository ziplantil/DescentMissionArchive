<?php if (!defined('DXMA_VERSION')) {
    die();
} ?>
<?php if ($m["m"] == $m["n"]) : ?>
<b><?= $m["t"] ?></b>
<?php else : ?>
<a href="<?= routePage($m["m"] + 1) ?>"><?= $m["t"] ?></a>
<?php endif; ?>
