<select id="<?=$name;?>" name="<?=$name;?>" <?=$attributes_html;?>>
    <?php if ( isset($first_option) ): ?>
        <option selected="selected" disabled="disabled" value=""><?= $first_option ?></option>
    <?php endif; ?>

    <?php foreach ($options as $key => $title) : ?>
        <option value="<?=$key;?>" <?php selected($key, $value); ?>><?=$title;?></option>
    <?php endforeach; ?>
</select>
