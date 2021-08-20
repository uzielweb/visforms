<?php
/**
 * Visforms Layout class Bootstrap default
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

/**
 * Set properties of a form field according to it's type and layout settings
 *
 * @package        Joomla.Site
 * @subpackage     com_visforms
 * @since          1.6
 */
class VisformsHtmllayoutBtdefault extends VisformsHtmllayout
{
	/**
	 * method to attach properties relevant for field display to field object
	 * @return object modified field
	 */
	public function prepareHtml() {
		//attach error messages array for javascript validation to field
		$this->setFieldCustomErrorMessageArray();
		$this->removeNoBootstrapClasses();
		$this->removeUnsupportedShowLabel();
		$this->setErrorId();
		$this->setFieldAttributeArray();
		$this->setFieldValidateArray();
		$this->setFieldControlHtml();
		return $this->field;
	}

	/**
	 * method to set values in attribute_class properties to empty string
	 */
	protected function removeNoBootstrapClasses() {
		if (method_exists($this->fieldHtml, 'removeNoBootstrapClasses')) {
			$this->field = $this->fieldHtml->removeNoBootstrapClasses($this->field);
		}
		else {
			//default implementation
			$this->field->attribute_class = "";
		}
	}

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
			$control = new VisformsHtmlControlDecoratorBtDefault($ocontrol);
		}
		//set field property
		$this->field->controlHtml = $control->getControlHtml();
	}
}