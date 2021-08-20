<?php
/**
 * Visforms HTML class for fields
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

//load control html classes
JLoader::discover('VisformsHtml', dirname(__FILE__) . '/html/control/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControl', dirname(__FILE__) . '/html/control/decorator/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlDecorator', dirname(__FILE__) . '/html/control/decorator/decorators/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlVisforms', dirname(__FILE__) . '/html/control/default/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlBtdefault', dirname(__FILE__) . '/html/control/btdefault/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlBthorizontal', dirname(__FILE__) . '/html/control/bthorizontal/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlMcindividual', dirname(__FILE__) . '/html/control/mcindividual/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlEdit', dirname(__FILE__) . '/html/control/edit/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlEditbtdefault', dirname(__FILE__) . '/html/control/editbtdefault/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlEditbthorizontal', dirname(__FILE__) . '/html/control/editbthorizontal/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlEditmcindividual', dirname(__FILE__) . '/html/control/editmcindividual/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlBt3default', dirname(__FILE__) . '/html/control/bt3default/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlBt3horizontal', dirname(__FILE__) . '/html/control/bt3horizontal/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlBt3mcindividual', dirname(__FILE__) . '/html/control/bt3mcindividual/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlEditbt3default', dirname(__FILE__) . '/html/control/editbt3default/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlEditbt3horizontal', dirname(__FILE__) . '/html/control/editbt3horizontal/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlEditbt3mcindividual', dirname(__FILE__) . '/html/control/editbt3mcindividual/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlBt4mcindividual', dirname(__FILE__) . '/html/control/bt4mcindividual/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlEditbt4mcindividual', dirname(__FILE__) . '/html/control/editbt4mcindividual/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlUikit3', dirname(__FILE__) . '/html/control/uikit3/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlEdituikit3', dirname(__FILE__) . '/html/control/edituikit3/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlUikit2', dirname(__FILE__) . '/html/control/uikit2/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlEdituikit2', dirname(__FILE__) . '/html/control/edituikit2/', $force = true, $recurse = false);

/**
 * Create HTML of a form field according to it's type
 *
 * @package        Joomla.Site
 * @subpackage    com_visforms
 * @since        1.6
 */
abstract class VisformsHtml
{
	protected $type;
	protected $field;
	protected $decorable;
	protected $attribute_type;

	public function __construct($field, $decorable, $attribute_type) {
		$this->type = $field->typefield;
		$this->field = $field;
		$this->setDecorable($decorable);
		$this->setAttributeType($attribute_type);
		$this->setAttributePlaceholder();
		$this->addScriptToDoc();
	}

	public static function getInstance($field, $decorable = null) {
		if (!(isset($field->typefield))) {
			return false;
		}

		$classname = get_called_class() . ucfirst($field->typefield);
		if (!class_exists($classname)) {
			//try to register it
			JLoader::register($classname, dirname(__FILE__) . '/html/field/' . $field->typefield . '.php');
			if (!class_exists($classname)) {
				//return a default class?
				return false;
			}
		}
		//delegate to the appropriate subclass
		return new $classname($field, $decorable, $attribute_type = null);
	}

	abstract public function getFieldAttributeArray();

	public function setFieldValidateArray($field) {
		$validateArray = array();
		//validation rules are stored in xml-definition-fields with name that ends on _validate_rulename (i.e. _validate_minlength).
		//each form field is represented by a fieldset in xml-definition file
		foreach ($field as $name => $value) {

			if (!is_array($value)) {
				if (strpos($name, 'validate') !== false) {
					if ($value) {
						$newname = str_replace('validate_', "", $name);
						$validateArray[$newname] = $value;
					}
				}
				//user can use custom regex for custom field validation
				if (strpos($name, 'customvalidation') !== false) {
					if ($value) {
						$validateArray['customvalidation'] = "/" . $value . "/";
					}
				}
			}
		}
		if (count($validateArray) > 0) {
			$field->validateArray = $validateArray;
		}
		return $field;
	}

	public function getFieldType() {
		return $this->type;
	}

	public function getField() {
		return $this->field;
	}

	public function getErrorId($field) {
		return 'field' . $field->id;
	}

	public function setDecorable($state) {
		if (is_null($state)) {
			if (!(isset($this->decorable))) {
				$this->decorable = true;
			}
		} 
		else {
			$this->decorable = $state;
		}
	}

	public function getDecorable() {
		return $this->decorable;
	}

	protected function setAttributeType($type) {
		if (!is_null($type)) {
			$this->field->attribute_type = $type;
		}
	}

	protected function setAttributePlaceholder() {
		//show label is set to hide
		if (!empty($this->field->show_label)) {
			// no placeholder available for field
			if (isset($this->field->attribute_placeholder)) {
				if ($this->field->attribute_placeholder != "") {
					$this->field->attribute_placeholder = htmlspecialchars($this->field->attribute_placeholder, ENT_COMPAT, 'UTF-8');
				} 
				else {
					//set label text into placeholder
					if (isset($this->field->label)) {
						$this->field->attribute_placeholder = htmlspecialchars($this->field->label . ((isset($this->field->attribute_required) && !empty($this->field->showRequiredAsterix)) ? ' *' : ''), ENT_COMPAT, 'UTF-8');
					}
				}
			}
		}
	}

	protected function addScriptToDoc() {
		if (empty($this->field->customJs)) {
			return true;
		}
		if (!(is_array($this->field->customJs))) {
			return true;
		}
		$doc = JFactory::getDocument();
		foreach ($this->field->customJs as $script) {
			$doc->addScriptDeclaration($script);
		}
	}

	// ToDo seems not to be used anywhere
    public function getCustomErrorDivLayout($field) {
    	return false;
    }
}