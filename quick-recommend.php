<?php

/*
Plugin Name:    Quick Recommend
Plugin URI:     http://oncapublishing.com/projects/quick-recommend
Description:    Add a recommendation box to the bottom of your posts. Great for promoting books, movies, and other products when that is not the primary focus of your blog.
Version:        1.2
Author:         Shed Simas, Onça Publishing
Author URI:     http://oncapublishing.com
License:        GPL2
License URI:    https://www.gnu.org/icenses/gpl-2.0.html
Text Domain:    quick-recommend
*/

/* !0. TABLE OF CONTENTS */

/*
	
	1. HOOKS
		1.1 - register custom admin column headers
		1.2 - register custom admin column data
        1.3 - load external files to public website
        1.4 - register our custom menus
        1.5 - load external files in WordPress admin
        1.6 - register plugin options
        1.7 - register activate/deactivate/uninstall functions
        1.8 - register cotent filter
        1.9 - register the relationship filter
	
	2. SHORTCODES
		
	3. FILTERS
		3.1 - registers custom recommendation column headers
		3.2 - registers custom recommendation column data
            3.2.1 - allow custom title data
            3.2.2 - output custom title data
        3.3 - registers custom plugin admin menus
        3.4 - inserts recommendation data into post
        3.5 - modify results in Quick Recommend relationship field
		
	4. EXTERNAL SCRIPTS
		4.1 - Advance Custom Fields settings
		4.2 - qrec_public_scripts()
        4.3 - loads external files into wordpress ADMIN
		
	5. ACTIONS
        5.1 - checks the current version of wordpress and displays a message in the plugin page if the version is untested
        5.2 - runs functions for plugin deactivation
        5.3 - removes plugin related custom post type post data
        5.4 - removes any custom options from the database 
		
	6. HELPERS
        6.1 - returns html for the Show in Search checkbox
        6.2 - returns html for the Divider Location selector
        6.3 - returns html for the Divider Type selector
        6.4 - returns html for the Tag selector
        6.5 - returns html for the Class text box
        6.6 - returns default option values as an associative array
        6.7 - returns the requested page option value or it's default
        6.8 - gets the current options and returns values in associative array
        6.9 - returns html formatted for WP admin notices
        6.10 - gets an array of plugin option data (group and settings) so as to save it all in one place
        6.11 - builds recommendation box contents
            6.11.1 - recommendation title
            6.11.2 - recommendation author names
            6.11.3 - recommendation image
        6.12 - validate and sanitize input class fields
		
	7. CUSTOM POST TYPES
        7.1 - recommendations
	
	8. ADMIN PAGES
        8.1 - plugin options admin page
	
	9. SETTINGS
        9.1 - registers all our plugin options

*/




/* !1. HOOKS */

// 1.1
// hint: register custom admin column headers
add_filter('manage_edit-recommendation_columns','qrec_rec_column_headers');

// 1.2
// hint: register custom admin column data
add_filter('manage_recommendation_posts_custom_column','qrec_rec_column_data',1,2);
add_action( 'admin_head-edit.php', 'qrec_register_custom_admin_titles');

// 1.3
// load external files to public website
add_action('wp_enqueue_scripts', 'qrec_public_scripts');

// 1.4 register our custom menus
add_action('admin_menu', 'qrec_admin_menus');

// 1.5 load external files in WordPress admin
add_action('admin_enqueue_scripts', 'qrec_admin_scripts');

// 1.6 register plugin options
add_action('admin_init', 'qrec_register_options');

// 1.7 register activate/deactivate/uninstall functions
add_action( 'admin_notices', 'qrec_check_wp_version' );
register_uninstall_hook( __FILE__, 'qrec_uninstall_plugin' );

// 1.8 register cotent filter
add_filter( 'the_content', 'qrec_show_rec' );

// 1.9 register the relationship filter
add_filter('acf/fields/relationship/result/name=qrec_recommendation', 'qrec_relationship_result', 10, 4);

/* !2. SHORTCODES */




/* !3. FILTERS */

