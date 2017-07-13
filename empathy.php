<?php
/*
Text Domain: empathy
Plugin Name: Empathy
Plugin URI: http://groups.google.com/group/empathy-discussion/
Description: You're emotional. Empathy understands. Make your emotions an integral part of your site with Empathy.
Version: 1.0.3
Author: Michael Sisk
Author URI: http://maikeruon.com/

Copyright 2009 Michael Sisk (email: mike@maikeruon.com)

The images included with this plugin are the work of their respective artists.
For full terms of use information please contact the original artists:

2s-Space Emotions v2 by kirozeng
http://kirozeng.deviantart.com/art/2s-space-Emotions-v2-72785912

Manto Emoticons by Manto
http://365icon.com/icon-styles/emoticons/manto-emotion-icons-emoticons/

Tango Emoticons by Furyo-kun
http://furyo-kun.deviantart.com/art/Tango-Emotes-121853363

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

if ( !class_exists( 'mgs_plugin_core' ) ) require_once( 'mgs-plugin-core.php' );

class empathy extends mgs_plugin_core {
	protected $version = '1.0.3';
	protected $file    = __FILE__;
	
	/**
	 * Run-once installation.
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function install() {
		if ( !is_admin() ) return;
		
		$this->domain();
		
		$this->option( array(
			'version'            => $this->version,
			'integrate'          => false,
			'integrate_format'   => false,
			'integrate_related'  => false,
			'integrate_archive'  => false,
			'integrate_position' => false,
			'current'            => 'tango',
			'defaults'           => array(),
			'files'              => array( '2s-space' => array(), 'manto' => array(), 'tango' => array() ),
			'themes'             => array( '2s-space' => '2s Space', 'manto' => 'Manto', 'tango' => 'Tango' )
		) );
	
		$defaults = array( __( 'Anger', 'empathy' ), __( 'Annoyance', 'empathy' ), __( 'Concern', 'empathy' ), __( 'Confusion', 'empathy' ), __( 'Contempt', 'empathy' ), __( 'Despair', 'empathy' ), __( 'Deviousness', 'empathy' ), __( 'Embarrassment', 'empathy' ), __( 'Exhaustion', 'empathy' ), __( 'Fear', 'empathy' ), __( 'Joy', 'empathy' ), __( 'Love', 'empathy' ), __( 'Obsession', 'empathy' ), __( 'Pain', 'empathy' ), __( 'Pride', 'empathy' ), __( 'Rage', 'empathy' ), __( 'Sadness', 'empathy' ), __( 'Shyness', 'empathy' ), __( 'Surprise', 'empathy' ), __( 'Vigilance', 'empathy' ) );
		
		foreach ( $defaults as $emotion )
			wp_insert_term( $emotion, 'empathy_emotion' );
		
		$files = $this->option( 'files' );
		
		foreach ( array_keys( $this->option( 'themes' ) ) as $theme ) {
			if ( !is_dir( $this->get_empathy_directory( 'abs', $theme ) ) )
				mkdir( $this->get_empathy_directory( 'abs', $theme ), 0775, true );
			
			foreach ( $defaults as $emotion ) {
				if ( is_file( $this->dir . $theme . '/' . strtolower( $emotion ) . '.png' ) ) {
					if ( copy( $this->dir . $theme . '/' . strtolower( $emotion ) . '.png', $this->get_empathy_directory( 'abs', $theme ) . strtolower( $emotion ) . '.png' ) ) {
						$files[ $theme ][ strtolower( $emotion ) ] = '.png';
						
						if ( strpos( PHP_OS, 'WIN' ) )
							chmod( $this->get_empathy_directory( 'abs', $theme ) . strtolower( $emotion ) . '.png', 0777 );
						else
							chmod( $this->get_empathy_directory( 'abs', $theme ) . strtolower( $emotion ) . '.png', 0664 );
					}
				}
			}
		}
		
		$this->option( 'files', $files );
	}
	
	/**
	 * Upgrades older versions.
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function upgrade() {
		if ( !is_admin() ) return;
		
		$this->option( 'version', $this->version );
	}
	
	/**
	 * Uninstalls the plugin
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function uninstall() {
		if ( !is_admin() ) return;
		
		if ( $emotions = get_terms( 'empathy_emotion', 'hide_empty=0' ) )
			foreach ( $emotions as $emotion )
				wp_delete_term( $emotion->term_id, 'empathy_emotion' );
		
		$files  = $this->option( 'files' );
		$themes = $this->option( 'themes' );
		
		foreach ( array_keys( $themes ) as $theme ) {
			foreach ( $files[ $theme ] as $k => $v )
				if ( is_file( $this->get_empathy_directory( 'abs', $theme ) . $k . $v ) )
					unlink( $this->get_empathy_directory( 'abs', $theme ) . $k . $v );
			
			if ( is_dir( $this->get_empathy_directory( 'abs', $theme ) ) )
				rmdir( $this->get_empathy_directory( 'abs', $theme ) );
			
			unset( $files[ $theme ], $themes[ $theme ] );
		}
		
		if ( is_dir( $this->get_empathy_directory( 'abs' ) ) )
			rmdir( $this->get_empathy_directory( 'abs' ) );
		
		$wp_user_search = new WP_User_Search( null, null, null );
		
		foreach ( $wp_user_search->get_results() as $userid ) {
			delete_usermeta( $userid, 'empathy_theme' );
			delete_usermeta( $userid, 'empathy_emotion' );
		}
		
		$this->option( array( 'version' => $this->version, 'defaults' => array(), 'files' => array(), 'themes' => array(), 'uninstall' => true ) );
	}
	
	
	
	//
	// Hooks
	//
	
	/**
	 * Registeres the empathy_emotion taxonomy and loads swfobject.
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function hook_init() {
		global $wp_rewrite;
		
		$this->domain();
		
		$rewrite = ( is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() ) ? array( 'slug' => 'emotion' ) : false;
		
		register_taxonomy( 'empathy_emotion', 'post', array( 'hierarchical' => true, 'label' => __( 'Emotion', 'empathy' ), 'update_count_callback' => '_update_post_term_count', 'rewrite' => $rewrite, 'query_var' => 'empathy_emotion' ) );
		
		$wp_rewrite->flush_rules();
		
		wp_enqueue_script( 'swfobject' );
	}
	
	
	function hook_template_redirect() {
		wp_enqueue_script( 'empathy-emotion-scripts', $this->url . 'empathy-scripts.js', array( 'jquery' ) );
	}
	
	/**
	 * Registers administrative pages, help menus, and columns.
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function hook_admin_menu() {
		$this->domain();
		
		add_menu_page( __( 'Empathy', 'empathy' ), __( 'Empathy', 'empathy' ), 'upload_files', 'empathy-themes', array( &$this, 'admin_themes' ), $this->url . 'icon-small.png' );
		
		$themes   = add_submenu_page( 'empathy-themes', __( 'Empathy Themes', 'empathy' ), __( 'Themes', 'empathy' ), 'manage_categories', 'empathy-themes', array( &$this, 'admin_themes' ) );
		$emotions = add_submenu_page( 'empathy-themes', __( 'Empathy Emotions', 'empathy' ), __( 'Emotions', 'empathy' ), 'manage_categories', 'empathy-emotions', array( &$this, 'admin_emotions' ) );
		$tools    = add_submenu_page( 'empathy-themes', __( 'Empathy Tools', 'empathy' ), __( 'Tools', 'empathy' ), 'manage_options', 'empathy-tools', array( &$this, 'admin_tools' ) );
		$settings = add_submenu_page( 'empathy-themes', __( 'Empathy Settings', 'empathy' ), __( 'Settings', 'empathy' ), 'manage_options', 'empathy-settings', array( &$this, 'admin_settings' ) );
		
		add_meta_box( 'empathy', __( 'Empathy', 'empathy' ), array( &$this, 'post_meta_box' ), 'post', 'normal', 'high' );
		add_meta_box( 'empathy', __( 'Empathy', 'empathy' ), array( &$this, 'page_meta_box' ), 'page', 'normal', 'high' );
		
		$help = '<a href="http://groups.google.com/group/empathy-discussion/web" target="_blank">' . __( 'Empathy Documentation', 'empathy' ) . '</a><br><a href="http://groups.google.com/group/empathy-discussion/" target="_blank">' . __( 'Empathy Support Group', 'empathy' ) . '</a>';
		
		add_contextual_help( $themes, $help );
		add_contextual_help( $emotions, $help );
		add_contextual_help( $tools, $help );
		add_contextual_help( $settings, $help );
		
		register_column_headers( $emotions, array( 'description' => __( 'Description', 'empathy' ), 'slug' => __( 'Slug', 'empathy' ), 'posts' => __( 'Posts', 'empathy' ) ) );
	}
	
	/**
	 * Corrects the pagenow javascript variable for dynamic administration columns.
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function hook_admin_enqueue_scripts( $suffix ) {
		if ( false !== strpos( $suffix, 'empathy-emotions' ) ) 
			echo "<script type='text/javascript'>\n//<![CDATA[\nvar pagenow = '$suffix';\n//]]>\n</script>";
	}
	
	/**
	 * Registers new widgets. See empathy-widgets.php.
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function hook_widgets_init() {
		register_widget( 'empathy_Widget_Emotions' );
		register_widget( 'empathy_Widget_SiteEmotion' );
		register_widget( 'empathy_Widget_EmotionCloud' );
	}
	
	/**
	 * Update post taxonomy relatinoships for empath_emotion.
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function hook_save_post( $id ) {
		if ( $_post = wp_is_post_revision( $id ) )
			$id = $_post;
		
		if ( !$_REQUEST[ 'original_publish' ] )
			return;
		
		if ( $_REQUEST[ 'empathy_emotions' ] )
			wp_set_object_terms( $id, $_REQUEST[ 'empathy_emotions' ], 'empathy_emotion' );
		else
			wp_delete_object_term_relationships( $id, 'empathy_emotion' );
	}
	
	/**
	 * Remove any empathy_emotion relations from a deleted post.
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function hook_delete_post( $id ) {
		wp_delete_object_term_relationships( $id, 'empathy_emotion' );
	}
	
	/**
	 * Displays the Empathy theme selector on user profile pages.
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function hook_show_user_profile( $user ) {
		?>
		<h3><?php _e( 'Empathy', 'empathy' ); ?></h3>
		<table class="form-table">
			<tr>
				<th><label for="empathy_emotion_theme"><?php _e( 'Emotion Theme' ); ?></label></th>
				<td>
					<select name="empathy_emotion_theme" id="empathy_emotion_theme">
						<option value=""><?php _e( '- default -', 'empathy' ); ?></option>
						<?php
							$utheme = get_usermeta( $user->ID, 'empathy_theme' );
							$themes = $this->option( 'themes' );
							foreach ( $themes as $k => $v ) {
								$select = ( $utheme == $k ) ? ' selected' : '';
								echo '<option value="' . $k . '"' . $select . '>' . $v . '</option>';
							}
						?>
					</select><br>
					<span class="description"><?php _e( 'Select an Empathy theme to use for displaying your emotions.', 'empathy' ); ?></span>
				</td>
			</tr>
			<tr>
				<th><label for="empathy_emotion"><?php _e( 'Current Emotion' ); ?></label></th>
				<td>
					<select name="empathy_emotion" id="empathy_emotion">
						<option value="0"><?php _e( 'None', 'empathy' ); ?></option>
						<?php
							$cmotion = get_usermeta( $user->ID, 'empathy_emotion' );
							$walker = new empathy_Walker_AdminEmotionsParent();
							echo $walker->walk( get_terms( 'empathy_emotion', 'hide_empty=0' ), 0, array( 'parent' => $cmotion ) );
						?>
					</select><br>
					<span class="description"><?php _e( 'Your current emotion can be displayed on your author page.', 'empathy' ); ?></span>
				</td>
			</tr>
		</table>
		<?php
	}
	
	/**
	 * Updates a users Empathy theme.
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function hook_profile_update( $id ) {
		if ( $_REQUEST[ 'empathy_emotion_theme' ] )
			update_usermeta( $id, 'empathy_theme', $_REQUEST[ 'empathy_emotion_theme' ] );
		else
			delete_usermeta( $id, 'empathy_theme' );
		
		if ( $_REQUEST[ 'empathy_emotion' ] )
			update_usermeta( $id, 'empathy_emotion', $_REQUEST[ 'empathy_emotion' ] );
		else
			delete_usermeta( $id, 'empathy_emotion' );
	}
	
	/**
	 * Integrates Empathy features based on user settings.
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function hook_the_content( $content ) {
		if ( !is_feed() && $this->in_empathy() ) {
			if ( $this->option( 'integrate' ) ) {
				switch ( $this->option( 'integrate_format' ) ) {
					case 'cloud': $emotions = $this->empathy_emotion_cloud( false, $this->get_empathy() ); break;
					case 'list' : $emotions = $this->the_empathy( false, false, false, false );break;
					case 'flat' :
					default     : $emotions =$this->the_empathy( ' ', false, false, false );
				}
				
				switch ( $this->option( 'integrate_position' ) ) {
					case 'above': $content = '<div>' . $emotions . '</div>' . $content; break;
					case 'below': 
					default     : $content = $content . '<div>' . $emotions . '</div>';
				}
			}
			
			if ( is_single() && $this->option( 'integrate_related' ) )
				$content .= $this->empathy_related_posts( 'echo=0' );
		}
		
		return $content;
	}
	
	/**
	 * Integrates Empathy features based on user settings.
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function hook_loop_start() {
		if ( is_empathy() && $this->option( 'integrate_archive' ) ) {
			echo '<h2>' . the_empathy_info( 'name', false, false ) . '</h2>';
			
			if ( the_empathy_info( 'descriptoin', false, false ) )
				echo '<p>' . the_empathy_info( 'descriptoin', false, false ) . '<p>';
		}
	}
	
	/**
	 * Adds empathy_emotion CSS classes to post_class.
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function hook_post_class( $classes ) {
		global $post;
		
		if ( $this->in_empathy() ) {
			$emotions = $this->get_empathy();
			
			foreach ( $this->get_empathy( $post->ID ) as $emotion ) {
				if ( !$emotion->slug ) continue;
				$classes[] = 'emotion-' . sanitize_html_class( $emotion->slug, $emotion->cat_ID );
			}
		}
		
		return $classes;
	}
	
	/**
	 * Adds empathy_emotion CSS classes to body_class.
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function hook_body_class( $classes ) {
		if ( $this->is_empathy() ) {
			$emotion   = $this->get_empathy_info();
			$classes[] = 'emotion';
			$classes[] = 'emotion-' . sanitize_html_class( $emotion->slug, $emotion->cat_ID );
		}
		
		return $classes;
	}
	
	/**
	 * Adds any file information related to the empathy_emotion term.
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function hook_get_empathy_emotion( $term ) {
		$term->empathy_files = array();
		
		$files = $this->option( 'files' );
		
		foreach ( $files as $k => $v )
			if ( in_array( $term->slug, array_keys( $v ) ) )
				$term->empathy_files[ $k ] = $this->get_empathy_directory( 'url', $k ) . $term->slug . $v[ $term->slug ];
		
		return $term;
	}
	
	/**
	 * Adds any file information related to any empathy_emotion terms.
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function hook_get_terms( $terms ) {
		$term->empathy_files = array();
		
		$files = $this->option( 'files' );
		
		foreach ( $terms as $term ) {
			if ( 'empathy_emotion' != $term->taxonomy )
				continue;
			
			foreach ( $files as $k => $v )
				if ( in_array( $term->slug, array_keys( $v ) ) )
					$term->empathy_files[ $k ] = $this->get_empathy_directory( 'url', $k ) . $term->slug . $v[ $term->slug ];
		}
		
		return $terms;
	}
	
	/**
	 * Adds any file information to the empathy_emotion terms.
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function hook_wp_get_object_terms( $terms ) {
		$term->empathy_files = array();
		
		$files = $this->option( 'files' );
		
		foreach ( $terms as $term ) {
			if ( 'empathy_emotion' != $term->taxonomy )
				continue;
			
			foreach ( $files as $k => $v )
				if ( in_array( $term->slug, array_keys( $v ) ) )
					$term->empathy_files[ $k ] = $this->get_empathy_directory( 'url', $k ) . $term->slug . $v[ $term->slug ];
		}
		
		return $terms;
	}
	
	/**
	 * Retrieves the correct feed link for a term based on taxonomy.
	 * 
	 * @package Empathy
	 * @since 1
	 * 
	 * @param str|int|obj $term A term ID, slug, or object.
	 * @param str $taxonomy Taxonomy the $term belongs to.
	 * @param str $feed The type of feed. Optional.
	 * @return Feed URL for the for the specified term in the specified taxonomy.
	 */
	function get_term_feed_link( $term = null, $taxonomy = null, $feed = null ) {
		global $wp_rewrite;
		
		if ( !is_object( $term ) ) {
			if ( is_int( $term ) )
				$term = &get_term( $term, $taxonomy );
			else
				$term = &get_term_by( 'slug', $term, $taxonomy );
		}
	
		if ( is_wp_error( $term ) )
			return false;
	
		if ( empty( $feed ) )
			$feed = get_default_feed();
		
		$termlink = $wp_rewrite->get_extra_permastruct( $taxonomy );
		
		if ( empty( $termlink ) ) {
			$file = get_option( 'home' ) . '/';
			$tax  = get_taxonomy( $taxonomy );
			
			$link = trailingslashit( get_option( 'home' ) ) . "?feed=$feed&amp;$tax->query_var=" . $term->slug;
		} else {
			$link = get_term_link( ( int ) $term->term_id, $taxonomy );
			
			if ( $feed == get_default_feed() )
				$feed_link = 'feed';
			else
				$feed_link = "feed/$feed";
			
			$link = trailingslashit( $link ) . user_trailingslashit( $feed_link, 'feed' );
		}
	
		$link = apply_filters( 'term_feed_link', $link, $feed );
	
		return $link;
	}
	
	/**
	 * Retrieves the specified directory string in the specified format.
	 * 
	 * @package Empathy
	 * @since 1
	 * 
	 * @param str $type The type of path to return, one of 'abs' or 'url'.
	 * @param str $theme Theme slug. Optional.
	 */
	function get_empathy_directory( $type = null, $theme = null ) {
		switch ( $type ) {
			case 'abs': if ( $theme ) return $this->cdir . 'empathy/' . $theme . '/'; else return $this->cdir . 'empathy/';
			case 'url': if ( $theme ) return $this->curl . 'empathy/' . $theme . '/'; else return $this->curl . 'empathy/';
			default: return;
		}
	}
	
	
	
	//
	// Template Tags
	//
	
	/**
	 * Checks whether the current post is related to any empathy_emotion terms.
	 * 
	 * @package Empathy
	 * @since 1
	 * 
	 * @param int|str|arr $emotion Emotion ID, name, or slug, or an array of those. Optional.
	 * @param int|obj $_post A post ID or object to check. Optional.
	 */
	function in_empathy( $emotion = null, $_post = null ) {
		if ( $_post )
			$_post = get_post( $_post );
		else
			$_post =& $GLOBALS[ 'post' ];
		
		if ( !$_post )
			return;
		
		$r = is_object_in_term( $_post->ID, 'empathy_emotion', $emotion );
		
		if ( is_wp_error( $r ) )
			return;
		
		return $r;
	}
	
	/**
	 * Checks whether the current page is related to the empathy_emotion taxonomy.
	 * 
	 * @package Empathy
	 * @since 1
	 * 
	 * @param str|arr $emotion Slug or slugs to check. Optional.
	 */
	function is_empathy( $emotion = null ) {
		global $wp_query;
	
		if ( !$wp_query->is_tax )
			return false;
		
		$taxonomy = get_taxonomy( get_query_var( 'taxonomy' ) );
		
		if ( 'empathy_emotion' == $taxonomy->name && !$emotion )
			return true;
		elseif ( 'empathy_emotion' != $taxonomy->name )
			return false;
		
		$col_obj = $wp_query->get_queried_object();
	
		$emotion = ( array ) $emotion;
	
		if ( in_array( $col_obj->term_id, $emotion ) )
			return true;
		elseif ( in_array( $col_obj->name, $emotion ) )
			return true;
		elseif ( in_array( $col_obj->slug, $emotion ) )
			return true;
	
		return false;
	}
	
	/**
	 * Returns an array of empathy_emotion taxonomy objects related to the current post
	 * 
	 * @package Empathy
	 * @since 1
	 * 
	 * @param int|str|obj $id A post ID or object. Defaults to the current post ID when used in a Loop. Optional.
	 * @return arr An array of taxonomy objects, or an empty array if no objects can be found.
	 */
	function get_empathy( $id = null ) {
		global $post;
	
		$id = ( int ) $id;
		
		if ( !$id )
			$id = ( int ) $post->ID;
	
		$emotions = get_object_term_cache( $id, 'empathy_emotion' );
		
		if ( false === $emotions ) {
			$emotions = wp_get_object_terms( $id, 'empathy_emotion' );
			wp_cache_add( $id, $emotions, 'empathy_emotion_relationships' );
		}
		
		if ( !empty( $emotions ) )
			usort( $emotions, '_usort_terms_by_name' );
		else
			$emotions = array();
		
		return $emotions;
	}
	
	/**
	 * Display empathy emotion list in either HTML list or custom format.
	 *
	 * @since 1.5.1
	 *
	 * @param str $separator Separator for between the emotions. Optional.
	 * @param int $_post Post ID to retrieve emotions. Defaults to the current post ID in the Loop.
	 * @param bool $display Display or return the information.
	 * @return str Returns the formatted list if $display is false.
	 */
	function the_empathy( $separator = null, $theme = null, $_post = null, $display = true ) {
		global $post;
		
		$_post = ( $_post ) ? $_post : $post->ID;
		
		$emotions = $this->get_empathy( $_post );
		
		if ( !$emotions )
			return;
	
		$thelist = '';
		
		if ( '' == $separator ) {
			$thelist .= '<ul class="post-emotions">';
			
			foreach ( $emotions as $emotion ) {
				if ( !( $name = $this->the_empathy_object( $emotion, $theme, false ) ) )
					$name = $emotion->name;
				
				$thelist .= '<li><a href="' . get_term_link( ( int ) $emotion->term_id, 'empathy_emotion' ) . '" title="' . sprintf( __( "View all %s posts" ), $emotion->name ) . '" rel="emotion">' . $name . '</a></li>';
			}
			
			$thelist .= '</ul>';
		} else {
			$i = 0;
			
			foreach ( $emotions as $emotion ) {
				if ( 0 < $i )
					$thelist .= $separator . ' ';
				
				if ( !( $name = $this->the_empathy_object( $emotion, '', false ) ) )
					$name = $emotion->name;
					
				$thelist .= '<a href="' . get_term_link( ( int ) $emotion->term_id, 'empathy_emotion' ) . '" title="' . sprintf( __( "View all %s posts" ), $emotion->name ) . '" rel="emotion">' . $name . '</a>';
				$i++;
			}
		}
		
		if ( $display )
			echo apply_filters( 'the_empathy', $thelist, $separator );
		else
			return apply_filters( 'the_empathy', $thelist, $separator );
	}
	
	/**
	 * Retrieves the taxonomy object for the specified empathy_emotion termm.
	 * 
	 * @package Empathy
	 * @since 1
	 * 
	 * @param str $emotion The slug of the empathy_emotion term to retrieve. Defaults to the slug specified in query_var, if any.
	 * @return obj Taxonomy object.
	 */
	function get_empathy_info( $emotion = null ) {
		$emotion = ( $emotion ) ? $emotion : get_query_var( 'empathy_emotion' );
		
		if ( $output = get_term_by( 'slug', $emotion, 'empathy_emotion' ) ) {
			$output = apply_filters( 'get_empathy_emotion', $output );
			return $output;
		}
	}
	
	/**
	 * Display the specified taxonomy information.
	 * 
	 * @package Empathy
	 * @since 1
	 * 
	 * @param str $info The information to display. May be any valid taxonomy object parameter.
	 * @param str $emotion Empathy emotion slug. Optional.
	 * @param bool $display Display or return the information.
	 * @return str Returns the specified information if $display is false.
	 */
	function the_empathy_info( $info = 'name', $emotion = null, $display = true ) {
		$term   = $this->get_empathy_info( $emotion );
		$output = ( $info ) ? $term->{ $info } : null;
		
		if ( 'link' == $info && !$output )
			$output = get_term_link( ( int ) $term->term_id, 'empathy_emotion' );
		elseif ( 'feed' == $info && !$output )
			$output = $this->get_term_feed_link( ( int ) $term->term_id, 'empathy_emotion' );
		
		if ( $display && $output )
			echo apply_filters( 'the_empathy_emotion', $output );
		else
			return apply_filters( 'the_empathy_emotion', $output );
	}
	
	/**
	 * Displays the object associated with the specified emotion from the specified theme.
	 * 
	 * @package Empathy
	 * @since 1
	 * 
	 * @param obj $emotion Empathy emotion taxonomy object.
	 * @param str $theme Theme slug. Optional.
	 * @param bool $display. Display or return the object. Optional.
	 * @param flo $size The dimensions returned by getimagesize will be multiplied by this. Optional
	 * @return str Returns the formatted string if $display is false.
	 */
	function the_empathy_object( $emotion = null, $theme = null, $display = true, $size = null ) {
		global $authordata;
		
		if ( !is_object( $emotion ) )
			return;
		
		if ( !$theme )
			$theme = ( get_usermeta( $authordata->ID, 'empathy_theme' ) ) ? get_usermeta( $authordata->ID, 'empathy_theme' ) : $this->option( 'current' );
		
		if ( !$emotion->empathy_files[ $theme ] )
			return;
		
		$abspath = $this->get_empathy_directory( 'abs', $theme ) . basename( $emotion->empathy_files[ $theme ] );
		$data    = getimagesize( $abspath );
		
		if ( $size ) { 
			$data[ 0 ] = ceil( ( $size / 100 ) * $data[ 0 ] );
			$data[ 1 ] = ceil( ( $size / 100 ) * $data[ 1 ] );
			$data[ 3 ] = 'width="' . $data[ 1 ] . '" heigh="' . $data[ 1 ] . '"';
		}
		
		$flash   = ( 'application/x-shockwave-flash' == $data[ 'mime' ] ) ? true : false;
		$hash    = ( $flash ) ? hash( 'md5', $abspath . rand() ) : false;
		$object  = ( $flash ) ? '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" id="empathy-' . $hash . '" width="' . $data[ 0 ] . '" height="' . $data[ 1 ] . '"><param name="movie" value="' . $emotion->empathy_files[ $theme ] . '"><!--[if !IE]>--><object type="application/x-shockwave-flash" data="' . $emotion->empathy_files[ $theme ] . '" width="' . $data[ 0 ] . '" height="' . $data[ 1 ] . '"><!--<![endif]--><p>' . $emotion->name . '</p><!--[if !IE]>--></object><!--<![endif]--></object><script type="text/javascript">swfobject.registerObject("empathy-' . $hash . '","9");</script>' : '<img src="' . $emotion->empathy_files[ $theme ] . '" alt="' . $emotion->description . '" title="' . $emotion->name . '" '. $data[ 3 ] .'>';
		
		if ( $display )
			echo apply_filters( 'the_empathy_object', $object );
		else
			return apply_filters( 'the_empathy_object', $object );
	}
	
	/**
	 * Displays the authors current emotion on author pages.
	 * 
	 * @package Empathy
	 * @since 1
	 * 
	 * @param int $user User ID. Defaults to the ID specified in the 'author' query variable.
	 * @param str $theme Theme slug. Optional.
	 * @param bool $display. Display or return the object. Optional.
	 * @return str Returns the formatted string if $display is false. 
	 */
	function the_author_emotion( $user = null, $theme = null, $display = true ) {
		$user = ( $user ) ? $user : get_query_var( 'author' );
		
		if ( !$theme )
			$theme = ( get_usermeta( $user, 'empathy_theme' ) ) ? get_usermeta( $user, 'empathy_theme' ) : $this->option( 'current' );
		
		if ( $id = get_usermeta( $user, 'empathy_emotion' ) ) {
			$emotion = get_term( $id, 'empathy_emotion' );
			
			$output = $this->the_empathy_object( $emotion, $theme, false );
			
			if ( $display )
				echo apply_filters( 'the_author_emotion', $output );
			else
				return apply_filters( 'the_author_emotion', $output );
		}
	}
	
	/**
	 * Displays a list of emotions based on the provided arguments.
	 * 
	 * This function accepts all of the parameters wp_list_categories does,
	 * as well as the following:
	 * 
	 * empathy_theme: The empathy theme to use for displaying emotions.
	 * 
	 * empathy_display: Set to 'text' to display all links as text. By default,
	 * links are displayed using their associated image for the specified theme.
	 * 
	 * @param str|arr $args String or array of arguments (see wp_list_categories for full list).
	 * @return Returns the formatted list if echo is false.
	 */
	function empathy_list_emotions( $args = null ) {
		$defaults = array(
			'show_option_all' => '', 'orderby' => 'name',
			'order' => 'ASC', 'style' => 'list', 'show_count' => 0,
			'hide_empty' => 1, 'use_desc_for_title' => 1,
			'child_of' => 0, 'feed' => '', 'feed_type' => '',
			'feed_image' => '', 'exclude' => '', 'exclude_tree' => '', 'current_emotion' => 0,
			'hierarchical' => true, 'title_li' => __( 'Emotions', 'empathy' ),
			'echo' => 1, 'depth' => 0
		);
	
		$r = wp_parse_args( $args, $defaults );
	
		if ( !isset( $r[ 'pad_counts' ] ) && $r[ 'show_count' ] && $r[ 'hierarchical' ] )
			$r[ 'pad_counts' ] = true;
	
		if ( isset( $r[ 'show_date' ] ) ) {
			$r[ 'include_last_update_time' ] = $r[ 'show_date' ];
		}
	
		if ( true == $r[ 'hierarchical' ] ) {
			$r[ 'exclude_tree' ] = $r[ 'exclude' ];
			$r[ 'exclude' ] = '';
		}
	
		extract( $r );
	
		$emotions = get_terms( 'empathy_emotion', $r );
	
		$output = '';
		
		if ( $title_li && 'list' == $style )
				$output = '<li class="emotions">' . $r[ 'title_li' ] . '<ul>';
	
		if ( empty( $emotions ) ) {
			if ( 'list' == $style )
				$output .= '<li>' . __( 'No Emotions', 'empathy' ) . '</li>';
			else
				$output .= __( 'No emotions', 'empathy' );
		} else {
			global $wp_query;
	
			if( !empty( $show_option_all ) )
				if ( 'list' == $style )
					$output .= '<li><a href="' .  get_bloginfo( 'url' )  . '">' . $show_option_all . '</a></li>';
				else
					$output .= '<a href="' .  get_bloginfo( 'url' )  . '">' . $show_option_all . '</a>';
	
			if ( empty( $r[ 'current_emotion' ] ) && is_empathy() )
				$r[ 'current_emotion' ] = $wp_query->get_queried_object_id();
	
			if ( $hierarchical )
				$depth = $r[ 'depth' ];
			else
				$depth = -1;
			
			if ( empty( $r[ 'walker' ]) || !is_a( $r[ 'walker' ], 'Walker' ) )
				$walker = new empathy_Walker_Emotions;
			else
				$walker = $r[ 'walker' ];
			
			$output .= call_user_func_array( array( &$walker, 'walk' ), array( $emotions, $depth, $r ) );
		}
	
		if ( $title_li && 'list' == $style )
			$output .= '</ul></li>';
	
		$output = apply_filters( 'empathy_list_emotions', $output );
	
		if ( $echo )
			echo $output;
		else
			return $output;
	}
	
	/**
	 * Displays a doropdown list of emotions based on the provided arguments.
	 * 
	 * This function accepts all of the parameters wp_dropdown_categories does.
	 * 
	 * @param str|arr $args String or array of arguments (see wp_list_categories for full list).
	 * @return Returns the formatted list if echo is false.
	 */
	function empathy_dropdown_emotions( $args = null ) {
		$defaults = array(
			'show_option_all' => '', 'show_option_none' => '',
			'orderby' => 'id', 'order' => 'ASC',
			'show_last_update' => 0, 'show_count' => 0,
			'hide_empty' => 1, 'child_of' => 0,
			'exclude' => '', 'echo' => 1,
			'selected' => 0, 'hierarchical' => 0,
			'name' => 'empathy_emotion', 'class' => 'empathy-dropdown-emotions',
			'depth' => 0, 'tab_index' => 0
		);
	
		$defaults[ 'selected' ] = ( $this->is_empathy() ) ? get_query_var( 'empathy_emotion' ) : 0;
	
		$r = wp_parse_args( $args, $defaults );
		
		extract( $r );
	
		$tab_index_attribute = '';
		
		if ( ( int ) $tab_index > 0 )
			$tab_index_attribute = " tabindex=\"$tab_index\"";
	
		$emotions = get_terms( 'empathy_emotion', $r );
	
		$output = '';
		
		if ( ! empty( $emotions ) ) {
			$output = "<select name='$name' id='$name' class='$class' $tab_index_attribute>\n";
	
			if ( $show_option_all ) {
				$show_option_all = apply_filters( 'dropdown_emotions', $show_option_all );
				$selected = ( '0' === strval( $r[ 'selected' ] ) ) ? ' selected' : '';
				$output .= "\t<option value='0'$selected>$show_option_all</option>\n";
			}
	
			if ( $show_option_none ) {
				$show_option_none = apply_filters( 'dropdown_emotions', $show_option_none );
				$selected = ( '-1' === strval( $r[ 'selected' ] ) ) ? ' selected' : '';
				$output .= "\t<option value='-1'$selected>$show_option_none</option>\n";
			}
	
			if ( $hierarchical )
				$depth = $r[ 'depth' ];
			else
				$depth = -1;
			
			if ( empty( $r[ 'walker' ]) || !is_a( $r[ 'walker' ], 'Walker' ) )
				$walker = new empathy_Walker_DropdownEmotions;
			else
				$walker = $r[ 'walker' ];
			
			$output .= call_user_func_array( array( &$walker, 'walk' ), array( $emotions, $depth, $r ) );
			$output .= "</select>\n";
		}
	
		$output = apply_filters( 'empathy_dropdown_emotions', $output );
		
		if ( $echo )
			echo $output;
		else
			return $output;
	}
	
	/**
	 * Displays a cloud list of emotions based on the provided arguments.
	 * 
	 * This function accepts all of the parameters wp_generate_tag_cloud does,
	 * as well as the following:
	 * 
	 * empathy_theme: The empathy theme to use for displaying emotions.
	 * 
	 * empathy_display: Set to 'text' to display all links as text. By default,
	 * links are displayed using their associated image for the specified theme.
	 * 
	 * @param str|arr $args String or array of arguments (see wp_generate_tag_cloud for full list).
	 * @return Returns the formatted list if echo is false.
	 */
	function empathy_emotion_cloud( $args = null, $terms = null ) {
		global $wp_rewrite;
		
		$defaults = array(
			'smallest' => 8, 'largest' => 22, 'unit' => 'pt', 'number' => 0,
			'format' => 'flat', 'orderby' => 'name', 'order' => 'ASC',
			'topic_count_text_callback' => 'default_topic_count_text',
			'filter' => 1, 'echo' => 1
		);
		
		if ( !isset( $args[ 'topic_count_text_callback' ] ) && isset( $args[ 'single_text' ] ) && isset( $args[ 'multiple_text' ] ) ) {
			$body = 'return sprintf (
				_n(' . var_export( $args[ 'single_text' ], true ) . ', ' . var_export( $args[ 'multiple_text' ], true ) . ', $count), number_format_i18n( $count ) );';
			
			$args[ 'topic_count_text_callback' ] = create_function( '$count', $body );
		}
		
		$r = wp_parse_args( $args, $defaults );
			
		extract( $r );
		
		$emotions = ( $terms ) ? $terms : get_terms( 'empathy_emotion', $r );
		
		if ( empty( $emotions ) )
			return;
		
		$largest  = ( $largest ) ? $largest : 22;
		$smallest = ( $smallest ) ? $smallest : 8;
		
		if ( 'name' == $orderby )
			uasort( $emotions, create_function( '$a, $b', 'return strnatcasecmp( $a->name, $b->name );' ) );
		else
			uasort( $emotions, create_function( '$a, $b', 'return ($a->count > $b->count);' ) );
		
		$emotions = apply_filters( 'empathy_cloud_sort', $emotions, $r );
	
		if ( 'DESC' == $order )
			$emotions = array_reverse( $emotions, true );
		elseif ( 'RAND' == $order ) {
			$keys = ( array ) array_rand( $emotions, count( $emotions ) );
			$temp = array();
			
			foreach ( $keys as $key )
				$temp[ $key ] = $emotions[ $key ];
			
			$emotions = $temp;
			$temp = null;
			unset( $temp );
		}
		
		if ( $number > 0 )
			$emotions = array_slice( $emotions, 0, $number );
		
		$counts = array();
		
		foreach ( ( array ) $emotions as $k => $v )
			$counts[ $k ] = $v->count;
		
		$a = array();
		
		if ( 'text' == $empathy_display ) {
			$min_count = min( $counts );
			$spread    = max( $counts ) - $min_count;
			
			if ( $spread <= 0 )
				$spread = 1;
			
			$font_spread = $largest - $smallest;
			
			if ( $font_spread < 0 )
				$font_spread = 1;
			
			$font_step = $font_spread / $spread;
			
			foreach ( $emotions as $k => $v ) {
				$count   = $counts[ $k ];
				$em_link = get_term_link( ( int ) $v->term_id, 'empathy_emotion' );
				$em_id   = isset($emotions[ $k ]->term_id) ? $emotions[ $k ]->term_id : $k;
				$em_name = $emotions[ $k ]->name;
				$a[]     = "<a href='$em_link' class='emotion-link-$em_id' title='" . esc_attr( $topic_count_text_callback( $count ) ) . "' rel='emotion' style='font-size:" . ( $smallest + ( ( $count - $min_count ) * $font_step ) ) . "$unit'>$em_name</a>";
			}
		} else {
			$largest  = 100;
			$smallest = ceil( $smallest );
			
			$min_count = min( $counts );
			$spread    = max( $counts ) - $min_count;
			
			if ( $spread <= 0 )
				$spread = 1;
			
			$dim_spread = $largest - $smallest;
			
			if ( $dim_spread < 0 )
				$dim_spread = 1;
			
			$dim_step = $dim_spread / $spread;
			
			foreach ( $emotions as $k => $v ) {
				$count   = $counts[ $k ];
				$em_link = get_term_link( ( int ) $v->term_id, 'empathy_emotion' );
				$em_id   = isset( $emotions[ $k ]->term_id ) ? $emotions[ $k ]->term_id : $k;
				
				if ( !( $em_name = $this->the_empathy_object( $v, $empathy_theme, false, ( $smallest + ( ( $count - $min_count ) * $dim_step ) ) ) ) )
					$em_name = $emotions[ $k ]->name;
				
				$a[] = "<a href='$em_link' class='emotion-link-$em_id' title='" . esc_attr( $topic_count_text_callback( $count ) ) . "' rel='emotion'>$em_name</a>";
			}
		}
	
		switch ( $format ) {
			case 'array' :
				$return =& $a;
				break;
			case 'list' :
				$return = "<ul class='empathy-emotion-cloud'>\n\t<li>";
				$return .= join( "</li>\n\t<li>", $a );
				$return .= "</li>\n</ul>\n";
				break;
			default :
				$return = join( "\n", $a );
				break;
		}
		
		$output = ( $filter ) ? apply_filters( 'empathy_emotion_cloud', $return, $emotions, $r ) : $return;
		
		if ( $echo )
			echo $output;
		else
			return $output;
	}
	
	/**
	 * Displays a list of posts related to the current post.
	 * 
	 * @package Empathy
	 * @since 1
	 */
	function empathy_related_posts( $args = null ) {
		global $post;
		
		$defaults = array(
			'number' => 0, 'format' => 'list', 'order' => 'ASC',
			'title' => __( 'Related by %s', 'empathy' ), 'theme' => false,
			'filter' => 1, 'echo' => 1, 'display' => 'text'
		);
	
		$r = wp_parse_args( $args, $defaults );
		
		extract( $r );
		
		$emotions = $this->get_empathy();
		
		if ( !$emotions )
			return;
		
		$random = array_rand( $emotions );
		
		$posts = get_objects_in_term( $emotions[ $random ]->term_id, 'empathy_emotion', array( 'order' => $order ) );
		
		foreach ( $posts as $k => $v )
			if ( $post->ID == $v )
				unset( $posts[ $k ] );
		
		if ( !$posts )
			return;
		
		if ( $number > 0 )
			$posts = array_slice( $posts, 0, $number );
		
		if ( $title ) {
			$object = $this->the_empathy_object( $emotions[ $random ], $theme, false );
			$label  = ( $object && 'text' != $display ) ? $object : $emotions[ $random ]->name;
			$title  = str_replace( '%s', $label, $title );
		}
		
		$s = $emotions[ $random ]->slug;
		$a = array();
		
		foreach ( $posts as $v ) {
			$pt_name = get_the_title( $v );
			$pt_link = get_permalink( $v );
			$pt_id   = $v;
			
			$a[] = "<a href='$pt_link' class='empathy-post empathy-post-$pt_id related-emotion-$s' title=''>$pt_name</a>";
		}
	
		switch ( $format ) {
			case 'array' :
				$return =& $a;
				break;
			case 'list' :
				$return = $title . "<ul class='empathy-related-posts'>\n\t<li>";
				$return .= join( "</li>\n\t<li>", $a );
				$return .= "</li>\n</ul>\n";
				break;
			default :
				$return = $title . join( "\n", $a );
				break;
		}
		
		$output = ( $filter ) ? apply_filters( 'empathy_related_posts', $return, $emotions, $r ) : $return;
		
		if ( $echo )
			echo $output;
		else
			return $output;
	}
	
	/**
	 * Displays the emotion with the most posts.
	 * 
	 * @package Empathy
	 * @since 1
	 * 
	 * @param str $theme Theme slug. Optional.
	 * @param bool $display Display the emotion.
	 * @return Returns the empathy_emotion object if $display is false.
	 */
	function empathy_site_emotion( $theme = null, $display = true ) {
		if ( !( $emotions = get_terms( 'empathy_emotion', array( 'order' => 'DESC', 'orderby' => 'count', 'number' => 1 ) ) ) )
			return;
		
		$output = $this->the_empathy_object( array_shift( $emotions ), $theme, false );
		
		if ( $display )
			echo apply_filters( 'empathy_site_emotion', $output );
		else
			return apply_filters( 'empathy_site_emotion', $output );
	}
	
	
	
	//
	// Shortcodes
	//
	function short_empathy( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'sep'   => null,
			'theme' => null
		), $atts ) );
		
		global $post;
		
		return $this->the_empathy( $sep, $theme, false, false );
	}
	
	function short_empathy_cloud( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'order' => null,
			'theme' => null,
			'format' => null,
			'orderby' => null,
			'display' => null,
			'largest' => null,
			'smallest' => null
		), $atts ) );
		
		global $post;
		
		return $this->empathy_emotion_cloud( array( 'smallest' => $smallest, 'largest' => $largest, 'orderby' => $orderby, 'format' => $format, 'empathy_theme' => $theme, 'order' => $order, 'empathy_display' => $display, 'echo' => false ) );
	}
	
	function short_empathy_related( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'title' => __( 'Related by %s', 'empathy' ),
			'order' => null,
			'theme' => null,
			'number' => 5,
			'format' => null,
			'display' => null
		), $atts ) );
		
		global $post;
		
		return $this->empathy_related_posts( array( 'title' => $title, 'order' => $order, 'theme' => $theme, 'number' => $number, 'format' => $format, 'display' => $display, 'echo' => false ) );
	}
	
	
	
	//
	// Administrative Functions
	//
	function admin_themes() {
		if ( !is_admin() ) return;
		
		$this->domain();
		
		global $current_user, $paged, $post;
		
		$page    = $_REQUEST[ 'page' ];
		$subpage = $_REQUEST[ 'subpage' ];
		$subview = ( $subpage ) ? '&amp;subpage=' . $subpage : '';
		$themer  = ( $subpage ) ? '&amp;empathy_theme=' . $_REQUEST[ 'empathy_theme' ] : '';
		$view    = '?page=' . $page . $subview . $themer;
		
		if ( 'add_empathy_theme' == $_REQUEST[ 'action' ] ) {
			$themes = $this->option( 'themes' );
			$files  = $this->option( 'files' );
			$new    = trim( $_REQUEST[ 'empathy_theme_name' ] );
			
			if ( !$new )
				$errors[ 'no_name' ] = __( 'A theme name must be provided.', 'empathy' );
			elseif ( in_array( $new, $themes ) )
				$errors[ 'already_exists' ] = sprintf( __( 'A theme named &#8220;%s&#8221; already exists.', 'empathy' ), $new );
			else {
				$key = sanitize_title( $new );
				
				$files[ $key ]  = array();
				$themes[ $key ] = $new;
				
				natsort( $themes );
				
				$this->option( 'files', $files );
				$this->option( 'themes', $themes );
				
				if ( !is_dir( $this->get_empathy_directory( 'abs', $key ) ) )
					if ( !mkdir( $this->get_empathy_directory( 'abs', $key ), 0775, true ) ) 
						$errors[ 'mkdir' ] = sprintf( __( 'Empathy was not able to create some necessary directories. You will need to create a directory named %s in %s', 'empathy' ), $key, $this->get_empathy_directory( 'url' ) );
			}
		}
		
		if ( 'delete_empathy_theme' == $_REQUEST[ 'action' ] ) {
			check_admin_referer( 'delete_empathy_theme' );
			
			$themes = $this->option( 'themes' );
			
			if ( in_array( $_REQUEST[ 'empathy_theme' ], array_keys( $themes ) ) ) {
				$i     = array();
				$files = $this->option( 'files' );
				
				foreach ( $files[ $_REQUEST[ 'empathy_theme' ] ] as $k => $v )
					if ( !unlink( $this->get_empathy_directory( 'abs', $_REQUEST[ 'empathy_theme' ] ) . $k . $v ) )
						$i[] = '<a href="' . $this->get_empathy_directory( 'url', $_REQUEST[ 'empathy_theme' ] ) . $k . $v . '" target="_blank">' . $this->get_empathy_directory( 'url', $_REQUEST[ 'empathy_theme' ] ) . $k . $v . '</a>';
				
				if ( !rmdir( $this->get_empathy_directory( 'abs', $_REQUEST[ 'empathy_theme' ] ) ) )
					$errors[ 'bad_dir' ] = sprintf( __( 'The directory for <a href="%s" target="_blank">&#8220;%s&#8221;</a> could not be deleted.', 'empathy' ), $this->get_empathy_directory( 'url', $_REQUEST[ 'empathy_theme' ] ), $_REQUEST[ 'empathy_theme' ] );
				
				$updated = sprintf( __( '&#8220;%s&#8221; deleted.', 'empathy' ), $themes[ $_REQUEST[ 'empathy_theme' ] ] );
				
				unset( $files[ $_REQUEST[ 'empathy_theme' ] ], $themes[ $_REQUEST[ 'empathy_theme' ] ] );
				
				$this->option( 'files', $files );
				$this->option( 'themes', $themes );
				
				if ( $i )
					$errors[ 'no_delete' ] = sprintf( __ngettext( 'This file for &#8220;%s&#8221; could not be deleted: <ul><li>%s</li></ul>', 'These files for &#8220;%s&#8221; could not be deleted: <ul><li>%s</li></ul>', count( $i ), 'empathy' ), $themes[ $_REQUEST[ 'empathy_theme' ] ], implode( '</li><li>', $i ) );
			} else
				$errors[ 'not_a_theme' ] = __( 'The specified theme does not exist.', 'empathy' );
		}
		
		if ( 'activate_empathy_theme' == $_REQUEST[ 'action' ] ) {
			$current = $this->option( 'current' );
			
			if ( $_REQUEST[ 'empathy_theme' ] == $current )
				$errors[ 'active' ] = __( 'The selected theme is already active.', 'empathy' );
			else {
				$current = $_REQUEST[ 'empathy_theme' ];
				$this->option( 'current', $current );
			}
		}
		
		if ( 'delete_empathy_file' == $_REQUEST[ 'action' ] ) {
			check_admin_referer( 'delete_empathy_file' );
			
			$files = $this->option( 'files' );
					
			if ( in_array( $_REQUEST[ 'empathy_emotion' ], array_keys( $files[ $_REQUEST[ 'empathy_theme' ] ] ) ) ) {
				if ( unlink( $this->get_empathy_directory( 'abs', $_REQUEST[ 'empathy_theme' ] ) . $_REQUEST[ 'empathy_emotion' ] . $files[ $_REQUEST[ 'empathy_theme' ] ][ $_REQUEST[ 'empathy_emotion' ] ] ) ) {
					$updated = sprintf( __( '%s deleted.', 'empathy' ), $_REQUEST[ 'empathy_emotion' ] . $files[ $_REQUEST[ 'empathy_theme' ] ][ $_REQUEST[ 'empathy_emotion' ] ] );
					
					unset( $files[ $_REQUEST[ 'empathy_theme' ] ][ $_REQUEST[ 'empathy_emotion' ] ] );
					$this->option( 'files', $files );
				} else
					$errors[ 'no_delete' ] = sprintf( __( '%s could not be deleted.', 'empathy' ), $_REQUEST[ 'empathy_emotion' ] . $files[ $_REQUEST[ 'empathy_theme' ] ][ $_REQUEST[ 'empathy_emotion' ] ] );
			} else
				$errors[ 'not_a_file' ] = __( 'The specified file could not be found.', 'empathy' );
		}
		
		if ( 'bulk_empathy_theme' == $_REQUEST[ 'action' ] && ( $_REQUEST[ 'submit1' ] || $_REQUEST[ 'submit2' ] ) ) {
			if ( $action = ( $_REQUEST[ 'submit1' ] ) ? $_REQUEST[ 'action1' ] : $_REQUEST[ 'action2' ] ) {
				if ( !$_REQUEST[ 'bulk' ] )
					$errors[ 'no_files' ] = __( 'You must select at least one file.', 'empathy' );
				elseif ( 'delete' == $action ) {
					$i     = 0;
					$files = $this->option( 'files' );
					
					foreach ( $_REQUEST[ 'bulk' ] as $bulk ) {
						if ( in_array( $bulk, array_keys( $files[ $_REQUEST[ 'empathy_theme' ] ] ) ) ) {
							if ( unlink( $this->get_empathy_directory( 'abs', $_REQUEST[ 'empathy_theme' ] ) . $bulk . $files[ $_REQUEST[ 'empathy_theme' ] ][ $bulk ] ) ) {
								unset( $files[ $_REQUEST[ 'empathy_theme' ] ][ $bulk ] );
								$i++;
							} else
								$errors[ $bulk ] = sprintf( __( '%s could not be deleted.', 'empathy' ), $bulk . $files[ $_REQUEST[ 'empathy_theme' ] ][ $bulk ] );
						}
					}
					
					$this->option( 'files', $files );
					
					if ( $i )
						$updated = sprintf( __ngettext( '%d file deleted.', '%d files deleted.', $i, 'empathy' ), $i );
					else
						$errors[ 'no_emotion' ] = __( 'None of the selected files could be deleted.', 'empathy' );
				} else
					$errors[ 'unknown_action' ] = __( 'Unknown action, please try again.', 'empathy' );
			} else
				$errors[ 'no_action' ] = __( 'You must select an action.', 'empathy' );
		} elseif ( 'bulk_empathy_theme' == $_REQUEST[ 'action' ] ) {
			$uploads = array();
			
			foreach ( $_FILES as $f )
				foreach ( $f as $att => $arr )
					foreach ( $arr as $k => $v )
						$uploads[ $k ][ $att ] = $v;
			
			$i         = 0;
			$files     = $this->option( 'files' );
			$file_path = $this->get_empathy_directory( 'abs', $_REQUEST[ 'empathy_theme' ] );
			
			foreach ( $uploads as $k => $v ) {
				if ( $v[ 'name' ] && !$v[ 'error' ] ) {
					$file     = pathinfo( $v[ 'name' ] );
					$invalid = ( in_array( $file[ 'extension' ], array( 'gif', 'jpg', 'jpeg', 'png', 'swf' ) ) ) ? false : true;
					
					if ( !$invalid ) {
						$target_path = $file_path . $k . '.' . $file[ 'extension' ];
						
						if ( move_uploaded_file( $v[ 'tmp_name' ], $target_path ) ) {
							if ( strpos( PHP_OS, 'WIN' ) )
								chmod( $target_path, 0777 );
							else
								chmod( $target_path, 0664 );
							
							$files[ $_REQUEST[ 'empathy_theme' ] ][ $k ] = '.' . $file[ 'extension' ];
							
							$i++;
						} else
							$errors[ 'upload_error' ] = __( 'There was a problem uploading the file, please try again.', 'empathy' );
					} else
						$errors[ 'bad_extension' ] = __( 'Invalid file format. Empathy files must be gif, jpg, jpeg, png, or swf.', 'empathy' );
				} elseif ( $v[ 'name' ] ) {
					switch ( $v[ 'error' ] ) {
						case 1:
						case 2: $errors[ $v[ 'name' ] ]  = sprintf( __( '%s is too large to upload.', 'empathy' ), basename( $v[ 'name' ] ) );	break;
						case 3: $errors[ $v[ 'name' ] ]  = sprintf( __( '%s was only partially uploaded.', 'empathy' ), basename( $v[ 'name' ] ) );	break;
						case 6: $errors[ 'no_temp_dir' ] = __( 'Your servers temporary directory could not be found.', 'empathy' ); break;
						case 7: $errors[ $v[ 'name' ] ]  = sprintf( __( '%s could not be saved properly after upload.', 'empathy' ), basename( $v[ 'name' ] ) ); break;
						case 8: $errors[ 'upload_halt' ] = __( 'The upload was halted by a PHP extensions. Some or all files may not have been uploaded.', 'empathy' ); break;
					}
				}
				
				if ( $i )
					$updated = sprintf( __ngettext( '%d file uploaded.', '%d files uploaded.', $i, 'empathy' ), $i ) . ' ' . __( 'You may need to <a href="' . $view . '">refresh this page</a> to see the changes.', 'empathy' );
				
				$this->option( 'files', $files );
			}
		}
		
		if ( $updated ) {
			?>
			<div id="message" class="updated fade">
				<p><strong><?php echo $updated; ?></strong></p>
			</div>
			<?php
		}
		
		if ( $errors ) {
			?>
			<div id="message" class="error">
				<p><?php echo implode( '</p><p>', $errors ); ?></p>
			</div>
			<?php
		}
		
		if ( 'edit_empathy_theme' == $_REQUEST[ 'subpage' ] ) {
			$themes   = $this->option( 'themes' );
			$emotions = get_terms( 'empathy_emotion', 'hide_empty=0' );
		?>
		<div class="wrap">
			<div id="icon-empathy" class="icon32"><img src="<?php echo $this->url . 'icon.png'; ?>" alt="icon" /></div>
			<h2><?php _e( 'Edit Theme', 'empathy' ); ?> - <?php echo $themes[ $_REQUEST[ 'empathy_theme' ] ]; ?></h2>
			<p><a href="<?php echo '?page=' . $page; ?>">&laquo; Back to Themes</a></p>
			<form action="" method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'bulk_empathy_theme' ); ?>
				<div class="tablenav">
					<div class="tablenav-pages"><input type="submit" name="submit-empathy-upload1" value="<?php _e( 'Upload', 'empathy' ); ?>" class="button-primary"></div>
					<div class="alignleft actions">
						<select name="action1">
							<option value=""><?php _e( 'Bulk Actions', 'empathy' ); ?></option>
							<option value="delete"><?php _e( 'Delete', 'empathy' ); ?></option>
						</select>
						<input type="submit" value="<?php _e( 'Apply', 'empathy' ); ?>" name="submit1" class="button-secondary action" />
					</div>
				</div>
				<table class="widefat">
					<thead>
						<tr>
							<th scope="col" class="manage-column column-cb check-column"><input type="checkbox"></th>
							<th scope="col" class="manage-column column-file"></th>
							<th scope="col" class="manage-column column-name"><?php _e( 'Emotion', 'empathy' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th scope="col" class="manage-column column-cb check-column"><input type="checkbox"></th>
							<th scope="col" class="manage-column column-file"></th>
							<th scope="col" class="manage-column column-name"><?php _e( 'Emotion', 'empathy' ); ?></th>
						</tr>
					</tfoot>
					<tbody>
						<?php
							$emotions = $this->option( 'defaults' );
							$walker = new empathy_Walker_AdminEmotionsEdit();
							echo $walker->walk( get_terms( 'empathy_emotion', 'hide_empty=0' ), 0, array( 'view' => $view, 'theme' => $_REQUEST[ 'empathy_theme' ] ) );
						?>
					</tbody>
				</table>
				<div class="tablenav">
					<div class="tablenav-pages"><input type="submit" name="submit-empathy-upload1" value="<?php _e( 'Upload', 'empathy' ); ?>" class="button-primary"></div>
					<div class="alignleft actions">
						<select name="action2">
							<option value=""><?php _e( 'Bulk Actions', 'empathy' ); ?></option>
							<option value="delete"><?php _e( 'Delete', 'empathy' ); ?></option>
						</select>
						<input type="submit" value="<?php _e( 'Apply', 'empathy' ); ?>" name="submit2" class="button-secondary action" />
					</div>
				</div>
				<input type="hidden" name="MAX_FILE_SIZE" value="20000000">
				<input type="hidden" name="action" value="bulk_empathy_theme">
			</form>
		</div>
		<?php
		} else { $themes = $this->option( 'themes' );
		?>
		<div class="wrap">
			<div id="icon-empathy" class="icon32"><img src="<?php echo $this->url . 'icon.png'; ?>" alt="icon" /></div>
			<h2><?php _e( 'Empathy Themes', 'empathy' ); ?></h2>
			<form action="" method="post">
				<?php wp_nonce_field( 'add_empathy_theme' ); ?>
				<p>
					<input type="text" name="empathy_theme_name">
					<input type="submit" name="submit-upload" class="button-secondary" value="<?php _e( 'Create Theme', 'empathy' ); ?>">
					<input type="hidden" name="action" value="add_empathy_theme">
				</p>
			</form>
			<style>.available-theme img{width:auto}</style>
			<table id="availablethemes" cellspacing="0" cellpadding="0">
			<?php
				$rows       = ceil (count( $themes ) / 3 );
				$table      = array();
				$emotions   = get_terms( 'empathy_emotion', 'hide_empty=0' );
				$theme_keys = array_keys( $themes );
				
				for ( $row = 1; $row <= $rows; $row++ )
					for ( $col = 1; $col <= 3; $col++ )
						$table[ $row ][ $col ] = array_shift( $theme_keys );
			
				foreach ( $table as $row => $cols ) {
				?>
				<tr>
				<?php
				foreach ( $cols as $col => $theme_index ) {
					$class = array( 'available-theme' );
					if ( $row == 1 ) $class[]     = 'top';
					if ( $col == 1 ) $class[]     = 'left';
					if ( $row == $rows ) $class[] = 'bottom';
					if ( $col == 3 ) $class[]     = 'right';
					$style = ( $theme_index == $this->option( 'current' ) ) ? ' style="background:#fffeeb"' : '';
					?>
					<td class="<?php echo join( ' ', $class ); ?>"<?php echo $style; ?>>
					<?php if ( isset( $themes[ $theme_index ] ) ) { ?>
						<a href="<?php echo $view . '&amp;subpage=edit_empathy_theme&amp;empathy_theme=' . $theme_index; ?>" title="<?php printf( __( 'Edit &#8220;%s&#8221;', 'empathy' ), $themes[ $theme_index ] ); ?>">
						<?php
							$random = array_rand( $emotions );
							$this->the_empathy_object( $emotions[ $random ], $theme_index );
						?>
						</a>
						<h3><?php echo $themes[ $theme_index ]; ?></h3>
						<span class="action-links">
							<?php if ( $theme_index != $this->option( 'current' ) ) { ?><a href="<?php echo $view . '&amp;action=activate_empathy_theme&amp;empathy_theme=' . $theme_index ?>" class="activatelink" title="<?php printf( __( 'Activate &#8220;%s&#8221;', 'empathy' ), $themes[ $theme_index ] ); ?>"><?php _e( 'Activate', 'empathy' ); ?></a> | <?php } ?>
							<a href="<?php echo $view . '&amp;subpage=edit_empathy_theme&amp;empathy_theme=' . $theme_index; ?>" title="<?php printf( __( 'Edit &#8220;%s&#8221;', 'empathy' ), $themes[ $theme_index ] ); ?>"><?php _e( 'Edit', 'empathy' ); ?></a>
							<?php if ( $theme_index != $this->option( 'current' ) ) { ?> | <a class="submitdelete deletion" href="<?php echo wp_nonce_url( $view . '&amp;action=delete_empathy_theme&amp;empathy_theme=' . $theme_index, 'delete_empathy_theme' ); ?>" title="<?php printf( __( 'Delete &#8220;%s&#8221;', 'empathy' ), $themes[ $theme_index ] ); ?>" onclick="if (confirm(' <?php echo js_escape( sprintf( __( "You are about to delete '%s' and any files associated with it.\n 'Cancel' to stop, 'OK' to delete.", "empathy" ), $themes[ $theme_index ] ) ); ?> ')){return true;}return false;"><?php _e( 'Delete', 'empathy' ); ?></a><?php } ?>
						</span>
					<?php } ?>
					</td>
					<?php } ?>
				</tr>
				<?php } ?>
			</table>
		</div>
		<?php
		}
	}
	
	function admin_emotions() {
		if ( !is_admin() ) return;
		
		$this->domain();
		
		global $current_user;
		
		$page     = $_REQUEST[ 'page' ];
		$pagenum  = ( $_REQUEST[ 'pagenum' ] ) ? $_REQUEST[ 'pagenum' ] : 1;
		$hidden   = get_hidden_columns( 'empathy_page_empathy-emotions' );
		$view     = '?page=' . $page . '&amp;pagenum=' . $pagenum;
		
		if ( !get_usermeta( $current_user->ID, 'empathy_emotions_per_page' ) )
			update_usermeta( $current_user->ID, 'empathy_emotions_per_page', 20 );
		if ( isset( $_REQUEST[ 'empathy_emotions_per_page' ] ) )
			update_usermeta( $current_user->ID, 'empathy_emotions_per_page', $_REQUEST[ 'empathy_emotions_per_page' ] );
		
		$epp = get_usermeta( $current_user->ID, 'empathy_emotions_per_page' );
		
		if ( 'add_empathy_emotion' == $_REQUEST[ 'action' ] ) {
			check_admin_referer( 'add_empathy_emotion' );
			
			$new_name        = trim( $_REQUEST[ 'empathy_emotion_name' ] );
			$new_nicename    = ( $_REQUEST[ 'empathy_emotion_nicename' ] ) ? sanitize_title( $_REQUEST[ 'empathy_emotion_nicename' ] ) : sanitize_title( $_REQUEST[ 'empathy_emotion_name' ] );
			$new_parent      = $_REQUEST[ 'empathy_emotion_parent' ];
			$new_description = $_REQUEST[ 'empathy_emotion_description' ];
			
			if ( is_term( $new_nicename, 'empathy_emotion' ) )
				$exists = true;
			
			if ( !$new_name )
				$error_class = ' form-invalid';
			elseif ( $exists )
				$errors[ 'emotion_exists' ] = __( 'The emotion you are trying to create already exists.', 'empathy' );
			else {
				$new_emotion = wp_insert_term( $new_name, 'empathy_emotion', array( 'description' => $new_description, 'parent' => $new_parent, 'slug' => $new_nicename ) );
				
				if ( is_wp_error( $new_emotion ) )
					$errors = $new_emotion->get_error_messages();
				else {
					$updated = sprintf( __( 'Added new emotion &#8220;%s&#8221;', 'empathy' ), $new_name );
				}
			}
		}
		
		if ( 'update_empathy_emotion' == $_REQUEST[ 'action' ] ) {
			check_admin_referer( 'update_empathy_emotion' );
			
			$emotion = get_term( $_REQUEST[ 'empathy_emotion' ], 'empathy_emotion' );
			
			$update_name        = trim( $_REQUEST[ 'empathy_emotion_name' ] );
			$update_nicename    = ( $_REQUEST[ 'empathy_emotion_nicename' ] ) ? sanitize_title( $_REQUEST[ 'empathy_emotion_nicename' ] ) : sanitize_title( $_REQUEST[ 'empathy_emotion_name' ] );
			$update_parent      = ( $_REQUEST[ 'empathy_emotion_parent' ] == $emotion->term_id ) ? 'self' : $_REQUEST[ 'empathy_emotion_parent' ];
			$update_description = $_REQUEST[ 'empathy_emotion_description' ];
			
			if ( !$update_name )
				$errors[ 'no_name' ] = sprintf( __( '&#8220;%s&#8221; could not be updated because no name was provided.', 'empathy' ), $emotion->name );
			elseif ( 'self' == $update_parent )
				$errors[ 'self_parent' ] = sprintf( __( '&#8220;%s&#8221; could not be updated because emotions cannot be set as their own parent.', 'empathy' ), $emotion->name );
			else {
				$update_emotion = wp_update_term( $_REQUEST[ 'empathy_emotion' ], 'empathy_emotion', array( 'name' => $update_name, 'slug' => $update_nicename, 'parent' => $update_parent, 'description' => $update_description ) );
				
				if ( $emotion->slug != $update_nicename ) {
					$i     = array();
					$files = $this->option( 'files' );
					
					foreach ( $files as $k => $v ) {
						if ( in_array( $emotion->slug, array_keys( $v ) ) ) {
							$files[ $k ][ $update_nicename ] = $files[ $k ][ $emotion->slug ];
							
							if ( !rename( $this->get_empathy_directory( 'abs', $k ) . $emotion->slug . $v[ $emotion->slug ], $this->get_empathy_directory( 'abs', $k ) . $update_nicename . $v[ $emotion->slug ] ) )
								$i[] = '<a href="' . $this->get_empathy_directory( 'url', $k ) . $emotion->slug . $v[ $emotion->slug ] . '" target="_blank">' . $this->get_empathy_directory( 'url', $k ) . $emotion->slug . $v[ $emotion->slug ] . '</a>';
							
							unset( $files[ $k ][ $emotion->slug ] );
						}
					}
					
					$this->option( 'files', $files );
					
					if ( $i )
						$errors[ 'no_rename' ] = sprintf( __ngettext( 'This file previously associated with &#8220;%s&#8221; could not be renamed: <ul><li>%s</li></ul>', 'These files previously associated with &#8220;%s&#8221; could not be renamed: <ul><li>%s</li></ul>', count( $i ), 'empathy' ), $update_name, implode( '</li><li>', $i ) );
				}
				
				if ( is_wp_error( $update_emotion ) )
					$errors = $update_emotion->get_error_messages();
				else
					$updated = sprintf( __( '&#8220;%s&#8221; updated.', 'empathy' ), $update_name );
			}
		}
		
		if ( 'delete_empathy_emotion' == $_REQUEST[ 'action' ] ) {
			check_admin_referer( 'delete_empathy_emotion' );
			
			if ( $old_emotion = get_term( $_REQUEST[ 'empathy_emotion' ], 'empathy_emotion' ) ) {
				$emotions = $this->option( 'defaults' );
				
				if ( in_array( $old_emotion->slug, $emotions ) ) {
					unset( $emotions[ $emotion->term_id ] );
					$this->option( 'defaults', $emotions );
				}
				
				$i     = array();
				$files = $this->option( 'files' );
				
				foreach ( $files as $k => $v ) {
					if ( in_array( $old_emotion->slug, array_keys( $v ) ) ) {
						if ( !unlink( $this->get_empathy_directory( 'abs', $k ) . $old_emotion->slug . $v[ $old_emotion->slug ] ) )
							$i[] = '<a href="' . $this->get_empathy_directory( 'url', $k ) . $old_emotion->slug . $v[ $old_emotion->slug ] . '" target="_blank">' . $this->get_empathy_directory( 'url', $k ) . $old_emotion->slug . $v[ $old_emotion->slug ] . '</a>';
						
						unset( $files[ $k ][ $old_emotion->slug ] );
					}
				}
				
				$this->option( 'files', $files );
				
				if ( $i )
					$errors[ 'no_rename' ] = sprintf( __ngettext( 'This file previously associated with &#8220;%s&#8221; could not be deleted: <ul><li>%s</li></ul>', 'These files previously associated with &#8220;%s&#8221; could not be deleted: %s', count( $i ), 'empathy' ), $update_name, implode( '</li><li>', $i ) );
				
				wp_delete_term( $_REQUEST[ 'empathy_emotion' ], 'empathy_emotion' );
				
				$updated = sprintf( __( 'Deleted &#8220;%s&#8221;', 'empathy' ), $old_emotion->name );
			} else
				$errors[ 'not_an_emotion' ] = __( 'The specified emotion does not exist.', 'empathy' );
		}
		
		if ( 'add_default_empathy_emotion' == $_REQUEST[ 'action' ] ) {
			$emotion  = get_term( $_REQUEST[ 'empathy_emotion' ], 'empathy_emotion' );
			$emotions = $this->option( 'defaults' );
			
			if ( in_array( $emotion->slug, $emotions ) )
				$errors[ 'already_default' ] = sprintf( __( '&#8220;%s&#8221; is already a default emotion.', 'empathy' ), $emotion->name );
			else {
				$emotions[ $emotion->term_id ] = $emotion->slug;
				$this->option( 'defaults', $emotions );
			}
		}
		
		if ( 'remove_default_empathy_emotion' == $_REQUEST[ 'action' ] ) {
			$emotion  = get_term( $_REQUEST[ 'empathy_emotion' ], 'empathy_emotion' );
			$emotions = $this->option( 'defaults' );
			
			if ( !in_array( $emotion->slug, $emotions ) )
				$errors[ 'not_default' ] = sprintf( __( '&#8220;%s&#8221; is not a default emotion.', 'empathy' ), $emotion->name );
			else {
				unset( $emotions[ $emotion->term_id ] );
				$this->option( 'defaults', $emotions );
			}
		}
		
		if ( 'bulk_empathy_emotion' == $_REQUEST[ 'action' ] ) {
			if ( $action = ( $_REQUEST[ 'submit-1' ] ) ? $_REQUEST[ 'action-1' ] : $_REQUEST[ 'action-2' ] ) {
				if ( !$_REQUEST[ 'bulk' ] )
					$errors[ 'no_emotions' ] = __( 'You must select at least one emotion.', 'empathy' );
				elseif ( 'add_defaults' == $action ) {
					$emotions = $this->option( 'defaults' );
					
					if ( $new_defaults = array_diff( $_REQUEST[ 'bulk' ], array_keys( $emotions ) ) ) {
						foreach ( $new_defaults as $new_default ) {
							$new = get_term( $new_default, 'empathy_emotion' );
							$emotions[ $new->term_id ] = $new->slug;
						}
						
						$this->option( 'defaults', $emotions );
					} else
						$errors[ 'no_new_defaults' ] = __( 'All of the selected emotions are already default collections.', 'empathy' );
						
				} elseif ( 'remove_defaults' == $action ) {
					$emotions = $this->option( 'defaults' );
					
					if ( $old_defaults = array_intersect( $_REQUEST[ 'bulk' ], array_keys( $emotions ) ) ) {
						foreach ( $old_defaults as $old_default )
							unset( $emotions[ $old_default ] );
						
						$this->option( 'defaults', $emotions );
					} else
						$errors[ 'no_new_defaults' ] = __( 'None of the selected emotions are default emotions.', 'empathy' );
				} elseif ( 'delete' == $action ) {
					$emotions = $this->option( 'defaults' );
					
					$i     = 0;
					$files = $this->option( 'files' );
					
					foreach ( $_REQUEST[ 'bulk' ] as $bulk ) {
						if ( $old_emotion = get_term( $bulk, 'empathy_emotion' ) ) {
							$emotions = $this->option( 'defaults' );
							
							if ( in_array( $old_emotion->slug, $emotions ) ) {
								unset( $emotions[ $emotion->term_id ] );
								$this->option( 'defaults', $emotions );
							}
							
							foreach ( $files as $k => $v ) {
								if ( in_array( $old_emotion->slug, array_keys( $v ) ) ) {
									unlink( $this->get_empathy_directory( 'abs', $k ) . $old_emotion->slug . $v[ $old_emotion->slug ] );
									unset( $files[ $k ][ $old_emotion->slug ] );
								}
							}
							
							wp_delete_term( $bulk, 'empathy_emotion' );
							
							$i++;
						}	
					}
					
					$this->option( 'files', $files );
					
					if ( $i )
						$updated = sprintf( __ngettext( '%d emotion deleted.', '%d emotions deleted.', $i, 'empathy' ), $i );
					else
						$errors[ 'no_emotion' ] = __( 'None of the selected emotions could be deleted.', 'empathy' );
				} else
					$errors[ 'unknown_action' ] = __( 'Unknown action, please try again.', 'empathy' );
			} else
				$errors[ 'no_action' ] = __( 'You must select an action.', 'empathy' );
		}
		
		if ( $updated ) {
			?>
			<div id="message" class="updated fade">
				<p><strong><?php echo $updated; ?></strong></p>
			</div>
			<?php
		}
		
		if ( $errors ) {
			?>
			<div id="message" class="error">
				<p><?php echo implode( '</p><p>', $errors ); ?></p>
			</div>
			<?php
		}
		
		if ( 'edit_empathy_emotion' == $_REQUEST[ 'subpage' ] ) {
			$emotion = get_term_to_edit( $_REQUEST[ 'empathy_emotion' ], 'empathy_emotion' );
		?>
		<style type="text/css">#screen-options-link-wrap{visibility:hidden}</style>
		<div class="wrap">
			<div id="icon-empathy" class="icon32"><img src="<?php echo $this->url . 'icon.png'; ?>" alt="icon" /></div>
			<h2><?php _e( 'Edit Emotion', 'empathy' ); ?></h2>
			<form action="" method="post">
				<?php wp_nonce_field( 'update_empathy_emotion' ); ?>
				<table class="form-table">
					<tr class="form-field">
						<th scope="row"><label for="empathy_emotion_name"><?php _e( 'Name', 'empathy' ); ?></label></th>
						<td><input name="empathy_emotion_name" id="empathy_emotion_name" type="text" value="<?php echo $emotion->name; ?>"><br>
						<?php _e( 'The name is used to identify the emotion almost everywhere.', 'empathy' ); ?></td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label for="empathy_emotion_nicename"><?php _e( 'Slug', 'empathy' ); ?></label></th>
						<td><input name="empathy_emotion_nicename" id="empathy_emotion_nicename" type="text" value="<?php echo $emotion->slug; ?>"><br>
						<?php _e('The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'empathy' ); ?></td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label for="empathy_emotion_parent"><?php _e( 'Parent', 'empathy' ); ?></label></th>
						<td>
							<select name="empathy_emotion_parent" id="empathy_emotion_parent">
								<option value="0"><?php _e( 'None', 'empathy' ); ?></option>
								<?php
									$walker = new empathy_Walker_AdminEmotionsParent();
									echo $walker->walk( get_terms( 'empathy_emotion', 'hide_empty=0' ), 0, array( 'parent' => $emotion->parent ) );
								?>
							</select><br>
							<?php _e( 'Emotions are hierarchical, so one emotion may contain any number of sub-emotions.', 'empathy' ); ?>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label for="empathy_emotion_description"><?php _e( 'Description', 'empathy' ); ?></label></th>
						<td>
							<textarea name="empathy_emotion_description" id="empathy_emotion_description" rows="5" cols="40"><?php echo $emotion->description; ?></textarea>
							<br /><?php _e( 'The description is useful for providing a brief explanation of the emotion.', 'empathy' ); ?>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" name="submit" value="<?php _e( 'Update Emotion', 'empathy' ); ?>" class="button-primary">
					<input type="hidden" name="action" value="update_empathy_emotion">
					<input type="hidden" name="subpage" value="">
				</p> 
			</form>
		</div>
		<?php
		} else { $num_ems = wp_count_terms( 'empathy_emotion' );
		?>
		<div class="wrap">
			<div id="icon-empathy" class="icon32"><img src="<?php echo $this->url . 'icon.png'; ?>" alt="icon" /></div>
			<h2><?php _e( 'Empathy Emotions', 'empathy' ); ?></h2>
			<div id="col-container">
				<div id="col-right">
					<div class="col-wrap">
						<form action="" method="post">
							<?php wp_nonce_field( 'bulk_empathy_emotions' ); ?>
							<div class="tablenav">
								<div class="tablenav-pages"><?php echo paginate_links( array( 'base' => add_query_arg( 'pagenum', '%#%' ), 'format' => '', 'prev_text' => __( '&laquo;', 'empathy' ), 'next_text' => __( '&raquo;', 'empathy' ), 'total' => ceil ( $num_ems / $epp ), 'current' => $pagenum ) ); ?></div>
								<div class="alignleft actions">
									<select name="action-1">
										<option value=""><?php _e( 'Bulk Actions', 'empathy' ); ?></option>
										<option value="delete"><?php _e( 'Delete', 'empathy' ); ?></option>
										<optgroup label="<?php _e( 'Defaults', 'empathy' ); ?>">
											<option value="add_defaults"><?php _e( 'Add', 'empathy' ); ?></option>
											<option value="remove_defaults"><?php _e( 'Remove', 'empathy' ); ?></option>
										</optgroup>
									</select>
									<input type="submit" value="<?php _e( 'Apply', 'empathy' ); ?>" name="submit-1" class="button-secondary action" />
								</div>
							</div>
							<table class="widefat">
								<thead>
									<tr>
										<th scope="col" class="manage-column column-cb check-column"><input type="checkbox"></th>
										<th scope="col" class="manage-column column-name"><?php _e( 'Name', 'empathy' ); ?></th>
										<?php print_column_headers( 'empathy_page_' . $page ); ?>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<th scope="col" class="manage-column column-cb check-column"><input type="checkbox"></th>
										<th scope="col" class="manage-column column-name"><?php _e( 'Name', 'empathy' ); ?></th>
										<?php print_column_headers( 'empathy_page_' . $page ); ?>
									</tr>
								</tfoot>
								<tbody>
									<?php
										$emotions = $this->option( 'defaults' );
										$walker = new empathy_Walker_AdminEmotionsList();
										echo $walker->paged_walk( get_terms( 'empathy_emotion', 'hide_empty=0' ), 0, $pagenum, $epp, array( 'hidden' => $hidden, 'view' => $view, 'defaults' => $emotions ) );
									?>
								</tbody>
							</table>
							<div class="tablenav">
								<div class="tablenav-pages"><?php echo paginate_links( array( 'base' => add_query_arg( 'pagenum', '%#%' ), 'format' => '', 'prev_text' => __( '&laquo;', 'empathy' ), 'next_text' => __( '&raquo;', 'empathy' ), 'total' => ceil ( $num_ems / $epp ), 'current' => $pagenum ) ); ?></div>
								<div class="alignleft actions">
									<select name="action-2">
										<option value=""><?php _e( 'Bulk Actions', 'empathy' ); ?></option>
										<option value="delete"><?php _e( 'Delete', 'empathy' ); ?></option>
										<optgroup label="<?php _e( 'Defaults', 'empathy' ); ?>">
											<option value="add_defaults"><?php _e( 'Add', 'empathy' ); ?></option>
											<option value="remove_defaults"><?php _e( 'Remove', 'empathy' ); ?></option>
										</optgroup>
									</select>
									<input type="submit" value="<?php _e( 'Apply', 'empathy' ); ?>" name="submit-2" class="button-secondary action">
									<input type="hidden" name="category" value="<?php echo $category->term_id; ?>">
									<input type="hidden" name="action" value="bulk_empathy_emotion">
								</div>
							</div>
						</form>
					</div>
				</div>
				<div id="col-left">
					<div class="col-wrap">
						<div class="form-wrap">  
							<h3><?php _e( 'Add Emotion', 'empathy' ); ?></h3>
							<form action="" method="post">
								<?php wp_nonce_field( 'add_empathy_emotion' ); ?>
								<div class="form-field form-required<?php echo $error_class; ?>">
									<label for="empathy_emotion_name"><?php _e( 'Name', 'empathy' ); ?></label>
									<input type="text" name="empathy_emotion_name" id="empathy_emotion_name" size="40" value="<?php if ( $error_class || $errors[ 'emotion_exists' ] ) echo $_REQUEST[ 'empathy_emotion_name' ]; ?>">
									<p><?php _e( 'The name is used to identify the emotion almost everywhere.', 'empathy' ); ?></p>
								</div>
								<div class="form-field">
									<label for="empathy_emotion_nicename"><?php _e( 'Slug', 'empathy' ); ?></label>
									<input type="text" name="empathy_emotion_nicename" id="empathy_emotion_nicename" size="40" value="<?php if ( $error_class || $errors[ 'emotion_exists' ] ) echo $_REQUEST[ 'empathy_emotion_nicename' ]; ?>">
									<p><?php _e( 'The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'empathy' ); ?></p>
								</div>
								<div class="form-field">
									<label for="empathy_emotion_parent"><?php _e( 'Parent', 'empathy' ); ?></label>
									<select name="empathy_emotion_parent" id="empathy_emotion_parent">
										<option value="0"><?php _e( 'None', 'empathy' ); ?></option>
										<?php
											$parent = ( $error_class || $errors[ 'emotion_exists' ] ) ? $_REQUEST[ 'empathy_emotion_parent' ] : '';
											$walker = new empathy_Walker_AdminEmotionsParent();
											echo $walker->walk( get_terms( 'empathy_emotion', 'hide_empty=0' ), 0, array( 'parent' => $parent ) );
										?>
									</select>
									<p><?php _e( 'Emotions are hierarchical, so one emotion may contain any number of sub-emotions.', 'empathy' ); ?></p>
								</div>
								<div class="form-field">
									<label for="empathy_emotion_description"><?php _e( 'Description', 'empathy' ); ?></label>
									<textarea name="empathy_emotion_description" id="empathy_emotion_description" rows="5" cols="40"><?php if ( $error_class || $errors[ 'emotion_exists' ] ) echo $_REQUEST[ 'empathy_emotion_description' ]; ?></textarea>
									<p><?php _e( 'The description is useful for providing a brief explanation of the emotion.', 'empathy' ); ?></p>
								</div> 
								<p class="submit">
									<input type="submit" class="button" name="submit" value="<?php _e( 'Add Emotion', 'empathy' ); ?>">
									<input type="hidden" name="action" value="add_empathy_emotion">
								</p> 
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script type="text/javascript">var x = '<form action="" method="get"><input type="hidden" name="page" value="<?php echo $page; ?>"><h5><?php _e( 'Options', 'empathy' ); ?></h5><div class="screen-options"><label><?php _e( 'Emotions per page:', 'empathy' ); ?> <input type="text" name="empathy_emotions_per_page" value="<?php echo $epp; ?>" maxlength="3" class="screen-per-page"></label> <input type="submit" value="<?php _e( 'Apply', 'empathy' ); ?>" class="button-secondary action"></div></form>';	jQuery( '#screen-options-wrap' ) . append( x );</script>
		<?php
		}
	}
	
	function admin_tools() {
		if ( !is_admin() ) return;
		
		$this->domain();
		
		$page    = $_REQUEST[ 'page' ];
		$subpage = $_REQUEST[ 'subpage' ];
		$subview = ( $subpage ) ? '&amp;subpage=' . $subpage : '';
		$view    = '?page=' . $page . $subview;
		
		if ( 'change_emotions' == $_REQUEST[ 'action' ] ) {
			check_admin_referer( 'change_emotions' );
			
			
			if ( !$_REQUEST[ 'bulk' ] )
				$errors[ 'no_posts' ] = __( 'You must select at least one post.', 'empathy' );
			else {
				if ( $_REQUEST[ 'empathy_emotions' ] )
					foreach ( $_REQUEST[ 'bulk' ] as $id )
						wp_set_object_terms( $id, $_REQUEST[ 'empathy_emotions' ], 'empathy_emotion' );
				else
					foreach ( $_REQUEST[ 'bulk' ] as $id )
						wp_delete_object_term_relationships( $id, 'empathy_emotion' );
				
				$updated = __( 'Emotions changed.', 'empathy' );
			}
		}
		
		if ( 'uninstall_empathy' == $_REQUEST[ 'action' ] ) {
			$this->uninstall();
			$updated = sprintf( __( 'Empathy information, files, and settings have been removed. Please <a href="%s">deactivate the plugin</a> to complete the uninstallation.', 'empathy' ), 'plugins.php' );
		}
		
		if ( $updated ) {
			?>
			<div id="message" class="updated fade">
				<p><strong><?php echo $updated; ?></strong></p>
			</div>
			<?php
		}
		
		if ( $errors ) {
			?>
			<div id="message" class="error">
				<p><?php echo implode( '</p><p>', $errors ); ?></p>
			</div>
			<?php
		}
		
		if ( 'batch_emotions' == $_REQUEST[ 'subpage' ] ) {
		?>
		<div class="wrap">
			<div id="icon-empathy" class="icon32"><img src="<?php echo $this->url . 'icon.png'; ?>" alt="icon" /></div>
			<h2><?php _e( 'Batch Emotions', 'empathy' ); ?></h2>
			<form action="" method="post">
			<?php wp_nonce_field( 'change_emotions' ); ?>
			<div id="col-container">
				<div id="col-right">
					<div class="col-wrap">
						<table class="widefat">
							<thead>
								<tr>
									<th scope="col" class="manage-column column-cb check-column"><input type="checkbox"></th>
									<th scope="col" class="manage-column column-name"><?php _e( 'Post', 'empathy' ); ?></th>
									<th scope="col" class="manage-column column-emotions"><?php _e( 'Emotions', 'empathy' ); ?></th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<th scope="col" class="manage-column column-cb check-column"><input type="checkbox"></th>
									<th scope="col" class="manage-column column-name"><?php _e( 'Post', 'empathy' ); ?></th>
									<th scope="col" class="manage-column column-emotions"><?php _e( 'Emotions', 'empathy' ); ?></th>
								</tr>
							</tfoot>
							<tbody>
								<?php
									$posts = get_posts( 'numberposts=-1' );
									
									foreach ( $posts as $_post ) {
								?>
								<tr>
									<th scope="row" class="check-column"><input type="checkbox" name="bulk[]" value="<?php echo $_post->ID; ?>"></th>
									<td class="name column-name"><a href="<?php echo get_permalink( $_post->ID ); ?>" title="<?php _e( 'View this post', 'empathy' ); ?>" target="_blank" class="row-title"><?php echo get_the_title( $_post->ID ); ?></a></td>
									<td>
									<?php
										if ( $emotions = $this->get_empathy( $_post->ID ) ) {
											$ems = array();
											
											foreach ( $emotions as $emotion )
												$ems[] = $emotion->name;
											
											echo join( ', ', $ems );
												
										} else
											_e( '&hellip;', 'empathy' );
									?>
									</td>
								</tr>
								<?php
									}
								?>
							</tbody>
						</table>
					</div>
				</div>
				<div id="col-left">
					<div class="col-wrap">
						<div class="form-wrap">
							<h3><?php _e( 'Emotions', 'empathy' ); ?></h3>
							<?php
								$defaults = ( !$post->ID ) ? $this->option( 'defaults' ) : array();
								$emotions = get_terms( 'empathy_emotion', 'hide_empty=0' );
								$post_ems = $this->get_empathy( $post->ID );
							?>
							<div style="height:14em;overflow:auto;border:1px solid #ccc;padding:0 0 0 .5em">
							<?php
							$walker   = new empathy_Walker_AdminEmotionsSelect();
							echo $walker->walk( $emotions, 0, array( 'emotions' => $post_ems, 'defaults' => $defaults ) );
							?>
							</div>
							<p><?php _e( 'The emotions you select will replace any existing emotions on the selected posts. Selecting no emotions will remove all emotions from the selected posts.', 'empathy' ); ?></p>
							<p><input type="submit" name="Submit" class="button-secondary" value="<?php _e( 'Change Emotions', 'empathy' ); ?>"></p>
							<input type="hidden" name="action" value="change_emotions">
						</div>
					</div>
				</div>
			</div>
			</form>
		</div>
		<?php
		} elseif ( 'uninstall_empathy' == $_REQUEST[ 'subpage' ] ) {
		?>
		<div class="wrap">
			<div id="icon-empathy" class="icon32"><img src="<?php echo $this->url . 'icon.png'; ?>" alt="icon" /></div>
			<h2><?php _e( 'Uninstall Empathy', 'empathy' ); ?></h2>
			<div id="col-wrap">
				<div id="col-left">
					<div class="col-wrap">
						<p><?php _e( 'This will completely remove any information, files, and settings related to Empathy. You will still need to deactivate and delete the plugin itself after uninstalling. Are you sure you want to uninstall Empathy?', 'empathy' ); ?></p>
						<p>
							<a href="?page=<?php echo $page; ?>" class="button-secondary"><?php _e( 'Cancel', 'empathy' ); ?></a>
							<a href="<?php echo wp_nonce_url( '?page=' . $page . '&amp;action=uninstall_empathy', 'uninstall_empathy' ); ?>" class="button-primary"><?php _e( 'Uninstall Empathy', 'empathy' ); ?></a>
						</p><br>
					</div>
				</div>
			</div>
		</div>
		<?php
		} else {
		?>
		<div class="wrap">
			<div id="icon-empathy" class="icon32"><img src="<?php echo $this->url . 'icon.png'; ?>" alt="icon" /></div>
			<h2><?php _e( 'Empathy Tools', 'empathy' ); ?></h2>
			<table class="widefat">
				<tr>
					<th scope="row" class="row-title"><a href="<?php echo $view . '&amp;subpage=batch_emotions'; ?> "><?php _e( 'Batch Emotions', 'empathy' ); ?></a></th>
					<td class="desc"><?php _e( 'Quickly change the emotions associated with the posts on your site.', 'empathy' ); ?></td>
				</tr>
				<?php if ( !$this->option( 'uninstall' ) ) { ?>
				<tr>
					<th scope="row" class="row-title delete"><a href="<?php echo $view . '&amp;subpage=uninstall_empathy'; ?> "><?php _e( 'Uninstall Empathy', 'empathy' ); ?></a></th>
					<td class="desc"><?php _e( 'Completely removes all files and information related to Empathy.', 'empathy' ); ?></td>
				</tr>
				<?php } ?>
			</table>
		</div>
		<?php
		}
	}
	
	function admin_settings() {
		if ( !is_admin() ) return;
		
		$this->domain();
		
		if ( 'empathy_settings' == $_POST[ 'action' ] ) {
			check_admin_referer( 'empathy_settings' );
			
			$new[ 'version' ]            = $this->version;
			$new[ 'integrate' ]          = ( $_POST[ 'integrate' ] ) ? true : false;
			$new[ 'integrate_format' ]   = $_POST[ 'integrate_format' ];
			$new[ 'integrate_related' ]  = ( $_POST[ 'integrate_related' ] ) ? true : false;
			$new[ 'integrate_archive' ]  = ( $_POST[ 'integrate_archive' ] ) ? true : false;
			$new[ 'integrate_position' ] = $_POST[ 'integrate_position' ];
			$new[ 'current' ]            = $this->option( 'current' );
			$new[ 'defaults' ]           = $this->option( 'defaults' );
			$new[ 'files' ]              = $this->option( 'files' );
			$new[ 'themes' ]             = $this->option( 'themes' );
			
			$this->option( $new );
			
			?>
			<div id="message" class="updated fade"><p><strong>
			<?php
			_e( 'Settings saved.', 'empathy' );
			?>
			</strong></p></div>
			<?php
			if ( $errors ) {
				?>
				<div id="message" class="error">
				<?php
				foreach ( $errors as $error )
					echo '<p>' . $error . '</p>';
				?>
				</div>
				<?php
			}
		}
		?>
		<div class="wrap">
			<div id="icon-empathy" class="icon32"><img src="<?php echo $this->url . 'icon.png'; ?>" alt="icon" /></div>
			<h2><?php _e( 'Empathy Settings', 'empathy' ); ?></h2>
			<form method="post" action="">
				<?php wp_nonce_field( 'empathy_settings' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="integrate"><?php _e( 'Site Integration', 'empathy' ); ?></label></th>
						<td>
							<input type="checkbox" name="integrate" id="integrate" value="1"<?php if ( $this->option( 'integrate' ) ) echo ' checked'; ?>>
								<?php
									switch ( $this->option( 'integrate_position' ) ) {
										case 'below': $b = ' selected'; break;
										case 'above':
										default     : $a = ' selected';
									}
									
									$where = '
									<select name="integrate_position">
										<option value="above"' . $a . '>' . __( 'above', 'empathy' ) . '</option>
										<option value="below"' . $b . '>' . __( 'below', 'empathy' ) . '</option>
									</select>';
									
									switch ( $this->option( 'integrate_format' ) ) {
										case 'cloud': $c = ' selected'; break;
										case 'list' : $l = ' selected'; break;
										case 'flat' :
										default     : $f = ' selected';
									}
									
									$how = '
									<select name="integrate_format">
										<option value="flat"' . $f . '>' . __( 'flat list', 'empathy' ) . '</option>
										<option value="list"' . $l . '>' . __( 'structured list', 'empathy' ) . '</option>
										<option value="cloud"' . $c . '>' . __( 'cloud', 'empathy' ) . '</option>
									</select>';
									
									printf( __( 'Show emotions %s post content as a %s' ), $where, $how );
								?>
							<p><label><input type="checkbox" name="integrate_related" value="1"<?php if ( $this->option( 'integrate_related' ) ) echo ' checked'; ?>> <?php _e( 'Show emotionally-related posts on single-post pages', 'empathy' ); ?></label></p>
							<p><label><input type="checkbox" name="integrate_archive" value="1"<?php if ( $this->option( 'integrate_archive' ) ) echo ' checked'; ?>> <?php _e( 'Show emotion names and descriptions on archive pages', 'empathy' ); ?></label></p>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" name="Submit" class="button-primary" value="<?php _e( 'Save Changes', 'empathy' ); ?>"> <span class="alignright description"><?php printf( __( '<a href="%s" target="_blank" title="Show your support by donating">Donate</a> | Empathy %s', 'empathy' ), 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9340610', $this->option( 'version' ) ); ?></span>
					<input type="hidden" name="action" value="empathy_settings">
				</p>
			</form>
		</div>
		<?php
	}
	
	function post_meta_box( $post ) {
		if ( !is_admin() ) return;
		
		$this->domain();
		
		$defaults = ( !$post->ID ) ? $this->option( 'defaults' ) : array();
		$emotions = get_terms( 'empathy_emotion', 'hide_empty=0' );
		$post_ems = $this->get_empathy( $post->ID );
		?>
		<div style="height:14em;overflow:auto">
		<?php
		$walker   = new empathy_Walker_AdminEmotionsSelect();
		echo $walker->walk( $emotions, 0, array( 'emotions' => $post_ems, 'defaults' => $defaults ) );
		?>
		</div>
		<?php
	}
	function page_meta_box( $post ) {
		if ( !is_admin() ) return;
		
		$this->domain();
		
		$defaults = ( !$post->ID ) ? $this->option( 'defaults' ) : array();
		$emotions = get_terms( 'empathy_emotion', 'hide_empty=0' );
		$post_ems = $this->get_empathy( $post->ID );
		?>
		<div style="height:14em;overflow:auto">
		<?php
		$walker   = new empathy_Walker_AdminEmotionsSelect();
		echo $walker->walk( $emotions, 0, array( 'emotions' => $post_ems, 'defaults' => $defaults ) );
		?>
		</div>
		<?php
	}
} global $empathy; $empathy = new empathy();

include_once( 'empathy-tags.php' );
include_once( 'empathy-walker.php' );
include_once( 'empathy-widgets.php' );

if ( is_admin() ) require_once( 'empathy-walker-admin.php' );
?>