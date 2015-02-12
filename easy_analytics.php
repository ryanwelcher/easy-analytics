<?php
/*
Plugin Name: Easy Analytics
Plugin URI: http://www.ryanwelcher.com/work/easy-analytics
Description: Easily add your Google Analytics tracking snippet to your WordPress site.
Author: Ryan Welcher
Version: 3.4.2
Author URI: http://www.ryanwelcher.com
Text Domain: easy-analytics
Copyright 2011  Ryan Welcher  (email : me@ryanwelcher.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if ( ! class_exists( 'EasyAnalytics' ) ):

	if ( ! class_exists( 'RW_Plugin_Base' ) ) {
		require_once plugin_dir_path( __FILE__ ) . '/_inc/RW_Plugin_Base.php';
	}

	class EasyAnalytics extends RW_Plugin_Base {


		/**
		 * @var string plugin version number
		 */
		const version = '3.4';


		/**
		 * @var bool Does this plugin need a settings page?
		 */
		private $_has_settings_page = true;

		/**
		 * var string Slug name for the settings page
		 */
		private $_settings_page_name = 'easy_analytics_settings_settings_page';


		/**
		 * @var array default settings
		 */
		private $_default_settings = array(
			'tracking_id'	=> '',
			'type' 			=> 'ua', // either ua (Universal - analytics.js) or ga (original  - ga.js)
			'location'		=> 'header', //either header or footer
			'enhanced_link_attribution' => 'no', //

		);

		/**
		 * @var array The current settings
		 */
		private $_settings = array();


		/**
		 * @var The name of the settings in the database
		 */
		private $_settings_name = 'easy_analytics_settings';




		/**
		 * Entry point
		 */

		function __construct() {

			//call super class constructor
			parent::__construct( __FILE__, $this->_has_settings_page, $this->_settings_page_name );

			//set some details
			$this->_settings_menu_title = __( 'Easy Analytics', 'easy-analytics' );


			//--Start your custom goodness
			$settings = $this->get_settings();

			//first - run the upgrade check
			add_action( 'plugins_loaded', array( $this, 'ea_action_run_upgrade_check' ) );

			//setup the actions for the front end
			$hook = ( isset( $settings['location'] ) && 'header' == $settings['location'] ) ? 'wp_head' : 'wp_footer';

			add_action( $hook , array(  $this, 'ea_action_insert_bug' ) );

		}


		//=================
		// ACTION CALLBACKS
		//=================

		/**
		 * Upgrade script
		 * @since  3.2
		 *
		 */
		function ea_action_run_upgrade_check() {

			//get the current version from site options
			$current_version = get_option( 'easy_analyics_version' );

			//check against the current one
			if ( ! $current_version || version_compare( $current_version, self::version, '<' ) ) {

				//run the upgrade script is the version is lower that 3.2
				if ( version_compare( $current_version, '3.2', '<' ) ) {
					$this->upgrade_to_3_2();
				}

				//update the version number
				update_option( 'easy_analyics_version', self::version );
			}

			load_plugin_textdomain( 'easy-analytics', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * methods that outputs the actual GA snippet
		 *
		 */
		public function ea_action_insert_bug() {

			$settings = $this->get_settings();

			//sanity check to be sure we have an ID before outputting something
			if ( ! isset( $settings['tracking_id'] ) || empty( $settings['tracking_id'] ) ) {
				return;
			}

			//get the template we want to show
			$snippet_template = ( isset( $settings['type'] ) && 'ua' == $settings['type'] ) ? 'ua-snippet.php' : 'ga-snippet.php';

			$template_path = plugin_dir_path( __FILE__ ) . '_views/'. $snippet_template;

			if ( ! is_user_logged_in() && file_exists( $template_path ) ) {
				include $template_path;
			}
		}


		/**
		 * setup for displaying the pointer dialogue box
		 * most of this code was resued from a WPMU Dev tutorial
		 *
		 * @link {http://premium.wpmudev.org/blog/using-wordpress-pointers-in-your-own-plugins/}
		 */
		function ea_action_admin_enqueue_scripts() {
			// You might of course have other scripts enqueued here,
			// for functionality other than WordPress Pointers.

			// WordPress Pointer Handling
			// find out which pointer ids this user has already seen
			$seen_it = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

			// at first assume we don't want to show pointers
			$do_add_script = false;

			// Handle our first pointer announcing the plugin's new settings screen.
			// check for dismissal of pksimplenote settings menu pointer 'pksn1'
			if ( ! in_array( 'ea-settings-moved-info', $seen_it ) ) {
				// flip the flag enabling pointer scripts and styles to be added later
				$do_add_script = true;
				// hook to function that will output pointer script just for pksn1
				add_action( 'admin_print_footer_scripts', array( $this, 'ea_pointer_note_moved_footer_script' ) );
			}

			// now finally enqueue scripts and styles if we ended up with do_add_script == TRUE
			if ( $do_add_script ) {
				// add JavaScript for WP Pointers
				wp_enqueue_script( 'wp-pointer' );
				// add CSS for WP Pointers
				wp_enqueue_style( 'wp-pointer' );
			}
		}

		/**
		 * Render the pointer on-screen
		 *
		 * Each pointer has its own function responsible for putting appropriate JavaScript into footer
		 */

		function ea_pointer_note_moved_footer_script() {
			// Build the main content of your pointer balloon in a variable
			$pointer_content = __( '<h3>Easy Analytics has moved!</h3>' ); // Title should be <h3> for proper formatting.
			$pointer_content .= sprintf( __( '<p>Configuration options are now found under the <a href="%s"><b>Settings</b></a> menu instead of Plugins menu</p>' ),
				add_query_arg( 'page', esc_url( $this->_settings_page_name ), admin_url( 'options-general.php' ) ) );

			// In JavaScript below:
			// 1. "#menu-plugins" needs to be the unique id of whatever DOM element in your HTML you want to attach your pointer balloon to.
			// 2. "pksn1" needs to be the unique id, for internal use, of this pointer
			// 3. "position" -- edge indicates which horizontal spot to hang on to; align indicates how to align with element vertically
			?>
			<script type="text/javascript">// <![CDATA[
				jQuery(document).ready(function($) {
					/* make sure pointers will actually work and have content */
					if(typeof(jQuery().pointer) != 'undefined') {
						$('#menu-settings').pointer({
							content: '<?php echo wp_kses_post( $pointer_content ); ?>',
							position: {
								edge: 'left',
								align: 'center'
							},
							close: function() {
								$.post( ajaxurl, {
									pointer: 'ea-settings-moved-info',
									action: 'dismiss-wp-pointer'
								});
							}
						}).pointer('open');
					}
				});
				// ]]></script>
		<?php
		}


		//=================
		// FILTER CALLBACKS
		//=================

		/**
		 * Filters the name of the settings page
		 * uses the custom filter "mp_settings_page_title"
		 */
		function rw_settings_page_title_filter( $title ) {
			return __( 'Easy Analytics Configuration', 'easy-analytics' );
		}


		//=================
		// UPGRADE SCRIPT
		//=================

		/**
		 * Upgrade script for ver 3.2
		 *
		 */
		private function upgrade_to_3_2() {


			//add them to the new settings
			$old_settings = array(
				'tracking_id'	=> get_option( 'ea_tracking_num' ),
				'domain_name' 	=> get_option( 'ea_domain_name' ),
				'type'			=> 'ga', //anything upgrading will be using the old snippet
				'location'		=> 'footer', //anything upgrading will have been using wp_footer
			);

			//parse the settings
			$new_settings = wp_parse_args( $old_settings, $this->_default_settings );
			//save the settings
			update_option( $this->_settings_name, $new_settings );

			//run a cleanup of old, deprecated settings
			delete_option( 'ea_site_speed' );
			delete_option( 'ea_site_speed_sr' );

			//add the action to display the pointer for this update.
			add_action( 'admin_enqueue_scripts', array( $this, 'ea_action_admin_enqueue_scripts' ) );
		}




		//=================
		// SETTINGS PAGE
		//=================
		/**
		 * Install
		 *
		 * @used-by register_activation_hook() in the parent class
		 */
		function rw_plugin_install() {

			//look for the settings
			$settings = get_option( $this->_settings_name );

			if ( ! $settings ) {
				add_option( $this->_settings_name, $this->_default_settings );
			} else {

				if ( isset( $_POST[ $this->_settings_name ] ) ) {
					$updated_settings = wp_parse_args( esc_html( $_POST[ $this->_settings_name ] ), $this->_default_settings );
				} else {
					$updated_settings = get_option( $this->_settings_name );
				}

				update_option( $this->_settings_name, $updated_settings );
			}

			//update the version number
			update_option( 'easy_analyics_version', self::version );
		}


		/**
		 * Settings Page Meta Boxes
		 *
		 * Hook to create the settings meta boxes
		 * Required by the interface
		 *
		 * @used-by add_meta_boxes_settings_page_{$this->_pagename} action  in the parent class
		 */
		function rw_plugin_create_meta_boxes() {

			//debug area

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				add_meta_box(
					'debug_area', //Meta box ID
					__( 'Debug', 'easy-analytics' ), //Meta box Title
					array( $this, 'rw_render_debug_setting_box' ), //Callback defining the plugin's innards
					'settings_page_' . esc_html( $this->_pagename ), // Screen to which to add the meta box
					'side' // Context
				);
			}


			//-- additional users to allow
			add_meta_box(
				'easy_analytic_settings',
				__( 'Analytics Settings', 'easy-analytics' ),
				array( $this, 'render_easy_analytics_settings_meta' ),
				'settings_page_' . esc_html( $this->_pagename ), // Screen to which to add the meta box
				'normal' // Context
			);

		}


		/**
		 * Render the debug meta box
		 */
		function rw_render_debug_setting_box() {
			$settings = $this->get_settings();
			?>
			<table class="form-table">
				<tr>
					<td colspan="2">
						<textarea class="widefat" rows="10"><?php print_r( $settings );?></textarea>
					</td>
				</tr>
			</table>
		<?php
		}


		/**
		 * render_easy_analytics_settings_meta
		 */
		function render_easy_analytics_settings_meta() {
			$settings = $this->get_settings();
			include plugin_dir_path( __FILE__ ) . '/_views/easy-analytics-settings.php';
		}

		/**
		 * Method to save the  settings
		 *
		 * Saves the settings
		 * Required by the interface
		 *
		 * @used-by Custom action "rw_plugin_save_options" in the parent class
		 */
		function rw_plugin_save_settings() {
			//lets just make sure we can save
			if ( ! empty( $_POST ) && check_admin_referer( "{$this->_pagename}_save_settings", "{$this->_pagename}_settings_nonce" ) ) {
				//save
				if ( isset( $_POST['submit'] ) ) {
					//status message
					$old_settings = get_option( $this->_settings_name );
					$updated_settings = wp_parse_args( $_POST[ $this->_settings_name ], $old_settings );
					update_option( $this->_settings_name, $updated_settings );
					printf( '<div class="updated"> <p> %s </p> </div>',  __( 'Settings Saved','easy-analytics' ) );
				}

				//reset
				if ( isset( $_POST['reset'] ) ) {
					//status message
					update_option( $this->_settings_name, $this->_default_settings );
					printf( '<div class="error"> <p> %s </p> </div>', __( 'Settings reset to defaults','easy-analytics' ) );
				}
			}
		}


		/**
		 * Retrieve the plugin settings
		 * @return array Saved settings for this plugin
		 */
		function get_settings() {
			$settings = ( $option = get_option( $this->_settings_name ) ) ? $option : $this->_default_settings;
			return $settings;
		}
	}

	new EasyAnalytics;

endif;

?>