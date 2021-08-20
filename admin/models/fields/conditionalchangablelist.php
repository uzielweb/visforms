<?php
/**
 * Visform field typefield
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');
require_once JPATH_ADMINISTRATOR.'/components/com_visforms/models/visfield.php';
JLoader::register('VisformsAEF', JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/aef/aef.php');

/**
 * Form Field class for Visforms.
 * Supports list field types. 
 * Prevents a user from changing the selected option if the field has restrictions (it's values are used in conditional field parameter of other fields)
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldConditionalChangableList extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'ConditionalChangableList';

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput() {
		$html = array();
		$attr = '';

		// initialize some field attributes
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';

		// to avoid user's confusion, readonly="true" should imply disabled="true"
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true' || (string) $this->disabled == '1'|| (string) $this->disabled == 'true') {
			$attr .= ' disabled="disabled"';
		}
        // initialize JavaScript field attributes
        // different onclick handler if field is used in an equalTo statement
        $form = $this->form; 
        $label = $form->getFieldAttribute($this->fieldname, 'label');
        if (empty($label)) {
            $label = $form->getFieldAttribute($this->fieldname, 'label', null, 'defaultvalue');
        }

        // get restrictions
        $restrictions = $this->form->getData()->get('restrictions');
        if (!empty($restrictions) && $restrictions) {
            $rFieldNames = array ();
            foreach ($restrictions as $r => $value) {
                $rFieldNames[] = implode(', ', array_keys($value));           
            }
            $fieldNames = implode(', ', $rFieldNames);
            // as long as the restrictions are not empty we do not allow to change the typefield
            $attr .= ' onchange="fieldUsed(this, \'' . $this->value. '\', \'' . JText::sprintf(("COM_VISFORMS_FIELD_HAS_RESTICTIONS_JS"), $fieldNames, JText::_($label)) . '\')"';
        }
        else {
            // we allow typefield change
            $attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';
        }

		// Get the field options.
		$options = $this->getAllOptions();


		// create a read-only list (no name) with a hidden input to store the value
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true') {
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
		}
		else {
            // create a regular list
            $html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		}

		return implode($html);
	}

	protected function getOptions()
	{
		return parent::getOptions();
	}

	protected function createAefTypefieldOption($value, $label) {
		$option = new StdClass();
		$option->value = $value;
		$option->text = JText::_($label);
		$option->disabled = false || ($this->readonly && $value != $this->value);
		$option->checked = false;
		$option->selected = false;
		return $option;
	}

	// interface

	public function getAllOptions() {
		// get xml defined options
		$options = (array) $this->getOptions();

		// add AEF dependent options
		$hasMPFs = VisformsAEF::checkAEF(VisformsAEF::$multiPageForms);
		if (($this->fieldname == 'typefield') && (!empty($hasMPFs))) {
			$options[] = $this->createAefTypefieldOption('pagebreak', 'COM_VISFORMS_FIELD_PAGE_BREAK');
		}

		$hasCal = VisformsAEF::checkAEF(VisformsAEF::$customFieldTypeCalculation);
		if (($this->fieldname == 'typefield') && (!empty($hasCal))) {
			$options[] = $this->createAefTypefieldOption('calculation', 'COM_VISFORMS_FIELD_CALCULATION');
		}

		$hasLocation = VisformsAEF::checkAEF(VisformsAEF::$customFieldTypeLocation);
		if (($this->fieldname == 'typefield') && (!empty($hasLocation))) {
			$options[] = $this->createAefTypefieldOption('location', 'COM_VISFORMS_FIELD_LOCATION');
		}
		
		$hassignature = VisformsAEF::checkAEF(VisformsAEF::$customFieldTypeSignature);
		if (($this->fieldname == 'typefield') && (!empty($hassignature))) {
			$options[] = $this->createAefTypefieldOption('signature', 'COM_VISFORMS_FIELD_SIGNATURE');
		}

		$hasMinSub340 = VisformsAEF::getVersion(VisformsAEF::$subscription);
		if (($this->fieldname == 'typefield') && !empty($hasMinSub340) && version_compare($hasMinSub340, '3.4.0', 'ge')) {
			$options[] = $this->createAefTypefieldOption('radiosql', 'COM_VISFORMS_FIELD_RADIO_FROM_SQL');
			$options[] = $this->createAefTypefieldOption('selectsql', 'COM_VISFORMS_FIELD_SELECT_FROM_SQL');
			$options[] = $this->createAefTypefieldOption('multicheckboxsql', 'COM_VISFORMS_FIELD_MULTICHECKBOX_FROM_SQL');
		}

		return $options;
	}

	public function getCreatorInput($value = '', $disabled = false) {
		$this->disabled = $disabled;
		$this->class = 'creator-typefield';
		$this->onchange = '';
		$this->value = $value;
		return parent::getInput();
	}
}