// 3.1 registers custom recommendation column headers
function qrec_rec_column_headers( $columns ) {
	
	// creating custom column header data
	$columns = array(
		'cb'=>'<input type="checkbox" />',
		'title'=>__('Title'),
		'by'=>__('By'),
        'types'=>__('Types'),
        'date'=>__('Date'),
	);
	
	// returning new columns
	return $columns;
	
}

// 3.2 registers custom recommendation column data
function qrec_rec_column_data( $column, $post_id ) {
	
	// setup our return text
	$output = '';
	
	switch( $column ) {
		case 'by':
			// get the custom email data
			$author = get_field('qrec_author', $post_id );
			$output .= $author;
			break;
        case 'types':
            $output .= get_the_term_list( $post_id, 'rec_type', '', ' / ', '' );
            break;
	}
	
	// echo the output
	echo $output;
	
}
// 3.2.1 allow custom title data
function qrec_register_custom_admin_titles() {
    add_filter(
        'the_title',
        'qrec_custom_admin_title_style',
        99,
        2
    );
}
// 3.2.2 output custom title data
function qrec_custom_admin_title_style($title, $id) {
    $title_style = get_field('qrec_title_treatment', $id);
    $output = '';
    //if($title_style == 'italics') { $output .= '<i>'; } elseif($title_style == 'quotes'){ $output .= '&ldquo;'; }
    if($title_style == 'quotes'){ $output .= '&ldquo;'; }
    $output .= $title;
    //if($title_style == 'italics'){ $output .= '</i>'; } elseif($title_style == 'quotes'){ $output .= '&rdquo;'; }
    if($title_style == 'quotes'){ $output .= '&rdquo;'; }
    return $output;
}

// 3.3 registers custom plugin admin menus
function qrec_admin_menus() {
	
	/* main menu */
	
		$top_menu_item = 'qrec_options_admin_page';
	    
	    add_menu_page( 'Quick Recommend Options', 'Recommend', 'manage_options', $top_menu_item, $top_menu_item, 'dashicons-star-filled' );
    
    /* submenu items */
    
	    // options
	    add_submenu_page( $top_menu_item, 'Quick Recommend Options', 'Options', 'manage_options', $top_menu_item, $top_menu_item );
	    
	    // all recommendations
	    add_submenu_page( $top_menu_item, 'Recommendations', 'Recommendations', 'manage_options', 'edit.php?post_type=recommendation' );
	    
	    // add new recommendation
	    add_submenu_page( $top_menu_item, 'Add Recommendations', 'Add Rec.', 'manage_options', 'post-new.php?post_type=recommendation' );
	    
	    // recommendation types
	    add_submenu_page( $top_menu_item, 'Recommendations Types', 'Rec. Types', 'manage_options', 'edit-tags.php?taxonomy=rec_type&post_type=recommendation' );

}

// 3.4 inserts recommendation data into post
function qrec_show_rec( $content ) {
    if(is_single()) {
        if(get_field('qrec_recommendation')){
            //load divider options
            $divider_location = get_option('qrec_divider_location');
            $divider_type = get_option('qrec_divider_type');
            $divider = "";
            switch ( $divider_type ) {
                case 'hr':
                    $divider .= '<hr />';
                    break;
                case 'br':
                    $divider .= '<br />';
                    break;
            }
            
            //load class options
            $qrec_box_class = get_option('qrec_box_class');

            // add top divider if set
            if ($divider_location == 'above' || $divider_location == 'both') { $content .= $divider; }
            
            //open rec box
            $content .= '<div class="quick-recommend-box '. $qrec_box_class .'">';
            
            //load recommendations
            $recommendations = get_field('qrec_recommendation');
            
            //build recommendation display html
            foreach( $recommendations as $rec) {
                
                //load image html
                $content .= qrec_get_rec_image( $rec );
                
                //load recommendation box title
                $content .= qrec_get_rec_box_title( $rec );
                
                //load recommendation title html
                $content .= qrec_get_rec_title( $rec );
                
                //load author name html
                $content .= qrec_get_rec_author_names( $rec );
                
                //load rec body text
                $content .= wpautop($rec->post_content, true);
            }
                
            //close rec box
            $content .= '</div>';
            
            // add bottom divider if set
            if ($divider_location == 'below' || $divider_location == 'both') { $content .= $divider; }

        }
    }
    return $content;
}

