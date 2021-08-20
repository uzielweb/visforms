<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );
use Joomla\String\StringHelper;

if (JFactory::getApplication()->isSite()) {
	JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
}


JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.framework', true);

$function	= JFactory::getApplication()->input->getCmd('function', 'jSelectVisdatadetail');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_visforms&view=visdatas&layout=modal&fid='.$this->fid.'&tmpl=component&function='.$function.'&'.JSession::getFormToken().'=1');?>" method="post" name="adminForm" id="adminForm" class="form-inline">
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
			<select name="filter_published" class="input-medium" onchange="this.form.submit()">
				<?php $options = array();
				$options[] = JHtml::_('select.option', '', JText::_('JOPTION_SELECT_PUBLISHED'));
				$options[] = JHtml::_('select.option', '1', JText::_('JPUBLISHED'));
				$options[] = JHtml::_('select.option', '0', JText::_('JUNPUBLISHED'));
				?>
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
		</div>
	</fieldset>

	<table class="table table-striped table-condensed">
		<thead>
			<tr>
				<th class="center nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_VISFORMS_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
                <?php
                $k = 0;
                $n=count( $this->fields );
                for ($i=0; $i < $n; $i++) {
	                $width = 30;
	                if ($n > 0) {
		                $width = floor(89/$n);
	                }
	                $rowField = $this->fields[$i];
	                if (!($rowField->showFieldInDataView === false)) {
		                if (empty($rowField->unSortable)) { ?>
                            <th width="<?php echo $width ?>%" class="nowrap"><?php
			                echo $this->getSortHeader($rowField->name, "a.F$rowField->id"); ?>
                            </th><?php
		                }
		                else { ?>
                            <th width="<?php echo $width ?>%" class="nowrap"><?php
			                echo $rowField->name; ?>
                            </th><?php
		                }
	                }
                }
                ?>
				<th width="10%" class="center nowrap">
					<?php echo JHtml::_('grid.sort',  'JDATE', 'a.created', $listDirn, $listOrder); ?>
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
					<a href="javascript:void(0)" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->id)); ?>');">
						<?php echo $this->escape($item->id); ?></a>
				</td>
				<?php $z = count( $this->fields );
				for ($j=0; $j < $z; $j++) {
					$rowField = $this->fields[$j];
					if (!($rowField->showFieldInDataView === false)) {
						$prop="F".$rowField->id;
						if (isset($item->$prop) == false) {
							$prop=$rowField->name;
						}

						if (isset($item->$prop)) {
							$texts = $item->$prop;
						}
						else {
							$texts = "";
						}
						if ($rowField->typefield == 'file') {
							//info about uploaded files are stored in a JSON Object. Earlier versions just have a string.
							$texts = JHtml::_('visforms.getUploadFileName', $texts);
							echo "<td>". $texts . "</td>";
						}
						else if ($rowField->typefield == 'signature') {
							$layout             = new JLayoutFile('visforms.datas.fields.signature', null);
							$layout->setOptions(array('component' => 'com_visforms'));
							$texts = $layout->render(array('field' => $rowField, 'data' => $texts, 'maxWidth' => 200));
							echo "<td>". $texts . "</td>";
						}
						else {
							if (StringHelper::strlen($texts) > 255) {
								$texts = StringHelper::substr($texts,0,255)."...";
							}
							echo "<td>" . $texts . "</td>";
						}
					}
				} ?>
                <td class="center nowrap">
					<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
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
