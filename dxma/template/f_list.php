<?php if (!defined('DXMA_VERSION')) die(); ?>
<table class="missionlist resulttable">
    <tr>
        <th>Mode</th>
        <th>For</th>
        <th>Name</th>
        <th>Updated</th>
        <th>Uploader</th>
    </tr>
    <?php if (empty($m)) : ?>
    <tr>
        <td colspan="5"><em>No missions found</em><td>
    </tr>
    <?php else : ?>
    <?php foreach($m as $i => &$mission): ?>
        <?php fragment("mission", $mission) ?>
    <?php endforeach; ?>
    <?php endif; ?>
</table>