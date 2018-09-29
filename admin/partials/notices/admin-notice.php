<?php
/**
 * Possible notices types
 */
$possible_types = [
	'success',
	'info',
	'warning',
	'error',
];
if ( ! isset($type)  || ! in_array($type, $possible_types) ) $type = 'info';
if ( empty($message) ) $message = '';
?>

<div class="notice notice-<?php echo $type ?>">
	<p><?php echo $message ?></p>
</div>