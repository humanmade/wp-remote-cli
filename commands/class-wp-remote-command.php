<?php
/**
 * Manage your remote WordPress site
 */
class WP_Remote_Command extends WP_CLI_Command {

	private $theme_fields = array(
			'name',
			'status',
			'update',
			'version',
		);

	/**
	 * Set the WP Remote user account
	 */
	protected function set_account() {

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
	 * Themes and plugins use roughly the same object model.
	 */
	protected function list_plugins_or_themes_for_site( $object, $site_id, $assoc_args ) {

		$this->set_account();

		$args = array(
			'endpoint'     => '/sites/' . $site_id . '/',
			'method'       => 'GET', 
			);
		$response = $this->api_request( $args );

		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		$items = array();
		foreach( $response->$object as $response_item ) {
			$item = new stdClass;

			$item->name = $response_item->name;
			if ( 'plugins' == $object )
				$item->slug = $response_item->slug;
			$item->status = ( $response_item->is_active ) ? 'active' : 'inactive';
			$item->update = ( version_compare( $response_item->latest_version, $response_item->version, '>' ) ) ? 'available' : 'none';
			$item->version = $response_item->version;

			$items[] = $item;
		}

		WP_CLI\Utils\format_items( $assoc_args['format'], $items, $assoc_args['fields'] );
	}

	/**
	 * Perform a plugin or theme action for a site.
	 * 
	 * @param string        $action     An action like 'install'
	 */
	protected function perform_plugin_or_theme_action_for_site( $object, $action, $name, $site_id, $assoc_args = array() ) {

		$this->set_account();

		$endpoint = array(
				'sites',
				$site_id,
				$object,
				$name,
				$action
			);

		$args = array(
			'endpoint'     => implode( '/', $endpoint ) . '/',
			'method'       => 'POST',
			'body'         => $assoc_args,
			);
		$response = $this->api_request( $args );
		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		$action_past_tense = rtrim( $action, 'e' ) . 'ed';
		WP_CLI::success( sprintf( "%s was %s.", ucwords( $object ), $action_past_tense ) );
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

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		// Response was good
		if ( 200 == $response_code ) {
			$response_body = json_decode( $response_body );
			// Maybe the API returned an error
			if ( isset( $response_body->status ) && 'error' == $response_body->status )
				return new WP_Error( $response_body->error_code, $response_body->error_message );
			else
				return $response_body;
		}

		// Invalid user account
		else if ( 401 == $response_code )
			return new WP_Error( 'WPR-401', 'Invalid account details.' );

		// Object or endpoint not found.
		else if ( 404 == $response_code )
			return new WP_Error( 'WPR-404', 'Not found.' );

		// Sometimes server exceptions have messages
		else if ( in_array( $response_code, array( 500, 501, 502, 503, 504, 505 ) ) && $response_body )
			return new WP_Error( 'WPR-' . $response_code, $response_body );

		// Catch-all
		else
			return new WP_Error( 'unknown', self::$unknown_error_message );

	}

}