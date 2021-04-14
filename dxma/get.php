<?php
if (!defined('DXMA_VERSION')) {
    die();
}

function getString($v, $default)
{
    return empty($_GET[$v]) ? $default : $_GET[$v];
}

function getNumber($v, $default)
{
    $s = getString($v, "");
    if (empty($s) || !is_numeric($s)) {
        return $default;
    }
    $n = intval($s);
    return $n < 0 ? $default : $n;
}

function getNumberArray($arr)
{
    $r = array();
    foreach ($arr as $k => &$v) {
        if (is_numeric($v) && intval($v) >= 0) {
            $r[] = intval($v);
        }
    }
    return $r;
}

function hasAllGet(...$params)
{
    foreach ($params as $k => &$v) {
        if (!isset($_GET[$v])) {
            return false;
        }
    }
    return true;
}

function hasAllPost(...$params)
{
    foreach ($params as $k => &$v) {
        if (!isset($_POST[$v])) {
            return false;
        }
    }
    return true;
}

function arrayget($array, string ...$keys)
{
    return array_intersect_key($array, array_flip($keys));
}

function formatDate($str)
{
    return $str;
}

function formatDateTime($str)
{
    return $str;
}
