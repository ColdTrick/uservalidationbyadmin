<?php
/**
 * The main plugin file
 */

require_once(dirname(__FILE__) . "/lib/events.php");
require_once(dirname(__FILE__) . "/lib/functions.php");
require_once(dirname(__FILE__) . "/lib/hooks.php");

/**
 * Gets called during system initialization
 *
 * @return void
 */
function uservalidationbyadmin_init() {
	global $USERVALIDATIONBYADMIN_ADMIN_NOTIFY_SETTING;
	
	// register pam handler to check authentication
	register_pam_handler("uservalidationbyadmin_pam_handler", "required");
	
	// extend admin js
	elgg_extend_view("js/admin", "uservalidationbyadmin/js/admin");
	
	$notify_admin = elgg_get_plugin_setting("admin_notify", "uservalidationbyadmin");
	switch ($notify_admin) {
		case "daily":
		case "weekly":
			$USERVALIDATIONBYADMIN_ADMIN_NOTIFY_SETTING = $notify_admin;
			
			elgg_register_plugin_hook_handler("cron", $notify_admin, "uservalidationbyadmin_cron_hook");
			break;
		case "direct":
			$USERVALIDATIONBYADMIN_ADMIN_NOTIFY_SETTING = $notify_admin;
			break;
		default:
			$USERVALIDATIONBYADMIN_ADMIN_NOTIFY_SETTING = "none";
			break;
	}
}

/**
 * Gets called just befor the first output is generated
 *
 * @return void
 */
function uservalidationbyadmin_pagesetup() {
	// register admin menu item
	elgg_register_admin_menu_item("administer", "pending_approval", "users");
}

// register on default Elgg events
elgg_register_event_handler("init", "system", "uservalidationbyadmin_init");
elgg_register_event_handler("pagesetup", "system", "uservalidationbyadmin_pagesetup");

// register events
elgg_register_event_handler("login", "user", "uservalidationbyadmin_login_event");

// register hooks
elgg_register_plugin_hook_handler("register", "user", "uservalidationbyadmin_register_user_hook");
elgg_register_plugin_hook_handler("permissions_check", "user", "uservalidationbyadmin_permissions_check_hook");
elgg_register_plugin_hook_handler("fail", "auth", "uservalidationbyadmin_auth_fail_hook");

// register actions
elgg_register_action("uservalidationbyadmin/validate", dirname(__FILE__) . "/actions/validate.php", "admin");
elgg_register_action("uservalidationbyadmin/delete", dirname(__FILE__) . "/actions/delete.php", "admin");
elgg_register_action("uservalidationbyadmin/bulk_action", dirname(__FILE__) . "/actions/bulk_action.php", "admin");
