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
if (!empty($displayData) && isset($displayData['form']) && isset($displayData['data'])) {
	$form = $displayData['form'];
	$data = $displayData['data'];
	$extension = (!empty($displayData['extension'])) ? $displayData['extension'] : 'component';
	$htmlTag = (!empty($displayData['htmlTag'])) ? $displayData['htmlTag'] : 'td';
	$class = (!empty($displayData['class'])) ? ' class="' . $displayData['class'] . '"' : '';
	$pparams = (!empty($displayData['pparams'])) ? $displayData['pparams'] : array();
	$viewType = (!empty($displayData['viewType'])) ? $displayData['viewType'] : 'column';
	$displayModifiedAt = false;
	$displayModifiedAtTime = false;
	switch ($extension) {
		case 'vfdataview' :
			if (!empty($form->displaymodifiedat_plg)) {
				$displayModifiedAt = true;
			}
			if (!empty($form->displaymodifiedattime_plg)) {
				$displayModifiedAtTime = true;
			}
			break;
		default:
			if ($viewType == 'column' && !empty($form->displaymodifiedat_list)) {
				$displayModifiedAt = true;
			}
			if ($viewType == 'row' && !empty($form->displaymodifiedat_detail)) {
				$displayModifiedAt = true;
			}
			if ($viewType == 'column' && !empty($form->displaymodifiedattime_list)) {
				$displayModifiedAtTime = true;
			}
			if ($viewType == 'row' && !empty($form->displaymodifiedattime_detail)) {
				$displayModifiedAtTime = true;
			}
			break;
	}
	if (!empty($displayModifiedAt)) {
		if ($data->modified === '0000-00-00 00:00:00') {
			echo '<' . $htmlTag . $class . '></' . $htmlTag . '>';
		}
		else {
			$date = JFactory::getDate($data->modified, 'UTC');
			$date->setTimezone(new DateTimeZone(JFactory::getConfig()->get('offset')));
			if (!empty($displayModifiedAtTime)) {
				$formatedDate = $date->format(JText::_('DATE_FORMAT_LC4') . " H:i:s", true, false);
			}
			else {
				$formatedDate = $date->format(JText::_('DATE_FORMAT_LC4'), true, false);
			}
			echo '<' . $htmlTag . $class . '>' . $formatedDate . '</' . $htmlTag . '>';
		}
	}
}