<?php
/**
 * PW_Model
 *
 * The base model object for WordPress option(s) data (stored in the options table)
 *
 * This class primarily does these things:
 * 1) Stores default values and parsing them up request
 * 2) Adding validation rules
 *
 * @package PW_Framework
 * @since 0.1
 */

class PW_Model extends PW_Object
{
	/**
	 * If a form was submitted, this will be the value of the submitted option data
	 * @var array The $_POST data of the submitted form
	 * @since 0.1
	 */
	protected $_input = array();
	
	
	/**
	 * Whether or option data was just updated
	 * @var bool
	 * @since 0.1
	 */
	protected $_updated = false;
	
	
	/**
	 * The title of this model
	 * This value is used as the default value for both the options page heading and nav menu text
	 * @var string
	 * @since 0.1
	 */
	protected $_title = '';
	
	
	/**
	 * The name of the option in the options table
	 * This value must be overridden in a subclass.
	 * @var string
	 * @since 0.1
	 */
	protected $_name = '';
	
	
	/**
	 * The current value of the option parsed against the default value
	 * @var array
	 * @since 0.1
	 */
	protected $_option = array();
	
	
	/**
	 * @var string HTML content for the contextual help section on a settings page
	 * @since 0.1
	 */
	protected $_help;
	
	
	/**
	 * @var string The admin page where the settings form will be rendered
	 * @since 0.1
	 */
	protected $_admin_page = 'options-general.php'; // use 'themes.php' for theme options
	
	
	/**
	 * The user capability required to edit this model's option
	 * @var string
	 * @since 0.1
	 */
	protected $_capability = 'manage_options'; // use 'edit_theme_options' for theme options
	
		
	/**
	 * An array of validation errors if any exist
	 * @var array
	 * @since 0.1
	 */
	protected $_errors = array();
	
	
	/**
	 * Whether or not the option should be stored as autoload, defaults to 'yes'
	 * @var string 'yes' or 'no'
	 * @since 0.1
	 */
	protected $_autoload = 'yes';


	/**
	 * Associate the option with this model instance. If the option doesn't exist, create it
	 * @since 0.1
	 */
	public function __construct()
	{
		$this->get_option();
	}

	
	/**
	 * Adds an error
	 * @param string $property The option property name
	 * @param string $message The error message
	 * @since 0.1
	 */
	public function add_error( $property, $message )
	{
		// Only add an error if an error for this property doesn't already exists
		// Only the first error encountered will be reported, order the validation rules based on this
		if ( empty($this->_errors[$property]) ) {
			$this->_errors[$property] = $message;
		}
	}


	/**
	 * Validates the option against the validation rules returned by $this->rules()
	 * @param array $option of option to be validated.
	 * @return array The default properties and values
	 * @since 0.1
	 */
	public function validate($input = array(), $validate_all = true)
	{		
		if ( $is_ajax = ( defined('DOING_AJAX') && constant('DOING_AJAX') == true ) ) {
			if ( isset($_GET[$this->_name]) ) {
				$input = $_GET[$this->_name];
				$validate_all = false;
			} else {
				exit();
			}
		} 
		
		$valid = true;
		$rules = $this->rules();
		foreach( $rules as $rule)
		{
			// remove spaces and then split up the comma delimited property string into an array
			$properties = str_replace(' ', '', $rule['properties']);
			$properties = strpos($properties, ',') === false ? array($properties) : explode(',', $properties);
			foreach ($properties as $property)
			{
				// set the field to null if no value was passed but a validation rules was set
				// this will allow for an error in a situation where someone used firebug to delete HTML dynamically
				$field = isset($input[$property]) ? $input[$property] : null;
				
				// if $validate_all is set to false, allow for empty properties
				if ( !$validate_all && $field === null ) {
					continue;
				}
				
				// create an array of values from the rule definition to pass as method arguments to the callback function
				$args = $rule;
				array_unshift($args, $field);
				unset($args['properties']);
				unset($args['validator']);
				unset($args['message']);
				
				if ( $error = call_user_func_array( $rule['validator'], $args) ) {
					$message = isset($rule['message']) ? $rule['message'] : $error;
					$message = str_replace("{property}", $this->get_label($property), $message);
					$this->add_error( $property, $message );
					$valid = false;
				}
			}
		}
		
		// Add an alert for any errors
		if ( $this->_errors && !$is_ajax ) {
			PW_Alerts::add(
				'error',
				'<p><strong>Please fix the following errors and trying submitting again.</strong></p>' . ZC::r('ul>li*' . count($this->errors), array_values($this->errors) ) ,
				0
			);
		}
		
		if ( $is_ajax ) {
			echo current($this->_errors);
			exit();
		} else {
			return $valid;
		}
	}


