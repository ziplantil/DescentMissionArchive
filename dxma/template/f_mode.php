<?php if (!defined('DXMA_VERSION')) {
    die();
}
$pair = isset(MODE_ENUM[$m["i"]]) ? MODE_ENUM[$m["i"]] : ["??", "??"];
?>
<?php if ($m["full"]) : ?>
<span class="mode-<?= $pair[0] ?>"><?= htmlspecialchars($pair[1]) ?></span>
<?php else : ?>
<abbr title="<?= $pair[1] ?>" class="mode-<?= $pair[0] ?>"><?= htmlspecialchars($pair[0]) ?></span>
<?php endif; ?>
