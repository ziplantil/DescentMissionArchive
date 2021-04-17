<?php if (!defined('DXMA_VERSION')) {
    die();
}
if (!$logged_in) {
    redirect(route());
} ?>
<h1>Edit mission: <?= htmlspecialchars($mission['title']) ?></h1>
<?php if (isset($error)) : ?>
<p><?= $error ?></p>
<?php endif; ?>
<form action="<?= route('mission') ?>">
    <input type="hidden" name="m" value="<?= $mission['id'] ?>" />
    <input type="submit" value="Cancel" />
</form>
<br />
<form action="./?m=<?= $mission['id'] ?>" method="post" id="editform">
    <input type="hidden" name="csrf" value="<?= $_SESSION["token"] ?>">
    <table class="missionform">
        <tr>
            <th><label for="title">Mission name:</label></th>
            <td><input type="text" class="textinput" id="title" name="title" required value="<?= $mission['title'] ?>"></td>
        </tr>
        <tr>
            <th><label for="version">Version:</label></th>
            <td><input type="text" id="version" name="version" value="<?= $mission['version'] ?>" required></td>
        </tr>
        <tr>
            <th><label for="author">Authors:</label></th>
            <td><textarea name="authors" id="authors" form="editform" maxlength="1024"><?php
                $lines = array();
                foreach ($mission["authors"] as &$author) {
                    if (!is_null($author["userid"])) {
                        $lines[] = $author["name"];
                    } else {
                        $lines[] = "#" . $author["name"];
                    }
                }
                echo(implode("\n", $lines));
            ?></textarea><br />
            One author per line. Prefix line by # if <i>not</i> a DXMA username</td>
        </tr>
        <tr>
            <th><label for="mode">Mode:</label></th>
            <td>
                <select name="mode" id="mode" form="editform" required value="<?= $mission['mode'] ?>">
                    <?php foreach (MODE_ENUM as $i => &$mode): ?>
                    <option value="<?= $i ?>" <?= $i === $mission['mode'] ? "selected" : "" ?>><?= $mode[1] ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="game">Game:</label></th>
            <td>
                <select name="game" id="game" form="editform" required value="<?= $mission['game'] ?>">
                    <?php foreach (GAME_ENUM as $i => &$game): ?>
                    <option value="<?= $i ?>" <?= $i === $mission['game'] ? "selected" : "" ?>><?= $game[1] ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="levels">Level count:</label></th>
            <td><input type="number" class="numinput" id="levels" name="levels" min="1" max="99" value="<?= $mission['levels'] ?>" required></td>
        </tr>
        <tr>
            <th><label for="playersMin">For how many players?</label></th>
            <td><input type="number" class="numinput" id="playersMin" name="playersMin" min="1" max="99" value="<?= $mission['playersMin'] ?>" required> &ndash; <input type="number" class="numinput" id="playersMax" name="playersMax" min="1" max="99" value="<?= $mission['playersMax'] ?>" required></td>
        </tr>
        <tr>
            <th><label for="released">Release date:</label></th>
            <td><input type="text" id="released" name="released" value="<?= $mission['released'] ?>"> YYYY-MM-DD</td>
        </tr>
        <tr>
            <th><label for="description">Description:</label></th>
            <td><textarea name="description" id="description" form="editform" maxlength="<?= DESC_MAXLENGTH ?>"><?= $mission['description'] ?></textarea></td>
        </tr>
        <tr>
            <td colspan="2"><input type="submit" value="Edit"></td>
        </tr>
    </table>
</form>