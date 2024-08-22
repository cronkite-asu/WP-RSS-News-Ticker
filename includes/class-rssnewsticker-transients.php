<?php

namespace RSS_News_Ticker;

/**
 * Define the transients functionality
 *
 * Loads and defines the transients functions for this plugin.
 *
 * @since      1.3.0
 *
 * @package    Rssnewsticker
 * @subpackage Rssnewsticker/includes
 */

/**
 * Define the transients functionality.
 *
 * Loads and defines the transients functions for this plugin.
 *
 * @since      1.3.0
 * @package    Rssnewsticker
 * @subpackage Rssnewsticker/includes
 * @author     Jeremy Leggat <jleggat@asu.edu>
 */
class Rssnewsticker_Transients {

	/**
	 * Creating key for transients.
	 *
	 * @since    1.3.0
	 */
	public static function get_transient_name($plugin_name, $key_name, $hash_value='') {
		$value = join("_", array($plugin_name,$key_name,md5( $hash_value . __CLASS__ )));

		return $value;
	}

	public static function set_transient( $key, $callback, $expire = 0 ) {
		$cached = get_transient( $key );
		//
		// Return the cached value.
		if ( false !== $cached ) {
			return $cached;
		}

		$value = $callback();

		if ( ! is_wp_error( $value ) ) {
			set_transient( $key, $value, $expire );
		}

		return $value;
	}

	/**
	 * Delete all transients from the database whose keys have a specific prefix.
	 *
	 * @param string $prefix The prefix. Example: 'my_cool_transient_'.
	 */
	public static function delete_transients_with_prefix( $prefix ) {
		foreach ( self::get_transient_keys_with_prefix( $prefix ) as $key ) {
			delete_transient( $key );
		}
	}

	/**
	 * Gets all transient keys in the database with a specific prefix.
	 *
	 * Note that this doesn't work for sites that use a persistent object
	 * cache, since in that case, transients are stored in memory.
	 *
	 * @param  string $prefix Prefix to search for.
	 * @return array          Transient keys with prefix, or empty array on error.
	 */
	public static function get_transient_keys_with_prefix( $prefix ) {
		global $wpdb;

		$prefix = $wpdb->esc_like( '_transient_' . $prefix );
		$sql    = "SELECT `option_name` FROM $wpdb->options WHERE `option_name` LIKE '%s'";
		$keys   = $wpdb->get_results( $wpdb->prepare( $sql, $prefix . '%' ), ARRAY_A );

		if ( is_wp_error( $keys ) ) {
			return [];
		}

		return array_map( function( $key ) {
			// Remove '_transient_' from the option name.
			return substr( $key['option_name'], strlen( '_transient_' ) );
		}, $keys );
	}

}
