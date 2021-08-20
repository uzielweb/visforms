<?php
/**
 * Visforms field fieldseparator class
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

class VisformsFieldFieldsep extends VisformsField
{
	protected function setField() {
		//preprocessing field
		$this->extractDefaultValueParams();
		$this->extractGridSizesParams();
		$this->extractRestrictions();
		$this->mendBooleanAttribs();
		$this->setIsConditional();
		$this->setCustomJs();
		$this->setFieldsetCounter();
		$this->setShowLabel();
	}

	protected function setFieldDefaultValue() {
		//Nothing to do
		return;
	}

	protected function setDbValue() {
		return;
	}

	protected function setRedirectParam() {
		return;
	}

	protected function setShowLabel() {
		$this->field->show_label = 1;
	}
}