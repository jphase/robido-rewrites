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
		// Call our after_theme_loaded function just to grab our settings available this early (query_vars happens before wp)
		$this->after_theme_loaded();

		if ( ! empty( $this->settings['rewrites'] ) ) {
			foreach ( $this->settings['rewrites'] as $rewrite => $template ) {
				add_rewrite_rule( $rewrite . '/(.*)/?', 'index.php?' . $rewrite . '=$matches[1]', 'top' );
			}
			if ( ! empty( $_GET ) ) {
				foreach ( $_GET as $key => $val ) {
					add_rewrite_tag( '%' . $key . '%', '([^&]+)' );
				}
			}
		}
	}

	// After theme loaded hook
	function after_theme_loaded() {
		// Initialize settings array (but let's add /property/ as a default rewrite for an example)
		$this->settings = apply_filters( 'robido_rewrites', array(
			'rewrites'	=> array(
				// 'property'		=> plugin_dir_path( __FILE__ ) . 'template-property.php',
			),
		));
	}

	// Template include filter
	function template_include( $original ) {
		global $wp_query;

		// Explode our URL bits for params
		$urlbits = explode( '?', $_SERVER['REQUEST_URI'] );
		$params = '';
		if ( count( $urlbits ) > 1 ) {
			$params = end( $urlbits );
			array_pop( $urlbits );
		}

		// Explode our URL bits for params
		$urlbits = array_filter( explode( '/', implode( '?', $urlbits ) ) );
		$urlbits = array_values( $urlbits );
		if ( empty( $urlbits ) ) $urlbits = array( $_SERVER['REQUEST_URI'] );

		// Check rewrites for matches from $this->settings['rewrites']
		if ( ! empty( $this->settings['rewrites'] ) && ! empty( $wp_query->query_vars ) ) {
			// Loop through our rewrites to map this to a template (if it's defined)
			foreach ( $this->settings['rewrites'] as $rewrite => $template ) {
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