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

abstract class createModelData
{
	protected $model;
	protected $item;
	protected $form;
	protected $data;
	protected $id;
	protected $isNew;

	// construction

	public function __construct() {}

	// getter and setter

	public function getData() {return $this->data; }

	public function getId() { return $this->id; }

	public function getIsNew() { return $this->isNew; }

	// interface

	public abstract function createObject();

	public function setDataArray($data = array()) {
		$this->data = array_merge($this->data, $data);
	}

	public function setParameter($name, $value) {
		$this->data[$name] = $value;
	}

	public function setGroupParameter($group, $name, $value) {
		$this->data[$group][$name] = $value;
	}

	public function saveObject() {
		// save new form
		$this->model->save($this->data);
		// get newly created id
		$this->isNew = $this->model->getState($this->model->getName() . '.new');
		$this->id    = $this->model->getState($this->model->getName() . '.id');
		// load newly saved item
		$this->item  = $this->model->getItem();
	}

	public function postSaveObjectHook() {}

	// implementation

	protected function addFieldSet($name, &$formData) {
		foreach ($this->form->getFieldset($name) as $field) {
			$type = $field->type;
			$value = $field->value;
			$default = $field->default;
			$setValue = ('' === $value ? (is_null($default) ? '' : $default) : $value);
			// no value for spacer
			if('spacer' === strtolower($type)) {
				continue;
			}
			// Remove all options which are set by a checkbox, except the required option, because they must not be stored with the field
			// (uncheckeck chechboxes are not submitted with POST)
			if('checkbox' === strtolower($type) && strpos($field->fieldname, '_attribute_required') === false) {
				continue;
			}
			// we have to render type aef to get the correct value (feature available or not)
			if('aef' === strtolower($type)) {
				$html = $field->getControlGroup();
				if(1 === preg_match('@value="(\d+)"@', $html, $matches)) {
					// set correct value to save
					$setValue = $matches[1];
				}
			}
			// with or without group (group name means database field name)
			if('' !== $field->group) {
				$formData[$field->group][$field->fieldname] = $setValue;
			}
			else {
				$formData[$field->fieldname] = $setValue;
			}
		}
	}
}