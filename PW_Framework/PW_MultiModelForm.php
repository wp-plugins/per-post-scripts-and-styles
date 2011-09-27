<?php
/**
 * PW_MultiModelForm
 *
 * A helper class to build a form based on a PW_MultiModel object
 *
 * This class primarily does these things:
 * 1) Renders the markup of the form fields
 * 2) Adds error messages for any validation errors that are found
 *
 * @package PW_Framework
 * @since 0.1
 */

class PW_MultiModelForm extends PW_Form
{

	/**
	 * @see parent
	 * @since 0.1
	 */
	public function __construct( $model )
	{
		$this->_model = $model;
	}

	/**
	 * @see parent
	 * @since 0.1
	 */
	public function begin_form( $atts = array() )
	{	
		$atts['class'] = 'pw-mm-form pw-form';
		$output = parent::begin_form($atts);
		$output .= PW_HTML::tag('input', null, array('type'=>'hidden', 'name'=>'_instance', 'value'=>$this->_model->instance) );

		// Loop through the multi model to create the form tabs
		$tabs = array();
		$instances = $this->_model->get_option();
		foreach($instances as $id=>$instance)
		{													
			if ( 0 === (int) $id || (string) $id === 'auto_id' ) {
				continue;
			}
																			
			$atts = $this->_model->instance == $id ? array('class'=>'selected') : array();
			$atts['href'] = $this->_model->admin_page . '?page='. $this->model->name . '&_instance=' . $id;
			$content = $instance['name'];

			$tabs[] = PW_HTML::tag('a', $content, $atts);
		}
		
		// create the [+] tab
		$atts = 0 == $this->_model->instance ? array('class'=>'selected') : array();
		$atts['href'] =  $this->_model->admin_page . '?page='. $this->model->name;
		$tabs[] = PW_HTML::tag('a', '+', $atts);
		
		$output .= ZC::r('ul.tabs>li*' . count($tabs), $tabs);
		
		// create the header
		if ($this->_model->instance) {
			$title = ZC::r('.model-title', $instances[$this->_model->instance]['name']);
			$subtitle = ZC::r('.model-subtext', 'Use the above name to reference this ' . $this->_model->singular_title . ' instance in widgets, shortcode, or function calls.');
			$output .= ZC::r('.header', $title . $subtitle);
		} else {
			$title = ZC::r('.model-title', 'Create New Instance' );
			$subtitle = ZC::r('.model-subtext', 'Click "HELP" in the upper right corner of your screen for instructions.');
			$output .= ZC::r('.header', $title . $subtitle);
		}
		
		// open the body tag
		$output .= '<div class="body">';
		
		$this->return_or_echo( $output );
	}
	
	/**
	 * @see parent
	 * @since 0.1
	 */
	public function end_form()
	{
		$output = '</div>'; // closes off .body
		$output .= ZC::r('.footer', $this->render_buttons() );
		
		$this->return_or_echo( $output );
	}
	
	/**
	 * Create the markup for the Create/Update and Delete buttons
	 * @return string The rendered HTML markup
	 * @since 0.1
	 */
	protected function render_buttons()
	{
		$output = ZC::r('input.button-primary{%1}', array('type'=>'submit', 'value'=> $this->_model->instance ? "Update" : "Create") , null);
		
		$instances = $this->_model->get_option();
		$instance = $instances[$this->_model->instance];
		
		if ( 0 != $this->_model->instance ) {
			$delete_url = wp_nonce_url( add_query_arg('delete_instance', '1'), 'delete_instance' );
			$output .= ZC::r('a.submitdelete[href="' . $delete_url .'"]', 'Delete ' . $instance['name'] );	
		}
		return $output;		
	}

	/**
	 * @see parent
	 * @since 0.1
	 */
	protected function get_field_data_from_model( $property )
	{		
		$errors = $this->_model->errors;
		$error = isset($errors[$property]) ? $errors[$property] : null;
	
		$data = $this->_model->data();
	
		// get the label and description of this property
		$label = isset($data[$property]['label']) ? $data[$property]['label'] : '';
		$desc = isset($data[$property]['desc']) ? $data[$property]['desc'] : '';
			
		// get the value of the model attribute by this name
		// if there was a validation error, get the previously submitted value
		// rather than what's stored in the database
		if ( isset($this->_model->input[$property]) ) {
			$value =  $this->_model->input[$property];
		} else {
			$value = $this->_model->get_option();
			$value = $value[$this->_model->instance][$property];
		}

		
		// add the model's option name for easy getting from the $_POST variable after submit
		$name = $this->_model->name . '[' . $property . ']';
		
		// get any options defined (for use in select, checkbox_list, and radio_button_list fields)
		$options = isset($data[$property]['options']) ? $data[$property]['options'] : array();
		
		// create the id from the name
		$id = PW_HTML::get_id_from_name( $name );
		
		return array( 'error'=>$error, 'label'=>$label, 'desc'=>$desc, 'value'=>$value, 'name'=>$name, 'id'=>$id, 'options'=>$options );
	}	
}