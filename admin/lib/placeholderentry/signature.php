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

class VisformsPlaceholderEntrySignature extends VisformsPlaceholderEntry {

	public function getReplaceValue() {
		$field = $this->field;
		if (!isset($field->canvasWidth)) {
			$field->canvasWidth = isset($field['defaultvalue']['f_signature_canvasWidth']) ? (int) $field['defaultvalue']['v_signature_canvasWidth'] : 280;
		}
		if (!isset($field->canvasHeight)) {
			$field->canvasWidth = isset($field['defaultvalue']['f_signature_canvasHeight']) ? (int) $field['defaultvalue']['v_signature_canvasHeight'] : 120;
		}
		$layout             = new JLayoutFile('visforms.datas.fields.signature', null);
		$layout->setOptions(array('component' => 'com_visforms'));
		return $layout->render(array('field' => $field, 'data' => $this->rawData, 'maxWidth' => 200));
	}
}