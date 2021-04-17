<?php if (!defined('DXMA_VERSION')) {
    die();
} ?>
<table class="authorlist resulttable">
    <tr>
        <th>Author</th>
        <th>Member?</th>
    </tr>
    <?php if (empty($m)) : ?>
    <tr>
        <td colspan="5"><em>No authors found</em><td>
    </tr>
    <?php else : ?>
    <?php foreach ($m as $i => &$author): ?>
        <tr>
            <td nowrap class="column-authorname">
                <?php fragment("author", $author) ?>
            </td>
            <td nowrap class="column-authorismember">
                <?= !is_null($author["userid"]) ? "Yes" : "No" ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php endif; ?>
</table>