// 3.5 modify results in Quick Recommend relationship field
function qrec_relationship_result( $title, $post, $field, $post_id ) {
	
	// load a custom field from this $object and show it in the $result
	$author = get_field('qrec_author', $post->ID);
    $title_treatment = get_field('qrec_title_treatment', $post->ID);
	
    $display_title = '';
    
    // start title treatment
	if( $title_treatment == 'italics' ) {
        $display_title .= '<i>';
    } elseif ( $title_treatment == 'quotes' ) {
        $display_title .= '&ldquo;';
    }
    
    //add title
    $display_title .= $title;
	
    // close title treatment
	if( $title_treatment == 'italics' ) {
        $display_title .= '</i>';
    } elseif ( $title_treatment == 'quotes' ) {
        $display_title .= '&rdquo;';
    }
    
	// append to title
	if($author) { $display_title .= ' by ' . $author; }
	
	// return
	return $display_title;
	
}



/* !4. EXTERNAL SCRIPTS */

// 4.1 Advanced Custom Fields Settings
if( ! class_exists('acf') ) {
    // 4.1.1 customize ACF path
    add_filter('acf/settings/path', 'qrec_acf_settings_path');
    function qrec_acf_settings_path( $path ) {
        // update path
        $path = plugin_dir_path( __FILE__ ) .'lib/advanced-custom-fields/';
        // return
        return $path;
    }
    // 4.1.2. customize ACF dir
    add_filter('acf/settings/dir', 'qrec_acf_settings_dir');
    function qrec_acf_settings_dir( $dir ) {
        // update path
        $dir = plugin_dir_url( __FILE__ ) .'lib/advanced-custom-fields/';
        // return
        return $dir;
    }
	
	// 4.1.3 Include ACF
    include_once( plugin_dir_path( __FILE__ ) .'lib/advanced-custom-fields/acf.php' );

	
	// 4.1.4 hide ACF from admin bar
    add_filter('acf/settings/show_admin', '__return_false');
    if( !defined('ACF_LITE') ) define('ACF_LITE',true);
}

// 4.2 loads external files into PUBLIC website
function qrec_public_scripts() {
	
	// register scripts with WordPress's internal library
	wp_register_style('quick-recommend-css-public', plugins_url('/css/public/quick-recommend.css',__FILE__));
	
	// add to que of scripts that get loaded into every page
	wp_enqueue_style('quick-recommend-css-public');
	
}

// 4.3 loads external files into wordpress ADMIN
function qrec_admin_scripts() {
	
	// register scripts with WordPress's internal library
	wp_register_script('quick-recommend-js-private', plugins_url('/js/private/quick-recommend.js',__FILE__), array('jquery'),'',true);
	
	// add to que of scripts that get loaded into every admin page
	wp_enqueue_script('quick-recommend-js-private');
	
}




/* !5. ACTIONS */
// 5.1 checks the current version of wordpress and displays a message in the plugin page if the version is untested
function qrec_check_wp_version() {
	
	global $pagenow;
	
	
	if ( $pagenow == 'plugins.php' && is_plugin_active('quick-recommend/quick-recommend.php') ):
	
		// get the wp version
		$wp_version = get_bloginfo('version');
		
		// tested vesions
		// these are the versions we've tested our plugin in
		$tested_versions = array(
			'4.9.0',
			'4.9.1',
			'4.9.2',
			'4.9.3',
			'4.9.4',
		);
		
		// IF the current wp version is not in our tested versions...
		if( !in_array( $wp_version, $tested_versions ) ):
			
			// get notice html
			$notice = qrec_get_admin_notice('Quick Recommend has not been tested in your version of WordPress. Watch out for unexpected errors.','error');
			
			// echo the notice html
			echo( $notice );
			
		endif;
	
	endif;
	
}

