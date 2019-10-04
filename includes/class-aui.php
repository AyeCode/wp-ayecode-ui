<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class AUI {

	// Hold the class instance.
	private static $instance = null;

	private function __construct(){
		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}
		spl_autoload_register( array( $this, 'autoload' ) );
	}
	
	private function autoload($classname){
		$class = str_replace( '_', '-', strtolower($classname) );
		$file_path = trailingslashit( dirname( __FILE__ ) ) ."components/class-". $class . '.php';
		if ( $file_path && is_readable( $file_path ) ) {
			include_once( $file_path );
		}
	}

	public static function instance() {
		if (self::$instance == null)
		{
			self::$instance = new AUI();
		}

		return self::$instance;
	}

	public function get_alert( $args = array() ) {
		return AUI_Component_Alert::get($args);
	}

	public function alert( $args = array() ) {
		echo self::get_alert( $args );
	}
}