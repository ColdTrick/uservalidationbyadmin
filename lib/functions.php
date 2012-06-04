<?php

	function uservalidationbyadmin_pam_handler($credentials){
		$result = null;
		
		if(!empty($credentials) && is_array($credentials)){
			if($username = elgg_extract("username", $credentials)){
				$result = false;
				
				// make sure we can see all users
				$hidden = access_get_show_hidden_status();
				access_show_hidden_entities(true);
				
				if($user = get_user_by_username($username)){
					// check if the user is enabled
					if($user->isEnabled()){
						
						if($user->isAdmin()){
							// admins can always login
							$result = true;
						} elseif(isset($user->admin_validated)){
							if(!$user->admin_validated){
								// this user should be admin validated
								access_get_show_hidden_status($hidden);
								
								// throw exception
								throw LoginException(elgg_echo("uservalidationbyadmin:login:pam:failed"));
							} else {
								// user is validated
								$result = true;
							}
						} else {
							// user register before this plugin was activated
							$result = true;
						}
					}
				}
				
				// restore hidden status
				access_get_show_hidden_status($hidden);
			}
		}
		
		return $result;
	}
	
	function uservalidationbyadmin_get_selection_options($count = false){
		$result = array(
			"type" => "user",
			"site_guids" => false,
			"limit" => 25,
			"offset" => max(0, (int) get_input("offset")),
			"relationship" => "member_of_site",
			"relationship_guid" => elgg_get_site_entity()->getGUID(),
			"inverse_relationship" => true,
			"count" => (bool) $count,
			"metadata_name_value_pairs" => array(
				"name" => "admin_validated",
				"value" => 0	// @todo this should be false, but Elgg doesn't support that (yet)
			)
		);
		
		// extra options
		if(!elgg_is_active_plugin("uservalidationbyemail")){
			// uservalidationbyemail handles part of this proccess
			$result["wheres"] = array("e.enabled = 'no'");
		}
		
		return $result;
	}
	
	function uservalidationbyadmin_view_users_list($entities, $options){
		$result = false;
		
		if(!empty($entities) && is_array($entities)){
			$nav_options = $options;
			$nav_options["offset_key"] = elgg_extract("offset_key", $options, "offset");
			
			$nav = elgg_view("navigation/pagination", $nav_options);
			
			$list_class = "elgg-list";
			if($extra_list_class = elgg_extract("list_class", $options)){
				$list_class .= " " . $extra_list_class;
			}
			
			$item_class = "elgg-item";
			if($extra_item_class = elgg_extract("item_class", $options)){
				$item_class .= " " . $extra_item_class;
			}
			
			$result = "<ul class='" . $list_class . "'>";
			
			foreach($entities as $entity){
				if(elgg_instanceof($entity)){
					$id = "elgg-" . $entity->getType() . "-" . $entity->getType();
				} else {
					$id = "elgg-" . $entity->getType() . "-" . $entity->id;
				}
				
				$result .= "<li id='" . $id . "' class='" . $item_class . "'>";
				$result .= elgg_view("uservalidationbyadmin/list/user", array("entity" => $entity));
				$result .= "</li>";
			}
			
			$result .= "</ul>";
			
			$result .= $nav;
		}
		
		return $result;
	}
	
	function uservalidationbyadmin_notify_validate_user(ElggUser $user){
		$result = false;
		
		if(!empty($user) && elgg_instanceof($user, "user")){
			$site = elgg_get_site_entity();
			
			$subject = elgg_echo("uservalidationbyadmin:notify:validate:subject", array($site->name));
			$msg = elgg_echo("uservalidationbyadmin:notify:validate:message", array($user->name, $site->name, $site->url));
			
			$result = notify_user($user->getGUID(), $subject, $msg, null, "email");
		}
		
		return $result;
	}
	
	function uservalidationbyadmin_notify_admins(){
		global $USERVALIDATIONBYADMIN_ADMIN_NOTIFY_SETTING;
		
		if(!empty($USERVALIDATIONBYADMIN_ADMIN_NOTIFY_SETTING) && ($USERVALIDATIONBYADMIN_ADMIN_NOTIFY_SETTING != "none")){
			// make sure we can see every user
			$hidden = access_get_show_hidden_status();
			access_show_hidden_entities(true);
			
			// get selection options
			$options = uservalidationbyadmin_get_selection_options(true);
			
			if($user_count = elgg_get_entities_from_relationship($options)){
				$site = elgg_get_site_entity();
				
				// there are unvalidated users, now find the admins to notify
				$admin_options = array(
					"type" => "user",
					"limit" => false,
					"site_guids" => false,
					"relationship" => "member_of_site",
					"relationship_guid" => $site->getGUID(),
					"inverse_relationship" => true,
					"joins" => array("JOIN " . elgg_get_config("dbprefix") . "users_entity ue ON e.guid = ue.guid"),
					"wheres" => array("ue.admin = 'yes'")
				);
				
				$admins = elgg_get_entities_from_relationship($admin_options);
				
				// trigger hook to adjust the admin list
				$params = array(
					"admins" => $admins,
					"user_count" => $user_count
				);
				$admins = elgg_trigger_plugin_hook("notify_admin", "uservalidationbyadmin", $params, $admins);
				
				// notify the admins
				if(!empty($admins)){
					foreach($admins as $admin){
						$subject = elgg_echo("uservalildationbyadmin:notify:admin:subject");
						$msg = elgg_echo("uservalildationbyadmin:notify:admin:message", array(
							$admin->name,
							$user_count,
							$site->name,
							$site->getURL() . "admin/users/pending_approval"
						));
						
						notify_user($admin->getGUID(), $site->getGUID(), $subject, $msg, null, "email");
					}
				}
			}
			
			// restore hidden setting
			access_show_hidden_entities($hidden);
		}
	}