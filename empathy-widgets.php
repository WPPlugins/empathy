<?php
global $empathy;

class empathy_Widget_Emotions extends WP_Widget {
	function empathy_Widget_Emotions() {
		$widget_ops = array( 'description' => __( 'A list or dropdown of emotions', 'empathy' ) );
		$this->WP_Widget( 'empathy-emotion-list', __( 'Emotions', 'empathy' ), $widget_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		
		global $empathy;
		
		$instance[ 'display' ] = ( $instance[ 'display' ] ) ? $instance[ 'display' ] : 'text';
		
		echo $before_widget;
		if ( !empty( $instance[ 'title' ] ) ) echo $before_title . $instance[ 'title' ] . $after_title;
		if ( $instance[ 'dropdown' ] )
			$empathy->empathy_dropdown_emotions( array( 'show_count' => $instance[ 'counts' ], 'hierarchical' => $instance[ 'hierarchy' ], 'show_option_none' => __( 'Select an Emotion', 'empathy' ) ) );
		else {
			echo '<ul>';
			$empathy->empathy_list_emotions( array( 'show_count' => $instance[ 'counts' ], 'hierarchical' => $instance[ 'hierarchy' ], 'empathy_display' => $instance[ 'display' ], 'empathy_theme' => $instance[ 'theme' ], 'title_li' => false ) );
			echo '</ul>';
		}
			
		echo $after_widget;
	}
	
	function update( $new, $old ) {
		if ( !isset( $new[ 'submit' ] ) )
			return;
		
		$instance = $old;
		$instance[ 'title' ]     = strip_tags( stripslashes( $new[ 'title' ] ) );
		$instance[ 'dropdown' ]  = $new[ 'dropdown' ];
		$instance[ 'counts' ]    = $new[ 'counts' ];
		$instance[ 'hierarchy' ] = $new[ 'hierarchy' ];
		$instance[ 'display' ]   = $new[ 'display' ];
		$instance[ 'theme' ]     = $new[ 'theme' ];
		
		return $instance;
	}
	
	function form( $instance ) {
		global $empathy;
		
		$empathy->domain();
		
		$instance = wp_parse_args( ( array ) $instance, array( 'title' => __( 'Emotions', 'empathy'), 'dropdown' => false, 'counts' => false, 'hierarchy' => false, 'display' => false, 'theme' => false ) );
		
		$title     = htmlspecialchars( $instance[ 'title' ], ENT_QUOTES );
		$dropdown  = $instance[ 'dropdown' ];
		$counts    = $instance[ 'counts' ];
		$hierarchy = $instance[ 'hierarchy' ];
		$display   = $instance[ 'display' ];
		$theme     = $instance[ 'theme' ];
		?>
			<p><label><?php _e( 'Title:', 'empathy' ); ?><input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $title; ?>" class="widefat"></label></p>
			<label><input type="checkbox" name="<?php echo $this->get_field_name( 'dropdown' ); ?>" value="1"<?php if ( $dropdown ) echo ' checked'; ?>> <?php _e( 'Show as dropdown', 'empathy' ); ?></label><br>
			<label><input type="checkbox" name="<?php echo $this->get_field_name( 'counts' ); ?>" value="1"<?php if ( $counts ) echo ' checked'; ?>> <?php _e( 'Show post counts', 'empathy' ); ?></label><br>
			<label><input type="checkbox" name="<?php echo $this->get_field_name( 'hierarchy' ); ?>" value="1"<?php if ( $hierarchy ) echo ' checked'; ?>> <?php _e( 'Show hierarchy', 'empathy' ); ?></label><br>
			<label><input type="checkbox" name="<?php echo $this->get_field_name( 'display' ); ?>" value="1"<?php if ( $display ) echo ' checked'; ?>> <?php _e( 'Show emotions as images', 'empathy' ); ?></label>
			<p>
				<label>Theme:
					<select name="<?php echo $this->get_field_name( 'theme' ); ?>">
					<option value=""><?php _e( '- default -', 'empathy' ); ?></option>
					<?php
						$themes = $empathy->option( 'themes' );
						
						foreach ( $themes as $k => $v ) {
							$select = ( $theme == $k ) ? ' selected' : '';
							echo '<option value="' . $k . '"' . $select . '>' . $v . '</option>';
						}
					?>
					</select>
				</label>
			</p>
			<input type="hidden" name="<?php echo $this->get_field_name( 'submit' ); ?>" value="1" />
		<?php
	}
}

class empathy_Widget_SiteEmotion extends WP_Widget {
	function empathy_Widget_SiteEmotion() {
		$widget_ops = array( 'description' => __( 'Display the emotional state of your site', 'empathy' ) );
		$this->WP_Widget( 'empathy-site-emotion', __( 'Site Emotion', 'empathy' ), $widget_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		
		global $empathy;
		
		$instance[ 'display' ] = ( $instance[ 'display' ] ) ? $instance[ 'display' ] : 'text';
		
		echo $before_widget;
		if ( !empty( $instance[ 'title' ] ) ) echo $before_title . $instance[ 'title' ] . $after_title;
		$empathy->empathy_site_emotion( $instance[ 'theme' ] );
		echo $after_widget;
	}
	
	function update( $new, $old ) {
		if ( !isset( $new[ 'submit' ] ) )
			return;
		
		$instance = $old;
		$instance[ 'title' ]   = strip_tags( stripslashes( $new[ 'title' ] ) );
		$instance[ 'theme' ]   = $new[ 'theme' ];
		
		return $instance;
	}
	
	function form( $instance ) {
		global $empathy;
		
		$empathy->domain();
		
		$instance = wp_parse_args( ( array ) $instance, array( 'title' => __( 'Site Emotion', 'empathy'), 'theme' => false ) );
		
		$title   = htmlspecialchars( $instance[ 'title' ], ENT_QUOTES );
		$theme   = $instance[ 'theme' ];
		?>
			<p><label><?php _e( 'Title:', 'empathy' ); ?><input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $title; ?>" class="widefat"></label></p>
			<p>
				<label>Theme:
					<select name="<?php echo $this->get_field_name( 'theme' ); ?>">
					<option value=""><?php _e( '- default -', 'empathy' ); ?></option>
					<?php
						$themes = $empathy->option( 'themes' );
						
						foreach ( $themes as $k => $v ) {
							$select = ( $theme == $k ) ? ' selected' : '';
							echo '<option value="' . $k . '"' . $select . '>' . $v . '</option>';
						}
					?>
					</select>
				</label>
			</p>
			<input type="hidden" name="<?php echo $this->get_field_name( 'submit' ); ?>" value="1" />
		<?php
	}
}

class empathy_Widget_EmotionCloud extends WP_Widget {
	function empathy_Widget_EmotionCloud() {
		$widget_ops = array( 'description' => __( 'Your most frequent emotions in cloud format', 'empathy' ) );
		$this->WP_Widget( 'empathy-emotion-cloud', __( 'Emotion Cloud', 'empathy' ), $widget_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		
		global $empathy;
		
		$instance[ 'display' ] = ( $instance[ 'display' ] ) ? $instance[ 'display' ] : 'text';
		
		echo $before_widget;
		if ( !empty( $instance[ 'title' ] ) ) echo $before_title . $instance[ 'title' ] . $after_title;
		$empathy->empathy_emotion_cloud( array( 'empathy_display' => $instance[ 'display' ], 'empathy_theme' => $instance[ 'theme' ] ) );
		echo $after_widget;
	}
	
	function update( $new, $old ) {
		if ( !isset( $new[ 'submit' ] ) )
			return;
		
		$instance = $old;
		$instance[ 'title' ]   = strip_tags( stripslashes( $new[ 'title' ] ) );
		$instance[ 'display' ] = $new[ 'display' ];
		$instance[ 'theme' ]   = $new[ 'theme' ];
		
		return $instance;
	}
	
	function form( $instance ) {
		global $empathy;
		
		$empathy->domain();
		
		$instance = wp_parse_args( ( array ) $instance, array( 'title' => __( 'Emotions', 'empathy'), 'display' => false, 'theme' => false ) );
		
		$title   = htmlspecialchars( $instance[ 'title' ], ENT_QUOTES );
		$display = $instance[ 'display' ];
		$theme   = $instance[ 'theme' ];
		?>
			<p><label><?php _e( 'Title:', 'empathy' ); ?><input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $title; ?>" class="widefat"></label></p>
			<p><label><input type="checkbox" name="<?php echo $this->get_field_name( 'display' ); ?>" value="1"<?php if ( $display ) echo ' checked'; ?>> <?php _e( 'Show emotions as images', 'empathy' ); ?></label></p>
			<p>
				<label>Theme:
					<select name="<?php echo $this->get_field_name( 'theme' ); ?>">
					<option value=""><?php _e( '- default -', 'empathy' ); ?></option>
					<?php
						$themes = $empathy->option( 'themes' );
						
						foreach ( $themes as $k => $v ) {
							$select = ( $theme == $k ) ? ' selected' : '';
							echo '<option value="' . $k . '"' . $select . '>' . $v . '</option>';
						}
					?>
					</select>
				</label>
			</p>
			<input type="hidden" name="<?php echo $this->get_field_name( 'submit' ); ?>" value="1" />
		<?php
	}
}
?>