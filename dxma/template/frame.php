<?php if (!defined('DXMA_VERSION')) die(); ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Descent Mission Archive - <?= htmlspecialchars($title) ?></title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="<?= route("dxma/main.css") ?>">
    </head>
    <body>
        <?php include("topbar.php") ?>
        <div class="container">
            <?php include("$content") ?>
        </div>
        <br />
        <br />
        <br />
        <br />
        <br />
        <div class="footer">
            <hr />
            <span class="footerleft">
            Descent Mission Archive software (C) ziplantil 2021<br />
            Copyright of all missions (C) respective owners<br />
            </span>
            <span class="footerright">
            <a href="https://github.com/ziplantil/DescentMissionArchive" target="_blank">Source code</a>
            <br />
            <span>v<?= DXMA_VERSION ?></span>
            </span>
            <div class="clear"> </div>
            <br />
        </div>
    </body>
</html>
