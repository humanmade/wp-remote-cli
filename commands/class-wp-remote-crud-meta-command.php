<?php
/**
 * CRUD for object meta on a remote site
 */
class WP_Remote_CRUD_Meta_Command extends WP_Remote_Command {

	/**
	 * Perform a meta action on a remote site
	 */
	protected function perform_meta_action( $action, $args, $assoc_args ) {

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

		switch ( $action ) {

			case 'list':

				list( $obj_id ) = $args;

				$endpoint_parts = array(
					'site',
					(int)$site_id,
					$this->obj_type,
					$obj_id,
					'meta'
					);

				$endpoint = implode( '/', $endpoint_parts );
				$method = 'GET';
				$api_args = $assoc_args;

				break;

			case 'get':

				list( $obj_id, $key ) = $args;

				$endpoint_parts = array(
					'site',
					(int)$site_id,
					$this->obj_type,
					$obj_id,
					'meta',
					$key
					);

				$endpoint = implode( '/', $endpoint_parts );
				$method = 'GET';
				$api_args = $assoc_args;

				break;

			case 'add':

				list( $obj_id, $key, $meta_value ) = $args;

				$endpoint_parts = array(
					'site',
					(int)$site_id,
					$this->obj_type,
					$obj_id,
					'meta'
					);

				$endpoint = implode( '/', $endpoint_parts );
				$method = 'POST';
				$api_args = array(
					'meta_key'   => $key,
					'meta_value' => $meta_value,
					);

				break;

			case 'update':

				list( $obj_id, $key, $meta_value ) = $args;

				$endpoint_parts = array(
					'site',
					(int)$site_id,
					$this->obj_type,
					$obj_id,
					'meta',
					$key
					);

				$endpoint = implode( '/', $endpoint_parts );
				$method = 'POST';
				$api_args = array(
					'meta_value' => $meta_value,
					);

				break;

			case 'delete':

				list( $obj_id, $key ) = $args;

				$endpoint_parts = array(
					'site',
					(int)$site_id,
					$this->obj_type,
					$obj_id,
					'meta',
					$key
					);

				$endpoint = implode( '/', $endpoint_parts );
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

				$this->show_multiple_fields( $response, array( 'format' => $format, 'fields' => $fields ) );

				break;

			case 'get':

				WP_CLI::print_value( $response, array( 'format' => $format, 'fields' => $fields ) );

				break;

			case 'add':
				
				WP_CLI::success( "Added {$this->obj_type} meta value." );

				break;

			case 'update':

				WP_CLI::success( "Updated {$this->obj_type} meta value." );

				break;

			case 'delete':

				WP_CLI::success( "Deleted {$this->obj_type} meta value." );

				break;

		}

	}

	protected function show_multiple_fields( $obj_data, $assoc_args ) {

		switch ( $assoc_args['format'] ) {

			case 'table':
				$this->assoc_array_to_table( $obj_data );
				break;

			case 'json':
				WP_CLI::print_value( $obj_data, $assoc_args );
				break;

			default:
				\WP_CLI::error( "Invalid format: " . $assoc_args['format'] );
				break;

		}
	}

	protected function assoc_array_to_table( $obj_data ) {
		$rows = array();

		foreach ( $obj_data as $field => $value ) {
			if ( ! is_string( $value ) ) {
					$value = json_encode( $value );
			}

			$rows[] = (object) array(
					'Key' => $field,
					'Value' => $value
			);
		}

		WP_CLI\Utils\format_items( 'table', $rows, array( 'Key', 'Value' ) );
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