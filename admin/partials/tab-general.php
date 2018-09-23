<form method="post" action="options.php">
	<?php settings_fields($settings_general); ?>

	<?php do_settings_sections($section_general); ?>

	<?php submit_button(); ?>
</form>
