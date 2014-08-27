<?php

?>
//<script>
elgg.provide("elgg.uservalidationbyadmin");

elgg.uservalidationbyadmin.check_all = function() {
	if ($(this).is(":checked")) {
		// so check everything
		$("#uservalidationbyadmin-wrapper input[type='checkbox'][name='user_guids[]']").attr("checked", "checked");
	} else {
		// uncheck everything
		$("#uservalidationbyadmin-wrapper input[type='checkbox'][name='user_guids[]']").removeAttr("checked");
	}
}

elgg.uservalidationbyadmin.bulk_action = function() {
	$checked = $("#uservalidationbyadmin-wrapper input[type='checkbox'][name='user_guids[]']:checked");

	if ($checked.length > 0) {
		$href = $(this).attr("href");
		$href = $href + "&" + $checked.serialize();

		$(this).attr("href", $href);
	} else {
		alert(elgg.echo("uservalidationbyadmin:bulk_action:select"));
		return false;
	}
}

elgg.uservalidationbyadmin.init = function() {
	// (un)check all users
	$("#uservalidationbyadmin-check-all").live("click", elgg.uservalidationbyadmin.check_all);

	// bulk actions
	$("#uservalidationbyadmin-bulk-validate").live("click", elgg.uservalidationbyadmin.bulk_action);
	$("#uservalidationbyadmin-bulk-delete").live("click", elgg.uservalidationbyadmin.bulk_action);
}

elgg.register_hook_handler("init", "system", elgg.uservalidationbyadmin.init);