<?php 

class TDS_Packages_Shortcode {
////////////////////////////////////////////////////
// PACKAGES - TOURS AND ACTIVITIES SHORTCODE
////////////////////////////////////////////////////

  public function __construct() {

    add_shortcode( 'show-packages', array($this, 'add_tds_packages_shortcode') );
//     add_shortcode( 'pkg-gallery-link', array($this, 'add_tds_pkg_gallery_link_shortcode') );
//     add_action( 'wp_ajax_nopriv_tds_load_slider_images', array($this, 'tds_load_slider_images') );
//     add_action( 'wp_ajax_tds_load_slider_images',  array($this, 'tds_load_slider_images') );
  }

  public function add_tds_packages_shortcode($tdsAttributes =[], $tdsContent = null, $tdsTag = '') {

    //make the array keys and attributes lowercase
    $tdsAttributes = array_change_key_case((array)$tdsAttributes, CASE_LOWER);
    //override any default attributes with the user defined parameters
    $tdsCustomAttributes = shortcode_atts([
      'class'          => 'all',
      'destination'    => 'all',
      'duration'       => 'all',
      'location'       => 'all',
      'type'           => 'all',
      'style'          => 'side',
      'desc'           => 'yes',
      'cols'           => 'two',
    ], $tdsAttributes, $tdsTag);

    $tds_meta_query_combined = array();
    $tds_taxonomy_query_combined = array();

    // $tds_class_query = '';
    //build our taxonomy query, depending on shortcodes
    if($tdsCustomAttributes['class'] != 'all') {
      //wee have to serialize the item to match
      $tds_meta_query_combined = array(
        'key' => 'tds_package_post_type',
        'value' => $tdsCustomAttributes['class'],
        'compare' => 'LIKE'
      );
    } //end if($tdsCustomAttributes['class'] != 'all')

    if($tdsCustomAttributes['destination'] != 'all') {
      //we have to "explode" the string into an array if there are multiple items
      $tds_taxonomy_query_combined[] = array(
        'taxonomy' => 'tds-packages-destination',
        'field' => 'slug',
        'terms' => explode(',', $tdsCustomAttributes['destination']),
      );
    } //end if($tdsCustomAttributes['destination'] != 'all')

    if($tdsCustomAttributes['duration'] != 'all') {
      //we have to "explode" the string into an array if there are multiple items
      $tds_taxonomy_query_combined[] = array(
        'taxonomy' => 'tds-packages-duration',
        'field' => 'slug',
        'terms' => explode(',', $tdsCustomAttributes['duration']),
      );
    } //end if($tdsCustomAttributes['duration'] != 'all')

    if($tdsCustomAttributes['location'] != 'all') {
      //we have to "explode" the string into an array if there are multiple items
      $tds_taxonomy_query_combined[] = array(
        'taxonomy' => 'tds-packages-location',
        'field' => 'slug',
        'terms' => explode(',', $tdsCustomAttributes['location']),
      );
    } //end if($tdsCustomAttributes['location'] != 'all')

    if($tdsCustomAttributes['type'] != 'all') {
      //we have to "explode" the string into an array if there are multiple items
      $tds_taxonomy_query_combined[] = array(
        'taxonomy' => 'tds-packages-type',
        'field' => 'slug',
        'terms' => explode(',', $tdsCustomAttributes['type']),
      );
    } //end if($tdsCustomAttributes['type'] != 'all')

    global $paged;


    $tds_packages_args = array(
      'post_type' => 'tds_packages',
      'post_status' => 'publish',
      'orderby' => 'title',
      'order' => 'ASC',
      'posts_per_page' => -1,
      // 'paged' => $paged,
      'fields' => 'ids',
      'meta_query' => array( $tds_meta_query_combined ),
      'tax_query' => $tds_taxonomy_query_combined
    );

    $tds_packages_query = new WP_Query( $tds_packages_args );

    if( $tds_packages_query->have_posts() ){
      while( $tds_packages_query->have_posts() ){
        $tds_packages_query->the_post();

        $tds_packages_post_id = get_the_ID();
        $tds_packages_query_title = get_the_title();


        //GET THE DURATIONS
        $tds_duration_names = array();
        $tds_packages_duration = get_the_terms($tds_packages_post_id, 'tds-packages-duration');
        if($tds_packages_duration):foreach($tds_packages_duration as $tds_duration):
          $tds_duration_names[] = $tds_duration->name;
        endforeach; endif;
        $tds_durations = implode('', $tds_duration_names);

        //Package price
        $tds_package_price = get_post_meta($tds_packages_post_id, 'tds_package_price', true);


        $tds_packages_query_items[] = array(
          'id'      => $tds_packages_post_id,
          'title'   => $tds_packages_query_title,
          'duration' => $tds_durations,
          'price'   => $tds_package_price
        );
      } // end while( $tds_packages_query->have_posts() )
    } // end if( $tds_packages_query->have_posts()

    //handle post titles that start with numbers. Order 10 after 9 instead of after 1
    usort($tds_packages_query_items, function($a, $b) {
        return strnatcasecmp($a['title'], $b['title']);
    });

    //See if this is a tour and sort differently
    if ($tdsCustomAttributes['class'] == strtolower('tour')){
      //get the list of items to sort
      foreach ($tds_packages_query_items as $tds_key => $tds_row) {
          $tds_sort_duration[$tds_key]  = $tds_row['duration'];
          $tds_sort_price[$tds_key] = $tds_row['price'];
      }

      array_multisort($tds_sort_duration, SORT_NUMERIC, $tds_sort_price, SORT_NUMERIC, $tds_packages_query_items);
    } //end if ($tdsCustomAttributes['class'] == strtolower('tour'))



    if( $tds_packages_query_items ) {
      $tdsHTML = '';
      //make the enclosing div
      $tdsHTML .= '<div class="tds-packages">';
      foreach($tds_packages_query_items as $tds_packages_query_item) {

        $tds_post_id = $tds_packages_query_item['id'];

        //Package title
        $tds_package_title = $tds_packages_query_item['title'];

        //Package price
        $tds_package_price = $tds_packages_query_item['price'];

        if($tds_package_price){
          $tds_package_price = number_format($tds_package_price);
        } else {
          $tds_package_price = 0;
        }

        //Package image
        $tds_package_thumbnail = get_the_post_thumbnail_url($tds_post_id, 'full');

        //Package description
        $tds_package_description = get_post_meta($tds_post_id, 'tds_package_description', true);

        // //Check if we will link to a lightbox or new window
        $tds_link_data_lightbox = '';
        $tds_package_link_type =  get_post_meta($tds_post_id, 'tds_package_link_type', true);
        $tds_package_url_target = '';

        //$tds_package_url_target = '_self';
        //Get the saved link
        $tds_package_url = get_post_meta($tds_post_id, 'tds_package_page_url', true);
        $tds_package_url_class = '';
        $tds_link_data = '';
        if($tds_package_link_type == 'blank') {
          // $tds_package_pdf_itinerary = get_post_meta($tds_post_id, 'tds_package_pdf_itinerary', true);
          // $tds_package_url = $tds_package_pdf_itinerary['url'];
          $tds_package_url_target = '_blank';
        } elseif($tds_package_link_type == 'self') {
          $tds_package_url_target = '_self';
        } elseif($tds_package_link_type == 'lightbox') {
          $tds_package_url_target = '_self';
          $tds_link_data_lightbox = 'data-featherlight="iframe"';
        } else {
          //check if it's a PDF
          if (substr($tds_package_url,-3)=="pdf") {
            $tds_package_url_target = '_blank';
          } else {
            $tds_package_url_target = '_self';
          }
        }//end if($tds_package_link_type[0] == 'pdf')

        //Get the link text, if any
        $tds_package_link_text = get_post_meta($tds_post_id, 'tds_package_page_link_text', true);

        //GET THE DESTINATIONS
        $tds_destination_names = array();
        $tds_packages_destination = get_the_terms($tds_post_id, 'tds-packages-destination');
        if($tds_packages_destination):foreach($tds_packages_destination as $tds_destination):
          $tds_destination_names[] = $tds_destination->name;
        endforeach; endif;
        $tds_destinations = implode(', ', $tds_destination_names);

        //GET THE DURATIONS
        $tds_duration_names = array();
        $tds_packages_duration = get_the_terms($tds_post_id, 'tds-packages-duration');
        if($tds_packages_duration):foreach($tds_packages_duration as $tds_duration):
          $tds_duration_names[] = $tds_duration->name;
        endforeach; endif;
        $tds_durations = implode(', ', $tds_duration_names);

        //GET THE TYPES
        $tds_type_names = array();
        $tds_packages_type = get_the_terms($tds_post_id, 'tds-packages-type');
        if($tds_packages_type):foreach($tds_packages_type as $tds_type):
          $tds_type_names[] = $tds_type->name;
        endforeach; endif;
        $tds_types = implode(', ', $tds_type_names);

        //GET THE LOCATIONS
        $tds_location_names = array();
        $tds_packages_location = get_the_terms($tds_post_id, 'tds-packages-location');
        if($tds_packages_location):foreach($tds_packages_location as $tds_location):
          $tds_location_names[] = $tds_location->name;
        endforeach; endif;
        $tds_locations = implode(', ', $tds_location_names);

        //GET THE TYPE OF ITEM - TOUR, ACTIVITY or LOCATION
        //Check what kind of post this is
        $tds_package_post_type = get_post_meta($tds_post_id, 'tds_package_post_type', true);

         //Set the header color
        $tds_package_color= '';
        //Set the header title
        $tds_header_line = $tds_package_title;
        //Initialize the subheader line
        $tds_sub_header_line = '';
        //BUILD THE HEADER LINE DEPENDING UPON THE STYLE
        if($tdsCustomAttributes['style'] == 'top') {
            $tds_click_to_view_text = $tds_package_title;
        } else if ($tdsCustomAttributes['style'] == 'side') {
            $tds_click_to_view_text = 'View ' . $tds_package_title;
        }

        // COLORS
         //  IS A TOUR OR ACTIVITY
        $tds_meta = '';
        if($tds_package_post_type == 'Destination') {
          $tds_package_color = get_option('tds_packages_destination_color_option');

        } elseif($tds_package_post_type == 'Activity') {
          $tds_package_color = get_option('tds_packages_activity_color_option');
          $tds_click_to_view_text = 'Read More About ' . $tds_package_title;        } elseif($tds_package_post_type == 'Tour') {
          $tds_header_line = $tds_destinations . ': ' . $tds_durations .' from $' . $tds_package_price;
          $tds_sub_header_line = '<h5>' . $tds_package_title . '</h5>';
          $tds_click_to_view_text = 'View Detailed Itinerary';
          $tds_package_color = get_option('tds_packages_tour_color_option');

        } elseif($tds_package_post_type == 'Location') {
          $tds_click_to_view_text = 'Read More About ' . $tds_locations;
          $tds_package_color = get_option('tds_packages_location_color_option');

        } elseif($tds_package_post_type == 'Other') {
          $tds_click_to_view_text = 'Read More';
          $tds_package_color = get_option('tds_packages_other_color_option');
          //$tds_meta .= '<span class="tds-meta-name">Location: ' . $tds_locations . '</span>';
        }

        if(!empty($tds_package_link_text) || $tds_package_link_text != '') {
          $tds_click_to_view_text =  $tds_package_link_text ;
        }
        // get_post_taxonomies($tds_post_id);//get_the_terms( $tds_post_id,  'tds-packages-duration');
        //Outer item HTML
        $tdsHTML .= '<div class="tds-package-item display-' . $tdsCustomAttributes['style'] . ' columns-' . $tdsCustomAttributes['cols'] .' desc-' . $tdsCustomAttributes['desc'] . '">';

          $tdsHTML .= '<div class="tds-item-img">';

          $tdsHTML .= '<a class="clickable-link ' . $tds_package_url_class . '" href="' . $tds_package_url . '" target="' . $tds_package_url_target . '" ' . $tds_link_data . '></a>';

            $tdsHTML .= '<div class="tds-item-img-bg" style="background-image: url(' . $tds_package_thumbnail. ')">';

              $tdsHTML .= get_the_post_thumbnail($tds_post_id, 'large');

              $tdsHTML .= '</div> <!-- end tds-item-img-bg-->';
            $tdsHTML .= '<div class="tds-click-overlay">';
              $tdsHTML .= '<a class="' . $tds_package_url_class . '" href="' . $tds_package_url . '" target="' . $tds_package_url_target . '" ' . $tds_link_data . '>' . $tds_click_to_view_text . '</a>';
            $tdsHTML .= '</div><!-- end tds-click-overlay-->';

          $tdsHTML .= '</div> <!-- end tds-item-img-->';

          if($tdsCustomAttributes['desc'] == 'yes') {
            $tdsHTML .= '<div class="tds-item-desc">';
              $tdsHTML .= '<div class="tds-combined-title" style="background-color: ' . $tds_package_color . ';"><h3>';
              $tdsHTML .= '<a class="' . $tds_package_url_class . '" href="' . $tds_package_url . '" target="' . $tds_package_url_target . '" ' . $tds_link_data . '>' .         $tds_header_line . '</a></h3></div>';
                $tdsHTML .= '<div class="tds-description">' . $tds_sub_header_line . '<span>';
                $tdsHTML .= $tds_package_description . '</span>';
                if($tds_meta != '') $tdsHTML .= '<div class="tds-meta">' . $tds_meta . '</div>';
                $tdsHTML .= '</div>';
  
            $tdsHTML .= '</div> <!-- end tds-item-desc-->';
          }

        //Close outer item HTML
        $tdsHTML .= '</div> <!-- end tds-package-item -->';

      } // end foreach($tds_packages_query_items as $tds_packages_query_item)

      //close the outer div
      $tdsHTML .= '</div> <!-- end tds-packages -->';

    }//end if( !empty($tds_packages_query_items)


    return $tdsHTML;
  } //end public function add_tds_packages_shortcode

} // end class 

$tds_packages_shortcode = new TDS_Packages_Shortcode();
