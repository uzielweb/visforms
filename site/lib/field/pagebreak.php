<?php
/**
 * Visforms field pagebreak class
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

class VisformsFieldPagebreak extends VisformsField
{
	protected function setField() {
		$this->extractDefaultValueParams();
		$this->extractGridSizesParams();
		$this->cleanFieldProperties();
		$this->setFieldDefaultValue();
		$this->setCustomJs();
		$this->setFieldsetCounter();
		$this->setMpDisplayType();
		if (empty($this->field->mpdisplaytype)) {
			$this->addFormStep();
		} else if ($this->field->mpdisplaytype == 1) {
			$this->setAccordionCounter();
			$this->setAccordionId();
			$this->setFirstPanelState();
			JHtml::_('bootstrap.framework');
		}
		$this->setBackBtnText();
		$this->setBackBtnCssClass();
	}

	protected function setFieldDefaultValue() {
		//Nothing to do for Submit buttons
		return;
	}

	protected function setDbValue() {
		return;
	}

	protected function setRedirectParam() {
		return;
	}

	protected function setBackBtnText() {
		$this->field->backbtntext = $this->form->backbtntext;
	}

	protected function setBackBtnCssClass() {
		$this->field->backbtncssclass = isset($this->form->backbtncssclass) ? $this->form->backbtncssclass : '';
	}

	protected function setMpDisplayType() {
		$this->field->mpdisplaytype = isset($this->form->mpdisplaytype) ? $this->form->mpdisplaytype : 0;
	}

	protected function setAccordionCounter() {
		if ((isset($this->form->accordioncounter)) && (is_numeric($this->form->accordioncounter))) {
			$this->form->accordioncounter++;
		} else {
			$this->form->accordioncounter = (int) 1;
		}
		$this->field->accordioncounter = $this->form->accordioncounter;
	}

	protected function setAccordionId() {
		$this->field->accordionid = (!empty($this->form->context)) ? $this->form->context . 'accordion' : 'visform' . $this->form->id . 'accordion';
	}

	protected function setFirstPanelState() {
		$this->field->firstpanelcollapsed = isset($this->form->firstpanelcollapsed) ? $this->form->firstpanelcollapsed : 0;
	}

	protected function cleanFieldProperties() {
		$this->field->customtext = '';
	}
}