<?php
/**
 * Visforms HTML class for date fields
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
 * Create HTML of a date field according to it's type
 *
 * @package        Joomla.Site
 * @subpackage     com_visforms
 * @since          1.6
 */
class VisformsHtmlDate extends VisformsHtml
{
	/**
	 *
	 * Constructor
	 *
	 * @param object $field field object as extracted from database
	 */
	public function __construct($field, $decorable, $attribute_type) {
		$attribute_type = "date";
		parent::__construct($field, $decorable, $attribute_type);
	}

	/**
	 * Method to create the field attribute array
	 * @return array Html tag attributes for field
	 */
	public function getFieldAttributeArray() {
		$attributeArray = array('class' => '');
		if (!empty($this->field->disableEnterKey)) {
			$attributeArray['class'] = 'noEnterSubmit ';
		}
		//attributes are stored in xml-definition-fields with name that ends on _attribute_attributename (i.e. _attribute_checked).
		//each form field is represented by a fieldset in xml-definition file
		//each form field should have in xml-definition file a field with name that ends on _attribute_class. default " " or class-Attribute values for form field
		foreach ($this->field as $name => $value) {
			if (!is_array($value)) {
				if (strpos($name, 'attribute_') !== false) {
					if ($name == 'attribute_required') {
						$attributeArray['aria-required'] = 'true';
					}
					if ($value || $name == 'attribute_class' || ($name == 'attribute_value')) {
						$newname = str_replace('attribute_', "", $name);
						if ($newname == "class") {
							$value = $attributeArray[$newname] . $value . $this->field->fieldCSSclass;
							$attributeArray[$newname] .= $value;
						}
						else {
							$attributeArray[$newname] = $value;
						}
					}
					//due to the way Joomla! creates a date control we have to add value manually and type is set automatically
					unset($attributeArray['value']);
					unset($attributeArray['type']);
				}
				if ($name == 'id') {
					$attributeArray['data-error-container-id'] = 'fc-tbxfield' . $value;
				}
				if (($name == 'isDisabled') && ($value == true)) {
					$attributeArray['class'] .= " ignore";
					$attributeArray['disabled'] = "disabled";
				}
				if (($name == 'isDisplayChanger') && ($value == true)) {
					$attributeArray['class'] .= " displayChanger";
				}
				if (($name == 'isValid') && ($value == false)) {
					$attributeArray['class'] .= " error";
				}
				$attributeArray['aria-labelledby'] = $this->field->name . 'lbl';
			}
		}
		return $attributeArray;
	}

	public function setFieldCustomValidateArray($field) {
		if (!isset($field->validateArray)) {
			$field->validateArray = array();
		}
		//custom validation for date fields
		if (isset($field->dateFormatJs)) {
			switch ($field->dateFormatJs) {
				case "%d.%m.%Y":
					$field->validateArray['dateDMY'] = 'true';
					break;
				case "%m/%d/%Y":
					$field->validateArray['dateMDY'] = 'true';
					break;
				case "%Y-%m-%d":
					$field->validateArray['dateYMD'] = 'true';
					break;
			}
			if (!empty($field->mindate)) {
				$field->validateArray['mindate'] = "{value:'" . $field->mindate . "', fromField: false, shift: '0', format: '" . $this->field->dateFormatJs . "', type: 'min'}";
			}
			else if (isset($this->field->minvalidation_type) && (strpos($this->field->minvalidation_type, '#field') !== false)) {
				$field->validateArray['mindate'] = "{value:'" . str_replace("#", "", $this->field->minvalidation_type) . "', fromField: true, shift:'" . $this->field->dynamic_min_shift . "', format: '" . $this->field->dateFormatJs . "', type: 'min'}";
			}
			if (!empty($field->maxdate)) {
				$field->validateArray['maxdate'] = "{value:'" . $field->maxdate . "', fromField: false, shift: '0', format: '" . $this->field->dateFormatJs . "', type: 'max'}";
			}
			else if (isset($this->field->maxvalidation_type) && (strpos($this->field->maxvalidation_type, '#field') !== false)) {
				$field->validateArray['maxdate'] = "{value:'" . str_replace("#", "", $this->field->maxvalidation_type) . "', fromField: true, shift:'" . $this->field->dynamic_max_shift . "', format: '" . $this->field->dateFormatJs . "', type: 'max'}";
			}
		}
		return $field;
	}

	/**
	 * Method for the individual field type specific set of span attributes for multi column layout (don't set for dates)
	 *
	 * @param object $field
	 *
	 * @return object field
	 */
	public function setBootstrapSpanClasses($field) {
		return $field;
	}
}