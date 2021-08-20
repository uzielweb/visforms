<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2018 vi-solutions
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.form.formfield');

class JFormFieldSignature extends JFormField {
	protected $type = 'Signature';

	protected function getInput()
	{
		$base30 = $this->value;
		$field = new stdClass();
		$field->canvasWidth = (isset($this->element['canvasWidth'])) ? (string) $this->element['canvasWidth'] : '280';
		$field->canvasHeight = (isset($this->element['canvasHeight'])) ? (string) $this->element['canvasHeight'] : '120';
		return JLayoutHelper::render('visforms.datas.fields.signature', array('field' => $field, 'data' => $base30, 'maxWidth' => 200), null, array('component' => 'com_visforms', 'client' => 'site'));
	}
}