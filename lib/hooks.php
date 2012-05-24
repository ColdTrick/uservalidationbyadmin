<?php

	function uservalidationbyadmin_register_user_hook($hook, $type, $return_value, $params){
		
		if(!empty($params) && is_array($params)){
			$user = elgg_extract("user", $params);
			
			if(!empty($user) && elgg_instanceof($user, "user")){
				// make sure we can see everything
				$hidden = access_get_show_hidden_status();
				access_show_hidden_entities(true);
				
				// make sure we can save metadata
				elgg_push_context("uservalidationbyadmin_new_user");
				
				// this user needs validation
				$user->admin_validated = false;
				
				// check if we need to disable the user
				if($user->isEnabled()){
					$user->disable();
				}
				
				// restore context
				elgg_pop_context();
				
				// restore access settings
				access_show_hidden_entities($hidden);
			}
		}
	}
	
	function uservalidationbyadmin_permissions_check_hook($hook, $type, $return_value, $params){
		$result = $return_value;
		
		if(!$result && !empty($params) && is_array($params)){
			$user = elgg_extract("entity", $params);
			
			// do we have a user
			if(!empty($user) && elgg_instanceof($user, "user")){
				// are we setting our validation flags
				if(elgg_in_context("uservalidationbyadmin_new_user")){
					$result = true;
				}
			}
		}
		
		return $result;
	}