// 5.2 runs functions for plugin deactivation
function qrec_uninstall_plugin() {

	// remove custom post types posts and data
	qrec_remove_post_data();
	// remove plugin options
	qrec_remove_options();
	
}


// 5.3 removes plugin related custom post type post data
function qrec_remove_post_data() {
	
	// get WP's wpdb class
	global $wpdb;
	
	// setup return variable
	$data_removed = false;
	
	try {
		
		// get our custom table name
		$table_name = $wpdb->prefix . "posts";
		
		// set up custom post types array
		$custom_post_types = array(
			'recommendation',
		);
		
		// remove data from the posts db table where post types are equal to our custom post types
		$data_removed = $wpdb->query(
			$wpdb->prepare( 
				"
					DELETE FROM $table_name 
					WHERE post_type = %s
				", 
				$custom_post_types[0]
			) 
		);
		
		// get the table names for postmeta and posts with the correct prefix
		$table_name_1 = $wpdb->prefix . "postmeta";
		$table_name_2 = $wpdb->prefix . "posts";
		
		// delete orphaned meta data
		$wpdb->query(
			$wpdb->prepare( 
				"
				DELETE pm
				FROM $table_name_1 pm
				LEFT JOIN $table_name_2 wp ON wp.ID = pm.post_id
				WHERE wp.ID IS NULL
				"
			) 
		);
		
		
		
	} catch( Exception $e ) {
		
		// php error
		
	}
	
	// return result
	return $data_removed;
	
}

// 5.4 removes any custom options from the database 
function qrec_remove_options() {
	
	$options_removed = false;
	
	try {
	
		// get plugin option settings
		$options = qrec_get_options_settings();
		
		// loop over all the settings
		foreach( $options['settings'] as &$setting ):
			
			// unregister the setting
			unregister_setting( $options['group'], $setting );
		
		endforeach;
		
		// return true if everything worked
		$options_removed = true;
	
	} catch( Exception $e ) {
		
		// php error
		
	}
	
	// return result
	return $options_removed;
	
}




/* !6. HELPERS */
// 6.1 returns html for the Show in Search checkbox
function qrec_get_checkbox( $check_name, $selected_value ) {
    
    //set up the checkbox html
    $check = '<input type="checkbox" name="'. $check_name .'" value="true"';
        
    //check if the box is currently checked
    if( $selected_value == 'true' ):
        $check .= ' checked="checked" ';
    endif;  
    
    //close checkbox html
    $check .= '>';
    
    //return checkbox html
    return $check;
}

// 6.2 returns html for the Divider Location selector
function qrec_get_divider_location( $selected_value='above' ) {
    //set up the option values
    $locations = array(
        'above',
        'below',
        'both',
        'none', 
    );
    
    //set up the select html
    $select = '<select name="qrec_divider_location">';
    
    //loop over each option
    foreach( $locations as $location ){
    
        //set up the option labels
        $label = "";
        switch( $location ) {
            case 'above':
                $label = 'Above';
                break;
            case 'below':
                $label = 'Below';
                break;
            case 'both':
                $label = 'Above &amp; below';
                break;
            case 'none':
                $label = 'No dividers';
                break;
        }
        
        //check if this option is the currently selected option
        $selected = '';
        if( $selected_value == $location ):
            $selected = ' selected="selected" ';
        endif;        
        
        //build the option html
        $option = '<option value="' . $location . '"' . $selected . '>';
        $option .= $label;
        $option .= '</option>';
        
        //append option to select html
        $select .= $option;
    }
	
	// close our select html tag
	$select .= '</select>';
    
	// return our new select 
	return $select;
}

// 6.3 returns html for the Divider Type selector
function qrec_get_divider_type( $selected_value='hr' ) {
    //set up the option values
    $locations = array(
        'hr',
        'br',
    );
    
    //set up the select html
    $select = '<select name="qrec_divider_type">';
    
    //loop over each option
    foreach( $locations as $location ){
    
        //set up the option labels
        $label = "";
        switch( $location ) {
            case 'hr':
                $label = 'Horizontal Line &lt;hr /&gt;';
                break;
            case 'br':
                $label = 'Line Beak &lt;br /&gt;';
                break;
        }
        
        //check if this option is the currently selected option
        $selected = '';
        if( $selected_value == $location ):
            $selected = ' selected="selected" ';
        endif;        
        
        //build the option html
        $option = '<option value="' . $location . '"' . $selected . '>';
        $option .= $label;
        $option .= '</option>';
        
        //append option to select html
        $select .= $option;
    }
	
	// close our select html tag
	$select .= '</select>';
	
	// return our new select 
	return $select;
}

