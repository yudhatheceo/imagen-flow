<?php
/**
 * Gemini API Handler for ImagenFlow.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ImagenFlow_Gemini_API {

	/**
	 * API Key.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Base URL for Gemini API.
	 *
	 * @var string
	 */
	private $base_url = 'https://generativelanguage.googleapis.com/v1beta';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$options       = get_option( 'imagen_flow_options' );
		$this->api_key = isset( $options['gemini_api_key'] ) ? $options['gemini_api_key'] : '';
	}

	/**
	 * Generate an image from a prompt.
	 *
	 * @param string $prompt The text prompt.
	 * @param array  $args   Additional arguments (aspect ratio, etc).
	 * @return array|WP_Error
	 */
	public function generate_image( $prompt, $args = array() ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'missing_api_key', __( 'Gemini API Key is missing.', 'imagen-flow' ) );
		}

		// Using Imagen 4.0 as requested
		$model = 'imagen-4.0-generate-001'; 
		$url   = "{$this->base_url}/models/{$model}:predict?key={$this->api_key}";

		// Request body for Imagen Predict
		$body = array(
			'instances' => array(
				array(
					'prompt' => $prompt,
				),
			),
			'parameters' => array(
				'sampleCount' => isset( $args['sampleCount'] ) ? absint( $args['sampleCount'] ) : 1,
				'aspectRatio' => isset( $args['aspectRatio'] ) ? sanitize_text_field( $args['aspectRatio'] ) : '1:1',
			),
		);

		// Debug Log: Request
		error_log( 'ImagenFlow: API Request URL: ' . $url );
		error_log( 'ImagenFlow: API Request Body: ' . wp_json_encode( $body ) );

		$response = wp_remote_post( $url, array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $body ),
			'timeout' => 60,
		) );

		if ( is_wp_error( $response ) ) {
			error_log( 'ImagenFlow: API Request Failed: ' . $response->get_error_message() );
			return $response;
		}

		$response_body = wp_remote_retrieve_body( $response );
		error_log( 'ImagenFlow: API Response Body: ' . $response_body );

		$body = json_decode( $response_body, true );

		if ( isset( $body['error'] ) ) {
			return new WP_Error( 'api_error', $body['error']['message'] );
		}

		return $body;
	}

	/**
	 * Analyze an image using Gemini 3 Flash.
	 *
	 * @param string $image_data Base64 encoded image data.
	 * @param string $prompt     Prompt for analysis.
	 * @return string|WP_Error
	 */
	public function analyze_image( $image_data, $prompt = 'Describe this image for a visual prompt.' ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'missing_api_key', __( 'Gemini API Key is missing.', 'imagen-flow' ) );
		}

		$model = 'gemini-3-flash-preview'; // Using Gemini 3 Flash Preview as the modern workforce
		$url   = "{$this->base_url}/models/{$model}:generateContent?key={$this->api_key}";

		$body = array(
			'contents' => array(
				array(
					'parts' => array(
						array( 'text' => $prompt ),
						array(
							'inline_data' => array(
								'mime_type' => 'image/jpeg',
								'data'      => $image_data,
							),
						),
					),
				),
			),
		);

		$response = wp_remote_post( $url, array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $body ),
			'timeout' => 30,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_body = wp_remote_retrieve_body( $response );
		$body = json_decode( $response_body, true );

		if ( isset( $body['error'] ) ) {
			return new WP_Error( 'api_error', $body['error']['message'] );
		}

		return isset( $body['candidates'][0]['content']['parts'][0]['text'] ) ? $body['candidates'][0]['content']['parts'][0]['text'] : '';
	}

	/**
	 * Extract essence/summarize text using Gemini 3 Flash.
	 *
	 * @param string $content The post content.
	 * @return string|WP_Error
	 */
	public function summarize_content( $content ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'missing_api_key', __( 'Gemini API Key is missing.', 'imagen-flow' ) );
		}

		$model = 'gemini-3-flash-preview'; 
		$url   = "{$this->base_url}/models/{$model}:generateContent?key={$this->api_key}";

		$prompt = "Extract the visual essence of the following text to create a detailed image generation prompt. Focus on mood, subjects, and style:\n\n" . $content;

		$body = array(
			'contents' => array(
				array(
					'parts' => array(
						array( 'text' => $prompt ),
					),
				),
			),
		);

		$response = wp_remote_post( $url, array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $body ),
			'timeout' => 30,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_body = wp_remote_retrieve_body( $response );
		$body = json_decode( $response_body, true );

		if ( isset( $body['error'] ) ) {
			return new WP_Error( 'api_error', $body['error']['message'] );
		}

		return isset( $body['candidates'][0]['content']['parts'][0]['text'] ) ? $body['candidates'][0]['content']['parts'][0]['text'] : '';
	}

	/**
	 * Generate a concise SEO Alt Text using Gemini 3 Flash.
	 *
	 * @param string $prompt  The visual prompt used.
	 * @param string $keyword Custom keyword from user.
	 * @return string|WP_Error
	 */
	public function generate_alt_text( $prompt, $keyword = '' ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'missing_api_key', __( 'Gemini API Key is missing.', 'imagen-flow' ) );
		}

		$model = 'gemini-3-flash-preview'; 
		$url   = "{$this->base_url}/models/{$model}:generateContent?key={$this->api_key}";

		$instruction = "Generate a concise, 1-sentence SEO alt text (max 15 words) for an image described as: \"$prompt\".";
		if ( ! empty( $keyword ) ) {
			$instruction .= " Ensure the keyword \"$keyword\" is naturally included.";
		}
		$instruction .= " Do not include any prefixes like 'Alt text:' or markdown.";

		$body = array(
			'contents' => array(
				array(
					'parts' => array(
						array( 'text' => $instruction ),
					),
				),
			),
		);

		$response = wp_remote_post( $url, array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $body ),
			'timeout' => 20,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_body = wp_remote_retrieve_body( $response );
		$body = json_decode( $response_body, true );

		$text = isset( $body['candidates'][0]['content']['parts'][0]['text'] ) ? trim( $body['candidates'][0]['content']['parts'][0]['text'] ) : '';
		
		// Final sanitization: Remove quotes and stars
		return str_replace( array( '"', '*' ), '', $text );
	}
}
