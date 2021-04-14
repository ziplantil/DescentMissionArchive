<?php if (!defined('DXMA_VERSION')) {
    die();
}
if (!$logged_in) {
    redirect(route());
} ?>
<h1>Delete mission: <?= htmlspecialchars($mission['title']) ?></h1>
<p>Are you sure you want to delete this mission?</p>
<form action="./?m=<?= $mission['id'] ?>" method="post">
    <input type="hidden" name="csrf" value="<?= $_SESSION["token"] ?>">
    <input type="hidden" name="confirm" value="1">
    <input type="submit" value="Yes">
</form>
<br />
<form action="<?= route("mission") ?>">
    <input type="hidden" name="m" value="<?= $mission['id'] ?>">
    <input type="submit" value="No" />
</form>