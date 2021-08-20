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
?>

<div  id="importFormModal" class="modal hide fade">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3><?php echo JText::_('COM_VISFORMS_NEW_FILE_HEADER');?></h3>
    </div>
    <div class="modal-body">
        <div class="column">
            <form method="post" action="<?php echo JRoute::_("index.php?option=com_visforms&view=visform&task=visform.importform"); ?>" class="well" enctype="multipart/form-data">
                <fieldset>
                    <input type="hidden" class="address" name="address" />
                    <input type="file" name="files" required /><?php
					echo JHtml::_('form.token'); ?>
                    <input type="submit" value="<?php echo JText::_('COM_VISFORMS_IMPORT');?>" class="btn btn-primary" />
                </fieldset>
            </form>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('COM_VISFORMS_CLOSE'); ?></a>
    </div>
</div>
