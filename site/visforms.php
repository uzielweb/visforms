<?php
/**
 * Form component for Joomla
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
include_once JPATH_ADMINISTRATOR . '/components/com_visforms/include.php';

$controller = JControllerLegacy::getInstance('Visforms');
$controller->execute(JFactory::getApplication()->input->get('task', 'display'));
$controller->redirect();

?>