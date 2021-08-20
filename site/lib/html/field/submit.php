<?php
/**
 * Visforms HTML class for submit button
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Create HTML of a submit button according to it's type
 *
 * @package        Joomla.Site
 * @subpackage     com_visforms
 * @since          1.6
 */
class VisformsHtmlSubmit extends VisformsHtml
{
	/**
	 *
	 * Constructor
	 *
	 * @param object $field field object as extracted from database
	 */
	public function __construct($field, $decorable, $attribute_type) {
		if (is_null($decorable)) {
			$decorable = false;
		}
		if (is_null($attribute_type)) {
			$attribute_type = "submit";
		}
		parent::__construct($field, $decorable, $attribute_type);
	}

	/**
	 * Method to create the field attribute array
	 * @return array Html tag attributes for field
	 */
	public function getFieldAttributeArray() {
		$attributeArray = array('class' => '');
		//attributes are stored in xml-definition-fields with name that ends on _attribute_attributename (i.e. _attribute_checked).
		//each form field is represented by a fieldset in xml-definition file
		//each form field should have in xml-definition file a field with name that ends on _attribute_class. default " " or class-Attribute values for form field
		foreach ($this->field as $name => $value) {
			if (!is_array($value)) {
				if (strpos($name, 'attribute_') !== false) {
					if ($value || $name == 'attribute_class') {
						$newname = str_replace('attribute_', "", $name);
						if ($newname == "class") {
							$value = $value . $this->field->fieldCSSclass;
							$attributeArray[$newname] .= $value;
						}
						else {
							$attributeArray[$newname] = $value;
						}
					}
				}
				if ($name == 'name') {
					$attributeArray['name'] = $value;
				}
				if ($name == 'id') {
					$value = 'field' . $value;
					$attributeArray['id'] = $value;
				}
				//specific settings for buttons
				$attributeArray['aria-label'] = $this->field->label;
				$attributeArray['value'] = $this->field->label;
				$attributeArray['disabled'] = 'disabled';
			}
		}
		return $attributeArray;
	}

	/**
	 * Method for the individual field type specific removal of bootstrap class attributes (no removal for buttons)
	 *
	 * @param object $field
	 *
	 * @return object field
	 */
	public function removeNoBootstrapClasses($field) {
		return $field;
	}

	/**
	 * Method for the individual field type specific set of span attributes for multi column layout (don't set for buttons)
	 *
	 * @param object $field
	 *
	 * @return object field
	 */
	public function setBootstrapSpanClasses($field) {
		return $field;
	}

	public function setControlHtmlClasses($field) {
		$field->attribute_class = (!empty($this->field->fieldCSSclass)) ? ' btn' : ' btn btn-primary';
		return $field;
	}

	public function setUikit3ControlHtmlClasses($field) {
		$field->attribute_class = (!empty($this->field->fieldCSSclass)) ? ' btn' : ' btn uk-button-primary';
		return $field;
	}

	public function setUikit2ControlHtmlClasses($field) {
		$field->attribute_class = (!empty($this->field->fieldCSSclass)) ? '' : ' uk-button uk-button-primary';
		return $field;
	}

	protected function cleanFieldProperties() {
		$this->field->customtext = '';
		parent::cleanFieldProperties();
	}
}