// 6.4 returns html for the Tag selector
function qrec_get_tag_options( $select_name, $selected_value='h1' ) {
    //set up the option values
    $locations = array(
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'p',
    );
    
    //set up the select html
    $select = '<select name="'.$select_name.'">';
    
    //loop over each option
    foreach( $locations as $location ){
    
        //set up the option labels
        $label = "";
        switch( $location ) {
            case 'h1':
                $label = '&lt;h1&gt;';
                break;
            case 'h2':
                $label = '&lt;h2&gt;';
                break;
            case 'h3':
                $label = '&lt;h3&gt;';
                break;
            case 'h4':
                $label = '&lt;h4&gt;';
                break;
            case 'h5':
                $label = '&lt;h5&gt;';
                break;
            case 'h6':
                $label = '&lt;h6&gt;';
                break;
            case 'p':
                $label = '&lt;p&gt;';
                break;
        }
        
        //check if this option is the currently selected option
        $selected = '';
        if( $selected_value == $location ):
            $selected = ' selected="selected" ';
        endif;        
        
        //build the option html
        $option = '<option value="' . $location . '"' . $selected . '>';
        $option .= $label;
        $option .= '</option>';
        
        //append option to select html
        $select .= $option;
    }
	
	// close our select html tag
	$select .= '</select>';
	
	// return our new select 
	return $select;
}

// 6.5 returns html for the Class text box
function qrec_get_text_field( $text_name, $value='' ) {  
    //build text input html
    $text = '<input type="text" placeholder="class"';
    $text .= ' name="'. $text_name . '"';
    if($value) { $text .= ' value="'. $value . '"'; }
    $text .= '>';
	
	// return our new text 
	return $text;
}

// 6.6 returns default option values as an associative array
function qrec_get_default_options() {
	
	$defaults = array();
	
	try {
		
		// setup defaults array
		$defaults = array(
			'qrec_search_check'=>'false',
			'qrec_archive_check'=>'false',
			'qrec_divider_location'=>'above',
			'qrec_divider_type'=>'hr',
			'qrec_box_class'=>'',
			'qrec_image_class'=>'qrec-image-holder',
			'qrec_box_title_tag'=>'h6',
			'qrec_box_title_class'=>'',
			'qrec_title_tag'=>'h4',
			'qrec_title_class'=>'',
			'qrec_author_tag'=>'h6',
			'qrec_author_class'=>'',
		);
			
	
	} catch( Exception $e) {
		
		// php error
		
	}
	
	// return defaults
	return $defaults;
	
	
}

