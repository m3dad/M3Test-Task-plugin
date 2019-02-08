<?php
/*
Plugin Name: Test Task plugin from M3DAD
Description: Plugin, which will create custom post type Task and metaboxes.
Version: 0.1
Author: M3DAD
License: GPL2
 * 
Copyright 2019  M3DAD  (email: m3dadd@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Exit, if direct access
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

// Action after activate plugin

add_action( 'init', 'm3test_set_custom_post_and_meta' );
if ( ! function_exists( 'm3test_set_custom_post_and_meta' ) ) {
    function m3test_set_custom_post_and_meta(){
	// Register new post type "Task"
	register_post_type( 'task', array(
            'label'                => __( 'Task', 'm3task' ),
            'public'               => true,
            'description'          => __( 'M3Test Task with metabox fields', 'm3task' ),
            'show_ui'              => true,
            'show_in_rest'         => true, // Important for WP > 5.0 for Guttenberg support
            'menu_position'        => 6,
            'menu_icon'            => 'dashicons-clock',
            'supports'             => array( 'title', 'editor' ),
            'register_meta_box_cb' => 'm3task_metaboxes',
	) );
    }
}

// Metaboxes 
// Add metabox
if ( ! function_exists( 'm3task_metaboxes' ) ) {
    function m3task_metaboxes() {
        add_meta_box( 'm3task_metabox', __( 'Task parameters', 'm3task' ), 'm3task_display_metabox', array( 'task' ) );
    }
}
// Callback for metabox
if ( ! function_exists('m3task_display_metabox') ) {
    function m3task_display_metabox( $post, $meta ) {
        $screens = $meta['args'];
        // Set default and load saved data
        wp_nonce_field( plugin_basename(__FILE__), 'm3task_nonce' ); // Security field
        $default_data = array(
            'task_date_start' => date( 'Y-m-d' ),
            'task_due_date'   => date( 'Y-m-d' ),
            'task_priority'   => 'Low',
        );
        $saved_data      = get_post_meta( $post->ID, 'm3task_metaboxes', true );
        $render_data     = wp_parse_args( $saved_data, $default_data );
        $priority_levels = array( 'Low', 'Normal', 'High' ); // Set levels priority of tasks
        // Display metabox fields, trying to do not use custom CSS, using existing classes
        echo '<div class="metabox-prefs"><table><tbody><tr>';
        echo '<td class="columns-prefs"><label for="task_date_start">'
            . __( 'Date of task start', 'm3task' ) . '</label>';
        echo '<input name="m3task_fields[task_date_start]" type="date" id="task_date_start" value="'
            . $render_data['task_date_start'] . '"></td>';
        echo '<td class="columns-prefs"><label for="task_due_date">'
            . __( 'Date of task end', 'm3task' ) . '</label>';
        echo '<input name="m3task_fields[task_due_date]" type="date" id="task_due_date" value="' 
            . $render_data['task_due_date'] . '"></td>';
        echo '<td class="columns-prefs"><label for="task_priority">' 
            . __( 'Priority of task', 'm3task' ) . '</label>';
        echo '<select name="m3task_fields[task_priority]" id="task_priority">';
        // Display options of Task priority 
        foreach( $priority_levels as $level_label ){
            $level_selected = '';
            if( $level_label === $render_data['task_priority'] ) {
                $level_selected = ' selected';
            }
            echo '<option' . $level_selected . '>' . $level_label . '</option>';
        }
        echo '</select></td>';
        echo '</tr></tbody></table></div>';
    }
}
// Save action
add_action( 'save_post', 'm3task_save' );
if ( ! function_exists( 'm3task_save' )) {
    function m3task_save( $post_id ) {
        // Checks
        // Check for input
        if ( ! isset( $_POST['m3task_fields'] ) ) {
            return;
        }
        // Check for origin
        if ( ! wp_verify_nonce( $_POST['m3task_nonce'], plugin_basename(__FILE__) ) ) {
            return;
        }
        // Check for autosave action
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return;
        }
        // Check for user rights
        if( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        
        // Sanitize and save fields
        $sanitized_data = array();
        foreach( $_POST['m3task_fields'] as $key => $value ) {
            $sanitized_data[ $key ] = wp_filter_post_kses( $value );
        }
        update_post_meta( $post_id, 'm3task_metaboxes', $sanitized_data );
    }
}

// Add columns to tasks page

add_filter( 'manage_task_posts_columns', 'm3task_add_columns' );
if( ! function_exists( 'm3task_add_columns' ) ) {
    function m3task_add_columns( $columns ) {
        $add_to = 2; // Count of columns, after which will added columns
        $new_columns = array(
            'task_date_start' => __( 'Date of start', 'm3task' ),
            'task_due_date'   => __( 'Due date', 'm3task' ),
            'task_priority'   => __( 'Priority', 'm3task' ),
        );
        
        return array_slice( $columns, 0, $add_to ) + $new_columns + array_slice( $columns, $add_to );
    }
}

add_action( 'manage_task_posts_custom_column', 'm3task_set_columns', 5, 2 );
if( ! function_exists( 'm3task_set_columns' ) ) {
    function m3task_set_columns( $column_name, $post_id ) {
        $get_data = get_post_meta( $post_id, 'm3task_metaboxes', true ); // Load data of columns
        $value    = $get_data[ $column_name ]; // Get value of column
        if( ! $value ) { // If false, show '---', not clear column
            echo '---';
            return;
        } 
        // Check for column name "Task priority" for set colored icon
        if ( $column_name == 'task_priority' ) {
            if( $value == 'Low' ) {
                echo '<span style="color:#82878C;" class="dashicons dashicons-arrow-down-alt2"></span>';
            } elseif ( $value == 'Normal' ) {
                echo '<span style="#46B450" class="dashicons dashicons-arrow-up-alt2"></span>';
            } elseif ( $value == 'High' ) {
                echo '<span style="color:#DC3232;" class="dashicons dashicons-arrow-up-alt"></span>';
            } else {
                echo $value; // Display another value
            }
        } else {
            echo $value;
        }
    }
}

// Taxonomies
add_action( 'init', 'm3test_set_taxonomy' );
if ( ! function_exists( 'm3test_set_taxonomy' ) ) {
    function m3test_set_taxonomy(){
	// Register new taxonomy "Task types"
        register_taxonomy( 'task_types', array( 'task' ), array(
            'labels' => array( 
                'name' => 'Task types',
                'singular_name'     => __( 'Task type', 'm3task' ),
                'search_items'      => __( 'Search Task types', 'm3task' ),
                'all_items'         => __( 'All Task types', 'm3task' ),
		'view_item '        => __( 'View Task type', 'm3task' ),
		'edit_item'         => __( 'Edit Task type', 'm3task' ),
		'update_item'       => __( 'Update Task type', 'm3task' ),
		'add_new_item'      => __( 'Add New Task type', 'm3task' ),
		'new_item_name'     => __( 'New Task type Name', 'm3task' ),
		'menu_name'         => __( 'Task types', 'm3task' ),
            ),
            'public' => true,
            'show_in_rest' => true, // Important for WP > 5.0 for Guttenberg support
            'hierarchical' => true,
        ) );
    }
}

register_activation_hook( __FILE__, 'm3task_activated' ); 
function m3task_activated(){
	// Run registering function
	m3test_set_custom_post_and_meta();
        m3test_set_taxonomy();
	// Reset SEF
	flush_rewrite_rules();
}

// Action after disabling plugin

register_deactivation_hook( __FILE__, 'm3task_deactivated' );
function m3task_deactivated() {
	// Reset SEF
	flush_rewrite_rules();
}
