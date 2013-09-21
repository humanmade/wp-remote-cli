<?php
/**
 * Manage backups on a remote WordPress site.
 */
class WP_Remote_Backup_Command extends WP_Remote_Command {

	private $fields = array(
			'id',
			'filesize',
			'date',
			'url',
		);

	/**
	 * List the backups for a given site.
	 *
	 * @subcommand list
	 * @synopsis --site-id=<site-id> [--<field>=<value>] [--format=<format>]
	 */
	public function _list( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		$defaults = array(
				'fields'      => implode( ',', $this->fields ),
				'format'      => 'table',
			);
		$assoc_args = array_merge( $defaults, $assoc_args );

		$this->set_account();

		$args = array(
			'endpoint'     => '/sites/' . $site_id . '/backup',
			'method'       => 'GET',
			);
		$response = $this->api_request( $args );
		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		$backups = array();

		foreach( $response as $backup ) {
			$backup_object = new stdClass;

			foreach( explode( ',', $assoc_args['fields'] ) as $field ) {
				$backup_object->$field = $backup->$field;
			}

			// 'date' is already delivered as a timestamp
			$backup_object->date = date( 'Y-m-d H:i:s', $backup_object->date ) . ' GMT';

			$backup_object->url = $backup_object->url;

			// Allow filtering based on field
			$continue = false;
			foreach( $this->fields as $backup_field ) {
				if ( isset( $assoc_args[$backup_field] ) 
					&& $backup->$backup_field != $assoc_args[$backup_field] )
					$continue = true;
			}

			if ( $continue )
				continue;

			$backups[] = $backup_object;
		}

		WP_CLI\Utils\format_items( $assoc_args['format'], $backups, $assoc_args['fields'] );

	}

	/**
	 * Get a specific backup for a given site
	 * 
	 * @synopsis <backup-id> --site-id=<site-id> [--<field>=<value>] [--format=<format>]
	 */
	public function get( $args, $assoc_args ) {
		$site_id = $assoc_args['site-id'];
		$backup_id = $args[0];

		unset( $assoc_args['site-id'] );

		$defaults = array(
				'fields'      => implode( ',', $this->fields ),
				'format'      => 'table',
			);
		$assoc_args = array_merge( $defaults, $assoc_args );

		$this->set_account();

		$args = array(
			'endpoint'     => '/sites/' . $site_id . '/backup/' . $backup_id,
			'method'       => 'GET',
			);
		$backup = $this->api_request( $args );

		if ( is_wp_error( $backup ) )
			WP_CLI::error( $backup->get_error_message() );

		$backup_object = new stdClass;

		foreach( explode( ',', $assoc_args['fields'] ) as $field ) {
			$backup_object->$field = $backup->$field;
		}

		// 'date' is already delivered as a timestamp
		$backup_object->date = date( 'Y-m-d H:i:s', $backup_object->date ) . ' GMT';
		$backup_object->url = $backup_object->url;

		WP_CLI\Utils\format_items( $assoc_args['format'], array( $backup_object ), $assoc_args['fields'] );
	}

	/**
	 * Delete a given backup for a given site
	 * 
	 * @synopsis <backup-id> --site-id=<site-id>
	 */
	public function delete( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		$backup_id = $args[0];

		$this->set_account();

		$args = array(
			'endpoint'     => '/sites/' . $site_id . '/backup/' . $backup_id,
			'method'       => 'DELETE',
			);

		$response = $this->api_request( $args );

		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::success( 'Deleted backup ' . $backup_id );
	}

	/**
	 * Initiate a download of the a site, use "download" once complete.
	 * 
	 * @subcommand initiate-download
	 * @synopsis --site-id=<site-id>
	 */
	public function initiate_download( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		$this->set_account();

		$args = array(
			'endpoint'     => '/sites/' . $site_id . '/backup/initiate-download',
			'method'       => 'POST',
			);

		$response = $this->api_request( $args );

		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::success( 'Initiated download of site, run "download" to get the status' );

	}

	/**
	 * Initiate a download of the a site, use "download" once complete.
	 * 
	 * @synopsis --site-id=<site-id>
	 */
	public function download( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		$this->set_account();

		$args = array(
			'endpoint'     => '/sites/' . $site_id . '/backup/download',
			'method'       => 'GET',
			);

		$response = $this->api_request( $args );

		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		if ( $response->status === 'backup-complete' )
			WP_Cli::success( $response->url );

		else
			WP_Cli::error( 'Backup status: ' . $response->status );
	}

	/**
	 * List the exclude rules for backups
	 * 
	 * @subcommand list-excludes
	 * @synopsis --site-id=<site-id> [--format=<format>]
	 */
	public function list_excludes( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		$this->set_account();

		unset( $assoc_args['site-id'] );

		$defaults = array(
			'format'      => 'table',
		);

		$assoc_args = array_merge( $defaults, $assoc_args );

		$args = array(
			'endpoint'     => '/sites/' . $site_id . '/backup/exclude',
			'method'       => 'GET',
		);

		$response = $this->api_request( $args );

		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		$rules = array();

		foreach ( $response as $rule ) {
			$rules[] = (object) array( 'Rule' => $rule );
		}

		WP_CLI\Utils\format_items( $assoc_args['format'], $rules, array( 'Rule') );
	}

	/**
	 * Set the exclude rules for backups, comma seperated if multiple
	 * 
	 * @subcommand set-excludes
	 * @synopsis <rules> --site-id=<site-id>
	 */
	public function set_excludes( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		$this->set_account();

		$rules = explode( ',', $args[0] );

		$args = array(
			'endpoint'     => '/sites/' . $site_id . '/backup/exclude',
			'method'       => 'POST',
			'body'         => array( 'rules' => $rules )
		);

		$response = $this->api_request( $args );

		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::success( 'Updated backup exclude rules.' );
	}

	/**
	 * Enable automatic backups on a given site
	 * 
	 * @subcommand enable-auto-backups
	 * @synopsis --site-id=<site-id>
	 */
	public function enable_auto_backups( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		$this->set_account();

		$rules = explode( ',', $args[0] );

		$args = array(
			'endpoint'     => '/sites/' . $site_id . '/backup/enable-auto-backup',
			'method'       => 'POST',
		);

		$response = $this->api_request( $args );

		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::success( 'Enabled automatic backups.' );
	}

	/**
	 * Disable automatic backups on a given site
	 * 
	 * @subcommand disable-auto-backups
	 * @synopsis --site-id=<site-id>
	 */
	public function disable_auto_backups( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		$this->set_account();

		$rules = explode( ',', $args[0] );

		$args = array(
			'endpoint'     => '/sites/' . $site_id . '/backup/disable-auto-backup',
			'method'       => 'POST',
		);

		$response = $this->api_request( $args );

		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::success( 'Disabled automatic backups.' );
	}
}

WP_CLI::add_command( 'remote-backup', 'WP_Remote_Backup_Command' );