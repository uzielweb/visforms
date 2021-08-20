<?php

/**
 * Visform field Selectfromdb
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 */

// no direct access
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;

require_once(JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/html/visforms.php');

JFormHelper::loadFieldClass('list');


class JFormFieldSelectfromdb extends JFormFieldList
{

	public $type = 'Selectfromdb';

	protected function getInput() {
		$attr = '';

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= $this->disabled ? ' disabled' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';
		$textfieldname = "a.title";
		$where = array();
		$order = '';
		$valueprefix = '';
		$textprefix = '';
		// Initialize JavaScript field attributes.
		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';
		if (!(empty($this->element['table']))) {
			$table = $this->getAttribute('table');
			unset($this->element['table']);
		}
		if (!(empty($this->element['textfieldname']))) {
			$textfieldname = $this->getAttribute('textfieldname');
			unset($this->element['textfieldname']);
		}
		if (!(empty($this->element['where']))) {
			$where[] = $this->getAttribute('where');
			unset($this->element['where']);
		}
		if (!(empty($this->element['singleform']))) {
			$where[] = 'fid = ' . $this->form->getData()->get('id');;
			unset($this->element['singleform']);
		}
		if (!(empty($this->element['order']))) {
			$order = $this->getAttribute('order');
			unset($this->element['order']);
		}
		if (!(empty($this->element['textprefix']))) {
			$textprefix = $this->getAttribute('textprefix');
			unset($this->element['textprefix']);
		}
		if (!(empty($this->element['valueprefix']))) {
			$valueprefix = $this->getAttribute('valueprefix');
			unset($this->element['valueprefix']);
		}
		// Get the field options.
		$options = array_merge($this->getOptions(), $this->addSubscriptionOptions());
		return JHtml::_('visforms.createSelectFromDb', $table, $this->name, $this->value, $attr, $options, $this->id, $textfieldname, $where, $order, $textprefix, $valueprefix);
	}

	// there is just no useful way to add conditional options in the form definition xml
	private function addSubscriptionOptions() {
		$subOptions = array();
		$hasSub = VisformsAEF::checkAEF(VisformsAEF::$subscription);
		if (empty($hasSub)) {
			return $subOptions;
		}
		switch ($this->fieldname) {
			case 'f_hidden_filluid' :
				$options = array(
					'url' => 'COM_VISFORMS_CURRENT_PAGE_URL',
					'2' => 'COM_VISFORMS_CONNECTED_USER_NAME',
					'3' => 'COM_VISFORMS_CONNECTED_USER_USERNAME',
					'usermail' => 'COM_VISFORMS_CONNECTED_USER_EMAIL',
					'address1' => 'COM_VISFORMS_CONNECTED_USER_PROFILE_FIELD_CITY',
					'address2' => 'COM_VISFORMS_CONNECTED_USER_PROFILE_FIELD_REGION',
					'city' => 'COM_VISFORMS_CONNECTED_USER_NAME',
					'region' => 'COM_VISFORMS_CONNECTED_USER_PROFILE_FIELD_COUNTRY',
					'postal_code' => 'COM_VISFORMS_CONNECTED_USER_PROFILE_FIELD_POSTAL_CODE',
					'phone' => 'COM_VISFORMS_CONNECTED_USER_PROFILE_FIELD_PHONE'
					);
				foreach ($options as $key => $label) {
					$option = new stdClass();
					$option->value = $key;
					$option->text = Text::_($label);
					$subOptions[] = $option;
				}
				break;
			default:
				break;
		}
		return $subOptions;
	}
}