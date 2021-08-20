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
//displaydata: form, data, extension, htmltag, class, pparams
if (!empty($displayData) && isset($displayData['form']) && isset($displayData['text']) && isset($displayData['name'])) {
	$form = $displayData['form'];
	$text = $displayData['text'];
	$name = $displayData['name'];
	$extension = (!empty($displayData['extension'])) ? $displayData['extension'] : 'component';
	$htmlTag = (!empty($displayData['htmlTag'])) ? $displayData['htmlTag'] : 'td';
	$class = (!empty($displayData['class'])) ? ' class="'.$displayData['class'] .'"' : '';
	$pparams = (!empty($displayData['pparams'])) ? $displayData['pparams'] : array();
	$viewType = (!empty($displayData['viewType'])) ? $displayData['viewType'] : 'column';
	$displayOverhead = false;
	switch ($extension) {
		case 'vfdataview' :
			$name .= '_plg';
			break;
		default:
			if ($viewType == 'column') {
				$name .= '_list';
			}
			if ($viewType == 'row') {
				$name .= '_detail';
			}
			break;
	}
	if (!empty($form->$name)) {
		$displayOverhead = true;
	}
	if (!empty($displayOverhead)) {
		echo '<' . $htmlTag . $class . '>' . $text . '</' . $htmlTag . '>';
	}
}