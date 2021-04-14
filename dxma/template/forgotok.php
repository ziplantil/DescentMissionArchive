<?php if (!defined('DXMA_VERSION')) {
    die();
}
if ($logged_in) {
    redirect(route());
} ?>
<h1>Forgot password</h1>
<p>Request sent. If an user account exists by that name and an email address
has been configured, an email will be sent with further instructions.

If no email arrives, try filling in the form again. If this does not help,
please contact the server administrator for assistance.</p>
