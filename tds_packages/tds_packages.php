<?php
/*
Plugin Name: Build Custom Packages for Tours and Activities
Plugin URI: http://talleyservices.com
Description: Allows adding custom packages that can be shown on pages using a simple shortcode
Version: 1.1
Author: Talley Services
Author URI: http://talleyservices.com
License: GPLv2
*/

//Include the shortcode class
require_once( dirname( __FILE__ ) . '/tds_packages_shortcode.php');

//Include the admin panel page
include( dirname( __FILE__ ) . '/tds_packages_admin.php');

class TDS_Packages {
  // private $meta_box_field_array = array();
  public function __construct() {
    //Fill the meta box fields in the object
    $this->meta_box_fields = $this->tds_packages_add_fields();

    // Register the JS for the admin screen
    add_action( 'admin_enqueue_scripts', array($this, 'register_admin_tds_packages_scripts'));

    // Register style sheet.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_tds_packages_styles' ) );

    // Allow file uploads
    add_action('post_edit_form_tag', array($this, 'update_edit_form'));

    // Register the CPT
    add_action( 'init', array( $this, 'register_tds_packages' ) );

    // Register the taxonomies
    add_action( 'init', array( $this, 'tds_packages_register_taxonomies' ));

    // Register the custom title
    add_filter( 'enter_title_here', array($this, 'tds_packages_change_title_text') );

    // Add the meta box fields
    add_action('add_meta_boxes', array($this, 'tds_add_main_meta_box'));

    // Add the custom columns to the post field view
    add_filter('manage_edit-tds_packages_columns', array($this, 'set_custom_edit_tds_packages_columns'));

    // Fill the custom columns with data from  postmeta
    add_action('manage_tds_packages_posts_custom_column', array($this, 'custom_tds_packages_column'), 10, 2);

    //Allow custom column sorting
    add_filter('manage_edit-tds_packages_sortable_columns', array($this, 'custom_tds_packages_sortable_columns'));
    add_filter('request', array($this, 'tds_package_post_type_orderby'));

    //Save the CPT data
    add_action('save_post',  array($this,'tds_save_data'));

    register_activation_hook( __FILE__, array($this, 'tds_default_settings' ));

  }


  //Enqueue the styles
  public function register_tds_packages_styles() {
    wp_register_style('tds-packages-css', plugins_url('tds-packages.css', __FILE__));
    wp_enqueue_style('tds-packages-css');
  } //end public function register_tds_packages_styles

  //Enqueue the scripts for the post editor
  public function register_admin_tds_packages_scripts() {
    wp_register_script('tds-packages-admin-js', plugins_url('tds-packages-admin.js', __FILE__));
    wp_enqueue_script('tds-packages-admin-js');
  } //end public function register_tds_packages_styles

  //Allow the CPT form to have file uploads
  public function update_edit_form() {
      echo ' enctype="multipart/form-data"';
  } // end update_edit_form


  public function register_tds_packages() {
    // debug_to_console('this');
    $labels = array(
      'name' => __( 'Package Profiles', 'tds_packages_plugin' ),
  		'singular_name' => __( 'Package', 'tds_packages_plugin' ),
  		'add_new_item' => __( 'Add New Package', 'tds_packages_plugin' ),
  		'edit_item' => __( 'Edit Package', 'tds_packages_plugin' ),
  		'new_item' => __( 'New Package', 'tds_packages_plugin' ),
  		'not_found' => __( 'No Packages found', 'tds_packages_plugin' ),
  		'all_items' => __( 'All Packages', 'tds_packages_plugin' )
    );
    $args   = array(
      'labels' => $labels,
      'public' => false,
      'publicly_queriable' => true,
      'show_ui' => true,
      'show_in_menu' => true,
      'show_in_nav_menus' => false,
      'has_archive' => false,
      'rewrite' => false,
      'map_meta_cap' => true,
      'menu_icon' => 'dashicons-index-card',
      'supports' => array( 'title', 'thumbnail', 'author' ),
      'exclude_from_search' => true,
    );

    register_post_type( 'tds_packages', $args );
  } //end private function register_tds_packages