// 6.7 returns the requested page option value or it's default
function qrec_get_option( $option_name ) {
	
	// setup return variable
	$option_value = '';	
	
	
	try {
		
		// get default option values
		$defaults = qrec_get_default_options();
		
		// get the requested option
		switch( $option_name ) {
			
			case 'qrec_search_check':
				// include in search
				$option_value = (get_option('qrec_search_check')) ? get_option('qrec_search_check') : $defaults['qrec_search_check'];
				break;
			case 'qrec_archive_check':
				// show archive
				$option_value = (get_option('qrec_archive_check')) ? get_option('qrec_archive_check') : $defaults['qrec_archive_check'];
				break;
			case 'qrec_divider_location':
				// confirmation page id
				$option_value = (get_option('qrec_divider_location')) ? get_option('qrec_divider_location') : $defaults['qrec_divider_location'];
				break;
			case 'qrec_divider_type':
				// reward page id
				$option_value = (get_option('qrec_divider_type')) ? get_option('qrec_divider_type') : $defaults['qrec_divider_type'];
				break;
			case 'qrec_box_class':
				// reward page id
				$option_value = (get_option('qrec_box_class')) ? get_option('qrec_box_class') : $defaults['qrec_box_class'];
				break;
			case 'qrec_image_class':
				// reward page id
				$option_value = (get_option('qrec_image_class')) ? get_option('qrec_image_class') : $defaults['qrec_image_class'];
				break;
			case 'qrec_box_title_tag':
				// reward page id
				$option_value = (get_option('qrec_box_title_tag')) ? get_option('qrec_box_title_tag') : $defaults['qrec_box_title_tag'];
				break;
			case 'qrec_box_title_class':
				// reward page id
				$option_value = (get_option('qrec_box_title_class')) ? get_option('qrec_box_title_class') : $defaults['qrec_box_title_class'];
				break;
			case 'qrec_title_tag':
				// reward page id
				$option_value = (get_option('qrec_title_tag')) ? get_option('qrec_title_tag') : $defaults['qrec_title_tag'];
				break;
			case 'qrec_title_class':
				// reward page id
				$option_value = (get_option('qrec_title_class')) ? get_option('qrec_title_class') : $defaults['qrec_title_class'];
				break;
			case 'qrec_author_tag':
				// reward page id
				$option_value = (get_option('qrec_author_tag')) ? get_option('qrec_author_tag') : $defaults['qrec_author_tag'];
				break;
			case 'qrec_author_class':
				// reward page id
				$option_value = (get_option('qrec_author_class')) ? get_option('qrec_author_class') : $defaults['qrec_author_class'];
				break;
		}
		
	} catch( Exception $e) {
		
		// php error
		
	}
	
	// return option value or it's default
	return $option_value;
	
}

// 6.8 gets the current options and returns values in associative array
function qrec_get_current_options() {
	
	// setup our return variable
	$current_options = array();
	
	try {
	
		// build our current options associative array
		$current_options = array(
			'qrec_search_check' => qrec_get_option('qrec_search_check'),
			'qrec_archive_check' => qrec_get_option('qrec_archive_check'),
			'qrec_divider_location' => qrec_get_option('qrec_divider_location'),
			'qrec_divider_type' => qrec_get_option('qrec_divider_type'),
			'qrec_box_class' => qrec_get_option('qrec_box_class'),
			'qrec_image_class' => qrec_get_option('qrec_image_class'),
			'qrec_box_title_tag' => qrec_get_option('qrec_box_title_tag'),
			'qrec_box_title_class' => qrec_get_option('qrec_box_title_class'),
			'qrec_title_tag' => qrec_get_option('qrec_title_tag'),
			'qrec_title_class' => qrec_get_option('qrec_title_class'),
			'qrec_author_tag' => qrec_get_option('qrec_author_tag'),
			'qrec_author_class' => qrec_get_option('qrec_author_class'),
		);
	
	} catch( Exception $e ) {
		
		// php error
	
	}
	// return current options
	return $current_options;
	
}

// 6.9 returns html formatted for WP admin notices
function qrec_get_admin_notice( $message, $class ) {
	
	// setup our return variable
	$output = '';
	
	try {
		
		// create output html
		$output = '
		 <div class="'. $class .'">
		    <p>'. $message .'</p>
		</div>
		';
	    
	} catch( Exception $e ) {
		
		// php error
		
	}
	
	// return output
	return $output;
	
}

// 6.10 gets an array of plugin option data (group and settings) so as to save it all in one place
function qrec_get_options_settings() {
	
	// setup our return data
	$settings = array( 
		'group'=>'qrec_plugin_options',
		'settings'=>array(
			'qrec_search_check',
            'qrec_archive_check',
			'qrec_divider_location',
			'qrec_divider_type',
			'qrec_box_class',
			'qrec_box_title_tag',
			'qrec_box_title_class',
			'qrec_image_class',
			'qrec_title_tag',
			'qrec_title_class',
			'qrec_author_tag',
			'qrec_author_class',
		),
	);
	
	// return option data
	return $settings;
	
}

