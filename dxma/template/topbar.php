<?php if (!defined('DXMA_VERSION')) {
    die();
} ?>
        <div id="topbar">
            <span class="topbarleft">
                <p><a href="<?= route() ?>">Descent Mission Archive</a></p>
                <a href="<?= route() ?>">Home</a><br />
                <a href="<?= route("about") ?>">About</a><br />
                <a href="<?= route("members") ?>">Members</a><br />
                <a href="<?= route("stats") ?>">Statistics</a><br />
            </span>
            <span class="topbarright">
                <?php if ($logged_in) : ?>
                <p><i>You are logged in as</i> <?= fragment("user", [ $userid, $username ]) ?></p>
                <a href="<?= route("favorites") ?>">Favorites</a><br />
                <a href="<?= route("add") ?>">Add mission</a><br />
                <a href="<?= route("usermod") ?>">Edit user info</a><br />
                <a href="<?= route("logout") ?>">Log out</a><br />
                <?php else : ?>
                <p>Not logged in</p>
                <a href="<?= route("login") ?>">Log in</a><br />
                <a href="<?= route("register") ?>">Create account</a><br />
                <?php endif; ?>
            </span>
            <div class="clear"> </div>
            <hr />
        </div>