	/**
	 * Save the option to the database if (and only if) the option passes validation
	 * @param array $option The option value to store
	 * @return boolean Whether or not the option was successfully saved
	 * @since 0.1
	 */
	public function save( $input )
	{
		if ( $this->validate($input) ) {
			$this->_errors = array();
			$this->update_option( $input );
			PW_Alerts::add('updated', '<p><strong>Settings Saved</strong></p>' );				
			return true;
		}
		// If you get to here, return false
		return false;
	}
	
	
	/**
	 * if the option is already stored in the database, get it and merge it with the defaults;
	 * otherwise, store the defaults
	 * @since 0.1
	 */
	public function get_option()
	{
		// If the option is already set in this model, return that
		if ( $this->_option ) {
			return $this->_option;
		} else {
			
			// If the option exists in the database, merge it with the defaults and return
			if ( $this->_option = get_option($this->_name) ) {
				return $this->_option = $this->merge_with_defaults( $this->_option );
			}
			// Still here? That means you need to create a new option with the default values
			add_option( $this->_name, $this->_option = $this->defaults(), '', $this->_autoload );
			return $this->_option;
		}
	}
	
	/**
	 * Updates the option in the database and performs any required logic beforhand
	 * @param mixed $option The new option value
	 * @since 0.1
	 */
	public function update_option( $option )
	{
		// merge with defaults again just in case
		$this->_option = $this->merge_with_defaults( $option );
		update_option( $this->_name, $this->_option );
	}
	
	
	/**
	 * Return the properties label
	 * @param string $property The option property
	 * @return string The label of the property from the data array
	 * @since 0.1
	 */	
	public function get_label( $property )
	{
		$data = $this->data();
		if ( isset($data[$property]['label']) ) {
			return $data[$property]['label'];
		}
	}


	/**
	 * Returns an array specifying the default option property values
	 * @return array The default property values (ex: array( $property => $value ))
	 * @since 0.1
	 */
	protected function defaults()
	{
		$defaults = array();
		$data = $this->data();
		foreach($data as $property=>$value) {
			$defaults[$property] = isset($value['default']) ? $value['default'] : '';
		}
		return $defaults;
	}
	
	
	/**
	 * Returns a multi-dimensional array of the label, description, and default value of each property
	 * HTML characters are allowed within the label and description strings
	 * @return array The property labels
	 * @since 0.1
	 */
	protected function data()
	{
		/* Override would look like this:
		return array(
			'prop1' => array(
				'label' => 'Prop1 Label',
				'desc' => 'This is a description of Prop1',
				'default' => 'Foo',
			),
			'prop2' => array(
				'label' => 'Prop2 Label',
				'default' => 'Bar'
			),
			'prop3' => array(
				'desc' => 'Prop3 only has a description',
			),
		)
		*/
		return array();
	}


	/**
	 * Returns a multi-dimensional array of the validation rules
	 * each returned rule is an array with the following keys:
	 * 1) 'properties' => a comma separated list of option property names
	 * 2) 'validator' => a php callback function that returns true if valid and false or an error message if invalid
	 * 3) 'message' => (optional) a custom message to override the default one (use {property} to refer to that property's label value)
	 * 4) '...' => (optional) Addition key-value pairs that will be passed to the callback function (order matters!)
	 * @return array The validation rules
	 * @since 0.1
	 */
	protected function rules()
	{
		/* Override would look like this:
		return array(
			array(
				'properties' => 'year_count, year_format, year_template',
			 	'validator'=> array('PW_Validator', 'required')
				'message' => '{property} is required, biatch!'
			),
			array(
				'properties' => 'year_format',
			 	'validator'=> array('PW_Validator', 'match')
				'message' => 'There is an error on field {property}.'
				'pattern' => '/[1-9]{2,4}/',
			),
			array(
				'properties' => 'order',
				'validator' => array('PW_Validator', 'in_array'),
				'haystack' => array('ASC','DESC'),
			),		
		);
		*/
		return array();
	}
		
	/**
	 * Merges an option with the defaults from self::defaults(). The default uses wp_parse_args()
	 * Override in a child class for custom merging.
	 * @return array The merged option
	 * @since 0.1
	 */
	protected function merge_with_defaults( $option )
	{
		return wp_parse_args( $option, $this->defaults() );
	}
	
	/**
	 * List any properties that should be readonly
	 * Call array_merge() with parent::readonly() when subclassing to add more values
	 * @return array A list of properties the magic method __set() can't access
	 * @since 0.1
	 */
	protected function readonly()
	{ 
		return array_merge( parent::readonly(), array('name', 'option', 'title') );
	}

}