  public function tds_packages_add_fields() {

    //Add the fields that will go beneath the main post area
    $meta_box_fields['tds_packages'][] = array(
        'section_name' => 'tds_packages_details',
        'title' => 'Package Details',
        'context' => 'normal',
        'priority' => 'high',
        'fields' => array(
          array(
              'name' => 'Post Type:',
              'desc' => '',
              'id' => 'tds_package_post_type',
              'type' => 'radio',
              'options' => array(
                  'tds_package_activity' => array(
                    'label' => 'Activity',
                    'value' => 'Activity',
                  ),
                  'tds_package_location' => array(
                    'label' => 'Location',
                    'value' => 'Location',
                  ),
                  'tds_package_tour' => array(
                    'label' => 'Tour',
                    'value' => 'Tour',
                  ),
                  'tds_package_other' => array(
                    'label' => 'Other',
                    'value' => 'Other',
                  ),
                ),
          ),
          array(
              'name' => 'Link Type:',
              'desc' => '',
              'id' => 'tds_package_link_type',
              'type' => 'radio',
              'options' => array(
                  'tds_package_same_window' => array(
                    'label' => 'Same Window',
                    'value' => 'self',
                  ),
                  'tds_package_new_window' => array(
                    'label' => 'New Window',
                    'value' => 'blank',
                  ),

                  // 'tds_package_lightbox' => array(
                  //   'label' => 'Lightbox',
                  //   'value' => 'lightbox',
                  // ),
                ),
          ),
          array(
              'name' => 'Link Location:',
              'desc' => '',
              'id' => 'tds_package_page_url',
              'type' => 'text',
              'default' => ''
          ),
          array(
              'name' => 'Link Text:',
              'desc' => '',
              'id' => 'tds_package_page_link_text',
              'type' => 'text',
              'default' => ''
          ),
          array(
              'name' => 'Package Description:',
              'desc' => '',
              'id' => 'tds_package_description',
              'type' => 'texteditor',
              'default' => ''
          ),


        )
    );
    //Add the fields that will go on the side of the main post area

    $meta_box_fields['tds_packages'][] = array(
        'section_name' => 'tds_packages_details_side',
        'title' => 'Package Price',
        'context' => 'side',
        'priority' => 'low',
        'fields' => array(
          array(
              'name' => 'Price:',
              'desc' => '',
              'id' => 'tds_package_price',
              'type' => 'text',
              'default' => ''
          ),
        )
    );
   return $meta_box_fields;
  }

  //Add custom columns and unset columns to post list view
  public function set_custom_edit_tds_packages_columns($columns)
  {
    $columns['cb'] = '<input type="checkbox" />';
    $columns['title'] = __('Package Name');
    $columns['tds_package_post_type'] = __('Package Type');
    unset($columns['author']);
    return $columns;
  } //end function set_custom_edit_tds_packages_columns

  //Add the custom postmeta info to the column
  public function custom_tds_packages_column($column, $post_id)
  {
    switch ($column) {
      case 'tds_package_post_type':
      echo get_post_meta( $post_id , 'tds_package_post_type' , true );
      break;
    } //end switch
  } //end function custom_tds_packages_column


  //set up the sortable columns
  public function custom_tds_packages_sortable_columns($columns)
  {
    $columns['tds_package_post_type'] = 'tds_package_post_type';
    return $columns;
  } //end function set_custom_edit_tds_packages_columns

