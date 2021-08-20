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
require_once __DIR__ . '/formlayoutstate.php';

class VisformsFormLayout {
	private $form = null;
	private $formLayoutState = null;

	public function __construct($form) {
		$this->form = $form;
		$className = $this->checkLayoutClassExists($this->form->formlayout) ? 'FormLayoutState' . ucfirst($this->form->formlayout): 'FormLayoutStateVisforms';
		$this->setFormLayoutState(new $className());
	}

	public function getForm() {
		return $this->form;
	}

	public function updateForm($form) {
		$this->form = $form;
	}

	public function fixInvalidLayoutSelection () {
		return $this->formLayoutState->fixInvalidLayoutSelection($this);
	}

	public function setLayoutOptions() {
		return $this->formLayoutState->setLayoutOptions($this);
	}

	public function setFormLayoutState($layoutState) {
		$this->formLayoutState = $layoutState;
	}

	public function checkLayoutClassExists($name) {
		$className = 'FormLayoutState' . ucfirst($name);
		if (!class_exists($className)) {
			JLoader::register($className, __DIR__ . '/'. strtolower($className) . '.php');
			if (!class_exists($className)) {
				JLoader::register('FormLayoutStateVisforms', __DIR__  . '/'. strtolower('FormLayoutStateVisforms') . '.php');
				return false;
			}
		}
		return true;
	}
}