<?php

	// If uninstall not called form WordPress, exit
	if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		exit();
	}
	
	// delete the option from the options table
	delete_option( 'ppss' );
	
	// delete the meta data from the postmeta table
	$allposts = get_posts('numberposts=-1&post_type=any&post_status=any');
  foreach( $allposts as $postinfo) {
    delete_post_meta($postinfo->ID, '_ppss_header_scripts');
    delete_post_meta($postinfo->ID, '_ppss_footer_scripts');
    delete_post_meta($postinfo->ID, '_ppss_styles');
    delete_post_meta($postinfo->ID, '_ppss_extras');
  }
	
?>