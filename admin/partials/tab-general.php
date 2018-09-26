<form method="post" action="options.php">
	<?php settings_fields($settings_general); ?>

	<?php do_settings_sections($section_general); ?>

	<?php submit_button(); ?>
</form>

<form method="post">
    <?php wp_nonce_field('wpb_make_backup', 'wpb_make_backup') ?>
    <?php submit_button(__('Make files backup', 'wpb'), '', 'wpb_backup_files', false); ?>

    <?php submit_button(__('Make database backup', 'wpb'), '', 'wpb_backup_db', false); ?>
</form>
