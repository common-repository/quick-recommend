<?php

/* ADD RECOMMENDATION POST TYPE */
function qrec_register_my_cpts_recommendation() {

	/**
	 * Post Type: Recommendations.
	 */
    //load options
    $search = get_option('qrec_search_check');
    $archive = get_option('qrec_archive_check');
    
	$labels = array(
		"name" => __( "Recommendations", "qrec" ),
		"singular_name" => __( "Recommendation", "qrec" ),
		"menu_name" => __( "Recommend", "qrec" ),
		"add_new_item" => __( "Add New Recommendation", "qrec" ),
		"edit_item" => __( "Edit Recommendation", "qrec" ),
	);

	$args = array(
		"label" => __( "Recommendations", "qrec" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => $archive,
		"show_in_menu" => false,
		"exclude_from_search" => !$search,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "recommendation", "with_front" => true ),
		"query_var" => true,
		"menu_icon" => "dashicons-star-filled",
		"supports" => array( "title", "editor", "thumbnail" ),
	);

	register_post_type( "recommendation", $args );
}

add_action( 'init', 'qrec_register_my_cpts_recommendation' );


/* ADD RECOMMENDATION TYPE TAXONOMY */
function qrec_register_my_taxes_rec_type() {

	/**
	 * Taxonomy: Recommendation Types.
	 */

	$labels = array(
		"name" => __( "Recommendation Types", "qrec" ),
		"singular_name" => __( "Recommendation Type", "qrec" ),
		"all_items" => __( "All Types", "qrec" ),
		"edit_item" => __( "Edit Type", "qrec" ),
		"view_item" => __( "View Type", "qrec" ),
		"update_item" => __( "Update Type", "qrec" ),
		"add_new_item" => __( "Add New Type", "qrec" ),
		"new_item_name" => __( "New Type", "qrec" ),
		"parent_item" => __( "Parent Type", "qrec" ),
		"parent_item_colon" => __( "Parent Type:", "qrec" ),
		"search_items" => __( "Search Types", "qrec" ),
		"popular_items" => __( "Popular Types", "qrec" ),
		"separate_items_with_commas" => __( "Separate Types with Commas", "qrec" ),
		"add_or_remove_items" => __( "Add or Remove types", "qrec" ),
		"choose_from_most_used" => __( "Choose from most used Types", "qrec" ),
		"not_found" => __( "No Recommendation Types found", "qrec" ),
		"no_terms" => __( "No types", "qrec" ),
		"items_list_navigation" => __( "Type list navigation", "qrec" ),
		"items_list" => __( "Type list", "qrec" ),
	);

	$args = array(
		"label" => __( "Recommendation Types", "qrec" ),
		"labels" => $labels,
		"public" => true,
		"hierarchical" => true,
		"label" => "Recommendation Types",
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => array( 'slug' => 'rec_type', 'with_front' => true, ),
		"show_admin_column" => false,
		"show_in_rest" => false,
		"rest_base" => "",
		"show_in_quick_edit" => true,
	);
	register_taxonomy( "rec_type", array( "recommendation" ), $args );
}

add_action( 'init', 'qrec_register_my_taxes_rec_type' );



/* ADD "BY" FIELD T0 RECOMMENDATION POST TYPE
 & ADD RECOMMENDATION FIELD TO POSTS */
if(function_exists("register_field_group"))
{
	register_field_group(array (
		'id' => 'acf_details',
		'title' => 'Details',
		'fields' => array (
            array (
				'key' => 'field_5a6be2d80f322',
				'label' => 'Title Treatment',
				'name' => 'qrec_title_treatment',
				'type' => 'select',
				'instructions' => 'This option will format the title automatically. Italics are usually used for titles of major or standalone works, like books, movies, records or paintings. Quotation marks are usually used for smaller pieces that are often included in major works, such as poems, TV episodes, songs and articles. Some types of works, like a book series, don&apos;t need any treatment.',
				'required' => 1,
				'choices' => array (
					'italics' => 'Italics',
					'quotes' => 'Quotations',
					'none' => 'None',
				),
				'default_value' => 'italics',
				'allow_null' => 0,
				'multiple' => 0,
			),
			array (
				'key' => 'field_5a6a87dc82ce3',
				'label' => '',
				'name' => 'qrec_author',
				'type' => 'text',
				'default_value' => '',
				'placeholder' => 'Author Name',
				'prepend' => 'by',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'recommendation',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'acf_after_title',
			'layout' => 'no_box',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
	register_field_group(array (
		'id' => 'acf_quick-recommend',
		'title' => 'Quick Recommend',
		'fields' => array (
			array (
				'key' => 'field_5a6a99696b047',
				'label' => '',
				'name' => 'qrec_recommendation',
				'type' => 'relationship',
				'return_format' => 'object',
				'post_type' => array (
					0 => 'recommendation',
				),
				'taxonomy' => array (
					0 => 'all',
				),
				'filters' => array (
					0 => 'search',
				),
				'result_elements' => array (
					0 => 'featured_image',
					1 => 'post_title',
				),
				'max' => 1,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'post',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'default',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
}
