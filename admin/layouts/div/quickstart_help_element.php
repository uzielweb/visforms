<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2019 vi-solutions
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
if (!empty($displayData) && isset($displayData['step'])) {
	$hideTextStep = isset($displayData['hideTextStep']) ? $displayData['hideTextStep'] : false;
	$step = (!$hideTextStep) ? JText::_('COM_VISFORMS_TXT_STEP') . ' ' . $displayData['step'] : $displayData['step'];
	$description = isset($displayData['description']) ? '<span class="circledNumberText"> ' . JText::_($displayData['description']) . '</span>' : '';
	$tag = isset($displayData['tag']) ? $displayData['tag'] : 'p';
	$component = JComponentHelper::getComponent('com_visforms');
	$hideQuickstart = $component->params->get('hideHelpBadges', '');

	if (!$hideQuickstart) {
		echo '<'.$tag.' class="quick-start-step-container"><span class="badge badge-warning quick-start-step">' . $step .'</span>'.$description.'</'.$tag.'>';
	}
	else {
		// ToDo enable, if success messages of creator are not displayes as alerts. container needed in creator for success messages, then
		// echo '<span class="quick-start-step-container"><span class="quick-start-step"></span></span>';
	}
}