<?php
/**
 * Visforms HTML class for selects
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
 * Create HTML of a select according to it's type
 *
 * @package        Joomla.Site
 * @subpackage     com_visforms
 * @since          1.6
 */
class VisformsHtmlSelect extends VisformsHtml
{
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
							$attributeArray[$newname] .= $value;
						}
						else {
							$attributeArray[$newname] = $value;
						}
					}
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
		$field->attribute_class = " uk-select";
		if (!empty($field->custominfo)) {
			$field->attribute_title = htmlspecialchars($field->custominfo, ENT_COMPAT, 'UTF-8');
			$field->attribute_class .= ' visToolTip';
			JHtmlVisforms::visformsTooltip();
		}
		return $field;
	}

	public function setUikit2ControlHtmlClasses($field) {
		$field->attribute_class = " uk-select uk-width-1-1";
		return $field;
	}

	public function removeUnsupportedShowLabel($field) {
		unset($field->show_label);
		return $field;
	}
}