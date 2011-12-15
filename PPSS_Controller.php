<?php

class PPSS_Controller extends PW_ModelController
{
  // store a list of all the header_extras from all $posts to print at the end of the header
  protected $_extras;
  
  // store a list of all the footer_extras from all $posts to print at the end of the body
  protected $_footer_extras;
  
  
  public function __construct()
  {
    // these two properties need to be set before parent::__construct() is called
    $this->_plugin_dir = plugin_basename( dirname(__FILE__) );
    $this->_plugin_file = plugin_basename( dirname(__FILE__) . '/per-post-scripts-and-styles.php' );
        
    parent::__construct();
    add_action( 'add_meta_boxes', array($this, 'add_meta_boxes') );
    add_action( 'save_post', array($this, 'save_meta_box_data') );
    add_action( 'wp_enqueue_scripts', array($this, 'add_scripts_and_styles') );
    add_action( 'wp_head', array($this, 'print_script_and_style_extras') );
    add_action( 'wp_print_footer_scripts', array($this, 'print_script_footer_extras') );
  } 
  
  public function add_scripts_and_styles()
  {
    global $posts, $post;
    $options = $this->model->option;
    
    // check what page we're on before determining what $posts to inspect
    if ( is_single() ) {
      $this->load_scripts_and_styles_for_post($post->ID);
    } else if ( ( is_home() && $options['on'] === 'home' ) || ( $options['on'] === 'all' ) ) {
      foreach($posts as $p) {
        $this->load_scripts_and_styles_for_post($p->ID);
      }
    }
  }
  
  public function load_scripts_and_styles_for_post( $post_id )
  {  
    $header_scripts = get_post_meta( $post_id, '_ppss_header_scripts', true);
    $footer_styles = get_post_meta( $post_id, '_ppss_footer_scripts', true);
    $styles = get_post_meta( $post_id, '_ppss_styles', true);
    $extras = get_post_meta( $post_id, '_ppss_extras', true);
    $footer_extras = get_post_meta( $post_id, '_ppss_footer_extras', true); 

    $header_scripts = explode("\n", $header_scripts);
    foreach($header_scripts as $s) {
      $this->process_url_to_script_array($s, false);
    }
    
    $footer_styles = explode("\n", $footer_styles);
    foreach($footer_styles as $s) {
      $this->process_url_to_script_array($s, true);
    }
    
    $styles = explode("\n", $styles);
    foreach($styles as $s) {
      $this->process_url_to_style_array($s);
    }
    
    $this->_extras .= $extras;
    $this->_footer_extras .= $footer_extras;
  }
  
  public function process_url_to_style_array($url)
  {
    // make sure the url isn't just white space
    if (preg_match('/\S/', $url) ) {
      $url = trim($url);
      $url = str_replace( array('%SITE_URL%', '%THEME_URL%'), array(site_url(), get_stylesheet_directory_uri()), $url);
      $this->_styles[] = array( md5($url), $url );
    }
  }
  
  
  public function process_url_to_script_array($url, $in_footer)
  {
    // make sure the url isn't just white space
    if (preg_match('/\S/', $url) ) { 
  
      $url = trim($url);
    
      // extract any dependencies and store in an array
      $dependencies = array();
      if (preg_match('/\{[^\}]+\}$/', $url, $dependencies)) {
        $dependencies = explode( ",", str_replace( array('{', '}', ' '), array('', '', ''), $dependencies[0] ) );
      }

      // remove {dependencies...} from the URL
      $url = preg_replace('/\{[^\}]+\}$/', '', $url);
    
      $url = str_replace( array('%SITE_URL%', '%THEME_URL%'), array(site_url(), get_stylesheet_directory_uri()), $url);
      $this->_scripts[] = array( md5($url), $url, $dependencies, false, $in_footer );
    }
  }
  
  public function print_script_and_style_extras()
  {
    echo $this->_extras;
  }
  
  public function print_script_footer_extras()
  {  
    echo $this->_footer_extras;
  }
  
  // Add meta box as a custom write panel
  public function add_meta_boxes()
  {
    foreach ( $this->model->option['post_types'] as $post_type ) { // get all post types
      add_meta_box('ppss', 'Per Post Scripts & Styles', array($this, 'print_meta_box'), $post_type, 'normal', 'default');
    }
  }
  
