<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

class JFormFieldModal_Visdata extends JFormField
{
	protected $type = 'Modal_Visdata';

	protected function getInput() {
		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal');

		// set id of selected record set (from modal window) in parent view
		// options in some selects depend on which form was selected for the data view
		// reload options depending on form selection
		// show/hide data detail selection depending on wether a form is selected or not
		$script = array();
		$script[] = '	function jSelectVisdata_'.$this->id.'(id, title, object) {';
		$script[] = '		document.getElementById("'.$this->id.'_id").value = id;';
		$script[] = '		document.getElementById("'.$this->id.'_name").value = title;';
		$script[] = '		jQuery("#modalVisform' . $this->id . '").modal("hide");';
		$script[] = '       setFormId(id)';
		$script[] = '	};
		function setFormId(fid) {
			jQuery.ajax({
				url: "index.php?option=com_visforms&task=visform.getSortOrderFieldOptions&fid=" + fid + "&'.JSession::getFormToken().'=1",
				dataType: "html"
			}).done(function(data) {
				jQuery("#jform_params_sortorder option").each(function() {jQuery(this).remove()});
				jQuery("#jform_params_sortorder").append(data);
				jQuery("#jform_params_sortorder").trigger("liszt:updated");
			});
			jQuery("#jform_request_cid_id").val("");
			jQuery("#jform_request_cid_name").val("Datensatz wählen");
			var id = jQuery("#'.$this->id.'_id").val(); document.getElementById("'.$this->id.'_id").value
			if (id) {
				jQuery("#jform_request_cid_id").parents(".control-group").show();
			}
			else {
				jQuery("#jform_request_cid_id").parents(".control-group").hide();
			}
		};';

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));


		// Setup variables for display.
		$html	= array();
		$link	= 'index.php?option=com_visforms&amp;view=visforms&amp;layout=modal_data&amp;tmpl=component&amp;function=jSelectVisdata_'.$this->id;

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('title'))
			->from($db->quoteName('#__visforms'))
			->where($db->qn('id') . ' = ' . (int) $this->value);
		$db->setQuery($query);

		try {
			$title = $db->loadResult();
		}
		catch (RuntimeException $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		if (empty($title)) {
			$title = JText::_('COM_VISFORMS_CHOOSE_FORM');
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The active article id field.
		if (0 == (int)$this->value) {
			$value = '';
		} else {
			$value = (int)$this->value;
		}
        
        // The current form display field.
		$html[] = '<span class="input-append">';
		$html[] = '<input type="text" class="input-medium" id="'.$this->id.'_name" value="'.$title.'" disabled="disabled" size="35" />';
		$html[] = '<a href="#modalVisform' . $this->id . '" class="btn hasTooltip" role="button"  data-toggle="modal" title="'
			. JHtml::tooltipText('COM_VISFORMS_CHANGE_FORM') . '">'
			. '<span class="icon-file"></span> '
			. JText::_('JSELECT') . '</a>';
		$html[] = '</span>';

// class='required' for client side validation
		$class = '';
		if ($this->required) {
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="'.$this->id.'_id"'.$class.' name="'.$this->name.'" value="'.$value.'" />';

		$html[] = JHtml::_(
			'bootstrap.renderModal',
			'modalVisform' . $this->id,
			array(
				'url' => $link,
				'title' => JText::_('COM_VISFORMS_CHANGE_FORM'),
				'width' => '800px',
				'height' => '300px',
				'footer' => '<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">'
					. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</button>'
			)
		);

		return implode("\n", $html);
	}
}
