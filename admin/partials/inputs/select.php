<select id="<?=$name;?>" name="<?=$name;?>" class="regular-text">
    <option value=""></option>

    <?php foreach ($options as $key => $title) : ?>
        <option value="<?=$key;?>" <?=selected($key, $value, false);?>><?=$title;?></option>
    <?php endforeach; ?>
</select>