// 6.11 builds recommendation box contents
// 6.11.1 recommendation title
function qrec_get_rec_title( $rec ) {
    //load title display option
    $tag = get_option( 'qrec_title_tag' );
    $class = get_option('qrec_title_class');
    $style = get_field('qrec_title_treatment', $rec->ID);
    
    //build title html
    $title = '';
    $title .= '<'. $tag .' class="'. $class .'">';
    if($style == 'italics') {
        $title .= '<i>';
    } elseif($style == 'quotes'){
        $title .= '&ldquo;';
    }
    $title .= $rec->post_title;
    if($style == 'italics'){
        $title .= '</i>';
    } elseif($style == 'quotes'){
        $title .= '&rdquo;';
    }
    $title .= '</'. $tag .'>';
    
    return $title;
}
// 6.11.2 recommendation author names
function qrec_get_rec_author_names( $rec ) {
    //load author name display options
    $tag = get_option( 'qrec_author_tag' );
    $class = get_option('qrec_author_class');

    //build author names html
    $author = '';
    $author .= '<'. $tag .' class="'. $class .'">';
    $author .= ' by ';
    $author .= get_field('qrec_author', $rec->ID);
    $author .= '</'. $tag .'>';
    
    return $author;
}
// 6.11.3 recommendation image
function qrec_get_rec_image( $rec ) {
    //load image class
    $class = get_option('qrec_image_class');
    
    //build image html
    $image = '';
    $image .= '<div class="'. $class .'">';
    $image .= get_the_post_thumbnail($rec->ID, 'post-thumbnail');
    $image .= '</div>';
    
    return $image;
}
// 6.11.4 recommendation box title
function qrec_get_rec_box_title( $rec ) {
    //load options
    $class = get_option('qrec_box_title_class');
    $tag = get_option( 'qrec_box_title_tag' );
    $archive = get_option( 'qrec_archive_check' );
    
    $types = get_the_term_list( $rec->ID, 'rec_type', 'Recommended ', ' / ', ':' );
    
    $box_title = '';
    $box_title .= '<'. $tag .' class="'. $class .'">';
    if(!$archive) {
        $box_title .= strip_tags( stripslashes( $types ) );
    } else {
        $box_title .= $types;
    }
    $box_title .= '</'. $tag .'>';
    
    return $box_title;
}

// 6.12 validate and sanitize input class fields
function qrec_html_classes($classes){
    
    $output = '';

    if(!is_array($classes)) {
        $classes = explode(' ', $classes);
    }

    if(!empty($classes)){
        foreach($classes as $class){
            $output .= sanitize_html_class(strip_tags( stripslashes( $class ) ));
            
            if ($class !== end($classes))
                $output.= ' ';
        }
    }
    
    // Return the output processing any additional functions filtered by this action
    return apply_filters( 'qrec_html_classes', $output, $classes );
 
}



/* !7. CUSTOM POST TYPES */

// 7.1 recommendations cpt
include_once( plugin_dir_path( __FILE__ ) . 'cpt/recommendations.php');



