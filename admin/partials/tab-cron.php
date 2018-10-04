<!--<form method="post" action="options.php">
	<?php /*settings_fields($settings_cron); */?>

	<?php /*do_settings_sections($section_general); */?>

	<?php /*submit_button(); */?>
</form>-->


<br class="clear /">

<table class="widefat">
    <thead>
    <tr>
        <th class="row-title"><?php esc_html_e( 'Event', 'wpb' ); ?></th>
        <th><?php esc_html_e( 'Schedule', 'wpb' ); ?></th>
        <th><?php esc_html_e( 'Arguments', 'wpb' ); ?></th>
        <th><?php esc_html_e( 'Next execution', 'wpb' ); ?></th>
    </tr>
    </thead>
    <tbody>
	<?php $i = 0; ?>
	<?php foreach ($plugin_cron_tasks as $name => $opt): ?>
        <tr <?php echo $i % 2 ? 'class="alternate"': '' ?>>
            <td class="row-title"><?php echo Wpb_Cron::get_readable_name($name) ?> <p class="description" style="font-weight: 400;"><?php echo $name ?></p></td>
            <td><?php echo $opt['schedule'] ?></td>
            <td><?php echo Wpb_Helpers::wrap_code_tag($opt['args']) ?></td>
            <td><?php echo human_time_diff($opt['next_execution']) ?></td>
        </tr>
		<?php $i++; ?>
	<?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <th class="row-title"><?php esc_html_e( 'Event', 'wpb' ); ?></th>
        <th><?php esc_html_e( 'Schedule', 'wpb' ); ?></th>
        <th><?php esc_html_e( 'Arguments', 'wpb' ); ?></th>
        <th><?php esc_html_e( 'Next execution', 'wpb' ); ?></th>
    </tr>
    </tfoot>
</table>