<?php
/**
 * Visforms Layout class Bootstrap horizontal
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
require_once(__DIR__ . '/btdefault.php');

/**
 * Set properties of a form field according to it's type and layout settings
 *
 * @package        Joomla.Site
 * @subpackage     com_visforms
 * @since          1.6
 */
class VisformsHtmllayoutBthorizontal extends VisformsHtmllayoutBtdefault
{
	/**
	 * Methode to set the html string as a field property
	 */
	protected function setFieldControlHtml() {
		//get Instance of field html control class occoriding to field type and layout type
		$ocontrol = VisformsHtmlControl::getInstance($this->fieldHtml, $this->type);
		if (!(is_object($ocontrol))) {
			//throw an error
		}
		else {
			//instanciate decorators
			$control = new VisformsHtmlControlDecoratorBthorizontal($ocontrol);
		}
		//set field property
		$this->field->controlHtml = $control->getControlHtml();
	}
}