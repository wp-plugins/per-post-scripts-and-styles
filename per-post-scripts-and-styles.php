<?php
/**
 * Plugin Name: Per Post Scripts & Styles
 * Plugin URI: http://philipwalton.com
 * Description: Add specific scripts and stylesheets to posts, pages, and custom post types.
 * Version: 1.0
 * Author: Philip Walton
 * Author URI: http://philipwalton.com
 */

function ppss_init()
{
	require_once('PPSS_Model.php');
	require_once('PPSS_Controller.php');	
	
	$PPSS = new PPSS_Controller();
	$PPSS->model = new PPSS_Model;
	$PPSS->view = dirname(__FILE__) . '/PPSS_View.php';
	
}
add_action( 'pw_framework_loaded', 'ppss_init' );

require_once('PW_Framework/bootstrap.php');