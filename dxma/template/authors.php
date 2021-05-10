<?php if (!defined('DXMA_VERSION')) {
    die();
} ?>
<h2>Search</h2>
<form action="." method="get" id="searchform">
    <table>
        <tr>
            <th>By name</th>
            <td><input name="q" id="search-q" type="text" value="<?= htmlspecialchars($_GET["q"] ?? "") ?>" maxlength="256" /></td>
        </tr>
        <tr>
            <th>Order</th>
            <td>
                <select name="order" id="order" form="searchform" value="<?= htmlspecialchars($_GET['order'] ?? '') ?>">
                    <option value="" <?= empty($_GET['order']) ? "selected" : "" ?>>(default)</option>
                    <option value="name_a" <?= "name_a" === ($_GET['order'] ?? '') ? "selected" : "" ?>>Name (ascending)</option>
                    <option value="name_d" <?= "name_d" === ($_GET['order'] ?? '') ? "selected" : "" ?>>Name (descending)</option>
                </select>
            </td>
        </tr>
    </table>
    <input type="submit" value="Search" />
</form>
<form action="." method="get" id="searchformreset">
    <input type="submit" value="Reset" />
</form>
<p>
<h2 class="resultcountontheright">Author List</h2>
<i class="resultcount"><?= $total ?> result(s)</i>
</p>
<?= fragment("authorlist", $authors) ?>
<br />
<?= fragment("pages", [ "num" => $pageNum, "count" => $pageCount ]) ?>
