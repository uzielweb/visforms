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

if (!empty($displayData)) :
	if (isset($displayData['form'])) :
		$form = $displayData['form'];
		$context = (!empty($form->context)) ? '&context=' . $form->context : '';
		$class = 'captchacode' . ((isset($displayData['class'])) ? ' ' . $displayData['class'] : '');
		$captchaLink = Juri::root(true).'/'.htmlspecialchars('index.php?option=com_visforms&task=visforms.captcha&sid=c4ce9d9bffcf8ba3357da92fd49c2457&id=' . $form->id . $context,ENT_COMPAT, 'UTF-8');
		echo '<img id="captchacode' . $form->id . '" class="' . $class.  '" src="' . $captchaLink . '" align="absmiddle"> &nbsp; ';
	endif;
endif;
