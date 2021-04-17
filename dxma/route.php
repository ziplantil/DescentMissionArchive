<?php
if (!defined('DXMA_VERSION')) {
    die();
}

function redirect($page)
{
    header('Location: ' . $page);
    die();
}

function route(string $name = '', array $args = [])
{
    $path = $name !== '' ? '/' . $name : '';
    $query = http_build_query($args);
    $query = $query !== '' ? '?' . $query : '';
    return FRONTEND . $path . $query;
}

function routePage(string $name, int $page)
{
    $params = array_merge($_GET, array($name => $page));
    return '.?' . http_build_query($params);
}

function fragment(string $fragment, $m)
{
    require "template/f_$fragment.php";
}
