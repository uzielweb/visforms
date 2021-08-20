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
if (!empty($displayData) && isset($displayData['form']) && isset($displayData['label'])) {
	$form = $displayData['form'];
	$label = $displayData['label'];
	//overhead field display parameter name i.e (displayid, displaycreated...)
	$name = (!empty($displayData['name'])) ? $displayData['name'] : '';
	$extension = (!empty($displayData['extension'])) ? $displayData['extension'] : 'component';
	$htmlTag = (!empty($displayData['htmlTag'])) ? $displayData['htmlTag'] : 'td';
	$class = (!empty($displayData['class'])) ? ' class="' . $displayData['class'] . '"' : '';
	$pparams = (!empty($displayData['pparams'])) ? $displayData['pparams'] : array();
	$showLabel = false;
	switch ($extension) {
		case 'vfdataview' :
			if (!empty($name)) {
				$name .= '_plg';
				if (!empty($form->$name)) {
					if (isset($pparams['showlabel']) && $pparams['showlabel'] == "true") {
						$showLabel = true;
					}
				}
			}
			else {
				if (isset($pparams['showlabel']) && $pparams['showlabel'] == "true") {
					$showLabel = true;
				}
			}
			break;
		default:
			if (!empty($name)) {
				$name .= '_detail';
				if (!empty($form->$name)) {
					$showLabel = true;
				}
			}
			else {
				$showLabel = true;
			}
			break;
	}
	if (!empty($showLabel)) {
		echo '<' . $htmlTag . $class . '>' . $label . ':</' . $htmlTag . '>';
	}
}