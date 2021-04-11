<?php if (!defined('DXMA_VERSION')) die(); ?>
<tr>
    <td nowrap class="column-mode"><?= fragment("mode", ["i" => $m["mode"], "full" => 0]) ?></td>
    <td nowrap class="column-game"><?= fragment("game", ["i" => $m["game"], "full" => 0]) ?></td>
    <td nowrap class="column-title"><a href="<?= route("mission", [ "m" => $m['id'] ]) ?>"><?= htmlspecialchars($m['title']) ?></a></td>
    <td nowrap class="column-date"><?= htmlspecialchars(formatDateTime($m['updated'])) ?></td>
    <td nowrap class="column-user"><?= fragment("user", [$m["user"], $m["username"]]) ?></td>
</tr>
