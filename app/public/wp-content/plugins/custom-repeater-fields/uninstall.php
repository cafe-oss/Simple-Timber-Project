<?php
/**
 * Main plugin class - Fixed Version
 * File: uninstall.php
 */

/**
 * Fired when the plugin is uninstalled - WordPress Best Practice Version
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Remove rewrite rules
 */
delete_option('rewrite_rules');
error_log("CRF Uninstall: Removed rewrite rules");

error_log("CRF Uninstall: Plugin uninstallation completed");

?>