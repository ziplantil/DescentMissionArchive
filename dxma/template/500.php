<?php if (!defined('DXMA_VERSION')) {
    die();
}
http_response_code(500);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Descent Mission Archive - Internal server error</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="<?= route("dxma/main.css") ?>">
    </head>
    <body>
        <div class="container">
            <h1>Internal server error</h1>
            <p>Contact the server administrator for assistance.</p>
        </div>
    </body>
</html>
