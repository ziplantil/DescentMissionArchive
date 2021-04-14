<?php
if (!defined('I_AM_DXMA')) die();
require_once dirname(__FILE__) . '/../config/config.php';
define("DXMA_VERSION", "0.4 alpha");

if (DEBUG == 1) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    function go500() {
        if (error_get_last())
            include('template/500.php');
    }
    register_shutdown_function('go500');
}

require 'view.php';
$dxma = new DescentMissionArchive();
?>
