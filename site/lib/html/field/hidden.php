<?php
/**
 * Visforms HTML class for hidden fields
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
 * Create HTML of a hidden field according to it's type
 *
 * @package        Joomla.Site
 * @subpackage     com_visforms
 * @since          1.6
 */
class VisformsHtmlHidden extends VisformsHtml
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
		$attribute_type = "hidden";
		//prevent email cloaking in hidden fields that may contain a email address as default value
		if (isset ($field->attribute_value) && ($field->attribute_value != "")) {
			$field->attribute_value = str_replace('@', '&#64', $field->attribute_value);
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
					if ($value || $name == 'attribute_class' || ($name == 'attribute_value')) {
						$newname = str_replace('attribute_', "", $name);
						if ($newname == "class") {
							$value = $value . $this->field->fieldCSSclass;
						}
						$attributeArray[$newname] = $value;
					}
				}
				if ($name == 'name') {
					$attributeArray['name'] = $value;
				}
				if ($name == 'id') {
					$value = 'field' . $value;
					$attributeArray['id'] = $value;
				}
			}
		}
		return $attributeArray;
	}
}