<?php if (!defined('DXMA_VERSION')) die(); ?>
<tr>
    <td nowrap class="column-username"><?= fragment("user", [$m["id"], $m["username"]]) ?></td>
    <td nowrap class="column-realname"><?= htmlspecialchars($m['realname']) ?></td>
    <td nowrap class="column-joined"><?= htmlspecialchars(formatDateTime($m['joined'])) ?></td>
</tr>
