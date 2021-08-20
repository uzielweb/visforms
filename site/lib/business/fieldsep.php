<?php
/**
 * Visforms field fieldseparator business class
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

class VisformsBusinessFieldsep extends VisformsBusiness
{
	public function getFields() {
		$this->setField();
		return $this->fields;
	}

	protected function setField() {
		$this->setIsDisabled();
		if (isset($this->field->dataSource) && $this->field->dataSource == 'post') {
			$this->validatePostValue();
		}
		$this->addShowWhenForForm();
	}

	protected function validatePostValue() {
		//nothing to do
		return true;
	}

	public function validateRequired() {
		//nothing to do, just return the field
		return $this->field;
	}
}