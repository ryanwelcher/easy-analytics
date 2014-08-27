<?php
/*
Plugin Name: Easy Analytics
Plugin URI: http://www.ryanwelcher.com/work/easy-analytics
Description: Easily add your Google Analytics tracking snippet to your WordPress site.
Author: Ryan Welcher
Version: 3.2
Author URI: http://www.ryanwelcher.com
Text Domain: ea
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


if( ! class_exists( 'EasyAnalytics' ) ):

if(!class_exists('RW_Plugin_Base')) {
	require_once plugin_dir_path( __FILE__ ) . '/_inc/RW_Plugin_Base.php';
}

class EasyAnalytics extends RW_Plugin_Base {


	/**
	 * @var string plugin version number
	 */
	const version = '3.2';


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
		$this->_settings_menu_title = __('Easy Analytics');


		//--Start your custom goodness
		$settings = $this->get_settings();
		
		//first - run the upgrade check
		add_action('plugins_loaded', array($this, 'ea_action_run_upgrade_check' ));

		//setup the actions for the front end
		$hook = ( isset( $settings['location'] ) && 'header' == $settings['location'] ) ? 'wp_head' : 'wp_footer';
		add_action( $hook , array(  $this, 'ea_action_insert_bug' ) );
		
		//admin side
		//--init the settings
		//add_action('admin_init',array( &$this,'ea_action_init_settings') );
		//--add the page to the admin area
		//add_action('admin_menu',array( &$this, 'ea_action_init_plugin_page') );
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

		//temp
		//delete_option( 'easy_analyics_version' );

		//get the current version from site options
		$current_version = get_option( 'easy_analyics_version');

		//check against the current one
		if( !$current_version || version_compare( $current_version, self::version, '<' ) ) {

			//run the upgrade script
			$this->upgrade_to_3_2();

			//update the version number
			update_option( 'easy_analyics_version', self::version );
		}
	}

	/**
	 * methods that outputs the actual GA snippet
	 *
	 */
	public function ea_action_insert_bug() {

		$settings = $this->get_settings();

		//sanity check to be sure we have an ID before outputting something
		if( !isset( $settings['tracking_id'] ) || empty( $settings['tracking_id'] ) ) {
			return;
		}

		//get the template we want to show
		$snippet_template = ( isset( $settings['type'] ) && 'ua' == $settings['type'] ) ? 'ua-snippet.php' : 'ga-snippet.php';

		$template_path = plugin_dir_path( __FILE__ ) . '_views/'. $snippet_template;
	
		if( file_exists( $template_path  ) ) {
			include $template_path;
		}
	}


	
	//=================
	// FILTER CALLBACKS
	//=================
	





	//=================
	// UPGRADE SCRIPT
	//=================
	//

	/**
	 * Upgrade script for ver 3.2
	 * 
	 */
	private function upgrade_to_3_2() {

		//get the old options we want to keep
		//
		$tracking_num 	= get_option('ea_tracking_num');
		$domain_name 	= get_option('ea_domain_name');

		//add them to the new settings
		$old_settings = array(
			'tracking_id'	=> get_option('ea_tracking_num'),
			'domain_name' 	=> get_option('ea_domain_name'),
			'type'			=> 'ga', //anything upgrading will be using the old snippet
			'location'		=> 'footer' //anything upgrading will have been using wp_footer
		);

		//parse the settings
		$new_settings = wp_parse_args( $old_settings, $this->_default_settings );
		//save the settings
		update_option( $this->_settings_name, $new_settings );

		//run a cleanup of old, deprecated settings
		delete_option( 'ea_site_speed' );
		delete_option( 'ea_site_speed_sr' );
	}




	//=================
	// SETTINGS PAGE
	//=================
	/**
	 * Install
	 *
	 * Required by the interface - can be stubbed out if nothing is required on activation
	 * @used-by register_activation_hook() in the parent class
	 */
	function rw_plugin_install() {

		if( $this->_has_settings_page ) {

			//look for the settings
			$settings = get_option($this->_settings_name);

			if(!$settings) {
				add_option( $this->_settings_name, $this->_default_settings );
			}else{

				if( isset( $_POST[$this->_settings_name] ) ) {
					$updated_settings = wp_parse_args( $_POST[$this->_settings_name], $this->_default_settings );
				}else{
					$updated_settings = get_option( $this->_settings_name );
				}
				
				update_option( $this->_settings_name, $updated_settings );
			}
		}
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
		add_meta_box(
			'debug_area', //Meta box ID
			__('Debug', 'ruc'), //Meta box Title
        array(&$this, 'rw_render_debug_setting_box'), //Callback defining the plugin's innards
        'settings_page_'.$this->_pagename, // Screen to which to add the meta box
        'side' // Context
    	);

    	//-- additional users to allow 
    	add_meta_box(
    		'easy_analytic_settings',
    		__('Analytics Settings', 'ed'),
    		array( $this, 'render_easy_analytics_settings_meta'),
    		'settings_page_'.$this->_pagename, // Screen to which to add the meta box
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
		if ( !empty($_POST) && check_admin_referer( "{$this->_pagename}_save_settings", "{$this->_pagename}_settings_nonce" ) ) {
			//save
			if( isset( $_POST['submit'] ) ) {
				//status message
				$old_settings = get_option( $this->_settings_name );
				$updated_settings = wp_parse_args( $_POST[$this->_settings_name], $old_settings );
				update_option($this->_settings_name, $updated_settings);
				printf('<div class="updated"> <p> %s </p> </div>', __('Settings Saved', 'ruc' ) );
			}
			
			//reset
			if( isset( $_POST['reset'] ) ) {
				//status message
				update_option($this->_settings_name, $this->_default_settings );
				printf('<div class="error"> <p> %s </p> </div>', __('Settings reset to defaults', 'ruc') );
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