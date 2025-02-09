<?php
require_once(__DIR__."/../init.php");

use \efi\global_settings;


const TYPE404 = 404;
const TYPE500 = 500;

function error_404($message = "") {
    error404($message);
}
function error404($message = "") {
    error_base($message, false, TYPE404, false);
}

function error_500($message = "") {
    error500($message);
}
function error500($message = "") {
    error_base($message, false, TYPE500, false);
}

function error_base($message, $IsPretty, $errType, $IsExpiredPage) {
    $errText = $errType == TYPE500 ? "500 Server Error" : "404 Not Found";
    header($_SERVER["SERVER_PROTOCOL"]." ".$errType, true, $errType);
    if ($IsPretty)
        include("inc/header.inc.php");
    if ($errType == TYPE500)
        include(__DIR__."/../html/not_found.php");
    else
        include(__DIR__."/../html/not_found.php");
    if ($message)
        print("Info: <b>$message</b>\n<br>");
    if ($IsPretty)
        include("inc/footer.inc.php");
    die();
}

function pretty_error_404($message = "") {
    prettyError404($message);
}
function prettyError404($message = "") {
    error_base($message, true, TYPE404, false);
}

function pretty_error_expired($message = "") {
    prettyErrorExpired($message);
}
function prettyErrorExpired($message = "") {
    error_base($message, true, TYPE500, true);
}
function error_expired($header_file, $footer_file, $time_completed = "") {
    require(__DIR__."/../init.php"); // Scope of variables has changed, so we need to re-include it.
    require_once($header_file);
    echo "<p class='center'><br>Your job results are only retained for a period of " . global_settings::get_retention_days(). " days";
    if ($time_completed)
        echo "<br>Your job was completed on $time_completed";
    echo "<br>Please go back to the <a href='" . global_settings::get_server_name() . "'>homepage</a></p><br><br><br><br>";
    require_once($footer_file);
    exit(0);
}

