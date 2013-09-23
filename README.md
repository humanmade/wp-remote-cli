# WP Remote CLI

Manage your WordPress sites using WP Remote and WP-CLI.

**Note:** This is currently in beta. While we have a good sense of how it should work, we may rename methods, etc. as we refine it up until 1.0.

Until WP-CLI's [package installer](https://github.com/wp-cli/wp-cli/pull/602) is complete, there are a couple of ways you can use WP Remote CLI:

1. `cd ~/.wp-cli; composer require humanmade/wp-remote-cli`
1. Clone the Git repo somewhere and load it with a `wp-cli.yml` file.

Currently, you'll need to run these commands inside of an existing WordPress install. We plan to [eventually remove that dependency](https://github.com/humanmade/wp-remote-cli/issues/19).

Authentication is via API key or HTTP Basic Auth. Both of these can be defined inside of wp-config.php so you don't need to specify them each time.

Feel free to open an issue with any questions you might have!