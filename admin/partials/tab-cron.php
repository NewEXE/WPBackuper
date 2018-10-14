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
            <th><?php esc_html_e( 'Execute now', 'wpb' ); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php $i = 0; ?>
        <?php foreach ($plugin_cron_tasks as $name => $opt): ?>
            <tr <?php echo $i % 2 ? 'class="alternate"': '' ?>>
                <td class="row-title">
	                <?php Wpb_Admin::do_settings_section_field(
		                $section_general,
		                $opt['name']
	                ) ?>
                </td>
                <td><?php echo Wpb_Cron::get_readable_name($name) ?>
                    <p class="description" style="font-weight: 400;"><?php echo $name ?></p>
                </td>
                <td>
                    <?php echo $opt['select_schedule'] ?>
                </td>
                <td>
                    <?php if ( is_numeric($opt['next_execution']) && $opt['next_execution'] <= time() ): ?>
                        <?php esc_html_e('In queue', 'wpb') ?>
                    <?php elseif ( is_numeric($opt['next_execution']) ): ?>
                        <?php echo human_time_diff($opt['next_execution']) ?>
                    <?php else: ?>
                        <?php echo $opt['next_execution'] ?>
                    <?php endif; ?>
                </td>
                <td>
                    <a class="button-secondary" href="<?php echo wp_nonce_url( Wpb_Helpers::plugin_url(
	                    Wpb_Admin::TAB_CRON, ['wpb_send_to_email_'.$name => true]
                    ), 'wpb_cron_tasks' ) ?>" title="<?php esc_attr_e( 'Send e-mail now' ); ?>"><?php esc_attr_e( 'Execute' ); ?></a>
                </td>
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
            <th><?php esc_html_e( 'Execute now', 'wpb' ); ?></th>
        </tr>
        </tfoot>
    </table>
	<?php submit_button(); ?>
</form>