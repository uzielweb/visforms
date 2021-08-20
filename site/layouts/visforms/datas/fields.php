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
if (!empty($displayData) && isset($displayData['form']) && isset($displayData['field']) && isset($displayData['data']) && isset($displayData['text']) && isset($displayData['view'])) {
	$form = $displayData['form'];
	$field = $displayData['field'];
	$data = $displayData['data'];
	$text = $displayData['text'];
	$view = $displayData['view'];
	$viewType = (!empty($displayData['viewType'])) ? $displayData['viewType'] : 'row';
	$extension = (!empty($displayData['extension'])) ? $displayData['extension'] : 'component';
	$htmlTag = (!empty($displayData['htmlTag'])) ? $displayData['htmlTag'] : 'td';
	$class = (!empty($displayData['class'])) ? $displayData['class'] : '';
	$pparams = (!empty($displayData['pparams'])) ? $displayData['pparams'] : array();
	switch ($field->typefield) {
		case 'email':
			echo JLayoutHelper::render('visforms.datas.fields.mail', array('text' => $text, 'htmlTag' => $htmlTag, 'class' => $class), null, array('component' => 'com_visforms'));
			break;
		case 'url' :
			echo JLayoutHelper::render('visforms.datas.fields.url', array('field' => $field, 'text' => $text, 'htmlTag' => $htmlTag, 'class' => $class), null, array('component' => 'com_visforms'));
			break;
		case 'file' :
			echo JLayoutHelper::render('visforms.datas.fields.file', array('field' => $field, 'text' => $text, 'htmlTag' => $htmlTag, 'class' => $class, 'viewType' => $viewType), null, array('component' => 'com_visforms'));
			break;
		case 'location' :
			$prop = "F" . $field->id;
			$makeUnique = (isset($displayData['makeLocationUnique'])) ? $displayData['makeLocationUnique'] : false;
			if (($view === 'detail' && !empty($field->displayAsMapInDetail)) || ($view === 'list' && !empty($field->displayAsMapInList))) {
				$form->mapCounter++;
			}
			$loadedApi = JFactory::getApplication()->input->getCmd('loadedApi', '');
			$gMapApiLoaded = ($loadedApi === 'gMap');
			echo JLayoutHelper::render('visforms.datas.fields.location', array('form' => $form, 'field' => $field, 'data' => $data->$prop, 'view' => $view, 'rowId' => $data->id, "gMapApiLoaded" => $gMapApiLoaded, 'htmlTag' => $htmlTag, 'class' => $class, 'makeUnique' => $makeUnique), null, array('component' => 'com_visforms'));
			break;
		case 'signature' :
			echo JLayoutHelper::render('visforms.datas.fields.signature', array('field' => $field, 'data' => $text, 'maxWidth' => 200, 'htmlTag' => $htmlTag, 'class' => $class), null, array('component' => 'com_visforms'));
			break;
		case 'textarea' :
			echo JLayoutHelper::render('visforms.datas.fields.textarea', array('field' => $field, 'text' => $text, 'viewType' => $viewType, 'extension' => $extension, 'htmlTag' => $htmlTag, 'class' => $class, 'pparams' => $pparams), null, array('component' => 'com_visforms'));
			break;
		default:
			echo JLayoutHelper::render('visforms.datas.fields.default', array('text' => $text, 'viewType' => $viewType, 'extension' => $extension, 'htmlTag' => $htmlTag, 'class' => $class, 'pparams' => $pparams), null, array('component' => 'com_visforms'));
			break;
	}
}