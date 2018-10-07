<br class="clear /">

<form method="post" action="options.php">
	<?php settings_fields($settings_cron); ?>
    <table class="widefat">
        <thead>
        <tr>
            <th class="row-title"><?php esc_html_e( 'Activated', 'wpb' ); ?></th>
            <th><?php esc_html_e( 'Event', 'wpb' ); ?></th>
            <th><?php esc_html_e( 'Schedule', 'wpb' ); ?></th>
            <th><?php esc_html_e( 'Next execution in', 'wpb' ); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php $i = 0; ?>
        <?php foreach ($plugin_cron_tasks as $name => $opt): ?>
            <tr <?php echo $i % 2 ? 'class="alternate"': '' ?>>
                <td class="row-title">
	                <?php Wpb_Admin::do_settings_section_field(
		                $section_general,
		                Wpb_Admin::OPTION_BACKUP_ACTIVATE_SCHEDULE_FILES
	                ) ?>
                </td>
                <td><?php echo Wpb_Cron::get_readable_name($name) ?>
                    <p class="description" style="font-weight: 400;"><?php echo $name ?></p>
                </td>
                <td>
                    <?php echo Wpb_Cron::get_readable_schedule($opt['schedule']) ?>
                    <p class="description" style="font-weight: 400;"><?php echo $opt['schedule'] ?></p>
                </td>
                <td><?php echo human_time_diff($opt['next_execution']) ?></td>
            </tr>
            <?php $i++; ?>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <th class="row-title"><?php esc_html_e( 'Activated', 'wpb' ); ?></th>
            <th><?php esc_html_e( 'Event', 'wpb' ); ?></th>
            <th><?php esc_html_e( 'Schedule', 'wpb' ); ?></th>
            <th><?php esc_html_e( 'Next execution in', 'wpb' ); ?></th>
        </tr>
        </tfoot>
    </table>
	<?php submit_button(); ?>
</form>