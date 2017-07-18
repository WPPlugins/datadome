<?php
/**
  * The file that is called when the plugin is uninstalled
  */

//if uninstall not called from WordPress exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

defined("DATADOME_PLUGIN_SLUG__") || define("DATADOME_PLUGIN_SLUG__", "__data_dome_");

$opts   = array("server", "servers", "timeout", "regex", "exclude", "key", "sendstats", "jskey", "https");
foreach ($opts as $opt) {
    delete_option(DATADOME_PLUGIN_SLUG__ . $opt);
}