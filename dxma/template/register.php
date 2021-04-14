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
<h1>Create account</h1>
<?php if (!ALLOW_REGISTRATIONS) : ?>
<p>New user account registration has been disabled by the administrator.</p>
<?php else : ?>
<?php if (isset($error)) : ?>
<p><?= $error ?></p>
<?php endif; ?>
<form action="." method="post">
    <input type="hidden" name="csrf" value="<?= $_SESSION["token"] ?>">
    <table class="form">
        <tr>
            <th><label for="uname">User name:</label></th>
            <td><input type="text" id="uname" name="uname" maxlength="32" required></td>
        </tr>
        <tr>
            <th><label for="upass">Password:</label></th>
            <td><input type="password" id="upass" name="upass" maxlength="240" required></td>
        </tr>
        <tr>
            <th><label for="upassc">Confirm:</label></th>
            <td><input type="password" id="upassc" name="upassc" maxlength="240" required></td>
        </tr>
        <tr>
            <th><label for="email">E-mail:</label></th>
            <td><input type="text" id="email" name="email" maxlength="240">
            (optional, for password recovery)</td>
        </tr>
        <tr>
            <th><label for="check">Trivia:</label></th>
            <td><input type="hidden" id="checkkey" name="checkkey" value="<?= $checkkey ?>">
            <input type="text" id="check" name="check" maxlength="240">
            <?= $checkquestion ?> </td>
        </tr>
        <tr>
            <td colspan="2"><input type="submit" value="Submit"></td>
        </tr>
    </table>
    <?php if (!$isSecure) : ?>
    <h2>WARNING!</h2>
    <p>You are currently not on a secure connection (https). Logging in
    and accessing your user account without HTTPS is not recommended.</p>
    <a href="<?= "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">Enter secure mode</a>
     (may not work if the current instance does not support HTTPS)
    <?php endif; ?>
</form> 
<?php endif; ?>
