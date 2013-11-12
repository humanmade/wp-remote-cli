<?php
/**
 * Access a remote WordPress site.
 */
class WP_Remote_Site_Command extends WP_Remote_Command {

	private $history_fields = array(
			'date',
			'type',
			'action',
			'description',
		);

	/**
	 * View the history for a given site.
	 *
	 * @subcommand list-history
	 * @synopsis [--site-id=<site-id>] [--type=<type>] [--action=<action>] [--start-date=<start-date>] [--end-date=<end-date>] [--per-page=<per-page>] [--page=<page>] [--format=<format>]
	 */
	public function list_history( $args, $assoc_args ) {

		if ( empty( $assoc_args['site-id'] ) || ( count( explode( ',', $assoc_args['site-id'] ) ) != 1 ) )
			array_unshift( $this->history_fields, 'site_name' );

		$defaults = array(
				'per-page'    => 10,
				'page'        => 1,
				'type'        => '',
				'action'      => '',
				'start-date'  => false,
				'end-date'    => false,
				'fields'      => implode( ',', $this->history_fields ),
				'format'      => 'table',
			);
		$assoc_args = array_merge( $defaults, $assoc_args );

		$this->set_account();

		$args = array(
			'endpoint'     => '/site/history/',
			'method'       => 'GET',
			'body'         => array(
				'per_page' => (int)$assoc_args['per-page'],
				'page'     => (int)$assoc_args['page'],
				'type'     => $assoc_args['type'],
				'action'   => $assoc_args['action'],
				'start_timestamp' => ( $assoc_args['start-date'] ) ? strtotime( $assoc_args['start-date'] ) : '',
				'end_timestamp'   => ( $assoc_args['end-date'] ) ? strtotime( $assoc_args['end-date'] ) : '',
				),
			);

		if ( ! empty( $assoc_args['site-id'] ) )
			$args['body']['site_ids'] = $assoc_args['site-id'];	

		$response = $this->api_request( $args );
		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		$site_history_items = array();
		foreach( $response as $response_history_item ) {
			$site_history_item = new stdClass;

			foreach( explode( ',', $assoc_args['fields'] ) as $field ) {
				$site_history_item->$field = $response_history_item->$field;
			}

			// 'description' sometimes has HTML
			$site_history_item->description = strip_tags( $site_history_item->description );

			// 'date' is already delivered as a timestamp
			$site_history_item->date = date( 'Y-m-d H:i:s', $site_history_item->date ) . ' GMT';

			if ( $continue )
				continue;

			$site_history_items[] = $site_history_item;
		}

		WP_CLI\Utils\format_items( $assoc_args['format'], $site_history_items, $assoc_args['fields'] );
	}

	/**
	 * Refresh the details for the remote site
	 * 
	 * @subcommand refresh
	 * @synopsis --site-id=<site-id>
	 */
	public function refresh( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		$this->set_account();

		$args = array(
			'endpoint'     => '/site/' . (int)$site_id . '/refresh-data',
			'method'       => 'POST',
			);
		$response = $this->api_request( $args );
		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::success( "Site refreshed." );
	}

	/**
	 * Lock all updates for the remote site.
	 * 
	 * @subcommand lock-update
	 * @synopsis --site-id=<site-id>
	 */
	public function lock_update( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		$this->set_account();

		$args = array(
			'endpoint'     => '/site/' . (int)$site_id . '/lock',
			'method'       => 'POST',
			);
		$response = $this->api_request( $args );
		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::success( "All updates are locked for Site." );
	}

	/**
	 * Permit updates to be performed on Site.
	 * 
	 * @subcommand unlock-update
	 * @synopsis --site-id=<site-id>
	 */
	public function unlock_update( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		$this->set_account();

		$args = array(
			'endpoint'     => '/site/' . (int)$site_id . '/lock',
			'method'       => 'DELETE',
			);
		$response = $this->api_request( $args );
		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::success( "Updates can be performed for Site." );
	}

	/**
	 * Download a remote site.
	 * 
	 * @subcommand download
	 * @synopsis --site-id=<site-id> [--type=<type>]
	 */
	public function download( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		$this->set_account();

		if ( empty( $assoc_args['type'] ) )
			$assoc_args['type'] = 'complete';

		WP_CLI::line( sprintf( "Initiating %s archive.", $assoc_args['type'] ) );

		$args = array(
			'endpoint'     => '/site/' . $site_id . '/download',
			'method'       => 'POST',
			'body'         => array(
				'type'     => $assoc_args['type'],
				),
			);

		$response = $this->api_request( $args );

		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		do {

			$args = array(
			'endpoint'     => '/site/' . $site_id . '/download',
			'method'       => 'GET',
			);

			$response = $this->api_request( $args );

			if ( ! is_wp_error( $response ) 
				&& ! empty( $response->status )
				&& $response->status == 'error-status' ) {
				WP_Cli::line( 'Backup status: ' . strip_tags( $response->backup_progress ) );
				sleep( 15 );
			}

		} while ( ! is_wp_error( $response ) && $response->status != 'backup-complete' );

		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::launch( sprintf( "wget '%s'", $response->url ) );

		WP_CLI::success( "Site downloaded." );
	}

}

WP_CLI::add_command( 'remote-site', 'WP_Remote_Site_Command' );