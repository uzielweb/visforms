<?php
/**
 * Visforms
 *
 * @author       Ingmar Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2018 vi-solutions
 * @since        Joomla 3.0.0
 */

defined('_JEXEC') or die('Restricted access');

?>
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&#215;</button>
		<h3><?php echo JText::_($displayData['title']);?></h3>
	</div>
	<div class="modal-body">
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="control-group span6">
                    <div class="controls"> <?php
                        $fid = JFactory::getApplication()->input->getInt( 'fid', -1 );
                        // create copy select options
                        $db = JFactory::getDbo();
                        $query = $db->getQuery(true);
                        $query->select('a.id, a.title');
                        $query->from('#__visforms AS a');
                        $db->setQuery($query);
                        $forms = $db->loadObjectList();
                        $options = array();
                        foreach ($forms as &$form){
                            $options[] = JHtml::_('select.option', $form->id, $form->title);
                        }
                        // Create the batch forms listbox, default selected value the form, the fields belong to.
                        ?>
                        <label id="batch-choose-action-lbl" for="batch-choose-action"><?php echo JText::_($displayData['label']); ?></label>
                        <fieldset id="batch-choose-action" class="combo">
                            <select name="batch[form_id]" class="inputbox hasTip" title="<?php echo JText::_($displayData['label']) . '::' . JText::_($displayData['description']); ?>" id="batch-form-id">
                                <?php echo JHtml::_('select.options', $options, 'value', 'text', $fid); ?>
                            </select>
                        </fieldset>
                    </div>
                </div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" type="button" onclick="document.getElementById('batch-form-id').value='<?php echo $fid; ?>'" data-dismiss="modal">
			<?php echo JText::_('JCANCEL'); ?>
		</button>
		<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('<?php echo $displayData['controller']; ?>.batch');">
			<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
		</button>
	</div>
</div>
