<?php
/**
 * Manage your WordPress sites using WP Remote.
 */
class WP_CLI_Remote_Command extends WP_CLI_Command {

	private $user;
	private $password;

	private $site_fields = array(
			'ID',
			'nicename',
			'home_url',
		);

	/**
	 * List all of the sites in your WP Remote account.
	 * 
	 * @subcommand site-list
	 * @synopsis [--fields=<fields>] [--format=<format>]
	 */
	public function site_list( $args, $assoc_args ) {

		$defaults = array(
				'fields'      => implode( ',', $this->site_fields ),
				'format'      => 'table',
			);
		$assoc_args = array_merge( $defaults, $assoc_args );

		$this->set_account();

		$args = array(
			'endpoint'     => '/sites/',
			'method'       => 'GET', 
			);
		$response = $this->api_request( $args );
		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		$sites = array();
		foreach( $response as $response_site ) {
			$site_item = new stdClass;

			foreach( explode( ',', $assoc_args['fields'] ) as $field ) {
				$site_item->$field = $response_site->$field;
			}
			$site_items[] = $site_item;
		}

		WP_CLI\Utils\format_items( $assoc_args['format'], $site_items, $assoc_args['fields'] );
	}

	/**
	 * Create a site on WP Remote.
	 * 
	 * @subcommand site-create
	 * @synopsis <domain> <nicename>
	 */
	public function site_create( $args ) {

		list( $domain, $nicename ) = $args;

		$this->set_account();

		$args = array(
			'endpoint'     => '/sites/',
			'method'       => 'POST',
			'body'         => array(
					'domain'   => $domain,
					'nicename' => $nicename,
				),
			);
		$response = $this->api_request( $args );
		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::success( "Site created." );
	}

	/**
	 * Delete a site on WP Remote.
	 * 
	 * @subcommand site-delete
	 * @synopsis <site-id>
	 */
	public function site_delete( $args ) {

		list( $id ) = $args;

		$this->set_account();

		$args = array(
			'endpoint'     => '/sites/' . $id . '/',
			'method'       => 'DELETE',
		);
		$response = $this->api_request( $args );
		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::success( "Site deleted." );
	}

	/**
	 * Set the WP Remote user account
	 */
	private function set_account() {

		if ( defined( 'WP_REMOTE_USER' ) )
			$this->user = WP_REMOTE_USER;
		else
			$this->user = $this->prompt( "What's the WP Remote user account?" );

		if ( defined( 'WP_REMOTE_PASSWORD' ) )
			$this->password = WP_REMOTE_PASSWORD;
		else
			$this->password = $this->prompt( "... and the password for the account?" );
	}

	/**
	 * Prompt for some input from the user
	 */
	private function prompt( $message ) {

		WP_CLI::out( trim( $message ) . " " );
		return trim( fgets( STDIN ) );
	}

	/**
	 * Make a call to the API.
	 */
	private function api_request( $assoc_args ) {

		if ( defined( 'WP_REMOTE_URL' ) )
			$this->api_url = rtrim( WP_REMOTE_URL, '/' ) . '/api/json';
		else
			$this->api_url = 'https://wpremote.com/api/json';

		$defaults = array(
			'endpoint'       => '',
			'method'         => 'GET',
			'headers'        => array(),
			'body'           => '',
			);
		$request_args = array_merge( $defaults, $assoc_args );

		if ( ! empty( $this->user ) && ! empty( $this->password ) ) {
			$request_args['headers']['Authorization'] = 'Basic ' . base64_encode( $this->user . ':' . $this->password );
		}

		$request_url = rtrim( $this->api_url, '/' ) . '/' . ltrim( $request_args['endpoint'], '/' );
		$response = wp_remote_request( $request_url, $request_args );

		// Something with the request failed
		if ( is_wp_error( $response ) )
			return $response;

		// Response was good
		if ( 200 == wp_remote_retrieve_response_code( $response ) )
			return json_decode( wp_remote_retrieve_body( $response ) );

		// Invalid user account
		else if ( 401 == wp_remote_retrieve_response_code( $response ) )
			return new WP_Error( 'WPR-401', 'Invalid account details.' );

		// Object or endpoint not found.
		else if ( 404 == wp_remote_retrieve_response_code( $response ) )
			return new WP_Error( 'WPR-404', 'Not found.' );

		// Catch-all
		else
			return new WP_Error( 'unknown', "An error occurred that we don't have code for. Please get in touch with WP Remote support or submit a pull request." );

	}


}
WP_CLI::add_command( 'remote', 'WP_CLI_Remote_Command' );