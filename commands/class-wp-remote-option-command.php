<?php
/**
 * Manage options for a remote site.
 */
class WP_Remote_Option_Command extends WP_Remote_Command {

	/**
	 * Get an option on a remote Site.
	 *
	 * @subcommand get
	 * @synopsis <option-name> [--format=<format>] --site-id=<site-id>
	 */
	public function get( $args, $assoc_args ) {
		$this->perform_option_action( 'get', $args, $assoc_args );
	}

	/**
	 * Update an option on a remote Site.
	 *
	 * @subcommand Update
	 * @synopsis <option-name> --site-id=<site-id>
	 */
	public function update( $args, $assoc_args ) {
		$this->perform_option_action( 'update', $args, $assoc_args );
	}

	/**
	 * Delete an option on a remote Site.
	 *
	 * @subcommand delete
	 * @synopsis <option-name> --site-id=<site-id>
	 */
	public function delete( $args, $assoc_args ) {
		$this->perform_option_action( 'delete', $args, $assoc_args );
	}

	/**
	 * Perform an option action on a remote site
	 */
	private function perform_option_action( $action, $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		list( $option_name ) = $args;

		$this->set_account();

		$method = strtoupper( $action );
		if ( 'update' == $action ) {
			$method = 'POST';
			$api_args = array(
				'option_value' => WP_CLI::read_value( $args[1], $assoc_args ),
			);
		} else {
			$api_args = array();
		}

		$args = array(
			'endpoint' => 'site/' . (int)$site_id . '/option/' . $option_name,
			'method'   => $method,
			'body'     => $api_args,
			);
		$response = $this->api_request( $args );

		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );
		
		switch ( $action ) {
			case 'get':

				if ( empty( $response ) )
					die(1);

				WP_CLI::print_value( $response, $assoc_args );

				break;

			case 'update':

				WP_CLI::success( "Updated '$option_name' option." );

				break;

			case 'delete':

				WP_CLI::success( "Deleted '$option_name' option." );

				break;
		}
	}

}

WP_CLI::add_command( 'remote-option', 'WP_Remote_Option_Command' );