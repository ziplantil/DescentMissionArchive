<?php if (!defined('DXMA_VERSION')) {
    die();
} ?>
<table class="missionlist resulttable">
    <tr>
        <th>User</th>
        <th>Name</th>
        <th>Joined</th>
    </tr>
    <?php if (empty($m)) : ?>
    <tr>
        <td colspan="5"><em>No members found</em><td>
    </tr>
    <?php else : ?>
    <?php foreach ($m as $i => &$member): ?>
        <?php fragment("member", $member) ?>
    <?php endforeach; ?>
    <?php endif; ?>
</table>