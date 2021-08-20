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

use Joomla\CMS\HTML\HTMLHelper;

if (!empty($displayData)) {
	if (isset($displayData['form']) && (!empty($displayData['form']->hasSearchableSelect))) {
		HTMLHelper::_('visformsselect.loadSearchableApi');
	}
}