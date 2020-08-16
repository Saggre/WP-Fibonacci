<?php
/*
Plugin Name: Fibonacci
Plugin URI:
description: Adds two new shortcodes: [fibonacci length=n] and [fibonacci-reverse length=n], which print fibonacci sequences
Version: 0.1
Author: Sakri Koskimies
Author URI:
License: GPL2
*/


/**
 * Class Fibonacci_Plugin
 */
class Fibonacci_Plugin {
	use Fibonacci;

	function __construct() {

		// Ajax call
		add_action( 'wp_ajax_save_fib', function () {
			$post_id   = $_POST['id'];
			$fibonacci = $_POST['fibonacci'];
			$reversed  = filter_var( $_POST['reversed'], FILTER_VALIDATE_BOOLEAN );

			if ( preg_match( '/[0-9]\s/', $fibonacci ) ) { // To prevent some user tampering
				update_post_meta( $post_id, ( $reversed ? 'fibonacci_reversed' : 'fibonacci_sequence' ), $fibonacci );
			}

			die();
		} );

		// Shortcodes
		$shortcode_func = function ( $atts, $content, $tag ) {
			$atts = shortcode_atts( array(
				'length' => 1,
			), $atts );

			if ( ! is_numeric( $atts['length'] ) ) {
				return '';
			}

			$seq = $this->get_sequence( $atts['length'] );

			return '<a href="#/" class="' . ( $tag === 'fibonacci' ? 'fibseq' : 'fibseq-rev' ) . '">' . ( $tag === 'fibonacci' ? $seq : implode( ' ', array_reverse( preg_split( '/\s/', $seq ) ) ) ) . '</a>';
		};

		add_shortcode( 'fibonacci', $shortcode_func );
		add_shortcode( 'fibonacci-reverse', $shortcode_func );

		// Don't add frontend stuff in admin
		if ( $GLOBALS['pagenow'] == 'wp-login.php' || is_admin() ) {
			return;
		}

		// JS
		add_action( 'wp_enqueue_scripts', function () {
			global $post;

			wp_register_script( 'fibonacci', plugins_url( 'js/fibonacci.min.js', __FILE__ ), array( 'jquery' ), '1.0.2', true );

			wp_localize_script( 'fibonacci', 'fib', array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'postId'  => $post->ID
			) );

			wp_enqueue_script( 'fibonacci' );
		} );
	}
}


/**
 * Trait Fibonacci
 */
trait Fibonacci {
	/**
	 * Gets fib sequence as string up to $length elements
	 *
	 * @param $length
	 *
	 * @return string
	 */
	public function get_sequence( $length ) {
		if ( $length <= 0 ) {
			return '';
		} else if ( $length < 2 ) {
			return $length;
		}

		$ret = '1 ';
		$na  = 0;
		$nb  = 1;

		// Limited to 91 numbers
		for ( $i = 0; $i < $length - 1 && $i < 91; $i ++ ) {
			$nc  = $nb + $na;
			$ret .= $nc . ' ';
			$na  = $nb;
			$nb  = $nc;
		}

		return trim( $ret );
	}
}

new Fibonacci_Plugin();