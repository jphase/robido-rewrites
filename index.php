<?php
namespace robido;
/**
* Plugin Name:	Robido Rewrites
* Plugin URI:	http://robido.com/robido-rewrites
* Description:	A utility plugin for developers to easily add rewrites and route them to templates
* Version:		1.0.0
* Author:		Jeff Hays (jphase)
* Author URI:	https://profiles.wordpress.org/jphase/
* Text Domain:	robido
* License:		GPL2
*/
if ( ! defined( 'ABSPATH' ) ) exit;

class Rewrites {

	// Plugin settings
	public $settings;

	// Construct (Actions/Filters/Shortcodes/etc.)
	function __construct() {
		// Actions
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'after_theme_loaded', array( $this, 'after_theme_loaded' ) );

		// Filters
		add_filter( 'template_include', array( $this, 'template_include' ) );

		// Shortcodes
		add_shortcode( 'properties', array( $this, 'list_properties' ) );
	}

	// Init hook (before anything)
	function init() {
		// Call our after_theme_loaded function just to grab our filtered settings this early (query_vars happens before wp)
		$this->after_theme_loaded();
		if ( ! empty( $this->settings ) ) {
			foreach ( $this->settings as $rewrite => $template ) {
				// Setup tag with either tag arg from filtered settings or just the rewrite rule word itself
				$tag = is_array( $template ) && isset( $template['tag'] ) ? $template['tag'] : $rewrite;

				// Add rewrite rules and tags from our filtered settings
				add_rewrite_rule( $rewrite . '/(.*)/?', 'index.php?' . $tag . '=$matches[1]', 'top' );

				// Check if this is an advanced rewrite (when the rewrite is an array of settings instead of a simple template)
				if ( is_array( $template ) ) {
					// Setup settings from advanced rewrite array
					$settings = $template;
					$template = $settings['template'];
					$params = isset( $settings['params'] ) ? $settings['params'] : false;

					// Add rewrite params
					if ( ! empty( $params ) ) {
						// Filter the REQUEST_URI into an array of items without $_GET params
						if ( ! empty( $_GET ) ) {
							$urlbits = explode( '?', str_replace( $rewrite, '', $_SERVER['REQUEST_URI'] ) );
							array_pop( $urlbits );
							$urlbits = array_values( array_filter( explode( '/', implode( '?', $urlbits ) ) ) );
						} else {
							$urlbits = array_values( array_filter( explode( '/', str_replace( $rewrite, '', $_SERVER['REQUEST_URI'] ) ) ) );
						}			

						// Add our params into the $_GET array
						foreach ( $params as $key => $param ) {
							if ( isset( $urlbits[ $key ] ) ) {
								$_GET[ $param ] = $urlbits[ $key ];
							}
						}
					}
				}
			}

			// Add $_GET params
			if ( ! empty( $_GET ) ) {
				foreach ( $_GET as $key => $val ) {
					add_rewrite_tag( '%' . $key . '%', '([^&]+)' );
				}
			}
		}
	}

	// After theme loaded hook
	function after_theme_loaded() {
		// Initialize settings by pulling our settings from the theme filters
		$this->settings = apply_filters( 'robido_rewrites', array() );
	}

	// Template include filter
	function template_include( $original ) {
		global $wp_query;

		// Explode our URL bits for params
		$params = '';
		$urlbits = explode( '?', $_SERVER['REQUEST_URI'] );
		if ( count( $urlbits ) > 1 ) {
			$params = end( $urlbits );
			array_pop( $urlbits );
		}

		// Explode our URL bits for params
		$urlbits = array_values( array_filter( explode( '/', implode( '?', $urlbits ) ) ) );
		if ( empty( $urlbits ) ) $urlbits = array( $_SERVER['REQUEST_URI'] );

		// Check rewrites for matches from $this->settings['rewrites']
		if ( ! empty( $this->settings ) && ! empty( $wp_query->query_vars ) ) {
			// Loop through our rewrites to map this to a template (if it's defined)
			foreach ( $this->settings as $rewrite => $template ) {
				// Check if they passed an array of settings for this rewrite rule
				if ( is_array( $template ) ) {
					$settings = $template;
					$template = $settings['template'];
					$params = $settings['params'];
				}
				// Check our URL against the rewrite rules from filtered settings
				if ( $urlbits[0] == $rewrite ) {
					// Setup some WP_Query stuff so WordPress doesn't throw funky headers or do anything weird
					$wp_query->is_404 = false;
					$wp_query->is_archive = true;
					$wp_query->is_category = true;
					return $template;
				}
			}
		}

		// Return the template WP would natively render
		return $original;
	}

}

$robido_rewrites = new Rewrites;