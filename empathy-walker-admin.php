<?php
global $empathy;

class empathy_Walker_AdminEmotionsParent extends Walker {
	var $tree_type = 'empathy_emotion';
	var $db_fields = array('parent' => 'parent', 'id' => 'term_id');
	
	function start_el( &$output, $emotion, $depth, $args ) {
		$output .= '<option value="' . $emotion->term_id . '"';
		
		if ( $emotion->term_id == $args[ 'parent' ] )
			$output .= ' selected';
		
		$output .= '>' . str_repeat( '&nbsp;', $depth * 3 ) . $emotion->name . '</option>';
	}
}

class empathy_Walker_AdminEmotionsSelect extends Walker {
	var $tree_type = 'empathy_emotion';
	var $db_fields = array('parent' => 'parent', 'id' => 'term_id');
	
	function start_el( &$output, $emotion, $depth, $args ) {
		if ( in_array( $emotion->slug, $args[ 'defaults' ] ) )
			$checked = ' checked';
		
		$output .= '<p><label>' . str_repeat( '&nbsp;', $depth * 6 ) . '<input type="checkbox" name="empathy_emotions[]" value="' . $emotion->slug . '"' . $checked;
		
		foreach ( $args[ 'emotions' ] as $em ) {
			if ( $emotion->slug == $em->slug ) {
				$output .= ' checked';
				break;
			}
		}
		
		$output .= '> ' . $emotion->name . '</label></p>';
	}
}

class empathy_Walker_AdminEmotionsList extends Walker {
	var $tree_type = 'empathy_emotion';
	var $db_fields = array( 'parent' => 'parent', 'id' => 'term_id' );
	
	function start_el( &$output, $emotion, $depth, $args ) {
		global $wpdb;
		
		static $i = 0;
		
		if ( !( $i % 2 ) )
			$alt = ' class="alt"';
		
		if ( in_array( 'description', $args[ 'hidden' ] ) )
			$dhide = ' style="display:none"';
		
		if ( in_array( 'slug', $args[ 'hidden' ] ) )
			$shide = ' style="display:none"';
		
		if ( in_array( 'posts', $args[ 'hidden' ] ) )
			$phide = ' style="display:none"';
		
		if ( in_array( $emotion->slug, $args[ 'defaults' ] ) ) {
			$df = ' | <a href="' . $args[ 'view' ] . '&amp;action=remove_default_empathy_emotion&amp;empathy_emotion=' . $emotion->term_id . '">Remove from Defaults</a>';
			$style = ' style="background:#fffeeb"';
		} else
			$df = ' | <a href="' . $args[ 'view' ] . '&amp;action=add_default_empathy_emotion&amp;empathy_emotion=' . $emotion->term_id . '">Add to Defaults</a>';
		
		$output .= '<tr' . $alt . $style . '><th scope="row" class="check-column"><input type="checkbox" name="bulk[]" value="' . $emotion->term_id . '"></th><td class="name column-name"><a href="' . $args[ 'view' ] . '&amp;subpage=edit_empathy_emotion&amp;empathy_emotion=' . $emotion->term_id . '" title=\'' . sprintf( __( 'Edit "%s"', 'empathy' ), $emotion->name ) . '\' class="row-title">' . str_repeat( '&mdash; ', $depth ) . $emotion->name . '</a><div class="row-actions"><a href="' . $args[ 'view' ] . '&amp;subpage=edit_empathy_emotion&amp;empathy_emotion=' . $emotion->term_id . '">' . __( 'Edit', 'empathy' ) . '</a>' . $df . ' | <span class="delete"><a href="' . wp_nonce_url( $args[ 'view' ] . '&amp;action=delete_empathy_emotion&amp;empathy_emotion=' . $emotion->term_id, 'delete_empathy_emotion' ) . '" onclick="if (confirm(\'' . js_escape( sprintf( __( "You are about to delete '%s' and any files associated with it.\n 'Cancel' to stop, 'OK' to delete.", "empathy" ), $emotion->name ) ) . '\')){return true;}return false;">' . __( 'Delete', 'empathy' ) . '</a></span></div></td><td class="description column-description"' . $dhide . '>' . $emotion->description . '</td><td class="slug column-slug"' . $shide . '>' . $emotion->slug . '</td><td class="posts column-posts"' . $phide . '>' . $emotion->count . '</td></tr>';
		
		$i++;
	}
}

class empathy_Walker_AdminEmotionsEdit extends Walker {
	var $tree_type = 'empathy_emotion';
	var $db_fields = array( 'parent' => 'parent', 'id' => 'term_id' );
	
	function start_el( &$output, $emotion, $depth, $args ) {
		global $empathy, $wpdb;
		
		static $i = 0;
		
		if ( !( $i % 2 ) )
			$alt = ' class="alt"';
		
		$info   = $empathy->get_empathy_info( $emotion->term_id, $args[ 'theme' ] );
		$object = $empathy->the_empathy_object( $emotion, $args[ 'theme' ], false );
		$delete = ( $object ) ? ' <span class="delete"><a href="' . wp_nonce_url( $args[ 'view' ] . '&amp;action=delete_empathy_file&amp;empathy_theme=' . $args[ 'theme' ] . '&amp;empathy_emotion=' . $emotion->slug, 'delete_empathy_file' ) . '" onclick="if (confirm(\'' . js_escape( sprintf( __( "You are about to delete the file associated with '%s' for this theme.\n 'Cancel' to stop, 'OK' to delete.", "empathy" ), $emotion->name ) ) . '\')){return true;}return false;">' . __( 'Delete', 'empathy' ) . '</a></span>' : '';
		$check  = ( $object ) ? '<input type="checkbox" name="bulk[]" value="' . $emotion->slug . '">' : '';
		
		$output .= '<tr' . $alt . '><th scope="row" class="check-column">' . $check . '</th><td>' . $object . '</td><td class="name column-name"><span class="row-title">' . str_repeat( '&mdash; ', $depth ) . $emotion->name . '</span> '. sprintf( __( '(%d posts)', 'empathy' ), $emotion->count ) . '<div class="row-actions">' . wp_nonce_field( 'upload_empathy_file' ) . '<input type="file" name="new_empathy_file[' . $emotion->slug . ']">' . $delete . '</div><p>' . $emotion->description . '</p></td></tr>';
		
		$i++;
	}
}
?>