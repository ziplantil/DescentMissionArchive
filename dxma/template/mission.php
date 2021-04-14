<?php if (!defined('DXMA_VERSION')) {
    die();
} ?>
<br />
<div>
<h1 class="inline"><span><?= htmlspecialchars($m["title"]) ?></span></h1>
<i>&nbsp;uploaded by <?= fragment("user", [$m["user"], $m["username"]]) ?></i><br />
</div>
<p><i>Actions</i> 
&bull; <a class="mission-action mission-action-main" href="<?= htmlspecialchars(getMissionFileURL($m["user"], $m["id"], $m["filename"])) ?>" download>Download (<?= formatFileSize(filesize(getMissionFilePath($m["user"], $m["id"], $m["filename"]))) ?>)</a> 
&bull; <a class="mission-action" href="<?= route("favorite", ["m" => $m["id"]]) ?>"><?= $fav ? "Unfavorite" : "Favorite" ?></a>
<?php if ($userid == $m["user"]) : ?>
&bull; <a class="mission-action" href="<?= route("edit", ["m" => $m["id"]]) ?>">Edit</a>
&bull; <a class="mission-action" href="<?= route("update", ["m" => $m["id"]]) ?>">Update</a>
&bull; <a class="mission-action mission-action-danger" href="<?= route("delete", ["m" => $m["id"]]) ?>">Delete</a>
<?php endif; ?>
</p>
<table class="missionmaintable">
    <tr>
        <td>
<?php if (!empty($m["screenshot"])) : ?>
<?php $imgpath = htmlspecialchars(getScreenshotFileURL($m["user"], $m["id"], $m["screenshot"])); ?>
            <a target="_blank" href="<?= $imgpath ?>"><img width="320" title="Screenshot for <?= $m["title"] ?>" alt="Screenshot for <?= $m["title"] ?>" src="<?= $imgpath ?>"></a>
<?php else : ?>
            <img width="320" title="No screenshot" alt="No screenshot" src="<?= route('dxma/noimg.png') ?>"></a>
<?php endif; ?>
        </td>
        <td>
            <table class="missioninfo">
                <tr>
                    <th>Version</th>
                    <td><?= htmlspecialchars($m["version"]) ?></td>
                </tr>
                <tr>
                    <th>Authors</th>
                    <td>
                        <?php foreach ($m["authors"] as $i => &$author): ?>
                            <?= $i > 0 ? "<br />" : "" ?>
                            <?php fragment("author", $author) ?>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr>
                    <th>Players</th>
                    <td><?= $m["playersMin"] ?> &ndash; <?= $m["playersMax"] ?></td>
                </tr>
                <tr>
                    <th>Mode</th>
                    <td><?= fragment("mode", ["i" => $m["mode"], "full" => 1]) ?></td>
                </tr>
                <tr>
                    <th>Game</th>
                    <td><?= fragment("game", ["i" => $m["game"], "full" => 1]) ?></td>
                </tr>
                <tr>
                    <th>Number of levels</th>
                    <td><?= $m["levels"] ?></td>
                </tr>
                <tr>
                    <th>Released</th>
                    <td><?= htmlspecialchars(formatDate($m["released"])) ?></td>
                </tr>
                <tr>
                    <th>Posted</th>
                    <td><?= htmlspecialchars(formatDateTime($m["created"])) ?></td>
                </tr>
                <tr>
                    <th>Updated</th>
                    <td><?= htmlspecialchars(formatDateTime($m["updated"])) ?></td>
                </tr>
                <tr>
                    <td colspan="2"><hr /></td>
                </yr>
                <tr>
                    <th>Rating</th>
                    <td><?= $ratings["count"] > 0 ? "<b>" . number_format($ratings["average"], 2) . "</b> / 10, out of " . $ratings["count"] . " rating(s)" : "<i>not rated</i>" ?></td>
                </tr>
                <tr>
                    <th><?= !is_null($ratings["you"]) ? "Change rating" : "Rate" ?></th>
                    <td>
                    <?php if ($logged_in) : ?>
                    <form action="<?= route("rate/", array("m" => $m['id'])) ?>" method="post" id="rateform">
                        <input type="hidden" name="csrf" value="<?= $_SESSION["token"] ?>">
                        <input name="rating" class="numinput" id="rating" type="number" value="<?= !is_null($ratings["you"]) ? $ratings["you"] : "" ?>" min="0" max="10" required />/10
                        <input type="submit" value="<?= !is_null($ratings["you"]) ? "Change" : "Rate" ?>" />
                    </form>
                    <?php if (!is_null($ratings["you"])) : ?>
                    <form action="<?= route("rate/", array("m" => $m['id'])) ?>" method="post" id="ratedeleteform">
                    <input type="hidden" name="csrf" value="<?= $_SESSION["token"] ?>">
                        <input name="delete" id="delete" type="hidden" value="1" />
                        <input type="submit" value="Delete rating" />
                    </form>
                    <?php endif; ?>
                    <?php else : ?>
                    <i>(log in to rate)</i>
                    <?php endif; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<?php if (!empty($m["description"])) : ?>
<h2>Mission notes</h2>
<pre><?= $m["description"] ?></pre>
<?php endif; ?>
