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
if (!empty($displayData) && isset($displayData['form']) && isset($displayData['label']) && isset($displayData['dbName']) && isset($displayData['listDirn']) && isset($displayData['listOrder']) && isset($displayData['context'])) {
	$form = $displayData['form'];
	$label = $displayData['label'];
	$dbName = 'a.' .$displayData['dbName'];
	$listDirn = $displayData['listDirn'];
	$listOrder = $displayData['listOrder'];
	$context = $displayData['context'];
	$sortFunctionName = 'vftableOrdering';
	//overhead field display parameter name i.e (displayid, displaycreated...)
	$name = (!empty($displayData['name'])) ? $displayData['name'] : '';
	$extension = (!empty($displayData['extension'])) ? $displayData['extension'] : 'component';
	$htmlTag = (!empty($displayData['htmlTag'])) ? $displayData['htmlTag'] : 'td';
	$class = (!empty($displayData['class'])) ? ' class="' . $displayData['class'] . '"' : '';
	$pparams = (!empty($displayData['pparams'])) ? $displayData['pparams'] : array();
	$unSortable = (!empty($displayData['unSortable'])) ? true : false;
	$task = 'visformsdata.display';
	$showLabel = false;
	switch ($extension) {
		case 'vfdataview' :
			$sortFunctionName .= $context;
			$task = '';
			if (!empty($name)) {
				$name .= '_plg';
				if (!empty($form->$name)) {
					$showLabel = true;
				}
			} else {
				$showLabel = true;
			}
			break;
		default:
			if (!empty($name)) {
				$name .= '_list';
				if (!empty($form->$name)) {
					$showLabel = true;
				}
			} else {
				$showLabel = true;
			}
			break;
	}
	if (!empty($showLabel)) {
		$label = JHtmlVisforms::sort($label, $dbName, $listDirn, $listOrder, $task, 'asc', '', $context . 'adminForm', $sortFunctionName, $unSortable);
		echo '<' . $htmlTag . $class . '>' . $label . '</' . $htmlTag . '>';
	}
}