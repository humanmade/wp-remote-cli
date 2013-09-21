<?php
/**
 * Access your WP Remote account
 */
class WPR_Account_Command extends WP_Remote_Command {

	private $site_fields = array(
			'ID',
			'nicename',
			'home_url',
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

}

WP_CLI::add_command( 'wpr-account', 'WPR_Account_Command' );