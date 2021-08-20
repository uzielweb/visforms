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

class createFieldData extends createModelData
{
	private $type;
	private $fid;

	public function __construct($fid) {
		$this->fid = $fid;
		// get field model
		$this->model = JModelLegacy::getInstance('Visfield', 'VisformsModel', array('ignore_request' => true));
		// get the filed model of the matching form
		$this->form  = $this->model->getForm(array("fid" => $this->fid), false);
	}

	public function setType($type) {
		$this->type = $type;
	}

	public function createObject() {
		// create parameter array with all default values set
		$formFieldSets = $this->form->getFieldsets();
		$formData = array();
		foreach ($formFieldSets as $name => $fieldSet) {
			$preFix = 'visf_';
			if($preFix === substr($name, 0, strlen($preFix))) {
				// here: all field type specific sets
				if($preFix . $this->type != $name) {
					// here: all wrong field type sets
					continue;
				}
			}
			$this->addFieldSet($name, $formData);
		}
		// we know the field's fid since the constructor: set it now
		$formData['fid'] = $this->fid;
		// set the class member
		$this->data = $formData;
	}

	public function postSaveObjectHook() {
		return;
	}
}