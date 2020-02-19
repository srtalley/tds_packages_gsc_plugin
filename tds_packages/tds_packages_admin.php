<?php
class TDSPackagesSettingsPage
{

	//private var to hold the options from the callbacks
	private $options;

	//Create the object
	public function __construct() {
		//Register the menu
		add_action( 'admin_menu', array($this, 'tds_packages_admin_menu' ));
		add_action( 'admin_init', array($this, 'tds_packages_register_settings' ));
		add_action( 'admin_enqueue_scripts', array($this,  'tds_packages_add_color_picker' ));

	} //end public function __construct()


	//WordPress function to sanitize inputs
	/**
 * Sanitize each setting field as needed
 *
 * @param array $input Contains all settings fields as array keys
 */
	public function sanitize( $input )
	{
		$new_input = array();
		if( isset( $input['id_number'] ) )
				$new_input['id_number'] = absint( $input['id_number'] );

		if( isset( $input['title'] ) )
				$new_input['title'] = sanitize_text_field( $input['title'] );

		return $new_input;
	}
	// Adds admin menu under the Sections section in the Dashboard
	public function tds_packages_admin_menu() {

		add_submenu_page(
				'edit.php?post_type=tds_packages',
				__('Settings', 'tds_packages'),
				__('Settings', 'tds_packages'),
				'manage_options',
				'tds_packages_menu',
				array($this, 'tds_packages_menu_options'));
	}





	/* Create the actual options page */

	public function tds_packages_menu_options() {

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		if ( ! isset( $_REQUEST['settings-updated'] ) )
	          $_REQUEST['settings-updated'] = false;
	     ?>

		<div class="wrap">
			<?php if ( false !== $_REQUEST['settings-updated'] ) : ?>
				<div class="updated fade"><p><strong><?php _e( 'Options saved!', 'tds_packages_plugin' ); ?></strong></p></div>
			<?php endif; ?>

			<h2>Package Profiles Settings</h2>
			<p>The shortcode is <strong>[show-packages]</strong></p>
			<p>You can add additional options to select from one or more categories. For example, the shortcode <strong>[show-packages class="tours" destination="rwanda"]</strong> would list all of the tours for Rwanda.</P>
			<p>The part after the equals sign must match the exact name you've entered for that category. You can choose multiple items from a category by adding a comma, such as destination="rwanda, uganda".</p>

			<p>You can combine any of these shortcodes:</p>
			<p>
				<ul style="list-style:disc; margin-left: 40px;">
					<li>class="xyz"</li>
					<li>destination="xyz"</li>
					<li>destination_exclude="xyz"</li>
					<li>duration="xyz"</li>
					<li>duration_exclude="xyz"</li>
					<li>location="xyz"</li>
					<li>location_exclude="xyz"</li>
					<li>type="xyz"</li>
					<li>type_exclude="xyz"</li>
				</ul>
			</p>

			<hr>
			<div class="wrap form">
				<form action="options.php" method="POST">
					<?php settings_fields( 'tds_packages_settings' ); ?>
				  <?php do_settings_sections( 'tds_packages_menu' ); ?>
					<?php submit_button(); ?>
				</form>
			</div>
		</div>
		</div>

	<?php
	} //end function tds_packages_menu_options

	public function tds_packages_add_color_picker( $hook ) {

	    if( is_admin() ) {

	        // Add the color picker css file
	        wp_enqueue_style( 'wp-color-picker' );

	        // Include our custom jQuery file with WordPress Color Picker dependency
	        // wp_enqueue_script( 'custom-script-handle', plugins_url( 'custom-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
	    }
	}
	/* Register the various settings */
	public function tds_packages_register_settings() {

		add_settings_section('tds_packages_settings','Color Settings', array($this,'tds_packages_settings_section_callback'), 'tds_packages_menu');

		//activity colors
		register_setting( 'tds_packages_settings', 'tds_packages_activity_color_option' );
		add_settings_field('tds_packages_activity_color_option',__('Activities:', 'tds_packages'), array($this,'tds_packages_activity_color_option_callback'), 'tds_packages_menu', 'tds_packages_settings');

		//location colors
		register_setting( 'tds_packages_settings', 'tds_packages_location_color_option' );
		add_settings_field('tds_packages_location_color_option',__('Locations:', 'tds_packages'), array($this,'tds_packages_location_color_option_callback'), 'tds_packages_menu', 'tds_packages_settings');

		//other colors
		register_setting( 'tds_packages_settings', 'tds_packages_other_color_option' );
		add_settings_field('tds_packages_other_color_option',__('Other:', 'tds_packages'), array($this,'tds_packages_other_color_option_callback'), 'tds_packages_menu', 'tds_packages_settings');

		//tour colors
		register_setting( 'tds_packages_settings', 'tds_packages_tour_color_option' );
		add_settings_field('tds_packages_tour_color_option',__('Tours:', 'tds_packages'), array($this,'tds_packages_tour_color_option_callback'), 'tds_packages_menu', 'tds_packages_settings');
	} //end public function tds_packages_register_settings


	public function tds_packages_settings_section_callback() {
		echo '<P>Change colors for the various package types below.</P>';
	} //end function


	public function tds_packages_activity_color_option_callback() {
		$option = get_option('tds_packages_activity_color_option');
		if(empty($option)): $option = '#000000'; endif;
		echo '<input type="text" id="tds_packages_activity_color_option" name="tds_packages_activity_color_option" value="'. $option . '" class="cpa-color-picker" />';
	} //end function


	public function tds_packages_location_color_option_callback() {
		$option = get_option('tds_packages_location_color_option');
		if(empty($option)): $option = '#000000'; endif;
		echo '<input type="text" id="tds_packages_location_color_option" name="tds_packages_location_color_option" value="'. $option . '" class="cpa-color-picker" />';
	} //end function


	public function tds_packages_other_color_option_callback() {
		$option = get_option('tds_packages_other_color_option');
		if(empty($option)): $option = '#000000'; endif;
		echo '<input type="text" id="tds_packages_other_color_option" name="tds_packages_other_color_option" value="'. $option . '" class="cpa-color-picker" />';
	} //end function


	public function tds_packages_tour_color_option_callback() {
		$option = get_option('tds_packages_tour_color_option');
		if(empty($option)): $option = '#000000'; endif;
		echo '<input type="text" id="tds_packages_tour_color_option" name="tds_packages_tour_color_option" value="'. $option . '" class="cpa-color-picker" />';
	} //end function


} //end class TDSPackagesSettingsPage
if( is_admin() )
    $tds_packages_settings_page = new TDSPackagesSettingsPage();
