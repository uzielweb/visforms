<?php

/**
 * @author      Aicha Vack
 * @package     Joomla.Site
 * @subpackage  com_visforms
 * @link        http://www.vi-solutions.de
 * @copyright   2014 Copyright (C) vi-solutions, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die();

jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('hidden');
JHtml::_('bootstrap.framework');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('jquery.ui');
JHtml::_('jquery.ui', array('sortable'));

class JFormFieldItemlistcreator extends JFormFieldHidden
{
	protected $type='itemlistcreator';
	protected $hasSub;
    
	protected function getInput()
	{
		$version = VisformsAEF::getVersion(VisformsAEF::$subscription);
		$this->hasSub =  (version_compare($version, '3.3.2', 'ge')) ? true : false;
        $doc = JFactory::getDocument();
        $doc->addScript(JURI::root(true).'/administrator/components/com_visforms/js/itemlistcreator.js');
		$texts =  "{texts : {txtMoveUp: '" . addslashes(JText::_( 'COM_VISFORMS_ITEMLISTCREATOR_MOVE_UP' )). "',".
				"txtMoveDown: '" . addslashes(JText::_( 'COM_VISFORMS_ITEMLISTCREATOR_MOVE_DOWN' )). "',".
                "txtMoveDragDrop: '" . addslashes(JText::_( 'COM_VISFORMS_ITEMLISTCREATOR_MOVE_DRAG_AND_DROP' )). "',".
				"txtDelete: '" . addslashes(JText::_( 'COM_VISFORMS_DEL' )). "',".
                "txtCreateItem: '" . addslashes(JText::_( 'COM_VISFORMS_ITEMLISTCREATOR_CREATE_NEW_ITEM' )). "',".
                "txtAlertRequired: '" . addslashes(JText::_( 'COM_VISFORMS_ITEMLISTCREATOR_REQUIRED_LABEL_VALUE' )). "',".
                "txtItemsImported : '". addslashes(JText::_( 'COM_VISFORMS_IMPORT_OPTION_SUCCESS' )). "',".
                "txtReaderError : '" . addslashes(JText::_( 'COM_VISFORMS_INVALID_IMPORT_OPTIONS_FORMAT' )). "',".
                "txtNoDataToImport: '" . addslashes(JText::_( 'COM_VISFORMS_NO_DATA_TO_IMPORT' )). "',".
				"txtDescr: '" . addslashes(JText::_( 'COM_VISFORMS_SELECT_VALUE_DESC' )). "'".
			"},".
            " params: {fieldName : '" . $this->fieldname . "',".
                "idPrefix : 'jform_defaultvalue_',".
                "dbFieldExt : '_list_hidden',".
                "importField : '_importOptions', ".
                "importSeparator : '_importSeparator', ".
                "hdnMFlds : {".
					"listitemid:{'fname' : 'listitemid', 'ftype': 'hidden', 'frequired': false, 'fvalue' : ''},".
                    "listitemvalue:{'fname' : 'listitemvalue', 'ftype': 'text', 'frequired': true, 'fvalue' : ''},".
                    "listitemlabel:{'fname' : 'listitemlabel', 'ftype': 'text', 'frequired': true, 'fvalue' : ''},".
                    "listitemischecked:{'fname' : 'listitemischecked', 'ftype': 'checkbox', 'frequired': false, 'fvalue' : '1'},".
					"listitemredirecturl:{'fname' : 'listitemredirecturl', 'ftype': 'text', 'frequired': false, 'fvalue' : ''},".
					"listitemmail:{'fname' : 'listitemmail', 'ftype': 'text', 'frequired': false, 'fvalue' : ''},".
					"listitemmailcc:{'fname' : 'listitemmailcc', 'ftype': 'text', 'frequired': false, 'fvalue' : ''},".
					"listitemmailbcc:{'fname' : 'listitemmailbcc', 'ftype': 'text', 'frequired': false, 'fvalue' : ''}".
					(($this->fieldname != 'f_select_list_hidden') ? ", listitemlabelclass:{'fname' : 'listitemlabelclass', 'ftype': 'text', 'frequired': false, 'fvalue' : ''},": "").
                "},".
            //add ctype for custom use, where ctype is not field name based
            //"ctype : 'test'".
			"header: '". $this->createListHeader()."',".
			"items: '". $this->createExistingListItems()."',".
			"rowTemplate: '". $this->createEmptyRowTemplate()."',".
            "}".
            "}";
		$script = 'jQuery(document).ready(function() {jQuery("#jform_defaultvalue_'.$this->fieldname.'").createVisformsOptionCreator(' . $texts . ')});';
		$doc->addScriptDeclaration($script);
		
        $hiddenInput = parent::getInput();
		$html = $hiddenInput;
		
		return $html;
	}

	protected function createEmptyRowTemplate() {
		return '<tr class="liItem">' .
			'<td class="hiddenNotSortable"><span class="itemMove"><i class="icon-menu" title="'. JText::_( "COM_VISFORMS_ITEMLISTCREATOR_MOVE_DRAG_AND_DROP" ).'"></i></span></td>' .
			'<td class="hiddenSortable"><a class="itemUp"><i class="icon-arrow-up-3" title="'. JText::_( "COM_VISFORMS_ITEMLISTCREATOR_MOVE_UP" ).'"></i></a></td>' .
			'<td class="hiddenSortable"><a class="itemDown"><i class="icon-arrow-down-3" title="'. JText::_( "COM_VISFORMS_ITEMLISTCREATOR_MOVE_DOWN" ).'"></i></a></td>' .
			'<td><input type="hidden" class="itemlist listitemid" value="" /></td>' .
			'<td><input type="text" class="itemlist listitemvalue focus" value="" required="required" /></td>' .
			'<td><input type="text" class="itemlist listitemlabel" value="" required="required" /></td>' .
			'<td><input type="checkbox" class="itemlist listitemischecked" value="1"/></td>' .
			'<td><input type="text" class="itemlist listitemredirecturl" value=""'.((!$this->hasSub) ? " disabled=\"disabled\"" : "").' /></td>' .
			'<td><input type="text" class="itemlist listitemmail" value=""'.((!$this->hasSub) ? " disabled=\"disabled\"" : "").' /></td>' .
			'<td><input type="text" class="itemlist listitemmailcc" value=""'.((!$this->hasSub) ? " disabled=\"disabled\"" : "").' /></td>' .
			'<td><input type="text" class="itemlist listitemmailbcc" value=""'.((!$this->hasSub) ? " disabled=\"disabled\"" : "").' /></td>' .
			(($this->fieldname != 'f_select_list_hidden') ? '<td><input type="text" class="itemlist listitemlabelclass" value=""'.((!$this->hasSub) ? " disabled=\"disabled\"" : "").' /></td>' : '') .
			'<td><a class="itemRemove" href="#">'. JText::_( "COM_VISFORMS_DEL" ).'</a></td>' .
			'</tr>';
	}

	protected function createExistingListItems() {
		$data = $this->form->getValue($this->fieldname, 'defaultvalue');
		$html = array();
		if (!empty($data)) {
			$options = JHtmlVisformsselect::extractHiddenList($data);
			if (is_array($options)) {
				foreach ($options as $option) {
					$checked = $option['selected'] ? 'checked="checked"' : '';
					$html[] = '<tr class="liItem">' .
						'<td class="hiddenNotSortable"><span class="itemMove"><i class="icon-menu" title="'. JText::_( "COM_VISFORMS_ITEMLISTCREATOR_MOVE_DRAG_AND_DROP" ).'"></i></span></td>' .
						'<td class="hiddenSortable"><a class="itemUp"><i class="icon-arrow-up-3" title="'. JText::_( "COM_VISFORMS_ITEMLISTCREATOR_MOVE_UP" ).'"></i></a></td>' .
						'<td class="hiddenSortable"><a class="itemDown"><i class="icon-arrow-down-3" title="'. JText::_( "COM_VISFORMS_ITEMLISTCREATOR_MOVE_DOWN" ).'"></i></a></td>' .
						'<td><input type="hidden" class="itemlist listitemid" value="'. $option['id'] .'" /></td>' .
						'<td><input type="text" class="itemlist listitemvalue" value="'. $option['value'] .'" required="required" /></td>' .
						'<td><input type="text" class="itemlist listitemlabel" value="'. $option['label'].'" required="required" /></td>' .
						'<td><input type="checkbox" class="itemlist listitemischecked" value="1"'.$checked.'/></td>' .
						'<td><input type="text" class="itemlist listitemredirecturl" value="'. (($this->hasSub) ? $option['redirecturl'] : "").'"'.((!$this->hasSub) ? " disabled=\"disabled\"":"").' /></td>' .
						'<td><input type="text" class="itemlist listitemmail" value="'. (($this->hasSub) ? $option['mail'] : "").'"'.((!$this->hasSub) ? " disabled=\"disabled\"":"").' /></td>' .
						'<td><input type="text" class="itemlist listitemmailcc" value="'. (($this->hasSub) ? $option['mailcc'] : "").'"'.((!$this->hasSub) ? " disabled=\"disabled\"":"").' /></td>' .
						'<td><input type="text" class="itemlist listitemmailbcc" value="'. (($this->hasSub) ? $option['mailbcc'] : "").'"'.((!$this->hasSub) ? " disabled=\"disabled\"":"").' /></td>' .
						(($this->fieldname != 'f_select_list_hidden') ? '<td><input type="text" class="itemlist listitemlabelclass" value="'. (($this->hasSub) ? $option['labelclass'] : "").'"'.((!$this->hasSub) ? " disabled=\"disabled\"":"").' /></td>' : '') .
						'<td><a class="itemRemove" href="#">'. JText::_( "COM_VISFORMS_DEL" ).'</a></td>' .
						'</tr>';
				}
			}
		}
		return addslashes(implode('', $html));
	}

	protected function createListHeader() {
		return addslashes('<tr class="liItemHeader">' .
            '<th class="itemMoveHeader hiddenNotSortable"></th>' .
            '<th class="itemUpHeader hiddenSortable"></th>' .
            '<th class="itemDownHeader hiddenSortable"></th>' .
			'<th class="itemIdHeader"></th>' .
            '<th class="itemlistheader">'. JText::_( "COM_VISFORMS_VALUE" ).' *</th>' .
            '<th class="itemlistheader">'. JText::_( "COM_VISFORMS_LABEL" ).' *</th>' .
            '<th class="itemlistheader">'. JText::_( "COM_VISFORMS_DEFAULT" ).'</th>' .
			'<th class="itemlistheader hasPopover" title="'.htmlspecialchars(JText::_("COM_VISFORMS_REDIRECTURL")).'" data-content="'.htmlspecialchars(JText::_("COM_VISFORMS_CUSTOM_REDRECTS_DESC")).'" data-placement="top">'. JText::_( "COM_VISFORMS_REDIRECTURL" ). ((!$this->hasSub) ? JText::_( "COM_VISFORMS_SUBSCRIPTION_ONLY" ) : "") .'</th>' .
			'<th class="itemlistheader hasPopover" title="'.htmlspecialchars(JText::_("COM_VISFORMS_CUSTOM_MAIL")).'" data-content="'.htmlspecialchars(JText::_("COM_VISFORMS_CUSTOM_MAIL_DESC")).'" data-placement="top">'. JText::_( "COM_VISFORMS_CUSTOM_MAIL" ). ((!$this->hasSub) ? JText::_( "COM_VISFORMS_SUBSCRIPTION_ONLY" ) : "") .'</th>' .
			'<th class="itemlistheader hasPopover" title="'.htmlspecialchars(JText::_("COM_VISFORMS_CUSTOM_MAILCC")).'" data-content="'.htmlspecialchars(JText::_("COM_VISFORMS_CUSTOM_MAIL_DESC")).'" data-placement="top">'. JText::_( "COM_VISFORMS_CUSTOM_MAILCC" ). ((!$this->hasSub) ? JText::_( "COM_VISFORMS_SUBSCRIPTION_ONLY" ) : "") .'</th>' .
			'<th class="itemlistheader hasPopover" title="'.htmlspecialchars(JText::_("COM_VISFORMS_CUSTOM_MAILBCC")).'" data-content="'.htmlspecialchars(JText::_("COM_VISFORMS_CUSTOM_MAIL_DESC")).'" data-placement="top">'. JText::_( "COM_VISFORMS_CUSTOM_MAILBCC" ). ((!$this->hasSub) ? JText::_( "COM_VISFORMS_SUBSCRIPTION_ONLY" ) : "") .'</th>' .
			(($this->fieldname != 'f_select_list_hidden') ? '<th class="itemlistheader hasPopover" title="'.htmlspecialchars(JText::_("COM_VISFORMS_LABEL_CSS_CLASS")).'" data-content="'.htmlspecialchars(JText::_("COM_VISFORMS_LABEL_CSS_CLASS_DESC")).'" data-placement="top">'. JText::_( "COM_VISFORMS_LABEL_CSS_CLASS" ). ((!$this->hasSub) ? JText::_( "COM_VISFORMS_SUBSCRIPTION_ONLY" ) : "") .'</th>' : '') .
            '<th class="itemRemoveHeader">'. JText::_( "COM_VISFORMS_DEL" ).'</th>' .
			'</tr>');
	}
	
}