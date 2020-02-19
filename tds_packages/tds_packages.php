<?php
/*
Plugin Name: Build Custom Packages for Tours and Activities
Plugin URI: http://dustysun.com
Description: Allows adding custom packages that can be shown on pages using a simple shortcode
Version: 2.0.1
Date: 2020-02-19
Author: Dusty Sun
Author URI: http://dustysun.com
License: GPLv2
*/

//Include the shortcode class
require_once( dirname( __FILE__ ) . '/tds_packages_shortcode.php');

//Include the admin panel page
include( dirname( __FILE__ ) . '/tds_packages_admin.php');

//Include the CPT functions
require_once( dirname( __FILE__ ) . '/lib/dustysun-wp-cpt-api/ds_wp_cpt_api.php');

require_once( dirname( __FILE__ ) . '/cpt/tds_packages.php');
use \TDSPackages\CPT;

class TDS_Packages {

  public function __construct() {

    // activate the CPT
    $this->tds_packages_cpt = new CPT\TDSPackagesCPT();

    // Register the JS for the admin screen
    add_action( 'admin_enqueue_scripts', array($this, 'register_admin_tds_packages_scripts'));
    // Register style sheet.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_tds_packages_styles' ) );

    register_activation_hook( __FILE__, array($this, 'tds_default_settings' ));

  }


  //Enqueue the styles
  public function register_tds_packages_styles() {
    wp_register_style('tds-packages', plugins_url('css/tds-packages.css', __FILE__), '', '1.2.2');
    wp_enqueue_style('tds-packages');

    wp_register_style('tds-magnific-popup', plugins_url('lib/magnific-popup/magnific-popup.css', __FILE__));
    wp_enqueue_style('tds-magnific-popup');

    wp_register_style('tds-flexslider', plugins_url('lib/flexslider/flexslider.css', __FILE__));
    wp_enqueue_style('tds-flexslider');

    wp_register_script('tds-packages', plugins_url('js/tds-packages.js', __FILE__), array('jquery'), '1.4.3');
    wp_localize_script( 'tds-packages', 'ajaxfrontendurl',
    array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    wp_enqueue_script('tds-packages');

    wp_register_script('tds-magnific-popup', plugins_url('lib/magnific-popup/jquery.magnific-popup.min.js', __FILE__));
    wp_enqueue_script('tds-magnific-popup');

    wp_register_script('tds-flexslider', plugins_url('lib/flexslider/jquery.flexslider-min.js', __FILE__));
    wp_enqueue_script('tds-flexslider');


  } //end public function register_tds_packages_styles

  //Enqueue the scripts for the post editor
  public function register_admin_tds_packages_scripts() {
    wp_register_script('tds-packages-admin-js', plugins_url('/js/tds-packages-admin.js', __FILE__));
    wp_enqueue_script('tds-packages-admin-js');
  } //end public function register_tds_packages_styles


  public function tds_default_settings() {
    if(!get_option('tds_packages_activity_color_option')): add_option( 'tds_packages_activity_color_option', '#2d75b6'); endif;
    if(!get_option('tds_packages_destination_color_option')): add_option( 'tds_packages_destination_color_option', '#2d75b6'); endif;
    if(!get_option('tds_packages_location_color_option')): add_option( 'tds_packages_location_color_option', '#2d75b6'); endif;
    if(!get_option('tds_packages_other_color_option')): add_option( 'tds_packages_other_color_option', '#2d75b6'); endif;
    if(!get_option('tds_packages_tour_color_option')): add_option( 'tds_packages_tour_color_option', '#2d75b6'); endif;

  } //end function tds_default_settings()


} //end class TDS_Packages


  $tds_packages = new TDS_Packages();
