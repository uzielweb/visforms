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
//displaydata: form, data, extension, htmltag, class, pparams
if (!empty($displayData) && isset($displayData['form']) && isset($displayData['text']) ) {
	$displayData['text'] = (empty($displayData['text'])) ? JText::_('JNO') : JText::_('JYES');
	$displayData['name'] = 'displayismfd';
	echo JLayoutHelper::render('visforms.datas.fields.defaultoverhead', $displayData, null, array('component' => 'com_visforms'));
}