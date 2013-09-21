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
	 * @synopsis --site-id=<site-id> [--<field>=<value>] [--format=<format>]
	 */
	public function list_history( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		$defaults = array(
				'fields'      => implode( ',', $this->history_fields ),
				'format'      => 'table',
			);
		$assoc_args = array_merge( $defaults, $assoc_args );

		$this->set_account();

		$args = array(
			'endpoint'     => '/sites/' . $site_id . '/log/',
			'method'       => 'GET',
			);
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

			// Allow filtering based on field
			$continue = false;
			foreach( $this->history_fields as $site_history_field ) {
				if ( isset( $assoc_args[$site_history_field] ) 
					&& $response_history_item->$site_history_field != $assoc_args[$site_history_field] )
					$continue = true;
			}

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
	public function site_refresh( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		$this->set_account();

		$args = array(
			'endpoint'     => '/sites/' . (int)$site_id . '/refresh_data',
			'method'       => 'POST',
			);
		$response = $this->api_request( $args );
		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::success( "Site refreshed." );
	}	

}

WP_CLI::add_command( 'remote-site', 'WP_Remote_Site_Command' );