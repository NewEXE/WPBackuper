<form method="post" action="options.php">
	<?php settings_fields($settings_general); ?>

	<?php do_settings_sections($section_general); ?>

	<?php submit_button(); ?>
</form>

<form method="post">
    <?php wp_nonce_field('wpb_general_tasks', 'wpb_general_tasks') ?>

    <p class="description"><?php _e('Create backup now and download via browser. This may take some time.' , 'wpb') ?></p>
    <?php submit_button(__('Download files backup', 'wpb'), '', 'wpb_backup_files', false); ?>
    <?php submit_button(__('Download database backup', 'wpb'), '', 'wpb_backup_db', false); ?>
    <br /><br />
    <p class="description"><?php _e('Backups are save into temporary folder. You can clean this dir now.', 'wpb') ?></p>
    <?php submit_button(__('Clean temp dir', 'wpb'), '', 'wpb_clean_temp_dir', false); ?>
</form>
