<?php

	require_once(dirname(__FILE__) . "/lib/events.php");
	require_once(dirname(__FILE__) . "/lib/functions.php");
	require_once(dirname(__FILE__) . "/lib/hooks.php");

	function uservalidationbyadmin_init(){
		// register pam handler to check authentication
		register_pam_handler("uservalidationbyadmin_pam_handler", "required");
		
		// extend admin js
		elgg_extend_view("js/admin", "uservalidationbyadmin/js/admin");
	}
	
	function uservalidationbyadmin_pagesetup(){
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
	
	// register actions
	elgg_register_action("uservalidationbyadmin/validate", dirname(__FILE__) . "/actions/validate.php", "admin");
	elgg_register_action("uservalidationbyadmin/delete", dirname(__FILE__) . "/actions/delete.php", "admin");
	elgg_register_action("uservalidationbyadmin/bulk_action", dirname(__FILE__) . "/actions/bulk_action.php", "admin");