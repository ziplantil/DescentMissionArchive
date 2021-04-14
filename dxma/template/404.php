<?php if (!defined('DXMA_VERSION')) {
    die();
}
http_response_code(404);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Descent Mission Archive - Not found</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="<?= route("dxma/main.css") ?>">
    </head>
    <body>
        <?php include("topbar.php") ?>
        <div class="container">
            <h1>Not found</h1>
        </div>
    </body>
</html>
