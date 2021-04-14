<?php if (!defined('DXMA_VERSION')) {
    die();
}
if (!$logged_in) {
    redirect(route());
} ?>
<h1>Add mission</h1>
<?php if (isset($error)) : ?>
<p><?= $error ?></p>
<?php endif; ?>
<form enctype="multipart/form-data" action=".?upload=1" method="post" id="uploadform">
    <input type="hidden" name="csrf" value="<?= $_SESSION["token"] ?>">
    <table class="missionform">
        <tr>
            <th><label for="title">Mission name:</label></th>
            <td><input type="text" class="textinput" id="title" name="title" required></td>
        </tr>
        <tr>
            <th><label for="version">Version:</label></th>
            <td><input type="text" id="version" name="version" value="1.0" required></td>
        </tr>
        <tr>
            <th><label for="author">Authors:</label></th>
            <td><textarea name="authors" id="authors" form="uploadform" maxlength="1024"></textarea><br />
            One author per line. Prefix line by @ if a DXMA username. Leave empty if only your work</td>
        </tr>
        <tr>
            <th><label for="mode">Mode:</label></th>
            <td>
                <select name="mode" id="mode" form="uploadform" required>
                    <?php foreach (MODE_ENUM as $i => &$mode): ?>
                    <option value="<?= $i ?>"><?= $mode[1] ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="game">Game:</label></th>
            <td>
                <select name="game" id="game" form="uploadform" required>
                    <?php foreach (GAME_ENUM as $i => &$game): ?>
                    <option value="<?= $i ?>"><?= $game[1] ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="levels">Level count:</label></th>
            <td><input type="number" class="numinput" id="levels" name="levels" min="1" max="99" value="1" required></td>
        </tr>
        <tr>
            <th><label for="playersMin">For how many players?</label></th>
            <td><input type="number" class="numinput" id="playersMin" name="playersMin" min="1" max="99" value="1" required> &ndash; <input type="number" class="numinput" id="playersMax" name="playersMax" min="1" max="99" value="1" required></td>
        </tr>
        <tr>
            <th><label for="released">Release date:</label></th>
            <td><input type="text" id="released" name="released" value=""> (YYYY-MM-DD. empty = today)</td>
        </tr>
        <tr>
            <th><label for="file">Mission file:</label></th>
            <td><input type="hidden" name="MAX_FILE_SIZE" value="<?= MAXFILESIZE ?>" />
                <input name="file" type="file" /><br />
                Allowed file types: <?= implode(", ", ALLOWED_MISSION_EXTS) ?><br />
                Maximum file size: <?= formatFileSize(MAXFILESIZE) ?>
            </td>
        </tr>
        <tr>
            <th><label for="screenshot">Screenshot:</label><br />(optional)</th>
            <td><input type="hidden" name="MAX_FILE_SIZE" value="<?= MAXIMGSIZE ?>" />
                <input name="screenshot" type="file" /><br />
                Allowed screenshot file types: <?= implode(", ", ALLOWED_SCREENSHOT_EXTS) ?><br />
                Maximum file size: <?= formatFileSize(MAXIMGSIZE) ?>
            </td>
        </tr>
        <tr>
            <th><label for="description">Description:</label></th>
            <td><textarea name="description" id="description" form="uploadform" maxlength="<?= DESC_MAXLENGTH ?>"></textarea></td>
        </tr>
        <tr>
            <td colspan="2"><input type="submit" value="Upload"></td>
        </tr>
    </table>
</form>