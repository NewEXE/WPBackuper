<input type="<?=$type;?>"  id="<?=$name;?>" name="<?=$name;?>" class="<?=$class;?>" value="<?=$value;?>"<?=$attributes_html;?>>

<?php if ( !empty($description) ) : ?>
	<p class="description"><?=$description;?></p>
<?php endif; ?>