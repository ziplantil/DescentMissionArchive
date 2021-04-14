<?php if (!defined('DXMA_VERSION')) {
    die();
}
$pair = isset(GAME_ENUM[$m["i"]]) ? GAME_ENUM[$m["i"]] : ["??", "??"];
?>
<?php if ($m["full"]) : ?>
<span class="game-<?= $pair[0] ?>"><?= htmlspecialchars($pair[1]) ?></span>
<?php else : ?>
<abbr title="<?= $pair[1] ?>" class="game-<?= $pair[0] ?>"><?= htmlspecialchars($pair[0]) ?></span>
<?php endif; ?>