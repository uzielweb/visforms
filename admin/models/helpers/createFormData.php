<?php
/**
 * Visforms
 *
 * @author       Ingmar Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2018 vi-solutions
 * @since        Joomla 3.0.0
 */

defined('_JEXEC') or die('Restricted access');
require_once __DIR__ . '/createModelData.php';

class createFormData extends createModelData
{
	public function __construct() {
		// get form model
		$this->model = JModelLegacy::getInstance('Visform', 'VisformsModel', array('ignore_request' => true));
		// do not load the item
		$this->form  = $this->model->getForm(array(), false);
	}

	public function createObject() {
		// create parameter array with all default values set
		$formFieldSets = $this->form->getFieldsets();
		$formData = array();
		foreach ($formFieldSets as $name => $fieldSet) {
			$this->addFieldSet($name, $formData);
		}
		// set the class member
		$this->data = $formData;
	}

	public function postSaveObjectHook() {
		if (!$this->model->createDataTables($this->id, $this->data['saveresult'])) {
			// todo: handle error
		}
	}
}