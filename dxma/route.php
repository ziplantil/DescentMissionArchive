<?php
if (!defined('DXMA_VERSION')) die();

function redirect($page) {
    header('Location: ' . $page);
    die();
}

function route(string $name = '', array $args = []) {
    $path = $name !== '' ? '/' . $name : '';
    $query = http_build_query($args);
    $query = $query !== '' ? '?' . $query : '';
    return FRONTEND . $path . $query;
}

function fragment(string $fragment, $m) {
    require "template/f_$fragment.php";
}
?>
