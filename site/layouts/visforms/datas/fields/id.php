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
if (!empty($displayData) && isset($displayData['form']) && isset($displayData['data']) && isset($displayData['link'])) {
	$form = $displayData['form'];
	$data = $displayData['data'];
	$link = $displayData['link'];
	$name = 'displayid';
	$plgIndex = (isset($displayData['plgIndex'])) ? $displayData['plgIndex'] : 0;
	$linkClass = 'visToolTip';
	$extension = (!empty($displayData['extension'])) ? $displayData['extension'] : 'component';
	$htmlTag = (!empty($displayData['htmlTag'])) ? $displayData['htmlTag'] : 'td';
	$class = (!empty($displayData['class'])) ? ' class="' . $displayData['class'] . '"' : '';
	$pparams = (!empty($displayData['pparams'])) ? $displayData['pparams'] : array();
	$viewType = (!empty($displayData['viewType'])) ? $displayData['viewType'] : 'column';
	//Do not put detail link on id or icon but render a separate textlink
	$addLinkAsText = (!empty($displayData['addLinkAsText'])) ? $displayData['addLinkAsText'] : false;
	$displayDetail = false;
	$displayId = false;
	$dataAttrib = '';
	// hidden checkbox used to submit cid[] with admin tasks like publish/unpublish/createPdf/Export csv
	$hiddenCheckbox = (isset($displayData['cbIndex'])) ? '<input id="cb' . $displayData['cbIndex'] . '" type="checkbox" value="' . $data->id . '" name="cid[]" style="display:none;">' : '';
	switch ($extension) {
		case 'vfdataview' :
			$linkClass .= ' showDetail' . $plgIndex;
			$dataAttrib = 'data-item-id="' . $data->id . '"';
			$name .= '_plg';
			if ($form->displaydetail && (isset($pparams['displaydetail'])) && ($pparams['displaydetail'] == 'true')) {
				$displayDetail = true;
			}
			if (!empty($form->$name)) {
				$displayId = true;
			}
			break;
		default:
			$name .= '_list';
			if ($form->displaydetail) {
				$displayDetail = true;
			}
			if (!empty($form->$name)) {
				$displayId = true;
			}
			break;
	}
	if (!empty($displayDetail)) {
		if (!empty($link)) {
			if (!empty($displayId)) {
				if (!empty($addLinkAsText)) {
					echo '<' . $htmlTag . $class . '>'. $hiddenCheckbox . $data->id . '</' . $htmlTag . '>';
					echo '<' . $htmlTag . $class . '><a class="' . $linkClass . '" href="' . $link . '" data-original-title="' . JText::_('COM_VISFORMS_VIEW_DETAIL') . '"' . $dataAttrib . '>' . JText::_('COM_VISFORMS_VIEW_DETAIL') . '</a></' . $htmlTag . '>';
				}
				else {
					echo '<' . $htmlTag . $class . '>'. $hiddenCheckbox . '<a class="' . $linkClass . '" href="' . $link . '" data-original-title="' . JText::_('COM_VISFORMS_VIEW_DETAIL') . '"' . $dataAttrib . '>' . $data->id . '</a></' . $htmlTag . '>';
				}
			}
			else {
				if (!empty($addLinkAsText)) {
					echo '<' . $htmlTag . $class . '>'. $hiddenCheckbox . '<a class="' . $linkClass . '" href="' . $link . '" data-original-title="' . JText::_('COM_VISFORMS_VIEW_DETAIL') . '"' . $dataAttrib . '>' . JText::_('COM_VISFORMS_VIEW_DETAIL') . '</a></' . $htmlTag . '>';
				}
				else {
					$detailicon = (!empty($form->detaillinkitem)) ? $form->detaillinkitem : 'download';
					echo '<' . $htmlTag . $class . '>'. $hiddenCheckbox . '<a class="' . $linkClass . '" href="' . $link . '" data-original-title="' . JText::_('COM_VISFORMS_VIEW_DETAIL') . '"' . $dataAttrib . '><i class="visicon-' . $detailicon . '"></i></a></' . $htmlTag . '>';
				}
			}
		}
		else {
			echo '<' . $htmlTag . $class . '>'. $hiddenCheckbox  . $data->id . '</' . $htmlTag . '>';
		}
	}
	else {
		if (!empty($displayId)) {
			echo '<' . $htmlTag . $class . '>' . $hiddenCheckbox . $data->id . '</' . $htmlTag . '>';
		}
		else {
			echo $hiddenCheckbox;
		}
	}
}