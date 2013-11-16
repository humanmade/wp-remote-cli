<?php
/**
 * Manage posts for a remote site.
 */
class WP_Remote_Post_Command extends WP_Remote_CRUD_Object_Command {

	protected $obj_type = 'post';

	protected $obj_fields = array(
		'ID',
		'post_title',
		'post_name',
		'post_date',
		'post_status'
	);

	/**
	 * List all posts on a remote Site
	 * 
	 * @subcommand list
	 * @synopsis [--<field>=<value>] [--format=<format>] --site-id=<site-id>
	 */
	public function _list( $args, $assoc_args ) {
		$this->perform_item_action( 'list', $args, $assoc_args );
	}

	/**
	 * Get a post on a remote Site.
	 *
	 * @subcommand get
	 * @synopsis <id> [--format=<format>] --site-id=<site-id>
	 */
	public function get( $args, $assoc_args ) {
		$this->perform_item_action( 'get', $args, $assoc_args );
	}

	/**
	 * Create a post on a remote Site.
	 *
	 * @subcommand create
	 * @synopsis --<field>=<value> --site-id=<site-id>
	 */
	public function create( $args, $assoc_args ) {
		$this->perform_item_action( 'create', $args, $assoc_args );
	}

	/**
	 * Update a post on a remote Site.
	 *
	 * @subcommand update
	 * @synopsis <id> [--<field>=<value>] --site-id=<site-id>
	 */
	public function update( $args, $assoc_args ) {
		$this->perform_item_action( 'update', $args, $assoc_args );
	}

	/**
	 * Delete a post on a remote Site.
	 *
	 * @subcommand delete
	 * @synopsis <id> --site-id=<site-id>
	 */
	public function delete( $args, $assoc_args ) {
		$this->perform_item_action( 'delete', $args, $assoc_args );
	}

}

WP_CLI::add_command( 'remote-post', 'WP_Remote_Post_Command' );