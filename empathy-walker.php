<?php
global $empathy;

class empathy_Walker_Emotions extends Walker {
	var $tree_type = 'empathy_emotion';
	var $db_fields = array ( 'parent' => 'parent', 'id' => 'term_id' );
	
	function start_lvl( &$output, $depth, $args ) {
		if ( 'list' != $args[ 'style' ] )
			return;

		$indent = str_repeat( "\t", $depth );
		$output .= "$indent<ul class='children'>\n";
	}
	
	function end_lvl( &$output, $depth, $args ) {
		if ( 'list' != $args[ 'style' ] )
			return;

		$indent = str_repeat( "\t", $depth );
		$output .= "$indent</ul>\n";
	}
	
	function start_el( &$output, $emotion, $depth, $args ) {
		global $empathy;
		
		extract( $args );
		
		$em_name = esc_attr( $emotion->name);
		$em_name = apply_filters( 'list_emotions', $em_name, $emotion );
		
		$link = '<a href="' . get_term_link( ( int ) $emotion->term_id, 'empathy_emotion' ) . '" ';
		
		if ( $use_desc_for_title == 0 || empty( $emotion->description ) )
			$link .= 'title="' . sprintf( __( 'View all %s posts', 'empathy' ), $em_name) . '"';
		else
			$link .= 'title="' . esc_attr( strip_tags( apply_filters( 'emotion_description', $emotion->description, $emotion ) ) ) . '"';
		$link .= '>';
		
		$object = $empathy->the_empathy_object( $emotion, $empathy_theme, false );
		
		if ( $object && 'text' != $empathy_display )
			$link .= $object;
		else
			$link .= $em_name;
		
		$link .= '</a>';

		if ( ( !empty( $feed_image ) ) || ( !empty( $feed ) ) ) {
			$link .= ' ';

			if ( empty( $feed_image ) )
				$link .= '(';

			$link .= '<a href="' . $empathy->get_term_feed_link( $emotion, 'empathy_emotion', $feed_type ) . '"';

			if ( empty( $feed ) )
				$alt = ' alt="' . sprintf(__( 'Feed for all %s posts' ), $em_name ) . '"';
			else {
				$title = ' title="' . $feed . '"';
				$alt = ' alt="' . $feed . '"';
				$name = $feed;
				$link .= $title;
			}

			$link .= '>';

			if ( empty( $feed_image ) )
				$link .= $name;
			else
				$link .= "<img src='$feed_image'$alt$title" . '>';
			
			$link .= '</a>';
			
			if ( empty( $feed_image ) )
				$link .= ')';
		}

		if ( isset( $show_count ) && $show_count )
			$link .= ' (' . intval( $emotion->count ) . ')';

		if ( isset( $current_emotion ) && $current_emotion )
			$_current_emotion = get_term( $current_emotion, 'empathy_emotion' );

		if ( 'list' == $args[ 'style' ] ) {
			$output .= "\t<li";
			$class   = 'emotion-item emotion-item-' . $emotion->term_id;
			
			if ( isset( $current_emotion ) && $current_emotion && ( $emotion->term_id == $current_emotion ) )
				$class .=  ' current-emotion';
			elseif ( isset( $_current_emotion ) && $_current_emotion && ( $emotion->term_id == $_current_emotion->parent ) )
				$class .=  ' current-emotion-parent';
			
			$output .= ' class="' . $class . '"';
			$output .= ">$link\n";
		} else
			$output .= "\t$link<br>\n";
	}
	
	function end_el( &$output, $page, $depth, $args ) {
		if ( 'list' != $args['style'] )
			return;

		$output .= "</li>\n";
	}
}

class empathy_Walker_DropdownEmotions extends Walker {
	var $tree_type = 'empathy_emotion';
	var $db_fields = array ( 'parent' => 'parent', 'id' => 'term_id' );
	
	function start_el( &$output, $emotion, $depth, $args ) {
		$em_name = apply_filters( 'dropdown_emotions', $emotion->name, $emotion );
		
		$output .= "\t<option class=\"level-$depth\" value=\"" . get_term_link( ( int ) $emotion->term_id, 'empathy_emotion' ) . "\"";
		
		if ( $emotion->slug == $args[ 'selected' ] )
			$output .= ' selected';
		
		$output .= '>' . str_repeat( '&nbsp;', $depth * 3 ) . $em_name;
		
		if ( $args[ 'show_count' ] )
			$output .= '&nbsp;&nbsp;(' . $emotion->count . ')';
		
		$output .= "</option>\n";
	}
}
?>