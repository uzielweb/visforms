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
if (!empty($displayData) && isset($displayData['form']) && isset($displayData['label']) && isset($displayData['listDirn']) && isset($displayData['listOrder']) && isset($displayData['context'])) {
	$form = $displayData['form'];
	$label = $displayData['label'];
	$dbName = 'a.id';
	$listDirn = $displayData['listDirn'];
	$listOrder = $displayData['listOrder'];
	$context = $displayData['context'];
	//overhead field display parameter name i.e (displayid, displaycreated...)
	$name = 'displayid';
	$sortFunctionName = 'vftableOrdering';
	$extension = (!empty($displayData['extension'])) ? $displayData['extension'] : 'component';
	$htmlTag = (!empty($displayData['htmlTag'])) ? $displayData['htmlTag'] : 'td';
	$class = (!empty($displayData['class'])) ? ' class="' . $displayData['class'] . '"' : '';
	$pparams = (!empty($displayData['pparams'])) ? $displayData['pparams'] : array();
	$unSortable = (!empty($displayData['unSortable'])) ? true : false;
	$task = 'visformsdata.display';
	$displayDetail = false;
	$displayId = false;
	switch ($extension) {
		case 'vfdataview' :
			$sortFunctionName .= $context;
			$task = '';
			if ($form->displaydetail && (isset($pparams['displaydetail'])) && ($pparams['displaydetail'] == 'true')) {
				$displayDetail = true;
			}
			if ((isset($pparams[$name]) && $pparams[$name] == 'true' && isset($form->$name)) && !empty($form->$name)) {
				$displayId = true;
			}
			break;
		default:
			if ($form->displaydetail) {
				$displayDetail = true;
			}
			if ((isset($form->$name)) && (($form->$name == "1") || ($form->$name == "2"))) {
				$displayId = true;
			}
			break;
	}
	if (!empty($displayId)) {
		$label = JHtmlVisforms::sort($label, $dbName, $listDirn, $listOrder, $task, 'asc', '', $context . 'adminForm', $sortFunctionName, $unSortable);
	}
	else {
		$label = '';
	}
	if (!empty($displayDetail) || !empty($displayId)) {
		echo '<' . $htmlTag . $class . '>' . $label . '</' . $htmlTag . '>';
	}
}