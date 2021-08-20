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
if (!empty($displayData) && isset($displayData['text']) && isset($displayData['field'])) {
	$field = $displayData['field'];
	$text = $displayData['text'];
	$htmlTag = (!empty($displayData['htmlTag'])) ? $displayData['htmlTag'] : 'td';
	$class = (!empty($displayData['class'])) ? ' class="' . $displayData['class'] . '"' : '';
	$showLink = (isset($field->urlaslink) && !empty($field->urlaslink)) ? true : false;
	if (empty($text) || !$showLink) {
		echo '<' . $htmlTag . $class . '>' . $text . '</' . $htmlTag . '>';
	} else {
		echo '<' . $htmlTag . $class . '><a href="'.$text.'" target="_blank">' . $text . '</a></' . $htmlTag . '>';
	}
}