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

$requiredText = (!empty($displayData) && !empty($displayData['requiredText'])) ? $displayData['requiredText'] : JText::_('COM_VISFORMS_REQUIRED');
echo '<label class="vis_mandatory visCSSbot10 visCSStop10">' . $requiredText . ' *</label>';
