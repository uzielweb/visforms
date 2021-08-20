<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

if (JFactory::getApplication()->isSite()) {
	JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
}


JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.framework', true);

$function	= JFactory::getApplication()->input->getCmd('function', 'jSelectVisforms');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_visforms&view=visforms&layout=modal&tmpl=component&function='.$function.'&'.JSession::getFormToken().'=1');?>" method="post" name="adminForm" id="adminForm" class="form-inline">
	<fieldset class="filter">
		<div class="btn-toolbar">
			<div class="btn-group">
			<label for="filter_search">
				<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>
			</label>
            </div>
			<div class="btn-group">
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" size="30" title="<?php echo JText::_('COM_CONTENT_FILTER_SEARCH_DESC'); ?>" />
			</div>
			<div class="btn-group">
            	<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>" data-placement="bottom">
					<span class="icon-search"></span><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" data-placement="bottom" onclick="document.id('filter_search').value='';this.form.submit();">
					<span class="icon-remove"></span><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>
		</div>
		<hr class="hr-condensed" />
			

		<div class="filters">
			<select name="filter_access" class="input-medium" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
			</select>
			<select name="filter_published" class="input-medium" onchange="this.form.submit()">
                <?php $options = array(); 
                $options[] = JHtml::_('select.option', '', JText::_('JOPTION_SELECT_PUBLISHED'));
                $options[] = JHtml::_('select.option', '1', JText::_('JPUBLISHED'));
                $options[] = JHtml::_('select.option', '0', JText::_('JUNPUBLISHED'));
                ?>
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
			<select name="filter_language" class="input-medium" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'));?>
			</select>
		</div>
	</fieldset>

	<table class="table table-striped table-condensed">
		<thead>
			<tr>
				<th class="center nowrap">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th width="20%" class="nowrap center">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="nowrap center">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="center nowrap">
					<?php echo JHtml::_('grid.sort',  'JDATE', 'a.created', $listDirn, $listOrder); ?>
				</th>
				<th width="5%"class="center nowrap">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="15">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) : ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<a href="javascript:void(0)" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->title)); ?>');">
						<?php echo $this->escape($item->title); ?></a>
				</td>
				<td class="center">
					<?php echo $this->escape($item->access_level); ?>
				</td>
				<td class="center">
					<?php if ($item->language=='*'):?>
						<?php echo JText::alt('JALL', 'language'); ?>
					<?php else:?>
						<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
					<?php endif;?>
				</td>
				<td class="center nowrap">
					<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" /><?php
        $layout = new JLayoutFile('div.form_hidden_inputs');
        echo $layout->render(); ?>
	</div>
</form>
