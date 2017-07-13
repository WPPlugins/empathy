<?php
abstract class mgs_plugin_core {
	protected $version = '1';
	protected $file, $base, $cdir, $curl, $dir, $url, $options;
	
	/** Initialize the plugin. */
	final function __construct() {
		if ( !$this->file ) $this->file = __FILE__;
		
		$this->base    = plugin_basename( $this->file );
		$this->cdir    = ( !defined( 'BLOGUPLOADDIR' ) ) ? trailingslashit( WP_CONTENT_DIR ) : trailingslashit( BLOGUPLOADDIR );
		$this->curl    = ( !defined( 'BLOGUPLOADDIR' ) ) ? trailingslashit( WP_CONTENT_URL ) : trailingslashit( trailingslashit( get_settings( 'siteurl' ) ) . 'files' );
		$this->dir     = ( false === strpos( dirname( $this->file ), WPMU_PLUGIN_DIR ) ) ? trailingslashit( trailingslashit( WP_PLUGIN_DIR ) . dirname( $this->base ) ) : trailingslashit( trailingslashit( WPMU_PLUGIN_DIR ) . dirname( $this->base ) );
		$this->url     = ( false === strpos( dirname( $this->file ), WPMU_PLUGIN_DIR ) ) ? trailingslashit( trailingslashit( WP_PLUGIN_URL ) . str_replace( basename( $this->file ), '', $this->base ) ) : trailingslashit( trailingslashit( WPMU_PLUGIN_URL ) . str_replace( basename( $this->file ), '', $this->base ) );
		$this->options = $this->option();
		
		$this->setup();
	}
	
	/** Automatically add filters, shortcodes, install, upgrade, and uninstall functions. */
	final protected function setup() {
		$class   = new ReflectionClass( get_class( $this ) );
		$methods = $class->getMethods();
		
		foreach ( $methods as $method ) {
			if ( 0 === strpos( $method->name, 'hook_' ) ) {
				$hook = substr( $method->name, 5 );
				$prio = array_pop( explode( '_', $hook ) );
				
				if ( is_numeric( $prio ) )
					$hook = substr( $hook, 0, - ( strlen( '_' . $prio ) ) );
				else
					$prio = 10;
				
				add_filter( $hook, array( &$this, $method->name ), $prio, $method->getNumberOfParameters() );
			} elseif ( 0 === strpos( $method->name, 'short_' ) ) {
				$short = substr( $method->name, 6 );
				add_shortcode( $short, array( &$this, $method->name ) );
			}
		} unset( $class );
		
		if ( !$this->option() )
			add_filter( 'init', array( &$this, 'install' ) );
		elseif ( $this->option( 'version' ) != $this->version )
			add_filter( 'init', array( &$this, 'upgrade' ) );
		elseif ( $this->option( 'uninstall' ) )
			register_deactivation_hook( $this->file, array( &$this, 'deactivate' ) );
	}
	
	/** Deactivates the plugin. */
	final function deactivate() { $this->option( null ); }
	
	/** Load text domain for translations. */
	final function domain() { load_plugin_textdomain( get_class( $this ), $this->dir, dirname( $this->base ) ); }
	
	/** Add, retrieve, update, or delete an option or options. */
	final function option( $o = false, $v = false ) {
		if ( false === $o ) {
			return get_option( get_class( $this ) . '_options' );
		} if ( null === $o ) {
			$this->options = $o;
			return delete_option( get_class( $this ) . '_options' );
		} if ( is_array( $o ) ) {
			$this->options = $o;
			return update_option( get_class( $this ) . '_options', $o );
		}
		
		if ( array_key_exists( $o, $this->options ) ) {
			if ( false === $v )
				return $this->options[ $o ];
			elseif ( null === $v )
				unset ( $this->options[ $o ] );
			else
				$this->options[ $o ] = $v;
			
			return update_option( get_class( $this ) . '_options', $this->options );
		}
		
		return false;
	}
	
	/** Abstract functions that must be defined. */
	abstract function install();
	abstract function upgrade();
	abstract function uninstall();
}
?>