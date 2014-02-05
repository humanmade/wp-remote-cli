<?php
/**
 * Manage themes on a remote WordPress site.
 */
class WP_Remote_Theme_Command extends WP_Remote_Command {

	private $fields = array(
			'name',
			'slug',
			'status',
			'update',
			'version',
			'update_locked',
		);

	/**
	 * List all of the themes installed on a given site.
	 * 
	 * @subcommand list
	 * @synopsis --site-id=<site-id> [--fields=<fields>] [--format=<format>]
	 */
	public function _list( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		$defaults = array(
				'fields'      => implode( ',', $this->fields ),
				'format'      => 'table',
			);
		$assoc_args = array_merge( $defaults, $assoc_args );

		$this->list_plugins_or_themes_for_site( 'themes', $site_id, $assoc_args );
	}

	/**
	 * Install a given theme on the remote site.
	 *
	 * @subcommand install
	 * @synopsis <theme> --site-id=<site-id> [--version=<version>]
	 */
	public function install( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		list( $theme_slug ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'theme', 'install', $theme_slug, $site_id, $assoc_args );
	}

	/**
	 * Activate a given theme on the remote site.
	 *
	 * @subcommand activate
	 * @synopsis <theme> --site-id=<site-id>
	 */
	public function activate( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		list( $theme_slug ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'theme', 'activate', $theme_slug, $site_id );
	}

	/**
	 * Delete a given theme on the remote site.
	 *
	 * @subcommand delete
	 * @synopsis <theme> --site-id=<site-id>
	 */
	public function delete( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		list( $theme_slug ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'theme', 'delete', $theme_slug, $site_id );
	}

	/**
	 * Update a given theme on the remote site.
	 *
	 * @subcommand update
	 * @synopsis <theme> --site-id=<site-id>
	 */
	public function update( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		list( $theme_slug ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'theme', 'update', $theme_slug, $site_id );
	}

	/**
	 * Lock updates on a given theme for the remote site.
	 *
	 * @subcommand lock-update
	 * @synopsis <theme> --site-id=<site-id>
	 */
	public function lock_update( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		list( $theme_slug ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'theme', 'lock-update', $theme_slug, $site_id );
	}

	/**
	 * Unlock updates on a given theme for the remote site.
	 *
	 * @subcommand unlock-update
	 * @synopsis <theme> --site-id=<site-id>
	 */
	public function unlock_update( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		list( $theme_slug ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'theme', 'unlock-update', $theme_slug, $site_id );
	}

}

WP_CLI::add_command( 'remote theme', 'WP_Remote_Theme_Command' );
