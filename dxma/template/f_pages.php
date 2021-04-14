<?php if (!defined('DXMA_VERSION')) {
    die();
} ?>
<?php if ($m["count"] > 0) : ?>
<?php fragment("page", [ "m" => 0, "n" => $m["num"] - 1, "t" => "<<" ]) ?>
<?php fragment("page", [ "m" => max($m["num"] - 2, 0), "n" => $m["num"] - 1, "t" => "<" ]) ?>
<?php for ($i = 0; $i < $m["count"]; $i++): ?>
    <span> </span>
    <?php fragment("page", [ "m" => $i, "n" => $m["num"] - 1, "t" => $i + 1 ]) ?>
<?php endfor; ?>
<span> </span>
<?php fragment("page", [ "m" => min($m["num"], $m["count"] - 1), "n" => $m["num"] - 1, "t" => ">" ]) ?>
<?php fragment("page", [ "m" => $m["count"] - 1, "n" => $m["num"] - 1, "t" => ">>" ]) ?>
<?php endif; ?>
