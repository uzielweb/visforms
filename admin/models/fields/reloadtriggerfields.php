<?php
/**
 * Visform field parentoptionslist
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

class JFormFieldReloadtriggerfields extends JFormFieldList
{
	protected $type = 'Reloadtriggerfields';
	protected $isRestricted = array();
	protected $dbFields = array();

	protected function getOptions() {
		$options = array();
		$dbFields = $this->getFieldList();
		if (!empty($dbFields)) {
			foreach ($dbFields as $field) {
				$options[] = $this->createOptionObj($field);
			}
		}
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}

	private function createOptionObj($field) {
		$o = new StdClass();
		$o->value = 'field'.$field->id;
		$o->text = $field->label;
		$o->disabled = false;
		$o->checked = false;
		$o->selected = false;
		return $o;
	}

	protected function getFieldList() {
		$form = $this->form;
		$fid = $form->getValue('fid', '', 0);
		$id = $form->getValue('id', '', 0);
		if (empty($fid) || empty($id)) {
			return false;
		}
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn(array('id', 'typefield', 'label', 'restrictions', 'defaultvalue')))
			->from($db->qn('#__visfields'))
			->where($db->qn('fid') . ' = ' . $fid . ' AND' . $db->qn('published') . ' = 1' .
				' AND ' . $db->qn('typefield') . 'IN  (' . $db->quote('select') . ', ' . $db->quote('radio') . ', ' . $db->quote('checkbox') . ', ' . $db->quote('multicheckbox')  . ', ' . $db->quote('selectsql')  . ', ' . $db->quote('radiosql')  . ', ' . $db->quote('multicheckboxsql') . ')' .
				' AND NOT ' . $db->qn('editonlyfield') . ' = 1')
			->order($db->qn('label') . ' ASC');
		try {
			$db->setQuery($query);
			$this->dbFields =  $db->loadObjectList();
		}
		catch (RuntimeException $e) {
			return false;
		}
		if (!empty($this->dbFields)) {
			$this->removeDataListFields();
			$this->getRestrictedIds($id);
		}
		$allowedFields = array();
		foreach ($this->dbFields as $dbField) {
			if (!(in_array($dbField->id, $this->isRestricted))) {
				$allowedFields[] = $dbField;
			}
		}
		return $allowedFields;
	}

	private function getRestrictedIds($id) {
		//add id to list with restsricted id's.
		//on first call: don't show ourselfs in option list
		$this->isRestricted[] = $id;

		foreach ($this->dbFields as $field) {
			if ($field->id == $id) {
				//extract db field restrictions
				$restrictions = VisformsHelper::registryArrayFromString($field->restrictions);

				if (!isset($restrictions['usedAsReloadTrigger'])) {
					return;
				}

				//when we have a usedAsReloadTrigger item, call ourself with the id retrieved from $value
				foreach ($restrictions['usedAsReloadTrigger'] as $key => $value) {
					$this->getRestrictedIds($value);
				}
			}
		}
	}

	private function removeDataListFields() {
		if (empty($this->dbFields)) {
			return;
		}
		$count = count($this->dbFields);
		for ($i=0; $i < $count; $i++) {
			$defaultValues = VisformsHelper::registryArrayFromString($this->dbFields[$i]->defaultvalue);
			if (!empty($defaultValues['f_selectsql_render_as_datalist'])) {
				unset($this->dbFields[$i]);
			}
		}
		$this->dbFields = array_values($this->dbFields);
	}
}
