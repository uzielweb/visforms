<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

class JFormFieldModal_Visdatadetail extends JFormField
{
	protected $type = 'Modal_Visdatadetail';

	protected function getInput() {
		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal');

		// Build the script.
		$script = array();
		$script[] = '	function jSelectVisdatadetail_'.$this->id.'(id, title, object) {';
		$script[] = '		document.getElementById("'.$this->id.'_id").value = id;';
		$script[] = '		document.getElementById("'.$this->id.'_name").value = title;';
		$script[] = '		jQuery("#modalVisform' . $this->id . '").modal("hide");';
		$script[] = '	}';

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// modal select for data detail id
		// custom implementation because form id must be set in link according to form selection
		// hide option, as long as on form is selected
		$script = "
		jQuery(document).ready(function($) {
			var fid = $('#jform_request_id_id').val();
			var formselected = (fid) ? true : false;
			if (!formselected) {
				$('#jform_request_cid_id').parents('.control-group').hide();
			}
		   $('#modalVisformjform_request_cid').on('show.bs.modal', function() {
		       $('body').addClass('modal-open');
		       var modalBody = $(this).find('.modal-body');
		       var link = $('#modalDataDetailTrigger').attr('data-src');
		       var fid = $('#jform_request_id_id').val();
		       link += '&fid=' + fid;
		       modalBody.find('iframe').remove();
		       modalBody.prepend('<iframe class=\"iframe\" src=\"'+link+'\" name=\"".JText::_('COM_VISFORMS_CHOOSE_RECORD_SET')."\" height=\"300px\" width=\"800px\"></iframe>');
		   }).on('shown.bs.modal', function() {
		       var modalHeight = $('div.modal:visible').outerHeight(true),
		           modalHeaderHeight = $('div.modal-header:visible').outerHeight(true),
		           modalBodyHeightOuter = $('div.modal-body:visible').outerHeight(true),
		           modalBodyHeight = $('div.modal-body:visible').height(),
		           modalFooterHeight = $('div.modal-footer:visible').outerHeight(true),
		           padding = document.getElementById('modalVisformjform_request_id').offsetTop,
		           maxModalHeight = ($(window).height()-(padding*2)),
		           modalBodyPadding = (modalBodyHeightOuter-modalBodyHeight),
		           maxModalBodyHeight = maxModalHeight-(modalHeaderHeight+modalFooterHeight+modalBodyPadding);
		       var iframeHeight = $('.iframe').height();
		       if (iframeHeight > maxModalBodyHeight){;
		           $('.modal-body').css({'max-height': maxModalBodyHeight, 'overflow-y': 'auto'});
		           $('.iframe').css('max-height', maxModalBodyHeight-modalBodyPadding);
		       }
		   }).on('hide.bs.modal', function () {
		       $('body').removeClass('modal-open');
		       $('.modal-body').css({'max-height': 'initial', 'overflow-y': 'initial'});
		       $('.modalTooltip').tooltip('destroy');
		   });
		});
		";
		JFactory::getDocument()->addScriptDeclaration($script);


		// Setup variables for display.
		$html	= array();
		$link	= 'index.php?option=com_visforms&amp;view=visdatas&amp;layout=modal&amp;tmpl=component&amp;fid=1&amp;function=jSelectVisdatadetail_'.$this->id;
		$title = $this->value;
		if (empty($title)) {
			$title = JText::_('COM_VISFORMS_CHOOSE_RECORD_SET');
		}

		// The active article id field.
		if (0 == (int)$this->value) {
			$value = '';
		} else {
			$value = (int)$this->value;
		}
        
        // The current form display field.
		$html[] = '<span class="input-append">';
		$html[] = '<input type="text" class="input-medium" id="'.$this->id.'_name" value="'.$title.'" disabled="disabled" size="35" />';
		$html[] = '<a id="modalDataDetailTrigger" href="#modalVisform' . $this->id . '" class="btn hasTooltip" role="button"  data-toggle="modal" data-src="'.$link.'" title="'
			. JHtml::tooltipText('Datensatz ändern' ) . '" >'
			. '<span class="icon-file"></span> '
			. JText::_('JSELECT') . '</a>';
		$html[] = '</span>';


// class='required' for client side validation
		$class = '';
		if ($this->required) {
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="'.$this->id.'_id"'.$class.' name="'.$this->name.'" value="'.$value.'" />';
		$html[] = '<div id="modalVisform'. $this->id.'" tabindex="-1" class="modal hide fade">
		<div class="modal-header">
				<button type="button" class="close novalidate" data-dismiss="modal">×</button>
					<h3>Datensatz ändern</h3>
				</div>
			<div class="modal-body">
				</div>
			<div class="modal-footer">
				<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">'. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") .'</button></div>
		</div>';

		return implode("\n", $html);
	}
}
