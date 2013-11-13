<?php
/**
 * Manage comments for a remote site.
 */
class WP_Remote_Comment_Command extends WP_Remote_CRUD_Command {

	protected $obj_type = 'comment';

	protected $obj_fields = array(
		'comment_ID',
		'comment_post_ID',
		'comment_date',
		'comment_approved',
		'comment_author',
		'comment_author_email',
	);

	/**
	 * List all comments on a remote Site
	 * 
	 * @subcommand list
	 * @synopsis [--<field>=<value>] [--format=<format>] --site-id=<site-id>
	 */
	public function _list( $args, $assoc_args ) {
		$this->perform_item_action( 'list', $args, $assoc_args );
	}

	/**
	 * Get a comment on a remote Site.
	 *
	 * @subcommand get
	 * @synopsis <id> [--format=<format>] --site-id=<site-id>
	 */
	public function get( $args, $assoc_args ) {
		$this->perform_item_action( 'get', $args, $assoc_args );
	}

	/**
	 * Update a comment on a remote Site.
	 *
	 * @subcommand update
	 * @synopsis <id> [--<field>=<value>] --site-id=<site-id>
	 */
	public function update( $args, $assoc_args ) {
		$this->perform_item_action( 'update', $args, $assoc_args );
	}

	/**
	 * Delete a comment on a remote Site.
	 *
	 * @subcommand delete
	 * @synopsis <id> --site-id=<site-id>
	 */
	public function delete( $args, $assoc_args ) {
		$this->perform_item_action( 'delete', $args, $assoc_args );
	}

}

WP_CLI::add_command( 'remote-comment', 'WP_Remote_Comment_Command' );