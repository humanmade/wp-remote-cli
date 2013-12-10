<?php
/**
 * Interact with webhooks registered on WP Remote
 */

class WP_Remote_Webhook_Command extends WP_Remote_Command {

	private $fields = array(
			'id',
			'url',
		);

	/**
	 * List the webhooks for a given site or account
	 *
	 * @subcommand list
	 * @synopsis [--site-id=<site-id>] [--<field>=<value>] [--format=<format>]
	 */
	public function _list( $args, $assoc_args ) {

		$defaults = array(
				'fields'      => implode( ',', $this->fields ),
				'format'      => 'table',
			);
		$assoc_args = array_merge( $defaults, $assoc_args );

		$this->set_account();

		$site_id = isset( $assoc_args['site-id'] ) ? $assoc_args['site-id'] : false;
		unset( $assoc_args['site-id'] );

		if ( ! empty( $site_id ) )
			$endpoint = '/site/' . $site_id . '/webhook';
		else
			$endpoint = '/account/webhook';

		$args = array(
			'endpoint'     => $endpoint,
			'method'       => 'GET',
			);
		$response = $this->api_request( $args );
		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI\Utils\format_items( $assoc_args['format'], $response, $assoc_args['fields'] );

	}

	/**
	 * Get a specific webhook for a given site or account
	 * 
	 * @synopsis <webhook-id> [--site-id=<site-id>] [--<field>=<value>] [--format=<format>]
	 */
	public function get( $args, $assoc_args ) {
		
		$webhook_id = $args[0];

		$defaults = array(
				'fields'      => implode( ',', $this->fields ),
				'format'      => 'table',
			);
		$assoc_args = array_merge( $defaults, $assoc_args );

		$this->set_account();

		$site_id = isset( $assoc_args['site-id'] ) ? $assoc_args['site-id'] : false;
		unset( $assoc_args['site-id'] );

		if ( ! empty( $site_id ) )
			$endpoint = '/site/' . $site_id . '/webhook/' . $webhook_id;
		else
			$endpoint = '/account/webhook/' . $webhook_id;

		$args = array(
			'endpoint'     => $endpoint,
			'method'       => 'GET',
			);
		$webhook = $this->api_request( $args );

		if ( is_wp_error( $webhook ) )
			WP_CLI::error( $webhook->get_error_message() );

		WP_CLI\Utils\format_items( $assoc_args['format'], array( $webhook ), $assoc_args['fields'] );
	}

	/**
	 * Add a webhook to a given account or site
	 * 
	 * @synopsis <url> [--site-id=<site-id>] [--<field>=<value>] [--format=<format>]
	 */
	public function create( $args, $assoc_args ) {
		
		$url = $args[0];

		$defaults = array(
				'fields'      => implode( ',', $this->fields ),
				'format'      => 'table',
			);
		$assoc_args = array_merge( $defaults, $assoc_args );

		$this->set_account();

		$site_id = isset( $assoc_args['site-id'] ) ? $assoc_args['site-id'] : false;
		unset( $assoc_args['site-id'] );

		if ( ! empty( $site_id ) )
			$endpoint = '/site/' . $site_id . '/webhook';
		else
			$endpoint = '/account/webhook';

		$args = array(
			'endpoint'     => $endpoint,
			'method'       => 'POST',
			'body'         => array(
				'url'      => $url,
				),
			);
		$response = $this->api_request( $args );

		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::success( "Created webhook." );
	}

	/**
	 * Delete a given webhook for a given account or site
	 * 
	 * @synopsis <webhook-id> [--site-id=<site-id>]
	 */
	public function delete( $args, $assoc_args ) {

		$webhook_id = $args[0];

		$this->set_account();

		$site_id = isset( $assoc_args['site-id'] ) ? $assoc_args['site-id'] : false;
		unset( $assoc_args['site-id'] );

		if ( ! empty( $site_id ) )
			$endpoint = '/site/' . $site_id . '/webhook/' . $webhook_id;
		else
			$endpoint = '/account/webhook/' . $webhook_id;

		$args = array(
			'endpoint'     => $endpoint,
			'method'       => 'DELETE',
			);

		$response = $this->api_request( $args );

		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		WP_CLI::success( 'Deleted webhook ' . $webhook_id );
	}

}

WP_CLI::add_command( 'remote-webhook', 'WP_Remote_Webhook_Command' );