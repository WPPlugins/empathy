<?php
/**
 * All of the template tags here are wrapper functions for methods
 * of the empathy class. See the main plugin file for more
 * information about each function.
 */

function in_empathy( $emotion = null, $_post = null ) {
	global $empathy;
	
	return $empathy->in_empathy( $emotion, $_post );
}

function is_empathy( $emotion = null ) {
	global $empathy;
	
	return $empathy->is_empathy( $emotion );
}

function get_empathy( $id = null ) {
	global $empathy;
	
	return $empathy->get_empathy( $id );
}

function the_empathy( $separator = null, $_post = null, $display = true ) {
	global $empathy;
	
	$output = $empathy->the_empathy( $separator, $_post, false );
	
	if ( $display )
		echo $output;
	else
		return $output;
}

function get_empathy_info( $emotion = null ) {
	global $empathy;
	
	return $empathy->get_empathy_info( $emotion );
}

function the_empathy_info( $info = 'name', $emotion = null, $display = true ) {
	global $empathy;
	
	$output = $empathy->the_empathy_info( $info, $emotion, $display );
	
	if ( $display )
		echo $output;
	else
		return $output;
}

function the_empathy_object( $emotion = null, $theme = null, $display = true, $size = null ) {
	global $empathy;
	
	$output = $empathy->the_empathy_object( $emotion, $theme, false, $size );
	
	if ( $display )
		echo $output;
	else
		return $output;
}

function the_author_emotion( $user = null, $theme = null, $display = true ) {
	global $empathy;
	
	$output = $empathy->the_empathy_object( $user, $theme, false );
	
	if ( $display )
		echo $output;
	else
		return $output;
}

function empathy_list_emotions( $args = null ) {
	global $empathy;
	
	$defaults = array(
		'show_option_all' => '', 'orderby' => 'name',
		'order' => 'ASC', 'show_last_update' => 0,
		'style' => 'list', 'show_count' => 0,
		'hide_empty' => 1, 'use_desc_for_title' => 1,
		'child_of' => 0, 'feed' => '', 'feed_type' => '',
		'feed_image' => '', 'exclude' => '', 'exclude_tree' => '', 'current_emotion' => 0,
		'hierarchical' => true, 'title_li' => __( 'Emotions', 'empathy' ),
		'echo' => 1, 'depth' => 0
	);
	
	$r = wp_parse_args( $args, $defaults );
	
	$display     = ( $r[ 'echo' ] ) ? true : false;
	$r[ 'echo' ] = false;
	
	$output = $empathy->empathy_list_emotions( $r );
	
	if ( $display )
		echo $output;
	else
		return $output;
}

function empathy_dropdown_emotions( $args = null ) {
	global $empathy;
	
	$defaults = array(
		'show_option_all' => '', 'show_option_none' => '',
		'orderby' => 'id', 'order' => 'ASC',
		'show_last_update' => 0, 'show_count' => 0,
		'hide_empty' => 1, 'child_of' => 0,
		'exclude' => '', 'echo' => 1,
		'selected' => 0, 'hierarchical' => 0,
		'name' => 'empathy_emotion', 'class' => 'postform',
		'depth' => 0, 'tab_index' => 0
	);
	
	$r = wp_parse_args( $args, $defaults );
	
	$display     = ( $r[ 'echo' ] ) ? true : false;
	$r[ 'echo' ] = false;
	
	$output = $empathy->empathy_dropdown_emotions( $r );
	
	if ( $display )
		echo $output;
	else
		return $output;
}

function empathy_emotion_cloud( $args = null, $terms = null ) {
	global $empathy;
	
	$defaults = array(
		'smallest' => 8, 'largest' => 22, 'unit' => 'pt', 'number' => 0,
		'format' => 'flat', 'orderby' => 'name', 'order' => 'ASC',
		'topic_count_text_callback' => 'default_topic_count_text',
		'filter' => 1, 'echo' => 1
	);
	
	$r = wp_parse_args( $args, $defaults );
	
	$display     = ( $r[ 'echo' ] ) ? true : false;
	$r[ 'echo' ] = false;
	
	$output = $empathy->empathy_emotion_cloud( $r, $terms );
	
	if ( $display )
		echo $output;
	else
		return $output;
}

function empathy_related_posts( $args = null ) {
	global $empathy;
	
	$defaults = array(
		'number' => 0, 'format' => 'list', 'order' => 'ASC',
		'title' => __( 'Related by %s', 'empathy' ), 'theme' => false,
		'filter' => 1, 'echo' => 1, 'display' => 'text'
	);

	$r = wp_parse_args( $args, $defaults );
	
	$display     = ( $r[ 'echo' ] ) ? true : false;
	$r[ 'echo' ] = false;
	
	$output = $empathy->empathy_related_posts( $r );
	
	if ( $display )
		echo $output;
	else
		return $output;
}

function empathy_site_emotion( $theme = null, $display = true ) {
	global $empathy;
	
	$output = $empathy->empathy_site_emotion( $theme, false );
	
	if ( $display )
		echo $output;
	else
		return $output;
}
?>