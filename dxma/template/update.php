<?php if (!defined('DXMA_VERSION')) {
    die();
}
if (!$logged_in) {
    redirect(route());
} ?>
<h1>Update mission: <?= htmlspecialchars($mission['title']) ?></h1>
<?php if (isset($error)) : ?>
<p><?= $error ?></p>
<?php endif; ?>
<form action="<?= route('mission') ?>">
    <input type="hidden" name="m" value="<?= $mission['id'] ?>" />
    <input type="submit" value="Cancel" />
</form>
<h3>Mission</h2>
<form enctype="multipart/form-data" action="./?m=<?= $mission['id'] ?>&upload=1" method="post" id="uploadform">
    <input type="hidden" name="csrf" value="<?= $_SESSION["token"] ?>">
    <input type="hidden" name="updatefile" value="1">
    <label for="version">Version:</label>
    <input type="text" id="version" name="version" value="<?= htmlspecialchars($mission['version']) ?>" required><br />
    <label for="file">Mission file:</label>
    <input type="hidden" name="MAX_FILE_SIZE" value="<?= MAXFILESIZE ?>" />
    <input name="file" type="file" /><br />
    Allowed file types: <?= htmlspecialchars(implode(", ", ALLOWED_MISSION_EXTS)) ?><br />
    Maximum file size: <?= formatFileSize(MAXFILESIZE) ?><br />
    <input type="submit" value="Upload">
</form> 
<h3>Screenshot</h2>
<form enctype="multipart/form-data" action="./?m=<?= $mission['id'] ?>&upload=1" method="post" id="uploadform2">
    <input type="hidden" name="csrf" value="<?= $_SESSION["token"] ?>">
    <input type="hidden" name="updatescreenshot" value="1">
    <label for="screenshot">Screenshot:</label>
    <input type="hidden" name="MAX_FILE_SIZE" value="<?= MAXIMGSIZE ?>" />
    <input name="screenshot" type="file" /><br />
    Allowed screenshot file types: <?= htmlspecialchars(implode(", ", ALLOWED_SCREENSHOT_EXTS)) ?><br />
    Maximum file size: <?= formatFileSize(MAXIMGSIZE) ?><br />
    <input type="submit" value="Upload">
</form> 
