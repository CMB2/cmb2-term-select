<?php
/**
 * Plugin Name: CMB2 Term Select
 * Plugin URI: https://github.com/jtsternberg/cmb2-term-select
 * Description: Custom field type for CMB2 which adds a term-select input.
 * Author: Justin Sternberg
 * Author URI: http://dsgnwrks.pro
 * Version: 0.1.0
 * License: GPLv2
 */

/**
 * CMB2_Term_Select loader
 *
 * Handles checking for and smartly loading the newest version of this library.
 *
 * @category  WordPressLibrary
 * @package   CMB2_Term_Select
 * @author    Justin Sternberg <justin@dsgnwrks.pro>
 * @copyright 2016 Justin Sternberg <justin@dsgnwrks.pro>
 * @license   GPL-2.0+
 * @version   0.1.0
 * @link      https://github.com/jtsternberg/cmb2-term-select
 * @since     0.1.0
 */

/**
 * Copyright (c) 2016 Justin Sternberg (email : justin@dsgnwrks.pro)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Loader versioning: http://jtsternberg.github.io/wp-lib-loader/
 */

if ( ! class_exists( 'CMB2_Term_Select_010', false ) ) {

	/**
	 * Versioned loader class-name
	 *
	 * This ensures each version is loaded/checked.
	 *
	 * @category WordPressLibrary
	 * @package  CMB2_Term_Select
	 * @author   Justin Sternberg <justin@dsgnwrks.pro>
	 * @license  GPL-2.0+
	 * @version  0.1.0
	 * @link     https://github.com/jtsternberg/cmb2-term-select
	 * @since    0.1.0
	 */
	class CMB2_Term_Select_010 {

		/**
		 * CMB2_Term_Select version number
		 * @var   string
		 * @since 0.1.0
		 */
		const VERSION = '0.1.0';

		/**
		 * Current version hook priority.
		 * Will decrement with each release
		 *
		 * @var   int
		 * @since 0.1.0
		 */
		const PRIORITY = 9999;

		/**
		 * Starts the version checking process.
		 * Creates CMB2_TERM_SELECT_LOADED definition for early detection by
		 * other scripts.
		 *
		 * Hooks CMB2_Term_Select inclusion to the cmb2_term_select_load hook
		 * on a high priority which decrements (increasing the priority) with
		 * each version release.
		 *
		 * @since 0.1.0
		 */
		public function __construct() {
			if ( ! defined( 'CMB2_TERM_SELECT_LOADED' ) ) {
				/**
				 * A constant you can use to check if CMB2_Term_Select is loaded
				 * for your plugins/themes with CMB2_Term_Select dependency.
				 *
				 * Can also be used to determine the priority of the hook
				 * in use for the currently loaded version.
				 */
				define( 'CMB2_TERM_SELECT_LOADED', self::PRIORITY );
			}

			// Use the hook system to ensure only the newest version is loaded.
			add_action( 'cmb2_term_select_load', array( $this, 'include_lib' ), self::PRIORITY );

			// Use the hook system to ensure only the newest version is loaded.
			add_action( 'after_setup_theme', array( $this, 'do_hook' ) );
		}

		/**
		 * Fires the cmb2_attached_posts_field_load action hook
		 * (from the after_setup_theme hook).
		 *
		 * @since 1.2.3
		 */
		public function do_hook() {
			// Then fire our hook.
			do_action( 'cmb2_term_select_load' );
		}

		/**
		 * A final check if CMB2_Term_Select exists before kicking off
		 * our CMB2_Term_Select loading.
		 *
		 * CMB2_TERM_SELECT_VERSION and CMB2_TERM_SELECT_DIR constants are
		 * set at this point.
		 *
		 * @since  0.1.0
		 */
		public function include_lib() {
			if ( class_exists( 'CMB2_Term_Select', false ) ) {
				return;
			}

			if ( ! defined( 'CMB2_TERM_SELECT_VERSION' ) ) {
				/**
				 * Defines the currently loaded version of CMB2_Term_Select.
				 */
				define( 'CMB2_TERM_SELECT_VERSION', self::VERSION );
			}

			if ( ! defined( 'CMB2_TERM_SELECT_DIR' ) ) {
				/**
				 * Defines the directory of the currently loaded version of CMB2_Term_Select.
				 */
				define( 'CMB2_TERM_SELECT_DIR', dirname( __FILE__ ) . '/' );
			}

			// Include and initiate CMB2_Term_Select.
			require_once CMB2_TERM_SELECT_DIR . 'lib/init.php';
		}

	}

	// Kick it off.
	new CMB2_Term_Select_010;
}
