<?php
   /*
   Plugin Name: AJAX Suggest
   Plugin URI: 
   Description: AJAX Suggest for FAQ post type with Contact form 7 support.
   Author: Moe Babaee
   Author URI: 
   */

 function enqueue_styles_scripts() {
	 // style
	wp_enqueue_style( 'ajax_search_style',  plugin_dir_url(__FILE__) . '/css/style.css');
	// scripts 
	wp_enqueue_script('dynamic-search-ajax-handle', plugin_dir_url(__FILE__). 'js/ajax_search.js', array('jquery'));
	// localize scripts
	wp_localize_script( 'dynamic-search-ajax-handle', 'the_ajax_script', array(
        'ajaxurl'       => admin_url( 'admin-ajax.php' )
    ));
}

add_action( 'wp_enqueue_scripts', 'enqueue_styles_scripts' );

add_action("plugins_loaded", "ajax_suggest_init");
add_action('wp_ajax_ajax_search','the_search_function');
add_action('wp_ajax_nopriv_ajax_search','the_search_function');

function the_search_function(){
	if(isset($_POST['srch_txt'])){
		$search_string = mysql_escape_string(htmlspecialchars(stripslashes($_POST['srch_txt'])));
                $search_string = str_ireplace("script", "blocked", $search_string);
                $search_string = esc_sql($search_string);
		if(!empty($search_string)){
		//	global $wpdb;
		//	$search_result_posts = $wpdb->get_col("select ID from $wpdb->posts where tag like '%".$search_string."%' AND post_status = 'publish'");
			$args = array('post_type'=> 'suggestions', 'tag' => $search_string);
			$res = new WP_Query($args);
			if($res->have_posts()){
				echo '<ul>';
				while ( $res->have_posts() ) : $res->the_post();
					echo '<li><a href="'.get_permalink().'">'.get_the_title().'</a><br/>'.get_the_excerpt().'</li><br/>';
				endwhile;
				echo '</ul>';
			} else {
				echo '<p>No suggestions found!</p>';
			}
		}
	wp_reset_query();
//	die();
	}
}

function ajax_suggest_init(){
	
	load_plugin_textdomain('ajaxsuggest', false, dirname( plugin_basename(__FILE__)).'/languages');
	
	if ( ! function_exists('ajax_suggestions_func') ) {

	// Register Custom Post Type
	function ajax_suggestions_func() {

		$labels = array(
			'name'                => _x( 'Suggestions', 'Post Type General Name', 'ajaxsuggest' ),
			'singular_name'       => _x( 'Suggestion', 'Post Type Singular Name', 'ajaxsuggest' ),
			'menu_name'           => __( 'Suggestions', 'ajaxsuggest' ),
			'name_admin_bar'      => __( 'Suggestions', 'ajaxsuggest' ),
			'parent_item_colon'   => __( 'Parent Item:', 'ajaxsuggest' ),
			'all_items'           => __( 'All Items', 'ajaxsuggest' ),
			'add_new_item'        => __( 'Add New Item', 'ajaxsuggest' ),
			'add_new'             => __( 'Add New', 'ajaxsuggest' ),
			'new_item'            => __( 'New Item', 'ajaxsuggest' ),
			'edit_item'           => __( 'Edit Item', 'ajaxsuggest' ),
			'update_item'         => __( 'Update Item', 'ajaxsuggest' ),
			'view_item'           => __( 'View Item', 'ajaxsuggest' ),
			'search_items'        => __( 'Search Item', 'ajaxsuggest' ),
			'not_found'           => __( 'Not found', 'ajaxsuggest' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'ajaxsuggest' ),
		);
		$args = array(
			'label'               => __( 'suggestions', 'ajaxsuggest' ),
			'description'         => __( 'Suggestion post type to use with Contact form 7', 'ajaxsuggest' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', ),
			'taxonomies'          => array( 'post_tag' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-visibility',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => false,		
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
		);
		register_post_type( 'suggestions', $args );

		}

		// Hook into the 'init' action
		add_action( 'init', 'ajax_suggestions_func', 0 );

		}
		
		
		// Add Shortcode
	function ajax_suggest_shortcode_func( $atts ) {

		// Attributes
		extract( shortcode_atts(
			array(
				'form_id' => '',
			), $atts )
		);

		$output = '<div class="suggestions-form">
					<div class="contact-form">
						'.do_shortcode('[contact-form-7 id="'.$form_id.'" title="Contact page"]').'
					</div>
					<div class="result">
						<h3>Instant Answer</h3>
						<div class="suggestions">';
							$args = array('post_type'=> 'suggestions',  'posts_per_page' => 3);
							$res = new WP_Query($args);
							if($res){
								$output .= '<ul>';
								while ( $res->have_posts() ) : $res->the_post();
									$output .= '<li><a href="'.get_permalink().'">'.get_the_title().'</a><br/>'.get_the_excerpt().'</li><br/>';
								endwhile;
								$output .= '</ul>';
							} else {
								$output .= "No suggestions found!";
							}
						$output .= '</div>
						</div>
					</div>';
				
		return $output;
	}
	add_shortcode( 'ajax-suggest', 'ajax_suggest_shortcode_func' );
	
}
?>
