<?php
/**
 * Manage users for a remote site.
 */
class WP_Remote_User_Command extends WP_Remote_CRUD_Command {

	protected $obj_type = 'user';

	protected $obj_fields = array(
		'ID',
		'user_login',
		'display_name',
		'user_email',
		'user_registered',
		'roles'
	);

	/**
	 * List remote users.
	 *
	 * ## OPTIONS
	 *
	 * [--<field>=<value>]
	 * : Filter by one or more fields. For accepted fields, see get_users().
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific object fields. Defaults to ID,user_login,display_name,user_email,user_registered,roles
	 *
	 * [--format=<format>]
	 * : Output list as table, CSV, JSON, or simply IDs. Defaults to table.
	 * 
	 * --site-id=<site-id>
	 * : Site to run the command on.
	 *
	 * @subcommand list
	 */
	public function _list( $args, $assoc_args ) {
		$this->perform_item_action( 'list', $args, $assoc_args );
	}

	/**
	 * Create a user on a remote Site.
	 *
	 * ## OPTIONS
	 *
	 * <user-login>
	 * : The login of the user to create.
	 *
	 * <user-email>
	 * : The email address of the user to create.
	 *
	 * [--role=<role>]
	 * : The role of the user to create. Default: default role
	 *
	 * [--user_pass=<password>]
	 * : The user password. Default: randomly generated
	 *
	 * [--user_registered=<yyyy-mm-dd>]
	 * : The date the user registered. Default: current date
	 *
	 * [--display_name=<name>]
	 * : The display name.
	 *
	 * [--porcelain]
	 * : Output just the new user id.
	 * 
	 * --site-id=<site-id>
	 * : Site to run the command on.
	 * 
	 * @subcommand create
	 */
	public function create( $args, $assoc_args ) {

		list( $user_login, $user_email ) = $args;

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		$defaults = array(
			'role'            => false,
			'user_pass'       => false,
			'user_registered' => false,
			'display_name'    => false,
		);
		$assoc_args = array_merge( $defaults, $assoc_args );

		$assoc_args['user_login'] = $user_login;
		$assoc_args['user_email'] = $user_email;

		$this->set_account();

		$args = array(
			'endpoint' => 'site/' . (int)$site_id . '/user',
			'method'   => 'POST',
			'body'     => $assoc_args,
			);
		$response = $this->api_request( $args );

		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );

		if ( isset( $assoc_args['porcelain'] ) ) {
			WP_CLI::line( $response->ID );
		} else {
			WP_CLI::success( "Created user {$response->ID}." );
			// Password was generated on remote site.
			if ( ! $assoc_args['user_pass'] )
				WP_CLI::line( "Password: {$response->user_pass}" );
		}
	}

	/**
	 * Update a User on a remote Site.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The ID of the user to update.
	 *
	 * --<field>=<value>
	 * : One or more fields to update. For accepted fields, see wp_update_user().
	 * 
	 * --site-id=<site-id>
	 * : Site to run the command on.
	 */
	public function update( $args, $assoc_args ) {
		$this->perform_item_action( 'update', $args, $assoc_args );
	}

	/**
	 * Get a single User on a remote Site.
	 *
	 * ## OPTIONS
	 *
	 * <user>
	 * : User ID or user login.
	 *
	 * [--field=<field>]
	 * : Instead of returning the whole user, returns the value of a single field.
	 *
	 * [--format=<format>]
	 * : The format to use when printing the user; acceptable values:
	 *
	 *     **table**: Outputs all fields of the user as a table.
	 *
	 *     **json**: Outputs all fields in JSON format.
	 * 
	 * --site-id=<site-id>
	 * : Site to run the command on.
	 */
	public function get( $args, $assoc_args ) {
		$this->perform_item_action( 'get', $args, $assoc_args );
	}

	/**
	 * Delete a User on a remote Site.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The ID of the user to delete.
	 * 
	 * --site-id=<site-id>
	 * : Site to run the command on.
	 */
	public function delete( $args, $assoc_args ) {
		$this->perform_item_action( 'delete', $args, $assoc_args );
	}

}

WP_CLI::add_command( 'remote-user', 'WP_Remote_User_Command' );