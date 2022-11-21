<?php
require_once(__DIR__."/../../init.php");

use \efi\est\functions;
use \efi\est\stepa;
use \efi\est\analysis;
use \efi\training\example_config;


if (isset($_GET["type"])) {
    $type = $_GET["type"];
    $is_example = example_config::is_example();
    $stepa = new stepa($db, $_GET["id"], $is_example);
    if ($stepa->get_key() != $_GET["key"]) {
        echo "No EFI-EST Selected. Please go back";
        exit;
    }
    $stepa->download_graph($type);
} else if (isset($_GET["atype"]) && isset($_GET["aid"]) && isset($_GET["net"])) { // analysis
    $analysis = new analysis($db, $_GET["aid"], false);
    if ($analysis->get_key() != $_GET["key"]) {
        echo "No EFI-EST Selected. Please go back";
        exit;
    }
    $analysis->download_graph($_GET["atype"], $_GET["net"]);
} else {
    echo "No EFI-EST Selected. Please go back";
    exit;
}


