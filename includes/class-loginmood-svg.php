<?php
/**
 * Safe SVG upload support for logo attachments.
 *
 * @package LoginMood
 */

namespace Fegutogi\LoginMood;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Svg {
	/**
	 * Register SVG upload and media-library hooks.
	 */
	public function __construct() {
		add_filter( 'upload_mimes', array( $this, 'allow_svg_mime' ) );
		add_filter( 'wp_handle_upload_prefilter', array( $this, 'sanitize_upload' ) );
		add_filter( 'wp_handle_sideload_prefilter', array( $this, 'sanitize_upload' ) );
		add_filter( 'wp_check_filetype_and_ext', array( $this, 'confirm_svg_filetype' ), 10, 5 );
		add_filter( 'wp_get_attachment_image_src', array( $this, 'provide_svg_image_src' ), 10, 4 );
		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'prepare_svg_for_media_library' ), 10, 3 );
	}

	/**
	 * Permit SVG uploads only for site administrators.
	 *
	 * @param array<string, string> $mimes Allowed MIME types.
	 * @return array<string, string>
	 */
	public function allow_svg_mime( $mimes ) {
		if ( current_user_can( 'manage_options' ) ) {
			$mimes['svg'] = 'image/svg+xml';
		}

		return $mimes;
	}

	/**
	 * Sanitize an SVG before WordPress moves it into the uploads directory.
	 *
	 * @param array<string, mixed> $file Upload data.
	 * @return array<string, mixed>
	 */
	public function sanitize_upload( $file ) {
		$name = isset( $file['name'] ) ? (string) $file['name'] : '';
		if ( 'svg' !== strtolower( pathinfo( $name, PATHINFO_EXTENSION ) ) ) {
			return $file;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			$file['error'] = __( 'SVG files can only be uploaded by administrators.', 'loginmood' );
			return $file;
		}

		if ( ! class_exists( 'DOMDocument' ) ) {
			$file['error'] = __( 'SVG uploads require the PHP DOM extension.', 'loginmood' );
			return $file;
		}

		$path   = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';
		$result = self::sanitize_file( $path );
		if ( is_wp_error( $result ) ) {
			$file['error'] = $result->get_error_message();
		}

		return $file;
	}

	/**
	 * Confirm the MIME type after the sanitized file passes upload validation.
	 *
	 * @param array<string, mixed>  $data Filetype data.
	 * @param string                $file Full path.
	 * @param string                $filename Original filename.
	 * @param array<string, string> $mimes Allowed MIME types.
	 * @param string|false          $real_mime Detected MIME type.
	 * @return array<string, mixed>
	 */
	public function confirm_svg_filetype( $data, $file, $filename, $mimes, $real_mime ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( current_user_can( 'manage_options' ) && 'svg' === strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) ) {
			$data['ext']             = 'svg';
			$data['type']            = 'image/svg+xml';
			$data['proper_filename'] = false;
		}

		return $data;
	}

	/**
	 * Supply dimensions for SVG attachments, which WordPress cannot rasterize.
	 *
	 * @param array<int, mixed>|false $image Attachment image data.
	 * @param int                     $attachment_id Attachment ID.
	 * @param string|int[]            $size Requested size.
	 * @param bool                    $icon Whether an icon was requested.
	 * @return array<int, mixed>|false
	 */
	public function provide_svg_image_src( $image, $attachment_id, $size, $icon ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
			return $image;
		}

		$url  = wp_get_attachment_url( $attachment_id );
		$file = get_attached_file( $attachment_id );
		if ( ! $url || ! $file ) {
			return false;
		}

		$dimensions = self::dimensions_from_file( $file );

		return array( $url, $dimensions[0], $dimensions[1], false );
	}

	/**
	 * Add an SVG full-size entry to media-modal attachment data.
	 *
	 * @param array<string, mixed> $response Attachment response.
	 * @param WP_Post              $attachment Attachment post.
	 * @param array<string, mixed> $meta Attachment metadata.
	 * @return array<string, mixed>
	 */
	public function prepare_svg_for_media_library( $response, $attachment, $meta ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( 'image/svg+xml' !== $attachment->post_mime_type ) {
			return $response;
		}

		$file = get_attached_file( $attachment->ID );
		if ( ! $file ) {
			return $response;
		}

		$dimensions         = self::dimensions_from_file( $file );
		$response['width']  = $dimensions[0];
		$response['height'] = $dimensions[1];
		$response['sizes']  = array(
			'full' => array(
				'url'         => $response['url'],
				'width'       => $dimensions[0],
				'height'      => $dimensions[1],
				'orientation' => $dimensions[0] >= $dimensions[1] ? 'landscape' : 'portrait',
			),
		);

		return $response;
	}

	/**
	 * Sanitize an SVG file in place.
	 *
	 * @param string $path SVG file path.
	 * @return true|WP_Error
	 */
	public static function sanitize_file( $path ) {
		if ( ! $path || ! is_readable( $path ) || ! is_writable( $path ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable -- The upload temp file must be sanitized in place before WordPress moves it.
			return new \WP_Error( 'fegutogi_loginmood_invalid_svg', __( 'This SVG file is invalid or contains unsupported content.', 'loginmood' ) );
		}

		$contents = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( false === $contents || false !== stripos( $contents, '<!DOCTYPE' ) ) {
			return new \WP_Error( 'fegutogi_loginmood_invalid_svg', __( 'This SVG file is invalid or contains unsupported content.', 'loginmood' ) );
		}

		$previous = libxml_use_internal_errors( true );
		$document = new \DOMDocument();
		$loaded   = $document->loadXML( $contents, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );

		if ( ! $loaded || ! $document->documentElement || 'svg' !== strtolower( $document->documentElement->localName ) ) {
			return new \WP_Error( 'fegutogi_loginmood_invalid_svg', __( 'This SVG file is invalid or contains unsupported content.', 'loginmood' ) );
		}

		self::sanitize_node( $document->documentElement );
		$clean = $document->saveXML();
		if ( ! $clean || false === file_put_contents( $path, $clean, LOCK_EX ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			return new \WP_Error( 'fegutogi_loginmood_invalid_svg', __( 'This SVG file is invalid or contains unsupported content.', 'loginmood' ) );
		}

		return true;
	}

	/**
	 * Remove elements and attributes outside the safe logo subset.
	 *
	 * @param DOMNode $node Current DOM node.
	 */
	private static function sanitize_node( $node ) {
		$allowed_elements = array(
			'svg', 'g', 'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon',
			'title', 'desc', 'defs', 'lineargradient', 'radialgradient', 'stop', 'clippath',
			'mask', 'symbol', 'use',
		);
		$allowed_attributes = array(
			'xmlns', 'viewbox', 'preserveaspectratio', 'width', 'height', 'x', 'y', 'x1', 'y1',
			'x2', 'y2', 'cx', 'cy', 'r', 'rx', 'ry', 'd', 'points', 'fill', 'fill-opacity',
			'fill-rule', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin',
			'stroke-opacity', 'stroke-dasharray', 'stroke-dashoffset', 'stroke-miterlimit',
			'opacity', 'transform', 'id', 'offset', 'stop-color', 'stop-opacity', 'gradientunits',
			'gradienttransform', 'spreadmethod', 'fx', 'fy', 'fr', 'clip-path', 'clip-rule',
			'mask', 'href', 'style',
		);

		foreach ( iterator_to_array( $node->childNodes ) as $child ) {
			if ( XML_ELEMENT_NODE === $child->nodeType ) {
				if ( ! in_array( strtolower( $child->localName ), $allowed_elements, true ) ) {
					$node->removeChild( $child );
					continue;
				}
				self::sanitize_node( $child );
			} elseif ( ! in_array( $child->nodeType, array( XML_TEXT_NODE, XML_CDATA_SECTION_NODE ), true ) ) {
				$node->removeChild( $child );
			}
		}

		if ( ! $node instanceof \DOMElement || ! $node->hasAttributes() ) {
			return;
		}

		foreach ( iterator_to_array( $node->attributes ) as $attribute ) {
			$name  = strtolower( $attribute->localName );
			$value = trim( $attribute->value );
			$remove = ! in_array( $name, $allowed_attributes, true ) || 0 === strpos( $name, 'on' );

			if ( in_array( $name, array( 'href' ), true ) && '' !== $value && '#' !== substr( $value, 0, 1 ) ) {
				$remove = true;
			}
			if ( preg_match( '/(?:javascript|data)\s*:/i', $value ) ) {
				$remove = true;
			}
			if ( in_array( $name, array( 'fill', 'stroke', 'clip-path', 'mask' ), true ) && false !== stripos( $value, 'url(' ) && ! preg_match( '/^url\(\s*["\']?#[A-Za-z_][\w:.-]*["\']?\s*\)$/', $value ) ) {
				$remove = true;
			}
			if ( 'style' === $name ) {
				$value = self::sanitize_style( $value );
				if ( '' === $value ) {
					$remove = true;
				}
			}

			if ( $remove ) {
				$node->removeAttributeNode( $attribute );
			} elseif ( 'style' === $name ) {
				$node->setAttribute( 'style', $value );
			}
		}
	}

	/**
	 * Retain only simple SVG presentation declarations.
	 *
	 * @param string $style Inline declaration list.
	 * @return string
	 */
	private static function sanitize_style( $style ) {
		$allowed = array(
			'fill', 'fill-opacity', 'fill-rule', 'stroke', 'stroke-width', 'stroke-linecap',
			'stroke-linejoin', 'stroke-opacity', 'stroke-dasharray', 'stroke-dashoffset',
			'stroke-miterlimit', 'opacity', 'stop-color', 'stop-opacity',
		);
		$clean = array();

		foreach ( explode( ';', $style ) as $declaration ) {
			$parts = array_map( 'trim', explode( ':', $declaration, 2 ) );
			if ( 2 !== count( $parts ) || ! in_array( strtolower( $parts[0] ), $allowed, true ) ) {
				continue;
			}
			if ( preg_match( '/(?:url\s*\(|expression\s*\(|javascript\s*:|data\s*:|@|\\\\)/i', $parts[1] ) ) {
				continue;
			}
			$clean[] = strtolower( $parts[0] ) . ':' . $parts[1];
		}

		return implode( ';', $clean );
	}

	/**
	 * Read a stable width and height from an SVG viewBox or dimensions.
	 *
	 * @param string $path SVG file path.
	 * @return int[]
	 */
	public static function dimensions_from_file( $path ) {
		$fallback = array( 300, 150 );
		if ( ! class_exists( 'DOMDocument' ) || ! is_readable( $path ) ) {
			return $fallback;
		}

		$document = new \DOMDocument();
		$previous = libxml_use_internal_errors( true );
		$loaded   = $document->load( $path, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );
		if ( ! $loaded || ! $document->documentElement ) {
			return $fallback;
		}

		$root    = $document->documentElement;
		$viewbox = preg_split( '/[\s,]+/', trim( $root->getAttribute( 'viewBox' ) ) );
		if ( 4 === count( $viewbox ) && is_numeric( $viewbox[2] ) && is_numeric( $viewbox[3] ) && (float) $viewbox[2] > 0 && (float) $viewbox[3] > 0 ) {
			return array( max( 1, (int) round( (float) $viewbox[2] ) ), max( 1, (int) round( (float) $viewbox[3] ) ) );
		}

		$width  = self::numeric_dimension( $root->getAttribute( 'width' ) );
		$height = self::numeric_dimension( $root->getAttribute( 'height' ) );

		return $width && $height ? array( $width, $height ) : $fallback;
	}

	/**
	 * Convert a numeric SVG dimension with an optional px suffix.
	 *
	 * @param string $value Dimension value.
	 * @return int
	 */
	private static function numeric_dimension( $value ) {
		if ( ! preg_match( '/^\s*(\d+(?:\.\d+)?)\s*(?:px)?\s*$/i', $value, $matches ) ) {
			return 0;
		}

		return max( 1, (int) round( (float) $matches[1] ) );
	}
}
