<?php

class PPSS_Model extends PW_Model
{
  protected $_title = "Per Post Scripts & Styles";
  protected $_name = 'ppss';
  protected $_post_types;

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
				'label' => 'Only Load Scripts & Styles When:',
				'default' => 'single',
				'options' => array(
				  'single' => 'Viewing single post pages',
				  'home' => 'Viewing single post pages as well as the home page (if the specific post is displayed)',
				  'all' => 'Viewing all pages where the specific post is displayed',
				)
			),
    );
  } 
}