  //allow sorting of the custom columns
  public function tds_package_post_type_orderby($vars)
  {
    if ( isset( $vars['orderby'] ) && 'tds_package_post_type' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => 'tds_package_post_type',
            'orderby' => 'meta_value'
        ) );
    }

    return $vars;
  } //end function tds_package_post_type_orderby

  public function tds_packages_register_taxonomies(){

    $destination_labels =  array(
        'name'              => __( 'Destinations', 'tds_packages_plugin'),
        'singular_name'     => __( 'Destination', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Destinations', 'tds_packages_plugin' ),
        'all_items'         => __( 'Destinations', 'tds_packages_plugin' ),
        'parent_item'       => __( 'Parent Destination', 'tds_packages_plugin' ),
        'parent_item_colon' => __( 'Parent Destination:', 'tds_packages_plugin' ),
        'edit_item'         => __( 'Edit Destination', 'tds_packages_plugin' ),
        'update_item'       => __( 'Update Destination', 'tds_packages_plugin' ),
        'add_new_item'      => __( 'Add New Destination', 'tds_packages_plugin' ),
        'new_item_name'     => __( 'New Destination', 'tds_packages_plugin' ),
        'menu_name'         => __( 'Destinations', 'tds_packages_plugin' ),
    );
    $destination_args = array(
        'hierarchical'      => true,
        'labels'            => $destination_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array("slug" => "tds_destinations"),
    );
    register_taxonomy( 'tds-packages-destination', array( 'tds_packages' ), $destination_args );


    $duration_labels =  array(
        'name'              => __( 'Durations', 'tds_packages_plugin'),
        'singular_name'     => __( 'Duration', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Durations', 'tds_packages_plugin' ),
        'all_items'         => __( 'Durations', 'tds_packages_plugin' ),
        'parent_item'       => __( 'Parent Duration', 'tds_packages_plugin' ),
        'parent_item_colon' => __( 'Parent Duration:', 'tds_packages_plugin' ),
        'edit_item'         => __( 'Edit Duration', 'tds_packages_plugin' ),
        'update_item'       => __( 'Update Duration', 'tds_packages_plugin' ),
        'add_new_item'      => __( 'Add New Duration', 'tds_packages_plugin' ),
        'new_item_name'     => __( 'New Duration', 'tds_packages_plugin' ),
        'menu_name'         => __( 'Durations', 'tds_packages_plugin' ),
    );
    $duration_args = array(
        'hierarchical'      => true,
        'labels'            => $duration_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array("slug" => "tds_durations"),
    );
    register_taxonomy( 'tds-packages-duration', array( 'tds_packages' ), $duration_args );


    $location_labels =  array(
        'name'              => __( 'Locations', 'tds_packages_plugin'),
        'singular_name'     => __( 'Location', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Locations', 'tds_packages_plugin' ),
        'all_items'         => __( 'Locations', 'tds_packages_plugin' ),
        'parent_item'       => __( 'Parent Location', 'tds_packages_plugin' ),
        'parent_item_colon' => __( 'Parent Location:', 'tds_packages_plugin' ),
        'edit_item'         => __( 'Edit Location', 'tds_packages_plugin' ),
        'update_item'       => __( 'Update Location', 'tds_packages_plugin' ),
        'add_new_item'      => __( 'Add New Location', 'tds_packages_plugin' ),
        'new_item_name'     => __( 'New Location', 'tds_packages_plugin' ),
        'menu_name'         => __( 'Locations', 'tds_packages_plugin' ),
    );
    $location_args = array(
        'hierarchical'      => true,
        'labels'            => $location_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array("slug" => "tds_locations"),
    );
    register_taxonomy( 'tds-packages-location', array( 'tds_packages' ), $location_args );

    $type_labels =  array(
        'name'              => __( 'Types', 'tds_packages_plugin'),
        'singular_name'     => __( 'Type', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Types', 'tds_packages_plugin' ),
        'all_items'         => __( 'Types', 'tds_packages_plugin' ),
        'parent_item'       => __( 'Parent Type', 'tds_packages_plugin' ),
        'parent_item_colon' => __( 'Parent Type:', 'tds_packages_plugin' ),
        'edit_item'         => __( 'Edit Type', 'tds_packages_plugin' ),
        'update_item'       => __( 'Update Type', 'tds_packages_plugin' ),
        'add_new_item'      => __( 'Add New Type', 'tds_packages_plugin' ),
        'new_item_name'     => __( 'New Type', 'tds_packages_plugin' ),
        'menu_name'         => __( 'Types', 'tds_packages_plugin' ),
    );
    $type_args = array(
        'hierarchical'      => true,
        'labels'            => $type_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array("slug" => "tds_types"),
    );
    register_taxonomy( 'tds-packages-type', array( 'tds_packages' ), $type_args );

  }
  private function debug_to_console($data) {
      if(is_array($data) || is_object($data))
  	{
  		echo("<script>console.log('PHP: ".json_encode($data)."');</script>");
  	} else {
      echo("<script>console.log('PHP: ".addslashes($data)."');</script>");
  	}
  }
  private function print_r2($val){
          echo '<pre>';
          print_r($val);
          echo  '</pre>';
  }

  //Change the "Enter Title Here" text
  public function tds_packages_change_title_text( $title ){
       $addPostScreeen = get_current_screen();

     if  ( 'tds_packages' == $addPostScreeen->post_type ) {
            $title = 'Enter Package Name';
       }

       return $title;
  } //end function tds_packages_change_title_text( $title )

  public function tds_default_settings() {
    if(!get_option('tds_packages_activity_color_option')): add_option( 'tds_packages_activity_color_option', '#2d75b6'); endif;
    if(!get_option('tds_packages_location_color_option')): add_option( 'tds_packages_location_color_option', '#2d75b6'); endif;
    if(!get_option('tds_packages_other_color_option')): add_option( 'tds_packages_other_color_option', '#2d75b6'); endif;
    if(!get_option('tds_packages_tour_color_option')): add_option( 'tds_packages_tour_color_option', '#2d75b6'); endif;

  } //end function tds_default_settings()

  //Generic sections to add meta boxes to post types
  public function tds_add_main_meta_box($post_type) {
      $meta_box_fields_to_add = $this->meta_box_fields;
      foreach($meta_box_fields_to_add as $post_type => $meta_box_value) {
        foreach($meta_box_value as $value){
          // debug_to_console($value['fields']);
          add_meta_box($value['section_name'], $value['title'], array( $this, 'tds_standard_format_box'), $post_type, $value['context'], $value['priority'], $value['fields']);
        }
      }
  } //end function tds_add_main_meta_box($meta_box)

  //Format meta boxes
  public function tds_standard_format_box($post, $callback_fields) {

    // Use nonce for verification
    wp_nonce_field(basename(__FILE__), 'tds_meta_box_nonce');

    echo '<table class="form-table">';

    foreach ($callback_fields['args'] as $field) {

        // get current post meta data
        $meta = get_post_meta($post->ID, $field['id'], true);

        $standardFieldLabel = '<tr>'.
                '<th style="width:20%"><label for="'. $field['id'] .'">'. $field['name']. '</label></th>'.
                '<td>';
        $expandedFieldLabel = '<tr>'.
                '<th style="width:40%"><label for="'. $field['id'] .'">'. $field['name']. '</label></th>'.
                '<td>';
        $topFieldLabel = '<tr>'.
                '<th COLSPAN=2 style="width:20%; padding-bottom:0px;"><label for="'.
                $callback_fields['id'] .'">'. $field['name']. '</label></th></tr>'.
                '<tr><td COLSPAN=2>';
        switch ($field['type']) {
            case 'text':
                echo $standardFieldLabel;
                echo ' <input type="text" name="'. $field['id']. '" id="'. $field['id'] .'" value="'. ($meta ? $meta : $field['default']) . '" size="30" style="width:100%" />'. '<br />'. $field['desc'];
                break;
          case 'text_small':
            echo $standardFieldLabel;
            echo ' <input type="text" name="'. $field['id']. '" id="'. $callback_fields['id'] .'" value="'. ($meta ? $meta : $field['default']) . '" size="30" style="width:100%" />'. '<br />'. $field['desc'].'</p>';
          break;
            case 'textarea':
                echo $standardFieldLabel;
                echo '<textarea name="'. $field['id']. '" id="'. $field['id']. '" cols="60" rows="4" style="width:97%">'. ($meta ? $meta : $field['default']) . '</textarea>'. '<br />'. $field['desc'];
                break;
            case 'select':
                echo $expandedFieldLabel;
                echo '<select name="'. $field['id'] . '" id="'. $field['id'] . '">';
                foreach ($field['options'] as $option) {
                    echo '<option '. ( $meta == $option ? ' selected="selected"' : '' ) . '>'. $option . '</option>';
                }
                echo '</select>';
                break;
            case 'radio':
                echo $standardFieldLabel;
                //Set a counter for how many items there are
                //If this is the first item, we'll check it in case there
                //are no items actually checked
                $radioCounter = 1;
                foreach ($field['options'] as $radioKey => $option) {
                  echo '<input type="radio" value="'.$option['value'].'" name="'.$field['id'].'" id="'.$radioKey.'"',$meta == $option['value'] || $radioCounter == 1 ? ' checked="checked"' : '',' />
                  <label for="'.$radioKey.'">'.$option['label'].'</label> &nbsp;&nbsp;';
                  //increase the radioCounter
                  $radioCounter++;
                }
                break;
            case 'checkbox':
                echo $standardFieldLabel;
                foreach ($field['options'] as $checkKey => $option) {
                  echo '<input type="checkbox" value="'.$option['value'].'" name="'.$field['id'].'[]" id="'.$checkKey.'"',$meta && in_array($option['value'], $meta) ? ' checked="checked"' : '',' />
                  <label for="'.$checkKey.'">'.$option['label'].'</label> &nbsp;&nbsp;';
                }
                break;
            case 'texteditor':
                echo $topFieldLabel;
                wp_editor( $meta, $field['id'], array(
                  'wpautop'       => true,
                  'media_buttons' => false,
                  'textarea_name' => $field['id'],
                  'textarea_rows' => 10,
                  'teeny'         => true
                ));
                break;
            case 'pdfattachment':
                echo $standardFieldLabel;
                if(!empty($meta['url'])):
                  $path = parse_url($meta['url'], PHP_URL_PATH);
                  $pathFragments = explode('/', $meta['url']);
                  $end = end($pathFragments);
                  echo '<a href="'. $meta['url'] .'" target="_blank">' . $end . '</a>';
                endif;
                echo ' <input type="file" name="'. $field['id']. '" id="'. $field['id'] .'" size="30" style="width:100%" />'. '<br />'. $field['desc'];
              break;
        }
        echo     '</td>'.'</tr>';
    } //end   foreach ($meta_box[$post->post_type]['fields'] as $field) {

    echo '</table>';

  }//end  function tds_standard_format_box($post, $callback_fields)

  public function write_log ( $log )  {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
  public function tds_save_data($post_id) {

    global $post;
    $meta_box_fields = $this->meta_box_fields;

    //Verify nonce
    if (!isset($_POST['tds_meta_box_nonce']) || !wp_verify_nonce($_POST['tds_meta_box_nonce'], basename(__FILE__))) {
        return;
    } else {
      //Check autosave
      if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
          return $post_id;
      }

      //Check permissions
      if (!current_user_can('edit_page', $post_id)) {
          return $post_id;
      }


      // if ('page' == $_POST['post_type']) {
      //
      // } else {
      //     return $post_id;
      // }
      // $this->write_log($_FILES);

      foreach($meta_box_fields as $post_type => $meta_box_sections) {

        foreach($meta_box_sections as $meta_box_values){

          foreach($meta_box_values['fields'] as $field){





            //check if this is a file upload
            if($field['type'] == 'pdfattachment') {
              //Check if the $_FILES array is filled
              if(!$_FILES[$field['id']]['error'] == 4) {

              $supported_types = array('application/pdf');
              $arr_file_type = wp_check_filetype(basename($_FILES[$field['id']]['name']));
              $uploaded_type = $arr_file_type['type'];
               $upload = wp_upload_bits($_FILES[$field['id']]['name'], null, file_get_contents($_FILES[$field['id']]['tmp_name']));
              if(in_array($uploaded_type, $supported_types)) {
                  $upload = wp_upload_bits($_FILES[$field['id']]['name'], null, file_get_contents($_FILES[$field['id']]['tmp_name']));
                  if(isset($upload['error']) && $upload['error'] != 0) {
                      wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
                  } else {
                      update_post_meta($post_id, $field['id'], $upload);
                  }
              }
              else {
                  wp_die("The file type that you've uploaded is not a PDF.");
              }
              } //new
            } //end if($field['type'] == 'pdfattachment')

            else {
              //Do a regular update
              $old = get_post_meta($post_id, $field['id'], true);
              $new = $_POST[$field['id']];
              if ($new && $new != $old) {
                  update_post_meta($post_id, $field['id'], $new);
              } elseif ('' == $new && $old) {
                  delete_post_meta($post_id, $field['id'], $old);
              }
            }//end if($_FILES)



          } //end foreach($meta_box_value as $field)
        } //end foreach($meta_box_sections as $meta_box_values)
      } // end foreach($meta_box_fields as $post_type => $meta_box_sections)
    } //end if (!isset($_POST['tds_meta_box_nonce'])
  }


} //end class TDS_Packages

//http://stackoverflow.com/questions/2843356/can-i-pass-arguments-to-my-function-through-add-action

  $tds_packages = new TDS_Packages();
