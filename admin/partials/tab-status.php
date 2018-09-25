<br class="clear" />

<table class="widefat">
	<thead>
	<tr>
		<th class="row-title"><?php esc_html_e( 'Name', 'wpb' ); ?></th>
		<th><?php esc_html_e( 'Status', 'wpb' ); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php $i = 0; ?>
    <?php foreach ($items as $item): ?>
        <tr <?php echo $i % 2 ? 'class="alternate"': '' ?>>
            <td class="row-title"><?php echo $item['name'] ?> <p class="description" style="font-weight: 400;"><?php echo $item['hint'] ?></p></td>
            <td><?php echo $item['true'] ?
		            '<mark class="yes"></mark> <span class="dashicons dashicons-yes"></span> ' . $item['description_true'] :
		            '<mark class="warning"></mark> <span class="dashicons dashicons-warning"></span> ' . $item['description_false'] ?></td>
        </tr>
        <?php $i++; ?>
    <?php endforeach; ?>
	</tbody>
	<tfoot>
	<tr>
		<th class="row-title"><?php esc_html_e( 'Name', 'wpb' ); ?></th>
		<th><?php esc_html_e( 'Status', 'wpb' ); ?></th>
	</tr>
	</tfoot>
</table>