<?php

# Copyright (c) 2013 Alexey Shumkin
# Licensed under the MIT license

class FilesAsAttachmentsPlugin extends MantisPlugin {
	public function register() {
		$this->name = plugin_lang_get( 'title' );
		$this->description = plugin_lang_get( 'description' );

		$this->version = '0.1';
		$this->requires = array(
			'MantisCore' => '1.2.13'
		);

		$this->author = 'Alexey Shumkin';
		$this->contact = 'Alex.Crezoff@gmail.com';
		$this->url = 'http://github.com/ashumkin';
		$this->page = 'config';
	}

	public function config() {
		return array(
			'manage_threshold' => DEVELOPER,
			'view_threshold' => REPORTER,
			'scan_dir' => '',
			'file_prefix' => 'DISKFILE:',
		);
	}

	public function hooks() {
		return array(
			'EVENT_FILE_FILES_GOT' => 'scan_dir',
			'EVENT_FILE_UPDATE_PREVIEW_STATE' => 'update_preview_state',
			'EVENT_FILE_DOWNLOAD_PREPARE' => 'download_file',
			'EVENT_FILE_CAN_DOWNLOAD' => 'can_download',
		);
	}

	public function init() {
	}

	protected function plugin_id() {
		return 'plugin/' . get_class();
	}

	public function can_download( $event, $p_type, $p_file_id, $p_bug_id, $p_user_id ) {
		if ( $p_type == $this->plugin_id() ) {
			return true;
		}
	}

	public function download_file( $event, $p_type, $p_file_id, $p_bug_id ) {
		if ( $p_type != $this->plugin_id() ) {
			return;
		}
		list( , $t_files ) = $this->scan_dir( 'EVENT_FILE_FILES_GOT', $p_bug_id, array() );
		$row = $t_files[$p_file_id - 1];
		return $row;
	}

	public function update_preview_state( $event, $p_attachment ) {
		if ( $p_attachment['source'] == $this->plugin_id() ) {
			if( $p_attachment['can_download'] ) {
				$p_attachment['download_url'] = file_get_download_url( $p_attachment['id'], $this->plugin_id() )
					. '&amp;bug_id=' . $p_attachment['bug_id'];
			}
		}
		return array( $p_attachment );
	}

	/**
	 * Function finds and returns sub-directory named 000XXX (or XXX, or 0XXX) for bug XXX
	 * in a given directory
	 */
	protected function find_bug_dir( $p_scan_dir, $p_bug_id ) {
		if ( $p_scan_dir == '' ) {
			return false;
		}
		$t_padding = config_get( 'display_bug_padding' );
		if ($p_scan_dir[strlen( $p_scan_dir ) - 1] != DIRECTORY_SEPARATOR ) {
			$p_scan_dir .= DIRECTORY_SEPARATOR;
		}
		for ( $i = strlen( $p_bug_id ); $i <= $t_padding; $i++ ) {
			$t_bug_id = utf8_str_pad( $p_bug_id, $i, '0', STR_PAD_LEFT );
			$t_scan_dir = $p_scan_dir . $t_bug_id . DIRECTORY_SEPARATOR;
			if ( file_exists( $t_scan_dir ) && is_dir( $t_scan_dir ) ) {
				return $t_scan_dir;
			}
		}
		return false;
	}

	/**
	 * Returns all visible files (recursively) in a specified folder
	 * Names beginning with "." (dot) are hidden
	 */
	protected function do_scan_dir( $p_scan_dir ) {
		$r_files = array();
		$t_files = array_diff( scandir( $p_scan_dir ), array( '.', '..' ) );
		foreach ( $t_files as $t_file) {
			if ( $t_file[0] == '.' ) {
				continue;
			}
			$t_file_full = $p_scan_dir . $t_file;
			if ( is_dir( $t_file_full ) ) {
				$r_files = array_merge( $r_files, $this->do_scan_dir( $t_file_full . DIRECTORY_SEPARATOR ) );
			} else {
				$r_files[$t_file_full] = $t_file;
			}
		}
		return $r_files;
	}

	/**
	 * Returns all files (recursively) in a bug's folder
	 */
	public function scan_dir( $event, $p_bug_id, $p_attachments ) {
		$t_user_id = auth_get_current_user_id();
		$t_project_id = bug_get_field( $p_bug_id, 'project_id');
		$t_access_level = user_get_access_level( $t_user_id, $t_project_id );

		if ( $t_access_level >= plugin_config_get( 'view_threshold' ) ) {
			$t_scan_dir = plugin_config_get( 'scan_dir' );
			$t_scan_dir = $this->find_bug_dir( $t_scan_dir, $p_bug_id );
			if ( $t_scan_dir ) {
				$t_files = $this->do_scan_dir( $t_scan_dir );
			} else {
				$t_files = array();
			}

			$t_index = 0;
			$t_file_prefix = plugin_config_get( 'file_prefix' );
			foreach ( $t_files as $t_file_full => $t_file) {
				$t_attachment = array();
				$t_attachment['source'] = $this->plugin_id();
				$t_attachment['id'] = ++$t_index;
				$t_attachment['user_id'] = $t_user_id;
				$t_attachment['diskfile'] = $t_file_full;
				$t_attachment['filename'] = $t_file_prefix . str_replace( $t_scan_dir, '', $t_file_full );
				$t_attachment['title'] = '';
				$t_attachment['description'] = '';
				$t_attachment['filesize'] = filesize( $t_attachment['diskfile'] );
				$t_attachment['file_type'] = mime_content_type( $t_attachment['diskfile'] );
				$t_attachment['date_added'] = filemtime( $t_attachment['diskfile'] );
				$t_attachment['can_delete'] = false;
				$t_attachment['preview'] = '';
				$p_attachments[] = $t_attachment;
			}
		}
		return array( $p_bug_id, $p_attachments );
	}
}
