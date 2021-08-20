<?php
/**
 * Visforms field class
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

abstract class VisformsField
{
	protected $type;
	protected $field;
	protected $form;
	protected $value;
	protected $queryValue;
	protected $postValue;
	protected $input;
	protected $controlAttribs;

	public function __construct($field, $form) {
		$this->type = $field->typefield;
		$this->field = $field;
		$this->form = $form;
		$this->input = JFactory::getApplication()->input;
		//make fieldnames unique, allow use of one form more than once on one page
		if (!empty($this->form->context)) {
			$this->field->name = $this->form->context . $this->field->name;
		}
	}

	public static function getInstance($field, $form) {
		if (!(isset($field->typefield))) {
			return false;
		}

		$classname = get_called_class() . ucfirst($field->typefield);
		if (!class_exists($classname)) {
			//try to register it
			JLoader::register($classname, dirname(__FILE__) . '/field/' . $field->typefield . '.php');
			if (!class_exists($classname)) {
				//return a default class?
				return false;
			}
		}
		//delegate to the appropriate subclass
		return new $classname($field, $form);
	}

	public function getField() {
		$this->setField();
		return $this->field;
	}

	protected function extractDefaultValueParams() {
		$this->field->defaultvalue = VisformsHelper::registryArrayFromString($this->field->defaultvalue);
		foreach ($this->field->defaultvalue as $name => $value) {
			//make names shorter and set all default values as properties of field object
			$prefix = 'f_' . $this->field->typefield . '_';
			if (strpos($name, $prefix) !== false) {
				$key = str_replace($prefix, "", $name);
				$this->field->$key = $value;
			}
		}

        //delete defaultvalue array
        unset($this->field->defaultvalue);
    }

	protected function extractGridSizesParams() {

		$this->field->gridSizes = VisformsHelper::registryArrayFromString($this->field->gridSizes);
		foreach ($this->field->gridSizes as $key => $value) {
			$this->field->$key = $value;
		}
		unset($this->field->gridSizes);
	}

	protected function mendBooleanAttribs() {
		$attribs = array('required' => true, 'readonly' => 'readonly', 'checked' => 'checked');
		foreach ($attribs as $attrib => $value) {
			$attribname = 'attribute_' . $attrib;
			// Do not remove. Older fields may have set the attribute with empty walue!
			if (isset($this->field->$attribname) && ($this->field->$attribname == $attrib || $this->field->$attribname == '1' || $this->field->$attribname == true)) {
				$this->field->$attribname = $attrib;
			}
			else {
				unset($this->field->$attribname);
			}
		}
	}

	protected function extractRestrictions() {
		$this->field->restrictions = VisformsHelper::registryArrayFromString($this->field->restrictions);
	}

	protected function setIsConditional() {
		foreach ($this->field as $name => $avalue) {
			if (strpos($name, 'showWhen') !== false) {
				//as there can be more than one restrict, restricts are stored in an array
				if (is_array($avalue) && (count($avalue) > 0)) {
					foreach ($avalue as $value) {
						//if we have at least on restict with a field there is a condition set
						if (preg_match('/^field/', $value) === 1) {
							$this->field->isConditional = true;
							return true;
						}
					}
				}
			}

        }
        $this->field->isConditional = false;
        return true;
    }

	protected function setIsDisplayChanger() {
		if (isset($this->field->restrictions) && (is_array($this->field->restrictions))) {
			//loop through restrictions and check that there is at least one usedAsShowWhen restriction
			if (array_key_exists('usedAsShowWhen', $this->field->restrictions)) {
				$this->field->isDisplayChanger = true;
			}
		}
	}

	protected function setErrorMessageInForm($error) {
		if (!(isset($this->form->errors))) {
			$this->form->errors = array();
		}
		if (is_array($this->form->errors)) {
			array_push($this->form->errors, $error);
		}
	}

	protected function escapeCustomRegex() {
		if (!(isset($this->field->customvalidation))) {
			return;
		}
		$clean1 = str_replace("\/", "/", $this->field->customvalidation);
		$clean2 = str_replace("/", "\/", str_replace("\/", "/", $clean1));
		$this->field->customvalidation = $clean2;
	}

	public function setRecordId($cid) {
		$this->field->recordId = $cid;
	}

	protected function addFormStep() {
		if ((isset($this->form->steps)) && (is_numeric($this->form->steps))) {
			$this->form->steps++;
		} 
		else {
			$this->form->steps = 1;
		}
        if (empty($this->form->canHideSummaryOnMultiPageForms) && ($this->form->steps > 1)) {
            $this->form->displaysummarypage = true;
        }
    }

	protected function hasSearchableSelect() {
		if (!empty($this->field->is_searchable)) {
			$this->form->hasSearchableSelect = true;
		}
	}

	protected function setFieldsetCounter() {
		$this->field->fieldsetcounter = $this->form->steps;
	}

	// create empty array as field parameter
	// can be used in business class of the field
	protected function setCustomJs() {
		$this->field->customJs = array();
	}

    abstract protected function setRedirectParam();

    /**
     * Method to convert post values into a string that can be stored in db and attach it as property to the field object
     */
    abstract protected function setDbValue();

	//Set the default value of the field which is displayed in the form according field definition, query params, user inputs
	abstract protected function setFieldDefaultValue();

	protected function setQueryValue() {
		if ($this->form->displayState === VisformsModelVisforms::$displayStateIsNew) {
			$app = JFactory::getApplication();
			$task = $app->input->getCmd('task', '');
			if (($task !== 'editdata') && ($task !== 'saveedit')) {
				//using $this->input->get->get makes sure that the joomla! security functions are performed on the user inputs!
				//plugin form view sets get values as well
				$queryValue = $this->input->get->get($this->field->name, null, 'STRING');
				if (!is_null($queryValue)) {
					$urlparams = $app->getUserState('com_visforms.urlparams.' . $this->form->context);
					if (empty($urlparams)) {
						$urlparams = array();
					}
					$urlparams[$this->field->name] = $queryValue;
					$app->setUserState('com_visforms.urlparams.' . $this->form->context, $urlparams);
				}
			}
		}
	}

    protected function setEnterKeyAction() {
    	if (!empty($this->form->preventsubmitonenter)) {
    		$this->field->disableEnterKey = true;
	    }
    }

	protected function mendInvalidUncheckedValue() {
		$canCal = VisformsAEF::checkAEF(VisformsAEF::$customFieldTypeCalculation);
		if (!empty($canCal)) {
			if (!isset($this->field->unchecked_value) || $this->field->unchecked_value === "") {
				$this->field->unchecked_value = 0;
			}
			$this->field->unchecked_value = trim(str_replace(",", ".", $this->field->unchecked_value));
		}
	}

	protected function setShowRequiredAsterix() {
		if (!empty($this->form->requiredasterix)) {
			$this->field->showRequiredAsterix = true;
		}
	}

	protected function getParameterFieldNameForEditValue() {
		return "F" . $this->field->id;
	}
}