  // Meta box content
  public function print_meta_box( $post )
  {
    $header_scripts = get_post_meta( $post->ID, '_ppss_header_scripts', true);
    $footer_styles = get_post_meta( $post->ID, '_ppss_footer_scripts', true);
    $styles = get_post_meta( $post->ID, '_ppss_styles', true);
    $extras = get_post_meta( $post->ID, '_ppss_extras', true);
    $footer_extras = get_post_meta( $post->ID, '_ppss_footer_extras', true); 
    ?>
      <p style="margin-top:1em;"><strong>INSTRUCTIONS</strong></p>
      <ul style="color:#666; list-style:square; margin:0 0 0 1.5em;">
        <li>When entering the URLs, you may use the variables <code>%SITE_URL%</code> and <code>%THEME_URL%</code> for greater flexibility, e.g. <code>%SITE_URL%/scripts/this-post-script.js</code></li>
        <li>To have multiple scripts, put each on on its own line.</li>
        <li>If your script has one or more dependencies, add them as a comma separated list in braces at the end of the URL, e.g.  <code>%SITE_URL%/scripts/code.js{jquery,json2}</code></li>
        <li>Questions? Check out the <a href="http://philipwalton.com/2011/09/25/per-post-scripts-styles/">full documentation</a></li>
      </ul>
          
      <hr style="background-color:#ccc; border:1px solid #ccc; border-width:1px 0; border-color:#dfdfdf #fff #fff; height:0px; margin:1em 0" />
      <p>
        <label for="ppss_meta_header_scripts"><strong>Header Scripts:</strong></label> 
        <textarea id="ppss_meta_header_scripts" name="ppss_meta[header_scripts]" rows="1" style="width:100%;" ><?php echo esc_attr($header_scripts); ?></textarea>
      </p>
      <p>
        <label for="ppss_meta_header_scripts"><strong>Footer Scripts:</strong></label>
        <textarea id="ppss_meta_header_scripts" name="ppss_meta[footer_scripts]" rows="1" style="width:100%;" ><?php echo esc_attr($footer_styles); ?></textarea>
      </p>
      <p>
        <label for="ppss_meta_styles"><strong>Stylesheets:</strong></label>
        <textarea id="ppss_meta_styles" name="ppss_meta[styles]" rows="1" style="width:100%;" ><?php echo esc_attr($styles); ?></textarea>
      </p>
      <p>
        <label for="ppss_meta_extras"><strong>Header Extras:</strong></label>
        <textarea id="ppss_meta_extras" name="ppss_meta[extras]" rows="2" style="width:100%;" ><?php echo esc_attr($extras); ?></textarea>
        <span class="description">Hardcode Javascript or CSS here. It will be outputted right before the <code>&lt;/header&gt;</code> tag. Make sure to properly use the <code>&lt;script&gt;</code> and <code>&lt;style&gt;</code> tags.</span>
      </p>
      <p>
        <label for="ppss_meta_footer_extras"><strong>Footer Extras:</strong></label>
        <textarea id="ppss_meta_extras" name="ppss_meta[footer_extras]" rows="2" style="width:100%;" ><?php echo esc_attr($footer_extras); ?></textarea>
        <span class="description">Hardcode Javascript here. It will be outputted right before the <code>&lt;/body&gt;</code> tag. Make sure to properly use the <code>&lt;script&gt;</code> tags.</span>
      </p>
    <?php
  }
  
  //hook to save the meta box data
  public function save_meta_box_data( $post_id )
  {        
    if ( isset($_POST['ppss_meta']) ) {
      foreach($_POST['ppss_meta'] as $key=>$value)
      $this->update_or_delte_post_meta($key, $value, $post_id);
    }
  }
  
  protected function update_or_delte_post_meta( $key, $value, $post_id )
  {
    // If this value has data, update the meta in the database, otherwise delete the row
    if ( $value ) {
      update_post_meta( $post_id, '_ppss_' . $key, $value );
    } else {
      delete_post_meta( $post_id, '_ppss_' . $key );
    }
  }
}