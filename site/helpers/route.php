<?php
/**
 * Route Helper class    for visforms
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6
 */
defined('_JEXEC') or die;


abstract class VisformsHelperRoute
{
	public static function getDetailRoute($id, $formId, $language = 0) {
		// Create the link
		$link = 'index.php?option=com_visforms&view=visformsdata&layout=detailitem&id=' . $formId . '&cid=' . $id;
		if ($language && $language !== '*' && JLanguageMultilang::isEnabled()) {
			$link .= '&lang=' . $language;
		}
		return $link;
	}
}
