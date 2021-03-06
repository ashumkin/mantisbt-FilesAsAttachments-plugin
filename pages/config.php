<?php

# Copyright (c) 2012 Alexey Shumkin
# Licensed under the MIT license

auth_reauthenticate();
access_ensure_global_level( plugin_config_get( 'manage_threshold' ) );

html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();

print_manage_menu();

?>

<br/>
<form action="<?php echo plugin_page( 'manage_config' ) ?>" method="post">
<?php echo form_security_field( 'plugin_FilesAsAttachments_manage_config' ) ?>
<table class="width75" align="center" cellspacing="1">

<tr>
	<td class="form-title" colspan="2"><?php echo plugin_lang_get( 'title' ), ': ', plugin_lang_get( 'configuration' ) ?></td>
</tr>

<tr <?php echo helper_alternate_class() ?> >
	<td class="category" width="60%">
		<?php echo plugin_lang_get( 'view_threshold' ) ?>
	</td>
	<td width="20%">
		<select name="view_threshold">
		<?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'view_threshold'  ) ) ?>;
		</select>
	</td>
</tr>

<tr <?php echo helper_alternate_class() ?> >
	<td class="category" width="60%">
		<?php echo plugin_lang_get( 'scan_dir' ) ?>
	</td>
	<td width="20%">
		<input name="scan_dir" size="30" value="<?php echo plugin_config_get('scan_dir') ?>">
	</td>
</tr>

<tr <?php echo helper_alternate_class() ?> >
	<td class="category" width="60%">
		<?php echo plugin_lang_get( 'file_prefix' ) ?>
	</td>
	<td width="20%">
		<input name="file_prefix" size="30" value="<?php echo plugin_config_get('file_prefix') ?>">
	</td>
</tr>

<tr <?php echo helper_alternate_class() ?> >
	<td class="category" width="60%">
<?php
	// this was taken from bug_file_upload_inc.php
	$t_max_upload_file_size = number_format( (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) ) / 1000 );
	echo sprintf( plugin_lang_get( 'dont_show_small_files' ), $t_max_upload_file_size )
?>
	</td>
	<td width="20%">
		<input name="dont_show_small_files" type="checkbox" <?php echo ON == plugin_config_get('dont_show_small_files') ? 'checked="checked"' : '' ?>>
	</td>
</tr>

<tr>
	<td class="center" colspan="2"><input type="submit" value="<?php echo plugin_lang_get( 'update_configuration' ) ?>"/></td>
</tr>

</table>
</form>

<?php
html_page_bottom1( __FILE__ );

