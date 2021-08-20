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
?>
<table class="table visdata visdatatable visdatatabledetail"><?php
	$label = JLayoutHelper::render('visforms.datas.labels.row', array('form' => $this->form, 'label' => JText::_('COM_VISFORMS_ID'), 'name' => 'displayid', 'extension' => $this->extension, 'htmlTag' => $this->labelHtmlTag, 'class' => $this->labelClass . ' data-id'), null, array('component' => 'com_visforms'));
	$value = JLayoutHelper::render('visforms.datas.fields.defaultoverhead', array('form' => $this->form, 'text' => $this->item->id, 'name' => 'displayid', 'extension' => $this->extension, 'htmlTag' => $this->valueHtmlTag, 'class' => $this->valueClass . ' data-id', 'viewType' => $this->viewType), null, array('component' => 'com_visforms'));
	if (!empty($label) && !empty($value)) {
		echo '<tr class="tr-data-id">' . $label . $value . '</tr>';
		unset($label);
		unset($value);
	}
	if ($this->getLayout() === 'detailedit' && !empty($this->canPublish)) {
		$labelClass = (!empty($this->labelClass)) ? ' class="' . $this->labelClass . ' data-publish"' : 'data-publish';
		$valueClass = (!empty($this->valueClass)) ? ' class="' . $this->valueClass . ' data-publish"' : 'data-publish';
		echo '<tr class="tr-data-publish"><' . $this->labelHtmlTag . $labelClass . '>' . JText::_( 'JSTATUS' ) . ':</' . $this->labelHtmlTag . '>';
		echo '<' . $this->valueHtmlTag . $valueClass . '>' . ((!empty($this->item->published)) ? JText::_('JPUBLISHED') : JText::_('JUNPUBLISHED')) . '</' . $this->valueHtmlTag . '></tr>';
	}
	foreach ($this->fields as $rowField) {
		if (isset($rowField->frontdisplay) && ($rowField->frontdisplay == 1 || $rowField->frontdisplay == 3)) {
			$prop = "F" . $rowField->id;
			$texte = (isset($this->item->$prop)) ? $this->item->$prop : '';
			if (!empty($this->form->hideemptyfieldsindetail)) {
				if (VisformsHelper::checkValueIsEmpty($texte, $rowField->typefield, $this->form->detailcaliszero)) {
					$texte = "";
				}
			}
			if (empty($this->form->hideemptyfieldsindetail) || (!empty($this->form->hideemptyfieldsindetail) && !empty($texte))) {
				$label = JLayoutHelper::render('visforms.datas.labels.row', array('form' => $this->form, 'label' => $rowField->label, 'extension' => $this->extension, 'htmlTag' => $this->labelHtmlTag, 'class' => $this->labelClass . ' data-f'. $rowField->id), null, array('component' => 'com_visforms'));
				$value = JLayoutHelper::render('visforms.datas.fields', array('form' => $this->form, 'field' => $rowField, 'data' => $this->item, 'text' => $texte, 'htmlTag' => $this->valueHtmlTag, 'class' => $this->valueClass . ' data-f' . $rowField->id, 'extension' => $this->extension, 'view' => 'detail'), null, array('component' => 'com_visforms'));
				if (!empty($label) && !empty($value)) {
					echo '<tr class="tr-data-f'.$rowField->id.'">' . $label . $value . '</tr>';
				}
				unset($label);
				unset($value);
			}
		}
	}
	$label = JLayoutHelper::render('visforms.datas.labels.row', array('form' => $this->form, 'label' => JText::_('COM_VISFORMS_IP_ADDRESS'), 'name' => 'displayip', 'extension' => $this->extension, 'htmlTag' => $this->labelHtmlTag, 'class' => $this->labelClass . ' data-ip'), null, array('component' => 'com_visforms'));
	$value = JLayoutHelper::render('visforms.datas.fields.defaultoverhead', array('form' => $this->form, 'text' => $this->item->ipaddress, 'name' => 'displayip', 'extension' => $this->extension, 'htmlTag' => $this->valueHtmlTag, 'class' => $this->valueClass . ' data-ip', 'viewType' => $this->viewType), null, array('component' => 'com_visforms'));
	if (!empty($label) && !empty($value)) {
		echo '<tr class="tr-data-ip">' . $label . $value . '</tr>';
	}
	unset($label);
	unset($value);
	$label = JLayoutHelper::render('visforms.datas.labels.row', array('form' => $this->form, 'label' => JText::_('COM_VISFORMS_MODIFIED'), 'name' => 'displayismfd', 'extension' => $this->extension, 'htmlTag' => $this->labelHtmlTag, 'class' => $this->labelClass . ' data-mfd'), null, array('component' => 'com_visforms'));
	$value = JLayoutHelper::render('visforms.datas.fields.ismfd', array('form' => $this->form, 'text' => $this->item->ismfd, 'extension' => $this->extension, 'htmlTag' => $this->valueHtmlTag, 'class' => $this->valueClass . ' data-mfd', 'viewType' => $this->viewType), null, array('component' => 'com_visforms'));
	if (!empty($label) && !empty($value)) {
		echo '<tr class="tr-data-mfd">' . $label . $value . '</tr>';
	}
	unset($label);
	unset($value);
	$label = JLayoutHelper::render('visforms.datas.labels.row', array('form' => $this->form, 'label' => JText::_('COM_VISFORMS_SUBMISSIONDATE'), 'name' => 'displaycreated', 'extension' => $this->extension, 'htmlTag' => $this->labelHtmlTag, 'class' => $this->labelClass . ' data-created'), null, array('component' => 'com_visforms'));
	$value = JLayoutHelper::render('visforms.datas.fields.created', array('form' => $this->form, 'data' => $this->item, 'extension' => $this->extension, 'htmlTag' => $this->valueHtmlTag, 'class' => $this->valueClass . ' data-created', 'viewType' => $this->viewType), null, array('component' => 'com_visforms'));
	if (!empty($label) && !empty($value)) {
		echo '<tr class="tr-data-created">' . $label . $value . '</tr>';
	}
	unset($label);
	unset($value);
	$label = JLayoutHelper::render('visforms.datas.labels.row', array('form' => $this->form, 'label' => JText::_('COM_VISFORMS_MODIFICATION_DATE'), 'name' => 'displaymodifiedat', 'extension' => $this->extension, 'htmlTag' => $this->labelHtmlTag, 'class' => $this->labelClass . ' data-modifiedat'), null, array('component' => 'com_visforms'));
	$value = JLayoutHelper::render('visforms.datas.fields.modifiedat', array('form' => $this->form, 'data' => $this->item, 'extension' => $this->extension, 'htmlTag' => $this->valueHtmlTag, 'class' => $this->valueClass . ' data-modifiedat', 'viewType' => $this->viewType), null, array('component' => 'com_visforms'));
	if (!empty($label) && !empty($value)) {
		echo '<tr class="tr-data-modifiedat">' . $label . $value . '</tr>';
	}
	unset($label);
	unset($value);?>
</table>