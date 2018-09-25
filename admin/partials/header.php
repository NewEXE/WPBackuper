<div class="wrap <?php echo $page_key ?>">
    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

    <h2 class="nav-tab-wrapper">
		<?php foreach ($tabs as $key => $title) : ?>
			<?php $class = ($key === $selected_tab) ? 'nav-tab-active' : ''; ?>

            <a href="?page=<?= $page_key ?>&tab=<?= $key ?>" class="nav-tab <?= $class ?>"><?= $title ?></a>
		<?php endforeach; ?>
    </h2>