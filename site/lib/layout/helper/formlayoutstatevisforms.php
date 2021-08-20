<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2019 vi-solutions
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class FormLayoutStateVisforms implements FormLayoutState {
	public function fixInvalidLayoutSelection($formLayout) {
		// nothing to do
		return $formLayout->getForm();
	}

	public function setLayoutOptions($formLayout) {
		// nothing to do
		return $formLayout->getForm();
	}
}