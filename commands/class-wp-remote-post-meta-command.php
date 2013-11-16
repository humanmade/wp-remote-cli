<?php
/**
 * Manage post meta for a remote site.
 */
class WP_Remote_Post_Meta_Command extends WP_Remote_CRUD_Meta_Command {

	protected $obj_type = 'post';

	/**
	 * List meta for a given post on a remote Site.
	 *
	 * @subcommand list
	 * @synopsis <id> [--format=<format>] --site-id=<site-id>
	 */
	public function _list( $args, $assoc_args ) {
		$this->perform_meta_action( 'list', $args, $assoc_args );
	}

	/**
	 * Get a post meta on a remote Site.
	 *
	 * @subcommand get
	 * @synopsis <id> <key> [--format=<format>] --site-id=<site-id>
	 */
	public function get( $args, $assoc_args ) {
		$this->perform_meta_action( 'get', $args, $assoc_args );
	}

	/**
	 * Add post meta on a remote Site.
	 *
	 * @subcommand add
	 * @synopsis <id> <key> <value> --site-id=<site-id>
	 */
	public function add( $args, $assoc_args ) {
		$this->perform_meta_action( 'add', $args, $assoc_args );
	}

	/**
	 * Update post on a remote Site.
	 *
	 * @subcommand update
	 * @synopsis <id> <key> <value> --site-id=<site-id>
	 */
	public function update( $args, $assoc_args ) {
		$this->perform_meta_action( 'update', $args, $assoc_args );
	}

	/**
	 * Delete a post on a remote Site.
	 *
	 * @subcommand delete
	 * @synopsis <id> <key> --site-id=<site-id>
	 */
	public function delete( $args, $assoc_args ) {
		$this->perform_meta_action( 'delete', $args, $assoc_args );
	}

}

WP_CLI::add_command( 'remote-post-meta', 'WP_Remote_Post_Meta_Command' );