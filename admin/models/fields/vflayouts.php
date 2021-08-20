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
JLoader::register('VisformsAEF', JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/aef/aef.php');

/**
 * Form Field class for Visforms.
 * Supports list field types.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldVflayouts extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Vflayouts';

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
		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';
		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true' || (string) $this->disabled == '1' || (string) $this->disabled == 'true') {
			$attr .= ' disabled="disabled"';
		}
		// Initialize JavaScript field attributes.
		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';
		// Get the field options.
		$options = (array) $this->getOptions();
		$hasBt3 = VisformsAEF::checkAEF(VisformsAEF::$bootStrap3Layouts);
		$hasBt4 = VisformsAEF::checkAEF(VisformsAEF::$bootStrap4Layouts);
		$hasUikit3 = VisformsAEF::checkAEF(VisformsAEF::$uikit3Layouts);
		$hasUikit2 = VisformsAEF::checkAEF(VisformsAEF::$uikit2Layouts);
		if (!empty($hasBt3)) {
			$options[] = $this->createOption('bt3default', 'COM_VISFORMS_LAYOUT_BOOTSTRAP3_DEFAULT');
		}
		if (!empty($hasBt4)) {
				$options[] = $this->createOption('bt4mcindividual', 'COM_VISFORMS_BOOTSTRAP4_MULTICOLUMN_INDIVIDUAL');
		}

		if (!empty($hasUikit2)) {
		$options[] = $this->createOption('uikit2', 'COM_VISFORMS_UIKIT2');
		}
		if (!empty($hasUikit3)) {
				$options[] = $this->createOption('uikit3', 'COM_VISFORMS_UIKIT3');
		}
		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true') {
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
		}
		else // Create a regular list.
		{
			$html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		}
		return implode($html);
	}

	protected function getOptions() {
		return parent::getOptions();
	}

	protected function createOption($value, $text) {
		$option = new stdClass();
		$option->value = $value;
		$option->text = JText::_($text);
		$option->disabled = false || ($this->readonly && $value != $this->value);
		$option->checked = false;
		$option->selected = false;
		return $option;
	}
}
