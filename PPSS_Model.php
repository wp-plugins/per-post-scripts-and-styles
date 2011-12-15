<?php

class PPSS_Model extends PW_Model
{
  protected $_title = "Per Post Scripts & Styles";
  protected $_name = 'ppss';
  protected $_post_types;
	
	protected $_help_tab = 
	  array(
	    'title' => 'Overview',
  	  'id' => 'per-post-scripts-and-styles-help',
  	  'content' => '<p>In the edit screen of each post there\'s a meta box below the content titled "Per Post Scripts &amp; Styles". To add Javascript or CSS to the post, simply enter the URLs of any files that you want to load. There are even boxes to hardcode scripts or styles into.</p><p>If you want more help, check out the <a href="http://philipwalton.com/2011/09/25/per-post-scripts-and-styles/">full documentation.</a>'
	  );

  public function data()
  {
    // create an array of post types
    $post_types = get_post_types( array('public'=>true), 'objects');
    foreach($post_types as $name=>$object) {
      if ($name === 'attachment') continue;
      $this->_post_types[$name] = $object->labels->name;
    }
    
    return array(
      'post_types' => array(
				'label' => 'Allow Per Post Scripts & Styles For The Following Post Types:',
				'default' => array('post', 'page'),
				'options' => $this->_post_types,
			),
			'on' => array(
				'label' => 'Only Load Scripts/Styles When:',
				'default' => 'single',
				'options' => array(
				  'single' => 'Viewing a single post with attached scripts/styles',
				  'home' => 'Viewing a single post or the home page (if it contains posts with attached scripts/styles)',
				  'all' => 'Viewing any page containing posts with attached scripts/styles',
				)
			),
    );
  } 
}