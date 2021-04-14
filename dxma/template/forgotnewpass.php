<?php if (!defined('DXMA_VERSION')) {
    die();
}
if ($logged_in) {
    redirect(route());
} ?>
<h1>Forgot password</h1>
<?php if (isset($error)) : ?>
<p><?= $error ?></p>
<?php endif; ?>
<p>Enter a new password.</p>
<form action=".?<?= $_SERVER['QUERY_STRING'] ?? "" ?>" method="post">
    <input type="hidden" name="csrf" value="<?= $_SESSION["token"] ?>">
    <input type="hidden" name="uid" value="<?= htmlspecialchars($uid) ?>">
    <input type="hidden" name="ticket" value="<?= htmlspecialchars($ticket) ?>">
    <table class="form">
        <tr>
            <th><label for="upass">Password:</label></th>
            <td><input type="password" id="upass" name="upass" maxlength="240" required></td>
        </tr>
        <tr>
            <th><label for="upassc">Confirm:</label></th>
            <td><input type="password" id="upassc" name="upassc" maxlength="240" required></td>
        </tr>
        <tr>
            <td colspan="2"><input type="submit" value="Submit"></td>
        </tr>
    </table>
</form> 
