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

class JFormFieldLabelgridwidth extends JFormFieldList
{
	protected $type = 'Labelgridwidth';
	protected $layout;

	public function setup(\SimpleXMLElement $element, $value, $group = null) {
		$this->setLayout();
		// Fix for JCE triggering preparing events on forms multiple times
		$test = isset ($element['default']);
		if ($this->layout == 'bt4mcindividual' && !$test) {
			$value = (empty($value)) ? 12 : $value;
			$element->addAttribute('default', "12");
		}
		if (($this->layout == 'uikit3') && !$test) {
			$value = (empty($value)) ? 6 : $value;
			$element->addAttribute('default', 6);
		}
		if (($this->layout == 'uikit2') && !$test) {
			$value = (empty($value)) ? 10 : $value;
			$element->addAttribute('default', 10);
		}
		return parent::setup($element, $value, $group);
	}

	protected function getOptions() {
		$options = array();
		//extract form id
		if ($this->layout == 'bt4mcindividual') {
			$options[] = $this->createOptionObj(1,12);
			$options[] = $this->createOptionObj(2,12);
			$options[] = $this->createOptionObj(3,12);
			$options[] = $this->createOptionObj(4,12);
			$options[] = $this->createOptionObj(6,12);
			$options[] = $this->createOptionObj(7,12);
			$options[] = $this->createOptionObj(8,12);
			$options[] = $this->createOptionObj(9,12);
			$options[] = $this->createOptionObj(10,12);
			$options[] = $this->createOptionObj(11,12);
			$options[] = $this->createOptionObj(12,12, true);
		}
		if ($this->layout == 'uikit3') {
			$options[] = $this->createOptionObj(1, 6);
			$options[] = $this->createOptionObj(2, 6);
			$options[] = $this->createOptionObj(3, 6);
			$options[] = $this->createOptionObj(4, 6);
			$options[] = $this->createOptionObj(5, 6);
			$options[] = $this->createOptionObj(6, 6, true);
		}
		if ($this->layout == 'uikit2') {
			$options[] = $this->createOptionObj(1, 10);
			$options[] = $this->createOptionObj(2, 10);
			$options[] = $this->createOptionObj(3, 10);
			$options[] = $this->createOptionObj(4, 10);
			$options[] = $this->createOptionObj(5, 10);
			$options[] = $this->createOptionObj(6, 10);
			$options[] = $this->createOptionObj(7, 10);
			$options[] = $this->createOptionObj(8, 10);
			$options[] = $this->createOptionObj(9, 10);
			$options[] = $this->createOptionObj(10, 10, true);
		}
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}

	private function createOptionObj($num, $max, $selected = false) {
		$o = new StdClass();
		$o->value = $num;
		$o->text = $num . ' / ' . $max . ' '. JText::_('COM_VISFORMS_OF_CONTROL_WIDTH');
		$o->disabled = false;
		$o->checked = $selected;
		$o->selected = $selected;
		return $o;
	}

	protected function setLayout() {
		$fid = JFactory::getApplication()->input->getInt('fid', 0);
		if(empty($fid)) {
			$this->layout = 'visforms';
			return;
		}
		$model = JModelLegacy::getInstance('Visform', 'VisformsModel', array('ignore_request' => true));
		$visform = $model->getItem($fid);
		$this->layout = $visform->layoutsettings['formlayout'];
	}
}
