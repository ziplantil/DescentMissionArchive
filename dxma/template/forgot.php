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
<?php if (!CAN_EMAIL) : ?>
<p>E-mail is not configured on this server. Please contact the server
administrator for assistance.</p>
<?php else : ?>
<p>Enter your user name. If an email address has been configured, an email
will be sent with further instructions.</p>
<form action="." method="post">
    <input type="hidden" name="csrf" value="<?= $_SESSION["token"] ?>">
    <table class="form">
        <tr>
            <th><label for="uname">User name:</label></th>
            <td><input type="text" id="uname" name="uname" required></td>
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
</form> 
<?php endif; ?>
