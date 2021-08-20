<?php
/**
 * @package        Joomla.Site
 * @subpackage     mod_visforms
 * @copyright      Copyright (C) vi-solutions, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$base_dir = JPATH_SITE . '/components/com_visforms';
include_once JPATH_ADMINISTRATOR . '/components/com_visforms/include.php';

// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';

$language = JFactory::getLanguage();
$language->load('com_visforms', JPATH_ROOT . '/components/com_visforms', 'en-GB', true);
$language->load('com_visforms', JPATH_ROOT . '/components/com_visforms', null, true);
$language->load('com_visforms', JPATH_ROOT, 'en-GB', true);
$language->load('com_visforms', JPATH_ROOT, null, true);

$params->set('context', 'modvisform' . $module->id);
$visforms = modVisformsHelper::getForm($params);
if (empty($visforms)) {
	echo JText::_('COM_VISFORMS_FORM_MISSING');
	return false;
}
//check if user access level allows view
$user = JFactory::getUser();
$groups = $user->getAuthorisedViewLevels();
$access = (isset($visforms->access) && in_array($visforms->access, $groups)) ? true : false;
if ($access == false) {
	$app->setUserState('com_visforms.' . $visforms->context, null);
	echo JText::_('COM_VISFORMS_ALERT_NO_ACCESS');
	return false;
}

$menu_params = $params;
$correspondingMenuId = $params->get('connected_menu_item', '');
$formLink =  "index.php?option=com_visforms&task=visforms.send&id=" . $visforms->id . ((!empty($correspondingMenuId)) ? "&Itemid=" . $correspondingMenuId : "");

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
$app = JFactory::getApplication();
$app->setUserState('vis_send_once' . $params->get('catid'), "1");
$layout = $params->get('layout', 'default');
require JModuleHelper::getLayoutPath('mod_visforms', $layout);