/* !8. ADMIN PAGES */
// 8.1 plugin options admin page
function qrec_options_admin_page() {
	
	// get the default values for our options
	$options = qrec_get_current_options();
	
	echo('<div class="wrap">
		
		<h2>Quick Recommend Options</h2>
		
		<form action="options.php" method="post">');
		
			// outputs a unique nounce for our plugin options
			settings_fields('qrec_plugin_options');
			// generates a unique hidden field with our form handling url
			@do_settings_fields('qrec_plugin_options');
			
			echo('<table class="form-table">
			
				<tbody>
			
					<tr>
						<th scope="row"><label for="qrec_search_check">Show Recommendations in Search</label></th>
						<td>
							'. qrec_get_checkbox( 'qrec_search_check', $options['qrec_search_check'] ) .'
							<p class="description" id="qrec_search_check-description">Check this box to allow Recommendations to appear in search results.</p>
						</td>
					</tr>
			
					<tr>
						<th scope="row"><label for="qrec_archive_check">Show Recommendations Archives</label></th>
						<td>
							'. qrec_get_checkbox( 'qrec_archive_check', $options['qrec_archive_check'] ) .'
							<p class="description" id="qrec_archive_check-description">Check this box to enable archive pages for Recommendations and Recommendation Types.</p>
						</td>
					</tr>
			
					<tr>
						<th scope="row"><label for="qrec_divider_location">Divider Location</label></th>
						<td>
							'. qrec_get_divider_location( $options['qrec_divider_location'] ) .'
							<p class="description" id="qrec_divider_location-description">A divider can be used to separate the Recommendation from the post it is attached to.</p>
						</td>
					</tr>
			
					<tr>
						<th scope="row"><label for="qrec_divider_type">Divider Type</label></th>
						<td>
							'. qrec_get_divider_type( $options['qrec_divider_type'] ) .'
							<p class="description" id="qrec_divider_type-description">Divider styling will conform to your active theme.</p>
						</td>
					</tr>
			
					<tr>
						<th scope="row"><label for="qrec_box_class">Recommendation Box Display Options</label></th>
						<td>
							'. qrec_get_text_field( 'qrec_box_class', $options['qrec_box_class'] ) .'
							<p class="description" id="qrec_box_class-description">Any CSS classes entered here will be added to the recommendation box as a whole.</p>
						</td>
					</tr>
			
					<tr>
						<th scope="row"><label for="qrec_box_title_display">Recommendation Box Title Display Options</label></th>
						<td>
							'. qrec_get_tag_options( 'qrec_box_title_tag', $options['qrec_box_title_tag'] ). ' ' . qrec_get_text_field( 'qrec_box_title_class', $options['qrec_box_title_class'] ) .'
							<p class="description" id="qrec_box_title_display-description">Select the HTML tag and enter any CSS classes used to display the title of the recommendation box (eg. "Recommended Book").</p>
						</td>
					</tr>
			
					<tr>
						<th scope="row"><label for="qrec_image_class">Recommendation Image Display Options</label></th>
						<td>
							'. qrec_get_text_field( 'qrec_image_class', $options['qrec_image_class'] ) .'
							<p class="description" id="qrec_image_class-description">Any CSS classes entered here will be added to the recommendation image and replace default styling.<br />To return to the default styling, use class <code>qrec-image-holder</code>.</p>
						</td>
					</tr>
			
					<tr>
						<th scope="row"><label for="qrec_title_display">Recommendation Title Display Options</label></th>
						<td>
							'. qrec_get_tag_options( 'qrec_title_tag', $options['qrec_title_tag'] ) .' '. qrec_get_text_field( 'qrec_title_class', $options['qrec_title_class'] ) .'
							<p class="description" id="qrec_title_display-description">Select the HTML tag and enter any CSS classes used to display the recommendation title.</p>
						</td>
					</tr>
			
					<tr>
						<th scope="row"><label for="qrec_author_display">Recommendation Author Display Options</label></th>
						<td>
							'. qrec_get_tag_options( 'qrec_author_tag', $options['qrec_author_tag'] ) .' '. qrec_get_text_field( 'qrec_author_class', $options['qrec_author_class'] ) .'
							<p class="description" id="qrec_author_display-description">Select the HTML tag and enter any CSS classes used to display the recommendation author names.</p>
						</td>
					</tr>
			
				</tbody>
				
			</table>');
		
			// outputs the WP submit button html
			@submit_button();
		
		
		echo('</form>
	
	</div>');
    	
}



/* !9. SETTINGS */
// 9.1 registers all our plugin options
function qrec_register_options() {
	
	// get plugin options settings
	
	$options = qrec_get_options_settings();
    
	// loop over settings
	foreach( $options['settings'] as $setting ):
	
        //sanitize classes
        $callback = '';
        $sanitize = array(
            'qrec_box_class',
            'qrec_box_title_class',
            'qrec_image_class',
            'qrec_title_class',
            'qrec_author_class',
        );
        if(in_array($setting, $sanitize, TRUE ) ) {
            $callback = 'qrec_html_classes';
        }
	
		// register this setting
		register_setting($options['group'], $setting, $callback);
	
	endforeach;
	
}
