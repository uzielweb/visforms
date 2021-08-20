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
class VisformsHtmllayoutMcindividual extends VisformsHtmllayout
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
		$this->setBootstrapSpanClasses();
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
			//use default
			foreach ($this->field as $name => $value) {
				if (!is_array($value)) {
					if (strpos($name, 'attribute_class') !== false) {
						$this->field->$name = "";
					}
				}
			}
		}
	}

	/*
	 * Method to make sure that field and label have proper span-x class that adds up to 12
	 */
	protected function setBootstrapSpanClasses() {
		if (method_exists($this->fieldHtml, 'setBootstrapSpanClasses')) {
			$this->field = $this->fieldHtml->setBootstrapSpanClasses($this->field);
		}
		else {
			//use default
			$regex = '/span(\d{1,2})/';
			$fmatchcount = 0;
			$lmatchcount = 0;
			if (!empty($this->field->fieldCSSclass) && is_string($this->field->fieldCSSclass)) {
				//fmatches[0] is array with all complete matches, fmatches[1] is array with numbers
				$fmatchcount = preg_match_all($regex, $this->field->fieldCSSclass, $fmatches);
			}
			if (!empty($this->field->labelCSSclass) && is_string($this->field->labelCSSclass)) {
				//fmatches[0] is array with all complete matches, fmatches[1] is array with numbers
				$lmatchcount = preg_match_all($regex, $this->field->labelCSSclass, $lmatches);
			}
			if (((isset($fmatchcount)) && ($fmatchcount == 0)) && ((isset($lmatchcount)) && ($lmatchcount == 0))) {
				$this->addSpanInClassAttribute();
				return;
			}
			if (((isset($fmatchcount)) && ($fmatchcount > 1)) || ((isset($lmatchcount)) && ($lmatchcount > 1))) {
				//to many span class attributes, remove all
				$this->removeSpanInClassAttribute();
				$this->addSpanInClassAttribute();
				return;
			}
			if (((isset($fmatchcount)) && ($fmatchcount == 0)) || ((isset($lmatchcount)) && ($lmatchcount == 0))) {
				$show_label = (isset($this->field->show_label) && $this->field->show_label == 1) ? false : true;
				if ($lmatchcount == 0) {
					$fieldspanwidth = $fmatches[1][0];
					if (($fieldspanwidth === "0") || ($fieldspanwidth > 12)) {
						//invalid span widht values
						$this->removeSpanInClassAttribute();
						$this->addSpanInClassAttribute();
						return;
					}
					if (!($show_label)) {
						return;
					}
					$labelspanwidth = 12 - $fieldspanwidth;
					//we expect a value between 0 and 11 and shift 0 to 12
					$labelspanwidth = ($labelspanwidth === "0") ? 12 : $labelspanwidth;
					$this->addSpanInClassAttribute($fieldspanwidth, $labelspanwidth, false, true);
					return;
				}
				if ($fmatchcount == 0) {
					$labelspanwidth = $lmatches[1][0];
					if (($labelspanwidth === "0") || ($labelspanwidth > 12)) {
						//invalid span widht value
						$this->removeSpanInClassAttribute();
						$this->addSpanInClassAttribute();
						return;
					}
					$fieldspanwidth = 12 - $labelspanwidth;
					//we expect a value between 0 and 11 and shift 0 to 12
					$fieldspanwidth = ($fieldspanwidth === "0") ? 12 : $fieldspanwidth;
					$this->addSpanInClassAttribute($fieldspanwidth, $labelspanwidth, true, false);
					return;
				}
			}
			if (((isset($fmatchcount)) && ($fmatchcount == 1)) && ((isset($lmatchcount)) && ($lmatchcount == 1))) {
				$fieldspanwidth = $fmatches[1][0];
				if (($fieldspanwidth === "0") || ($fieldspanwidth > 12)) {
					//invalid span widht values
					$this->removeSpanInClassAttribute();
					$this->addSpanInClassAttribute();
					return;
				}
				$labelspanwidth = $lmatches[1][0];
				if (($labelspanwidth === "0") || ($labelspanwidth > 12)) {
					//invalid span widht value
					$this->removeSpanInClassAttribute();
					$this->addSpanInClassAttribute();
					return;
				}
			}
		}
		return;
	}

	private function removeSpanInClassAttribute($field = true, $label = true) {
		$regex = '/span(\d{1,2})/';
		if ($field) {
			$this->field->fieldCSSclass = preg_replace($regex, '', $this->field->fieldCSSclass);
		}
		if ($label) {
			$this->field->labelCSSclass = preg_replace($regex, '', $this->field->labelCSSclass);
		}
	}

	private function addSpanInClassAttribute($fspanwidth = 8, $lspanwidth = 4, $field = true, $label = true) {
		$show_label = (isset($this->field->show_label) && $this->field->show_label == 1) ? false : true;
		$fspanwidth = ($show_label) ? $fspanwidth : 12;
		if ($field) {
			$this->field->fieldCSSclass .= " span" . $fspanwidth;
		}
		if ($label && $show_label) {
			$this->field->labelCSSclass .= " span" . $lspanwidth;
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
			$control = new VisformsHtmlControlDecoratorMcindividual($ocontrol);
		}
		//set field property
		$this->field->controlHtml = $control->getControlHtml();
	}
}