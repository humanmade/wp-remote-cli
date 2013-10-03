<?php
/**
 * Manage plugins on a remote WordPress site.
 */
class WP_Remote_Plugin_Command extends WP_Remote_Command {

	private $fields = array(
			'name',
			'slug',
			'status',
			'update',
			'version',
			'update_locked',
		);

	/**
	 * List all of the plugins installed on a given site.
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

		$this->list_plugins_or_themes_for_site( 'plugins', $site_id, $assoc_args );
	}

	/**
	 * Install a given plugin on the remote site.
	 *
	 * @subcommand install
	 * @synopsis <plugin-slug> --site-id=<site-id> [--version=<version>]
	 */
	public function install( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		list( $plugin_slug ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'plugin', 'install', $plugin_slug, $site_id, $assoc_args );
	}

	/**
	 * Activate a given plugin on the remote site.
	 *
	 * @subcommand activate
	 * @synopsis <plugin-slug> --site-id=<site-id>
	 */
	public function activate( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		list( $plugin_slug ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'plugin', 'activate', $plugin_slug, $site_id );
	}

	/**
	 * Deactivate a given plugin on the remote site.
	 *
	 * @subcommand deactivate
	 * @synopsis <plugin-slug> --site-id=<site-id>
	 */
	public function deactivate( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		list( $plugin_slug ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'plugin', 'deactivate', $plugin_slug, $site_id );
	}

	/**
	 * Update a given plugin on the remote site.
	 *
	 * @subcommand update
	 * @synopsis <plugin-slug> --site-id=<site-id>
	 */
	public function update( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		list( $plugin_slug ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'plugin', 'update', $plugin_slug, $site_id );
	}

	/**
	 * Uninstall a given plugin on the remote site.
	 *
	 * @subcommand uninstall
	 * @synopsis <plugin-slug> --site-id=<site-id>
	 */
	public function uninstall( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		list( $plugin_slug ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'plugin', 'uninstall', $plugin_slug, $site_id );
	}

	/**
	 * Lock updates on a given plugin for the remote site.
	 *
	 * @subcommand lock-update
	 * @synopsis <plugin-slug> --site-id=<site-id>
	 */
	public function lock_update( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		list( $plugin_slug ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'plugin', 'lock-update', $plugin_slug, $site_id );
	}

	/**
	 * Unlock updates on a given plugin for the remote site.
	 *
	 * @subcommand unlock-update
	 * @synopsis <plugin-slug> --site-id=<site-id>
	 */
	public function unlock_update( $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		list( $plugin_slug ) = $args;
		$this->perform_plugin_or_theme_action_for_site( 'plugin', 'unlock-update', $plugin_slug, $site_id );
	}

}

WP_CLI::add_command( 'remote-plugin', 'WP_Remote_Plugin_Command' );