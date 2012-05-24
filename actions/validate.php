<?php

	$user_guid = (int) get_input("user_guid");
	
	// we need to see all users
	access_show_hidden_entities(true);
	
	if(!empty($user_guid)){
		if($user = get_user($user_guid)){
			// we got a user, so validate him/her
			$user->admin_validated = true;
			
			// do we also need to enable the user
			if(!$user->isEnabled()){
				$user->enable();
			}
			
			if($user->save()){
				$site = elgg_get_site_entity();
				
				$subject = elgg_echo("uservalidationbyadmin:notify:validate:subject", array($site->name));
				$msg = elgg_echo("uservalidationbyadmin:notify:validate:message", array($user->name, $site->name, $site->url));
				
				notify_user($user->getGUID(), $subject, $msg, null, "email");
				
				system_message(elgg_echo("uservalidationbyadmin:actions:validate:success", array($user->name)));
			} else {
				register_error(elgg_echo("uservalidationbyadmin:actions:validate:error:save", array($user->name)));
			}
		} else {
			register_error(elgg_echo("InvalidParameterException:GUIDNotFound", array($user_guid)));
		}
	} else {
		register_error(elgg_echo("InvalidParameterException:MissingParameter"));
	}
	
	forward(REFERER);