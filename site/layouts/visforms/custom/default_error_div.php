<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2017 vi-solutions
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if (!empty($displayData) && isset($displayData['field'])) {
	$field = $displayData['field'];
	if(empty($field->customErrorDivLayout)) {
		if (is_array($field->errorId)) {
			foreach ($field->errorId as $errorId) {
				echo '<div class="fc-tbx' . $errorId . '" data-error-field-id="' . $errorId . '"></div>';
			}
		} else {
			echo '<div class="fc-tbx' . $field->errorId . '" data-error-field-id="' . $field->errorId . '"></div>';
		}
	}
}