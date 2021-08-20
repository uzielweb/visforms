<?php
/**
 * Visform field equalto
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

class JFormFieldEqualTo extends JFormFieldList
{
	protected $type = 'EqualTo';
	protected $restrictionType;
	protected $isRestricted = array();

	protected function getOptions() {
		$access = (string) $this->element['access'];
		if (!empty($access)) {
			switch ($access) {
				case "sub" :
					$hasSub = VisformsAEF::checkAEF(VisformsAEF::$subscription);
					if (empty($hasSub)) {
						return parent::getOptions();
					}
					break;
				default :
					break;
			}
		}
		$this->restrictionType = (string) $this->element['restriction'];
		$options = array();
		$form = $this->form;
		$fid = $form->getValue('fid', '', 0);
		$id = $form->getValue('id', '', 0);
		//get field type
		$typefield = $form->getValue('typefield', null, '');
		$fieldname = $form->getValue('name', null, '');
		//only add fieldtype specific otpions to the visible equalTo parameter of the selected field type not the hidden equalTo parameters of fieldtypes which are not selected currently!
		if (($fid != 0) && ($typefield != '') && ($fieldname != '') && (strpos($this->fieldname, 'f_' . $typefield) === 0)) {
			// Create options according to visfield settings
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->qn(array('id', 'label', 'restrictions')))
				->from($db->qn('#__visfields'))
				->where($db->qn('fid') . ' = ' . $fid . ' AND' . $db->qn('published') . ' = 1' .
					' AND' . $db->qn('typefield') . ' = ' . $db->quote($typefield) .
					' AND NOT ' . $db->qn('editonlyfield') . ' = 1');
			$db->setQuery($query);
			$fields = $db->loadObjectList();
			if ($fields) {
				//get id's of all restricted fields
				$this->getRestrictedIds($fields, $id);
				foreach ($fields as $field) {
					if (!(in_array($field->id, $this->isRestricted))) {
						$label = (!empty($this->element['olabel'])) ? JText::_($this->element['olabel']) . ' ' . $field->label : $field->label;
						$tmp = JHtml::_(
							'select.option', '#field' . $field->id,
							$label, 'value', 'text',
							false
						);

						// Add the option object to the result set.
						$options[] = $tmp;
					}
				}
			}
		}
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	private function getRestrictedIds($fields, $id) {
		//add id to list with restsricted id's.
		//on first call: don't show ourselfs in option list
		$this->isRestricted[] = $id;

		foreach ($fields as $field) {
			if ($field->id == $id) {
				//extract db field restrictions
				$restrictions = VisformsHelper::registryArrayFromString($field->restrictions);

				if (!isset($restrictions[$this->restrictionType])) {
					return;
				}

				//when we have a usedAsShowWhen item, call ourself with the id retrieved from $value
				foreach ($restrictions[$this->restrictionType] as $key => $value) {
					$this->getRestrictedIds($fields, $value);
				}
			}
		}
	}
}
