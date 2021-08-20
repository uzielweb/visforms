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
<table id="table-creator-<?php echo $displayData['tag']; ?>" class="table table-striped table-hover table-bordered table-condensed" style="position:relative">
	<thead>
	<tr>
        <th style="text-align: center" class="hasTooltip hiddenNotSortable" title="<?php echo JText::_('COM_VISFORMS_ITEMLISTCREATOR_MOVE_DRAG_AND_DROP'); ?>"></th>
        <th style="text-align: center" class="hasTooltip hiddenSortable" title="<?php echo JText::_('COM_VISFORMS_MOVE_DESC'); ?>"><?php echo JText::_('COM_VISFORMS_MOVE'); ?></th>
		<th width="3%" class="nowrap center">
			<div class="checkbox" style="float: left"><label class="hasTooltip" style="font-weight: bold" data-original-title="<?php echo JText::_('COM_VISFORMS_CREATOR_CB_CREATE_DESC'); ?>"><?php
				echo str_replace('/>', ' checked/>', JHtml::_('grid.checkall', 'checkall-toggle', '', "Joomla.checkAll(this, '".$displayData['char'].'cb'."')")); ?>
					<?php echo JText::_('COM_VISFORMS_CREATOR_CB_CREATE_LABEL'); ?></label></div>
		</th>
		<th title="" class="hasTooltip"><?php echo JText::_('COM_VISFORMS_FIELD_TYPE'); ?></th>
		<th title="<?php echo JText::_('COM_VISFORMS_NAME_DESC'); ?>" class="hasTooltip"><?php echo JText::_('COM_VISFORMS_NAME'); ?></th>
		<th title="<?php echo JText::_('COM_VISFORMS_LABEL_DESCR'); ?>" class="hasTooltip"><?php echo JText::_('COM_VISFORMS_LABEL'); ?></th>
		<th width="3%" class="nowrap center">
			<div class="checkbox" style="float: left"><label class="hasTooltip" style="font-weight: bold" data-original-title="<?php echo JText::_('COM_VISFORMS_CREATOR_FIELD_FRONTEND_DISPLAY_DESCR'); ?>"><?php
				echo str_replace('/>', '/>', JHtml::_('grid.checkall', 'checkall-toggle', '', "Joomla.checkAll(this, '".$displayData['char'].'fb'."')"));  echo ' ' . JText::_('COM_VISFORMS_FRONTEND_DISPLAY_SIGN'); ?></label></div>
		</th>
        <th width="3%" class="nowrap center" title="">
            <div class="checkbox" style="float: left"><label class="hasTooltip" style="font-weight: bold" data-original-title="<?php echo JText::_('COM_VISFORMS_CREATOR_REQUIRED_DESC'); ?>"><?php
					echo str_replace('/>', '/>', JHtml::_('grid.checkall', 'checkall-toggle', '', "Joomla.checkAll(this, '".$displayData['char'].'rb'."')")); ?>
                    <?php echo JText::_('COM_VISFORMS_REQUIRED_SIGN'); ?> </label></div>
        </th>
		<th style="text-align: center" class="hasTooltip" title="<?php echo JText::_('COM_VISFORMS_CREATOR_DEL_DESC'); ?>"></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($displayData['data'] as $i => $row) {
        $disabled = false;
		// field selection
		if('submit' == $row[0]) {
			// create selected and unreachable for header function 'select/deselect all'
			$checked = str_replace('/>', ' checked disabled/>', JHtml::_('grid.id', $i, $i, false, 'xid', 'xb'));
			// no delete for the submit button
			$delete = '<td style="text-align: center; vertical-align: middle;"></td>';
			$disabled = true;
		}
		else {
			$checked = str_replace('/>', ' checked/>', JHtml::_('grid.id', $i, $i, false, $displayData['char'].'cid', $displayData['char'].'cb'));
			$delete = $displayData['tdDelete'];
		}
		// field front end display
		if('submit' == $row[0] || 'reset' == $row[0]) {
			// no checkbox for these buttons
			$frontEndDisplay = '';
			$required = '';
		}
		else {
			$frontEndDisplay = str_replace('/>', '/>', JHtml::_('grid.id', $i, $i, false, $displayData['char'].'fid', $displayData['char'].'fb'));
			$required        = str_replace('/>', '/>', JHtml::_('grid.id', $i, $i, false, $displayData['char'].'rid', $displayData['char'].'rb'));
		} ?>
		<tr><?php
            echo $displayData['tdMove']; ?>
			<td class="center "><?php echo $checked; ?></td>
			<td><?php echo $displayData['form']->typefield->getCreatorInput($row[0], $disabled); ?></td>
			<td><input name="" id="" value="<?php echo $row[1]?>" class="inputbox required" size="50" placeholder="" maxlength="50" required="" aria-required="true" type="text"></td>
			<td><input name="" id="" value="<?php echo $row[2]?>" class="inputbox required" size="50" placeholder="" maxlength="50" required="" aria-required="true" type="text"></td>
			<td class="center"><?php echo $frontEndDisplay; ?></td>
			<td class="center"><?php echo $required; ?></td>
			<?php echo $delete; ?>
		</tr>
	<?php }; ?>
	</tbody>
</table>
