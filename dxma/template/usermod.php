<?php if (!defined('DXMA_VERSION')) {
    die();
}
if (!$logged_in) {
    redirect(route());
} ?>
<h1>Edit user: <?= htmlspecialchars($user['username']) ?></h1>
<?php if (isset($error)) : ?>
<p><?= $error ?></p>
<?php endif; ?>
<form enctype="multipart/form-data" action="." method="post" id="editform">
    <input type="hidden" name="csrf" value="<?= $_SESSION["token"] ?>">
    <table class="userform">
        <tr>
            <th><label for="realname">Real name:</label></th>
            <td><input type="text" id="realname" name="realname" value="<?= htmlspecialchars($user['realname']) ?>"></td>
        </tr>
        <tr>
            <th><label for="website">Home page URL:</label></th>
            <td><input type="text" id="website" name="website" value="<?= htmlspecialchars($user['website']) ?>"></td>
        </tr>
        <tr>
            <th><label for="email">Email:</label></th>
            <td><input type="text" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
            (private, used for password recovery)</td>
        </tr>
        <tr>
            <th><label for="description">Description:</label></th>
            <td><textarea name="description" id="description" form="editform" maxlength="256"><?= htmlspecialchars($user['description']) ?></textarea></td>
        </tr>
        <tr>
            <td colspan="2"><input type="submit" value="Edit"><br /><br /></td>
        </tr>
        <tr>
            <td colspan="2"><h3>Change password</h3></td>
        </tr>
        <tr>
            <td colspan="2">Use the form below to change the password, or leave it empty to keep as is. You must enter the current password in order to change to a new one.</td>
        </tr>
        <tr>
            <td colspan="2"><br></td>
        </tr>
        <tr>
            <th><label for="cpass">Current password:</label></th>
            <td><input type="password" id="cpass" name="cpass" autocomplete="current-password"><br /></td>
        </tr>
        <tr>
            <th><label for="upass">New password:</label></th>
            <td><input type="password" id="upass" name="upass" autocomplete="new-password"><br /></td>
        </tr>
        <tr>
            <th><label for="upassc">Confirm:</label></th>
            <td><input type="password" id="upassc" name="upassc" autocomplete="new-password"><br /></td>
        </tr>
    </table>
</form>