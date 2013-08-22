<?php
/**
 * Manage your WordPress sites using WP Remote.
 */
class WP_CLI_Remote_Command extends WP_CLI_Command {

	private $user;
	private $password;

	private $plugin_fields = array(
			'name',
			'status',
			'update',
			'version',
		);

	private $site_fields = array(
			'ID',
			'nicename',
			'home_url',
		);

	static $unknown_error_message = "An error occurred that we don't have code for. Please get in touch with WP Remote support or submit a pull request.";

	/**
	 * Install a given plugin on a given site.
	 *
	 * @subcommand plugin-install
	 * @synopsis <site-id> <plugin-name> [--version=<version>] [--activate]
	 */
	public function plugin_install( $args, $assoc_args ) {

		list( $site_id, $plugin_name ) = $args;

		$defaults = array(
				'version' => '',
				'activate' => false,
			);
		

	}

	/**
	 * List all of the plugins installed on a given site.
	 * 
	 * @subcommand plugin-list
	 * @synopsis <site-id> [--fields=<fields>] [--format=<format>]
	 */
	public function plugin_list( $args, $assoc_args ) {

		list( $site_id ) = $args;

		$defaults = array(
				'fields'      => implode( ',', $this->plugin_fields ),
				'format'      => 'table',
			);
		$assoc_args = array_merge( $defaults, $assoc_args );

		$this->list_plugins_or_themes_for_site( 'plugins', $site_id, $assoc_args );
	}

	/**
	 * Install a given plugin on a given site.
	 *
	 * @subcommand plugin-install
	 * @synopsis <site-id> <plugin-name> [--version=<version>]
	 */
	public function plugin_install( $args, $assoc_args ) {

		list( $site_id, $plugin_name ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'plugin', 'install', $plugin_name, $site_id, $assoc_args );
	}

	/**
	 * Activate a given plugin on a given site.
	 *
	 * @subcommand plugin-activate
	 * @synopsis <site-id> <plugin-name>
	 */
	public function plugin_activate( $args ) {

		list( $site_id, $plugin_name ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'plugin', 'activate', $plugin_name, $site_id );
	}

	/**
	 * Deactivate a given plugin on a given site.
	 *
	 * @subcommand plugin-deactivate
	 * @synopsis <site-id> <plugin-name>
	 */
	public function plugin_deactivate( $args ) {

		list( $site_id, $plugin_name ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'plugin', 'deactivate', $plugin_name, $site_id );
	}

	/**
	 * Update a given plugin on a given site.
	 *
	 * @subcommand plugin-update
	 * @synopsis <site-id> <plugin-name>
	 */
	public function plugin_update( $args ) {

		list( $site_id, $plugin_name ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'plugin', 'update', $plugin_name, $site_id );
	}

	/**
	 * Uninstall a given plugin on a given site.
	 *
	 * @subcommand plugin-uninstall
	 * @synopsis <site-id> <plugin-name>
	 */
	public function plugin_uninstall( $args, $assoc_args ) {

		list( $site_id, $plugin_name ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'plugin', 'uninstall', $plugin_name, $site_id );
	}

	/**
	 * List all of the themes installed on a given site.
	 * 
	 * @subcommand theme-list
	 * @synopsis <site-id> [--fields=<fields>] [--format=<format>]
	 */
	public function theme_list( $args, $assoc_args ) {

		list( $site_id ) = $args;

		$defaults = array(
				'fields'      => implode( ',', $this->plugin_fields ),
				'format'      => 'table',
			);
		$assoc_args = array_merge( $defaults, $assoc_args );

		$this->list_plugins_or_themes_for_site( 'themes', $site_id, $assoc_args );
	}

	/**
	 * Install a given theme on a given site.
	 *
	 * @subcommand theme-install
	 * @synopsis <site-id> <theme-name> [--version=<version>]
	 */
	public function theme_install( $args, $assoc_args ) {

		list( $site_id, $theme_name ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'theme', 'install', $theme_name, $site_id, $assoc_args );
	}

	/**
	 * Activate a given theme on a given site.
	 *
	 * @subcommand theme-activate
	 * @synopsis <site-id> <theme-name>
	 */
	public function theme_activate( $args ) {

		list( $site_id, $theme_name ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'theme', 'activate', $theme_name, $site_id );
	}

	/**
	 * Update a given theme on a given site.
	 *
	 * @subcommand theme-update
	 * @synopsis <site-id> <theme-name>
	 */
	public function theme_update( $args ) {

		list( $site_id, $theme_name ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'theme', 'update', $theme_name, $site_id );
	}

	/**
	 * Delete a given theme on a given site.
	 *
	 * @subcommand theme-delete
	 * @synopsis <site-id> <theme-name>
	 */
	public function theme_delete( $args, $assoc_args ) {

		list( $site_id, $theme_name ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'theme', 'delete', $theme_name, $site_id );
	}

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
	 * Refresh the details for a given site.
	 * 
	 * @subcommand site-refresh
	 * @synopsis <site-id>
	 */
	public function site_refresh( $args ) {

		list( $site_id ) = $args;

		$this->set_account();

		$args = array(
			'endpoint'     => '/sites/' . $site_id . '/refresh_data',
			'method'       => 'POST',
			);
		$response = $this->api_request( $args );
		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::success( "Site refreshed." );
	}


	/**
	 * Create a site on WP Remote.
	 * 
	 * @subcommand site-add
	 * @synopsis <domain> <nicename>
	 */
	public function site_add( $args ) {

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

		if ( 'success' == $response->status ) {
			WP_CLI::success( "Site created." );
		} else if ( 'error' == $response->status ) {
			WP_CLI::error( $response->error_message );
		} else {
			WP_CLI::error( self::$unknown_error_message );
		}
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
	 * Themes and plugins use roughly the same object model.
	 */
	private function list_plugins_or_themes_for_site( $object, $site_id, $assoc_args ) {

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
	private function perform_plugin_or_theme_action_for_site( $object, $action, $name, $site_id, $assoc_args = array() ) {

		$this->set_account();

		$endpoint = array(
				'sites',
				$site_id,
				$object,
				$name,
				$action
			);

		$args = array(
			'endpoint'     => '/' . implode( '/', $endpoint ) . '/',
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
		if ( 200 == wp_remote_retrieve_response_code( $response ) ) {
			$response_body = json_decode( wp_remote_retrieve_body( $response ) );
			// Maybe the API returned an error
			if ( isset( $response_body->status ) && 'error' == $response_body->status )
				return new WP_Error( $response_body->error_code, $response_body->error_message );
			else
				return $response_body;
		}

		// Invalid user account
		else if ( 401 == wp_remote_retrieve_response_code( $response ) )
			return new WP_Error( 'WPR-401', 'Invalid account details.' );

		// Object or endpoint not found.
		else if ( 404 == wp_remote_retrieve_response_code( $response ) )
			return new WP_Error( 'WPR-404', 'Not found.' );

		// Catch-all
		else
			return new WP_Error( 'unknown', self::$unknown_error_message );

	}


}
WP_CLI::add_command( 'remote', 'WP_CLI_Remote_Command' );