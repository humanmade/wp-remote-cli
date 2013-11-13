<?php
/**
 * Create, Read, Update, and Delete items on a remote site.
 */
class WP_Remote_CRUD_Command extends WP_Remote_Command {

	/**
	 * Perform a action on an item on a remote site
	 */
	protected function perform_item_action( $action, $args, $assoc_args ) {

		$site_id = $assoc_args['site-id'];
		unset( $assoc_args['site-id'] );

		$this->set_account();

		// 'format' and 'fields' are present in a variety of requests
		if ( isset( $assoc_args['format'] ) ) {
			$format = $assoc_args['format'];
			unset( $assoc_args['format'] );
		} else {
			$format = 'table';
		}

		if ( isset( $assoc_args['fields'] ) ) {
			$fields = $assoc_args['fields'];
			unset( $assoc_args['fields'] );
		} else {
			$fields = implode( ',', $this->obj_fields );
		}

		switch ( $action ) {

			case 'list':

				$endpoint = 'site/' . (int)$site_id . '/' . $this->obj_type;
				$method = 'GET';
				$api_args = $assoc_args;
			
				break;

			case 'get':

				list( $obj_id ) = $args;

				$endpoint = 'site/' . (int)$site_id . '/' . $this->obj_type . '/' . $obj_id;
				$method = 'GET';
				$api_args = $assoc_args;

			case 'update':

				list( $obj_id ) = $args;

				$endpoint = 'site/' . (int)$site_id . '/' . $this->obj_type . '/' . $obj_id;
				$method = 'POST';
				$api_args = $assoc_args;

				break;

			case 'delete':

				list( $obj_id ) = $args;

				$endpoint = 'site/' . (int)$site_id . '/' . $this->obj_type . '/' . $obj_id;
				$method = 'DELETE';
				$api_args = $assoc_args;

				break;
			
		}

		$args = array(
			'endpoint' => $endpoint,
			'method'   => $method,
			'body'     => $api_args,
			);
		$response = $this->api_request( $args );

		if ( is_wp_error( $response ) )
			WP_CLI::error( $response->get_error_message() );
		
		switch ( $action ) {

			case 'list':

				$it = WP_CLI\Utils\iterator_map( $response, function ( $item ) {
					if ( !is_object( $item ) )
						return $item;

					return $item;
				} );

				WP_CLI\Utils\format_items( $params['format'], $it, $fields );

				break;

			case 'get':

				$this->show_multiple_fields( $response, $assoc_args );

				break;

			case 'update':

				WP_CLI::success( "Updated {$this->obj_type}." );

				break;

			case 'delete':

				WP_CLI::success( "Deleted {$this->obj_type}." );

				break;

		}

	}

	protected function show_multiple_fields( $obj_data, $assoc_args ) {

		switch ( $assoc_args['format'] ) {

			case 'table':
				\WP_CLI\Utils\assoc_array_to_table( $obj_data );
				break;

			case 'json':
				WP_CLI::print_value( $obj_data, $assoc_args );
				break;

			default:
				\WP_CLI::error( "Invalid format: " . $assoc_args['format'] );
				break;

		}
	}

	protected function show_single_field( $obj, $field ) {
		$value = null;

		foreach ( array( $field, $this->obj_type . '_' . $field ) as $key ) {
			if ( isset( $obj->$key ) ) {
				$value = $obj->$key;
				break;
			}
		}

		if ( null === $value ) {
			\WP_CLI::error( "Invalid {$this->obj_type} field: {$field}." );
		} else {
			\WP_CLI::print_value( $value );
		}
	}

}
