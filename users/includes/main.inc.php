<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

set_include_path(get_include_path() . ':../libs:../../libs:../includes/pear:../../includes/pear');
function __autoload($class_name) {
    if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
        require_once $class_name . '.class.inc.php';
    } elseif(file_exists("../../libs/" . $class_name . ".class.inc.php")) {
        require_once $class_name . '.class.inc.php';
    }
}


include_once '../conf/settings.inc.php';

date_default_timezone_set(__TIMEZONE__);
$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);

if (defined("__BASE_WEB_PATH__"))
    $SiteUrlPrefix = __BASE_WEB_PATH__;
else
    $SiteUrlPrefix = "";

require_once("../../includes/error_helpers.inc.php");

?>

