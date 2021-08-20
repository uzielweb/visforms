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
$text = (!empty($displayData) && isset($displayData['text'])) ? $displayData['text'] : 'COM_VISFORMS_NOSCRIPT_ALERT_FORM';
echo '<noscript><div class="alert alert-danger">'.JText::_($text).'</div></noscript>';