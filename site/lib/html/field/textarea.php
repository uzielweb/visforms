<?php
/**
 * Visforms HTML class for textarea fields
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
 * Create HTML of a textarea field according to it's type
 *
 * @package        Joomla.Site
 * @subpackage     com_visforms
 * @since          1.6
 */
class VisformsHtmlTextarea extends VisformsHtml
{
	/**
	 *
	 * Constructor
	 *
	 * @param object $field field object as extracted from database
	 */
	public function __construct($field, $decorable, $attribute_type) {
		//prevent email-cloaking in form displayed in content, when a default value is set
		if (isset ($field->initvalue) && ($field->initvalue != "")) {
			$field->initvalue = str_replace('@', '&#64', $field->initvalue);
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
					if ($name == 'attribute_required') {
						$attributeArray['aria-required'] = 'true';
					}
					if ($value || $name == 'attribute_class') {
						$newname = str_replace('attribute_', "", $name);
						if ($newname == "class") {
							$value = $value . $this->field->fieldCSSclass;
							if ((isset($this->field->textareaRequired) && $this->field->textareaRequired === true) || (isset($this->field->hasHTMLEditor) && $this->field->hasHTMLEditor == true)) {
								$value = "mce_editable";
							}
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
					$attributeArray['data-error-container-id'] = 'fc-tbx' . $value;
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
				if (isset($this->field->hasHTMLEditor) && $this->field->hasHTMLEditor) {
					//set some special attaribute for the textarea that is linked to the editor
					$attributeArray['style'] = "width: 97%; height: 200px;";
				}
				if (!isset($attributeArray['cols']) || $attributeArray['cols'] == "") {
					$attributeArray['cols'] = "10";
				}
				if (!isset($attributeArray['rows']) || $attributeArray['rows'] == "") {
					$attributeArray['rows'] = "20";
				}
				$attributeArray['aria-labelledby'] = $this->field->name . 'lbl';
			}
		}
		return $attributeArray;
	}

	public function setControlHtmlClasses($field) {
		$field->attribute_class = " form-control";
		if (!empty($field->custominfo)) {
			$field->attribute_title = htmlspecialchars($field->custominfo, ENT_COMPAT, 'UTF-8');
			$field->attribute_class .= ' visToolTip';
			JHtmlVisforms::visformsTooltip();
		}
		return $field;
	}

	public function setUikit3ControlHtmlClasses($field) {
		$field->attribute_class = " uk-textarea";
		if (!empty($field->custominfo)) {
			$field->attribute_title = htmlspecialchars($field->custominfo, ENT_COMPAT, 'UTF-8');
			$field->attribute_class .= ' visToolTip';
			JHtmlVisforms::visformsTooltip();
		}
		return $field;
	}

	public function setUikit2ControlHtmlClasses($field) {
		$field->attribute_class = " uk-textarea uk-width-1-1";
		return $field;
	}
}