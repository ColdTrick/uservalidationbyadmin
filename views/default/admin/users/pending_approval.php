<?php

	// make sure we can see everything
	$hidden = access_get_show_hidden_status();
	access_show_hidden_entities(true);

	echo "<div class='mbm'>" . elgg_echo("uservalidationbyadmin:pending_approval:description") . "</div>";
	
	$title = elgg_echo("uservalidationbyadmin:pending_approval:title");
	
	$options = uservalidationbyadmin_get_selection_options();
	
	if(!($body = elgg_list_entities($options, "elgg_get_entities_from_relationship", "uservalidationbyadmin_view_users_list"))){
		$body = elgg_echo("notfound");
	}
	
	echo elgg_view_module("inline", $title, $body);

	// restore access settings
	access_show_hidden_entities($hidden);