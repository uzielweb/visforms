<?php
/**
 * Visforms field multicheckbox business class
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
require_once(__DIR__ . '/select.php');

/**
 * Perform business logic on field multicheckbox
 *
 * @package        Joomla.Site
 * @subpackage     com_visforms
 * @since          1.6
 */
class VisformsBusinessMulticheckbox extends VisformsBusinessSelect
{
	protected function validatePostValue() {
		//rules for multicheckboxes are: minlength and maxlength
		//update $this->field with value from $this->fields
		$this->updateField();
		$valid = true;
		//check that we do not have to many selected values in user input
		if ((isset($this->field->attribute_maxlength)) && ($this->field->attribute_maxlength != "")) {
			$maxcount = (is_numeric($this->field->attribute_maxlength)) ? $this->field->attribute_maxlength : 1;
			$count = 0;
			//get count ouf selected options
			foreach ($this->field->opts as $opt) {
				if (isset($opt['selected']) && ($opt['selected'] == true)) {
					$count++;
				}
			}
			if (($count > 0) && VisformsValidate::validate('max', array('count' => $count, 'maxcount' => $maxcount)) == false) {
				//invalid value
				$valid = false;
				$error = JText::sprintf('COM_VISFORMS_FIELD_MAX_LENGTH_MULTICHECKBOX', $maxcount, $this->field->label);
				//attach error to form
				$this->setErrors($error);
				//only the last option will be displayed as selected in form
				//set selected to false except for the last selected option,
				$optCount = count($this->field->opts);
				for ($i = 0; $i < $optCount; $i++) {
					//unselect option
					if (isset($this->field->opts[$i]['selected']) && ($this->field->opts[$i]['selected'] == true) && $count > $maxcount) {
						$this->field->opts[$i]['selected'] = false;
						$count--;
					}
					//perform additional things, which may be necessary because of the wrong amount of selected values, when we reach the last option
					if ($i == ($optCount - 1)) {
						if (isset($this->field->isDisplayChanger) && ($this->field->isDisplayChanger == true)) {
							//mend isDisabled property in all depending fields (setIsDisabeld() is recursive)
							foreach ($this->fields as $child) {
								//only check for fields that are not $this->field
								if ($child->id != $this->field->id) {
									$this->setIsDisabled($child);
								}
							}
							break;
						} else {
							break;
						}
					}
				}
			}
		}

		//check for right minlength
		if ((isset($this->field->validate_minlength)) && ($this->field->validate_minlength != '')) {
			$mincount = (is_numeric($this->field->validate_minlength)) ? $this->field->validate_minlength : 0;
			$count = 0;
			//get count ouf selected options
			foreach ($this->field->opts as $opt) {
				if (isset($opt['selected']) && ($opt['selected'] == true)) {
					$count++;
				}
			}
			if (($count > 0) && VisformsValidate::validate('min', array('count' => $count, 'mincount' => $mincount)) == false) {
				//invalid value
				$valid = false;
				$error = JText::sprintf('COM_VISFORMS_FIELD_MIN_LENGTH_MULTICHECKBOX', $mincount, $this->field->label);
				//attach error to form
				$this->setErrors($error);
			}
		}

		//validate unique field value in database
		$this->validateUniqueValue();

		//at least one validation failed
		if (!$valid) {
			$this->field->isValid = false;
		}
	}
}