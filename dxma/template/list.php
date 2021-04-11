<?php if (!defined('DXMA_VERSION')) die(); ?>
<h2>Search</h2>
<form action="." method="get" id="searchform">
    <table>
        <tr>
            <th>By name</th>
            <td><input name="q" id="search-q" type="text" value="<?= htmlspecialchars($_GET["q"] ?? "") ?>" maxlength="256" /></td>
        </tr>
        <tr>
            <th>By mode</th>
            <td>
                <?php foreach(MODE_ENUM as $i => &$mode): ?>
                <input type="checkbox" id="mode<?= $i ?>" name="mode[]" value="<?= $i ?>" <?= in_array($i, $_GET["mode"] ?? []) ? "checked" : "" ?>>
                <label for="mode<?= $i ?>"><?= $mode[1] ?></label>
                <?php endforeach; ?>
            </td>
        </tr>
        <tr>
            <th>By game</th>
            <td>
                <?php foreach(GAME_ENUM as $i => &$game): ?>
                <input type="checkbox" id="game<?= $i ?>" name="game[]" value="<?= $i ?>" <?= in_array($i, $_GET["game"] ?? []) ? "checked" : "" ?>>
                <label for="game<?= $i ?>"><?= $game[1] ?></label>
                <?php endforeach; ?>
            </td>
        </tr>
        <tr>
            <th>By player count</th>
            <td><input name="players" class="numinput" id="search-players" type="number" min="1" max="99" value="<?= htmlspecialchars($_GET["players"] ?? "") ?>" /></td>
        </tr>
        <tr>
            <th>Order</th>
            <td>
                <select name="order" id="order" form="searchform" required value="<?= htmlspecialchars($_GET['order'] ?? '') ?>">
                    <option value="name_a" <?= "name_a" === ($_GET['order'] ?? '') || empty($_GET['order']) ? "selected" : "" ?>>Name (ascending)</option>
                    <option value="name_d" <?= "name_d" === ($_GET['order'] ?? '') ? "selected" : "" ?>>Name (descending)</option>
                    <option value="rdate_d" <?= "rdate_d" === ($_GET['order'] ?? '') ? "selected" : "" ?>>Release date (newest first)</option>
                    <option value="rdate_a" <?= "rdate_a" === ($_GET['order'] ?? '') ? "selected" : "" ?>>Release date (oldest first)</option>
                    <option value="udate_d" <?= "udate_d" === ($_GET['order'] ?? '') ? "selected" : "" ?>>Update date (newest first)</option>
                    <option value="udate_a" <?= "udate_a" === ($_GET['order'] ?? '') ? "selected" : "" ?>>Update date (oldest first)</option>
                    <option value="rating_d" <?= "rating_d" === ($_GET['order'] ?? '') ? "selected" : "" ?>>Rating (highest first)</option>
                    <option value="rating_a" <?= "rating_a" === ($_GET['order'] ?? '') ? "selected" : "" ?>>Rating (lowest first)</option>
                </select>
            </td>
        </tr>
    </table>
    <input type="submit" value="Search" />
</form>
<form action="." method="get" id="searchformreset">
    <input type="submit" value="Reset" />
</form>
<h2>Mission List</h2>
<?= fragment("list", $missions) ?>
<br />
<?= fragment("pages", [ "num" => $pageNum, "count" => $pageCount ]) ?>
