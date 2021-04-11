<?php if (!defined('DXMA_VERSION')) die(); 
http_response_code(403);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Descent Mission Archive - Forbidden</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="<?= route("dxma/main.css") ?>">
    </head>
    <body>
        <?php include("topbar.php") ?>
        <div class="container">
            <h1>Forbidden</h1>
        </div>
    </body>
</html>
