<?php
/**
 * Manage WordPress core on a remote site.
 */
class WP_Remote_Core_Command extends WP_Remote_Command {

	/**
	 * Update WordPress core on a remote site.
	 * 
	 * @subcommand
	 * @synopsis --site-id=<site-id>
	 */
	public function update( $args, $assoc_args ) {
		$this->perform_action( 'update', $args, $assoc_args );
	}

	/**
	 * Lock core updates for a remote WordPress site.
	 * 
	 * @subcommand lock-update
	 * @synopsis --site-id=<site-id>
	 */
	public function lock_update( $args, $assoc_args ) {
		$this->perform_action( 'lock-update', $args, $assoc_args );
	}

	/**
	 * Unlock core updates for a remote WordPress site.
	 * 
	 * @subcommand unlock-update
	 * @synopsis --site-id=<site-id>
	 */
	public function unlock_update( $args, $assoc_args ) {
		$this->perform_action( 'lock-update', $args, $assoc_args, 'DELETE' );
	}

	/**
	 * Perform one of the WordPress core actions
	 */
	private function perform_action( $action, $args, $assoc_args, $method = 'POST' ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		$this->set_account();

		$args = array(
			'endpoint' => 'site/' . (int)$site_id . '/core/' . $action,
			'method'   => $method
			);
		$response = $this->api_request( $args );

		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );
		
		switch ( $action ) {
			case 'update':
				WP_CLI::success( "Core updated." );
				break;

			case 'lock-update':
				WP_CLI::success( "Core updates locked." );
				break;

			case 'unlock-update':
				WP_CLI::success( "Core updates unlocked." );
				break;
		}
	}

}

WP_CLI::add_command( 'remote-core', 'WP_Remote_Core_Command' );