<?php

# Copyright (c) 2012 Alexey Shumkin
# Licensed under the MIT license

form_security_validate( 'plugin_FilesAsAttachments_manage_config' );
auth_reauthenticate();
access_ensure_global_level( plugin_config_get( 'manage_threshold' ) );

$f_view_threshold = gpc_get_int( 'view_threshold', REPORTER );
$f_scan_dir = gpc_get_string( 'scan_dir', '' );
$f_file_prefix = gpc_get_string( 'file_prefix', '' );
$f_dont_show_small_files = gpc_get_bool( 'dont_show_small_files', OFF );

plugin_config_set( 'view_threshold', $f_view_threshold );
plugin_config_set( 'scan_dir', $f_scan_dir );
plugin_config_set( 'file_prefix', $f_file_prefix );
plugin_config_set( 'dont_show_small_files', $f_dont_show_small_files );

form_security_purge( 'plugin_FilesAsAttachments_manage_config' );

print_successful_redirect( plugin_page( 'config', true ) );

