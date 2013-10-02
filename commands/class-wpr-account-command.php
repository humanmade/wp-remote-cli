<?php
/**
 * Access your WP Remote account
 */
class WPR_Account_Command extends WP_Remote_Command {

	private $site_fields = array(
			'ID',
			'nicename',
			'home_url',
			'is_premium',
		);

	/**
	 * List all of the sites in your WP Remote account.
	 * 
	 * @subcommand list-sites
	 * @synopsis [--fields=<fields>] [--format=<format>]
	 */
	public function list_sites( $args, $assoc_args ) {

		$defaults = array(
				'fields'      => implode( ',', $this->site_fields ),
				'format'      => 'table',
			);
		$assoc_args = array_merge( $defaults, $assoc_args );

		$this->set_account();

		$args = array(
			'endpoint'     => '/site/',
			'method'       => 'GET', 
			);
		$response = $this->api_request( $args );
		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		$sites = array();
		foreach( $response as $response_site ) {
			$site_item = new stdClass;

			foreach( explode( ',', $assoc_args['fields'] ) as $field ) {
				if ( $field == 'is_premium' )
					$site_item->$field = ( $response_site->$field ) ? 'true' : 'false';
				else 
					$site_item->$field = $response_site->$field;
			}
			$site_items[] = $site_item;
		}

		WP_CLI\Utils\format_items( $assoc_args['format'], $site_items, $assoc_args['fields'] );
	}

	/**
	 * Add a site to WP Remote.
	 * 
	 * @subcommand add-site
	 * @synopsis <domain> <nicename>
	 */
	public function add_site( $args ) {

		list( $domain, $nicename ) = $args;

		$this->set_account();

		$args = array(
			'endpoint'     => '/site/',
			'method'       => 'POST',
			'body'         => array(
					'domain'   => $domain,
					'nicename' => $nicename,
				),
			);
		$response = $this->api_request( $args );
		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::success( "Site added." );
	}

	/**
	 * Delete a site on WP Remote.
	 * 
	 * @subcommand delete-site
	 * @synopsis <site-id>
	 */
	public function delete_site( $args ) {

		list( $site_id ) = $args;

		$this->set_account();

		$args = array(
			'endpoint'     => '/site/' . (int)$site_id . '/',
			'method'       => 'DELETE',
		);
		$response = $this->api_request( $args );
		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::success( "Site deleted." );
	}

	/**
	 * Mark a Site as Premium (requires active subscription)
	 * 
	 * @subcommand set-site-premium
	 * @synopsis --site-id=<site-id>
	 */
	public function set_site_premium( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		$this->set_account();

		$args = array(
			'endpoint'     => '/site/' . (int)$site_id . '/premium',
			'method'       => 'POST',
			);
		$response = $this->api_request( $args );
		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::success( "Site is now Premium." );

	}

	/**
	 * Remove Premium from a Site.
	 * 
	 * @subcommand remove-site-premium
	 * @synopsis --site-id=<site-id>
	 */
	public function remove_site_premium( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		$this->set_account();

		$args = array(
			'endpoint'     => '/site/' . (int)$site_id . '/premium',
			'method'       => 'DELETE',
			);
		$response = $this->api_request( $args );
		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::success( "Premium has been removed from Site." );

	}

}

WP_CLI::add_command( 'wpr-account', 'WPR_Account_Command' );