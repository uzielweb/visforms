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

?>
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&#215;</button>
		<h3><?php echo JText::_('COM_VISFORMS_BATCH_OPTIONS'); ?></h3>
	</div>
    <div class="modal-body">
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="control-group span6">
                    <div class="controls">
                        <?php echo JHtml::_('batch.access'); ?>
                    </div>
                </div>
                <div class="control-group span6">
                    <div class="controls">
                        <?php echo JHtml::_('batch.language'); ?>
                    </div>
                </div>
            </div>
            <div class="row-fluid">
                <div class="control-group span12"><?php
                    // Create the copy/move options.
                    $options = array(JHtml::_('select.option', 'c', JText::_('JYes')),
                        JHtml::_('select.option', 'n', JText::_('JNo')));

                    // Create the batch selector to select whether to copy fields or not.
                    $lines = array('<label id="batch-choose-action-lbl" for="batch-choose-action">', JText::_('COM_VISFORMS_COPY_FIELDS'), '</label>',
                        '<fieldset id="batch-choose-action" class="combo">',
                        //show the radiolist with default 0
                        JHtml::_('select.radiolist', $options, 'batch[copy_fields]', '', 'value', 'text', 'c'), '</fieldset>');

                    echo implode("\n", $lines);
                    if ($hasPdf) {
                        // Create the batch selector to select whether to copy pdf-templates or not.
                        $lines = array('<label id="batch-choosepdf-action-lbl" for="batch-choosepdf-action">', JText::_('COM_VISFORMS_COPY_PDF_TEMPLATES'), '</label>',
                            '<fieldset id="batch-choose-pdf-action" class="combo">',
                            //show the radiolist with default 0
                            JHtml::_('select.radiolist', $options, 'batch[copy_pdf_templates]', '', 'value', 'text', 'c'), '</fieldset>');
                        echo implode("\n", $lines);
                    } ?>
                </div>
            </div>
        </div>
	</div>
    <div class="modal-footer">
        <button  class="btn" type="button" onclick="document.getElementById('batch-access').value='';document.getElementById('batch-language-id').value=''; document.getElementById('batch[copy_fields]c').checked=true;document.getElementById('batch[copy_fields]n').checked=false; document.getElementById('batch[copy_pdf_templates]c').checked=true;document.getElementById('batch[copy_pdf_templates]n').checked=false" data-dismiss="modal">
            <?php echo JText::_('JCANCEL'); ?>
        </button>
    <button  class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('visform.batch');">
		<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
	</button>
    </div>
</div>
