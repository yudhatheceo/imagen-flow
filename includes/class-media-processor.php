<?php
/**
 * Media Processor for ImagenFlow.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ImagenFlow_Media_Processor {

	public function process_and_sideload( $image_input, $metadata = array() ) {
		$tmp_file = '';

		if ( file_exists( $image_input ) ) {
			$tmp_file = $image_input;
		} else {
			$tmp_file = download_url( $image_input );
			if ( is_wp_error( $tmp_file ) ) {
				return $tmp_file;
			}
		}

		$options = get_option( 'imagen_flow_options' );
		$quality = isset( $options['default_quality'] ) ? absint( $options['default_quality'] ) : 80;
		$format  = isset( $options['preferred_format'] ) ? $options['preferred_format'] : 'webp';

		// Generate a clean filename.
		$filename_prefix = isset( $metadata['filename'] ) ? sanitize_title( $metadata['filename'] ) : 'generated-image';
		if ( empty( $filename_prefix ) ) {
			$filename_prefix = 'generated-image';
		}

		// Process the image.
		$processed_path = $this->process_image( $tmp_file, $quality, $format, $filename_prefix );

		if ( is_wp_error( $processed_path ) ) {
			@unlink( $tmp_file );
			return $processed_path;
		}

		// Sideload.
		$file_array = array(
			'name'     => basename( $processed_path ),
			'tmp_name' => $processed_path,
		);
// ... existing admin file inclusion logic ...

		// Ensure WordPress admin files are included for sideloading.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		$id = media_handle_sideload( $file_array, 0 );

		// Cleanup.
		@unlink( $tmp_file );
		if ( $processed_path !== $tmp_file ) {
			@unlink( $processed_path );
		}

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		// Set SEO Metadata.
		if ( ! empty( $metadata ) ) {
			if ( isset( $metadata['alt'] ) ) {
				update_post_meta( $id, '_wp_attachment_image_alt', sanitize_text_field( $metadata['alt'] ) );
			}
			if ( isset( $metadata['caption'] ) ) {
				wp_update_post( array(
					'ID'           => $id,
					'post_excerpt' => sanitize_text_field( $metadata['caption'] ),
				) );
			}
		}

		return $id;
	}

	/**
	 * Process the image using WP_Image_Editor (Imagick preferred).
	 *
	 * @param string $file_path The path to the image file.
	 * @param int    $quality   The compression level.
	 * @param string $format    The target format (webp/jpeg).
	 * @return string|WP_Error The path to the processed image or WP_Error.
	 */
	private function process_image( $file_path, $quality, $format, $filename_prefix = '' ) {
		// Use Imagick directly for metadata stripping if available.
		if ( class_exists( 'Imagick' ) ) {
			try {
				$imagick = new Imagick( $file_path );
				$imagick->stripImage();
				$imagick->writeImage( $file_path );
				$imagick->clear();
				$imagick->destroy();
			} catch ( Exception $e ) {
				error_log( 'ImagenFlow: Imagick strip failed: ' . $e->getMessage() );
			}
		}

		$editor = wp_get_image_editor( $file_path );

		if ( is_wp_error( $editor ) ) {
			return $editor;
		}

		$editor->set_quality( $quality );

		// Convert to WebP if requested.
		$filename       = ! empty( $filename_prefix ) ? $filename_prefix : pathinfo( $file_path, PATHINFO_FILENAME );
		$new_extension  = ( 'webp' === $format ) ? 'webp' : 'jpg';
		$processed_path = dirname( $file_path ) . '/' . $filename . '.' . $new_extension;

		$saved = $editor->save( $processed_path, ( 'webp' === $format ) ? 'image/webp' : 'image/jpeg' );

		if ( is_wp_error( $saved ) ) {
			return $saved;
		}

		return $saved['path'];
	}
}
