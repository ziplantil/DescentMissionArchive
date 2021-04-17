<?php if (!defined('DXMA_VERSION')) {
    die();
}
if ($logged_in) {
    redirect(route());
} ?>
<?php
$isSecure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $isSecure = true;
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $isSecure = true;
}
?>
<h1>Log in</h1>
<?php if (isset($fail)) : ?>
<p>Wrong username/password</p>
<?php endif; ?>
<form action="." method="post">
    <input type="hidden" name="csrf" value="<?= $_SESSION["token"] ?>">
    <table class="form">
        <tr>
            <th><label for="uname">User name:</label></th>
            <td><input type="text" id="uname" name="uname" required></td>
        </tr>
        <tr>
            <th><label for="upass">Password:</label></th>
            <td><input type="password" id="upass" name="upass" required autocomplete="current-password"></td>
        </tr>
        <tr>
            <td colspan="2"><input type="submit" value="Log in"></td>
        </tr>
    </table>
    <br />
    <a href="<?= route('register') ?>">No account yet?</a><br />
    <a href="<?= route('forgot') ?>">Forgot password?</a><br />
    <?php if (!$isSecure) : ?>
    <h2>WARNING!</h2>
    <p>You are currently not on a secure connection (https). Logging in
    and accessing your user account without HTTPS is not recommended.</p>
    <a href="<?= "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">Enter secure mode</a>
     (may not work if the current instance does not support HTTPS)
    <?php endif; ?>
</form> 