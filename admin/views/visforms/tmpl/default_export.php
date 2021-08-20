<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$hasPdf = VisformsAEF::checkAEF(VisformsAEF::$pdf);
$options = array(JHtml::_('select.option', 'c', JText::_('JYes')),
	JHtml::_('select.option', 'n', JText::_('JNo')));

?>
<div class="modal hide fade" id="exportFormModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&#215;</button>
		<h3><?php echo JText::_('COM_VISFORMS_FORM_EXPORT_OPTIONS'); ?></h3>
	</div>
    <div class="modal-body">
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="control-group span12"><?php
                    // Create the export selector to select whether to copy fields or not.
                    $lines = array('<label id="export-field-choose-action-lbl" for="export-field-choose-action">', JText::_('COM_VISFORMS_FORM_EXPORT_COPY_FIELDS'), '</label>',
	                    '<fieldset id="export-field-choose-action">',
                        //show the radiolist with default 0
                        JHtml::_('select.radiolist', $options, 'export[copy-fields]', '', 'value', 'text', 'c'),
                        '</fieldset>');

                    echo implode("\n", $lines);
	                $lines = array('<label id="export-data-choose-action-lbl" for="export-data-choose-action">', JText::_('COM_VISFORMS_FORM_EXPORT_COPY_DATA'), '</label>',
		                '<fieldset id="export-data-choose-action">',
		                //show the radiolist with default 0
		                JHtml::_('select.radiolist', $options, 'export[copy-data]', '', 'value', 'text', 'n'),
		                '</fieldset>');

	                echo implode("\n", $lines);
                    if ($hasPdf) {
                        // Create the batch selector to select whether to copy pdf-templates or not.
                        $lines = array('<label id="export-pdf-choose-action-lbl" for="export-pdf-choose-action">', JText::_('COM_VISFORMS_FORM_EXPORT_COPY_PDF_TEMPLATES'), '</label>',
	                        '<fieldset id="export-pdf-choose-action">',
                            //show the radiolist with default 0
                            JHtml::_('select.radiolist', $options, 'export[copy-pdf-templates]', '', 'value', 'text', 'c'),
	                    '</fieldset>');
                        echo implode("\n", $lines);
                    }
                    echo '<hr/><p class="alert alert-danger">'.JText::_('COM_VISFORMS_EXPORT_OPTIONS_WARNING_USERID_ACL').'</p>';
	                $lines = array('<label id="export-userid-choose-action-lbl" for="export-userid-choose-action">', JText::_('COM_VISFORMS_FORM_EXPORT_COPY_USERID'), '</label>',
		                '<fieldset id="export-userid-choose-action">',
		                //show the radiolist with default 0
		                JHtml::_('select.radiolist', $options, 'export[copy-userid]', '', 'value', 'text', 'n'),
		                '</fieldset>');

	                echo implode("\n", $lines);
	                $lines = array('<label id="export-acl-choose-action-lbl" for="export-acl-choose-action">', JText::_('COM_VISFORMS_FORM_EXPORT_COPY_ACL'), '</label>',
		                '<fieldset id="export-acl-choose-action">',
		                //show the radiolist with default 0
		                JHtml::_('select.radiolist', $options, 'export[copy-acl]', '', 'value', 'text', 'n'),
		                '</fieldset>');
	                echo implode("\n", $lines);
	                ?>
                </div>
            </div>
        </div>
	</div>
    <div class="modal-footer">
        <button  class="btn" type="button" onclick="document.getElementById('export[copy-fields]c').checked=true;document.getElementById('export[copy-fields]n').checked=false; document.getElementById('export[copy-data]c').checked=false;document.getElementById('export[copy-data]n').checked=true;document.getElementById('export[copy-pdf-templates]c').checked=true;document.getElementById('export[copy-pdf-templates]n').checked=false;document.getElementById('export[copy-userid]c').checked=false;document.getElementById('export[copy-userid]n').checked=true;document.getElementById('export[copy-acl]c').checked=false;;document.getElementById('export[copy-acl]n').checked=true;" data-dismiss="modal">
            <?php echo JText::_('JCANCEL'); ?>
        </button>
    <button  class="btn btn-primary" type="button" onclick="exportForm()" data-dismiss="modal">
		<?php echo JText::_('COM_VISFORMS_EXPORT_FORM_DEFINITION'); ?>
	</button>
    </div>
</div>
