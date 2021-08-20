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
$jversion = new JVersion();
//require_once JPATH_ROOT . '/components/com_content/helpers/route.php';

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
if (version_compare($jversion->getShortVersion(), '3.7.0', 'lt'))
{
    JHtml::_('behavior.tooltip');
    JHtml::_('behavior.framework', true);
}
else
{
    JHtml::_('behavior.core');
    JHtml::_('behavior.polyfill', array('event'), 'lt IE 9');
    JHtml::_('script', 'com_visforms/admin-visformfields-modal.js', array('version' => 'auto', 'relative' => true));
    JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));
    JHtml::_('formbehavior.chosen', 'select');


    // Special case for the search field tooltip.
    $searchFilterDesc = $this->filterForm->getFieldAttribute('search', 'description', null, 'filter');
    JHtml::_('bootstrap.tooltip', '#filter_search', array('title' => JText::_($searchFilterDesc), 'placement' => 'bottom'));
}

$linkeditorname = '';

$function	= JFactory::getApplication()->input->getCmd('function', 'jSelectVisformfield');
$editor = JFactory::getApplication()->input->getCmd('editor', '');
if (version_compare($jversion->getShortVersion(), '3.7.0', 'ge'))
{
    $linkeditorname = '&amp;editor=' . $editor;
    //JHtml::_('script', 'com_visforms/admin-visformfields-modal.js', array('version' => 'auto', 'relative' => true));
    if (!empty($editor))
    {
        // This view is used also in com_menus. Load the xtd script only if the editor is set!
        JFactory::getDocument()->addScriptOptions('xtd-visformfields', array('editor' => $editor));
        $onclick = "jSelectVisformfield";
    }
}
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
//Add field types that should not be available as placeholder to this list
$hiddenFieldTypes = array('submit', 'reset', 'image', 'fieldsep', 'pagebreak');

?>
<form action="<?php echo JRoute::_('index.php?option=com_visforms&view=visfields&fid=' . JFactory::getApplication()->input->getInt('fid', -1) . '&layout=modal&tmpl=component&function='.$function.'&'.JSession::getFormToken().'=1'. $linkeditorname);?>" method="post" name="adminForm" id="adminForm" class="form-inline">
	<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>


	<table class="table table-striped table-condensed">
		<thead>
			<tr>
				<th width="1%"class="center nowrap">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
				<th class="center nowrap">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.label', $listDirn, $listOrder); ?>
				</th>					
                <th width="10%" class="nowrap center">
                    <?php echo JHtml::_('grid.sort', 'COM_VISFORMS_TYPE', 'a.typefield', $listDirn, $listOrder); ?>
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
            <?php if(!(in_array($item->typefield, $hiddenFieldTypes)))
                  { ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="center">
                            <?php echo (int) $item->id; ?>
                        </td>
                        <td class="center">
                            <?php if (version_compare($jversion->getShortVersion(), '3.7.0', 'lt')) { ?>
                            <a href="javascript:void(0)" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $this->escape(addslashes($item->name)); ?>');">
                            <?php } else { ?>
                            <a href="javascript:void(0)" onclick="if (window.parent) window.<?php echo $this->escape($function);?>('<?php echo $this->escape(addslashes($item->name)); ?>','<?php echo $this->escape(addslashes($editor)); ?>');">
                                <?php } ?>
                                <?php echo $this->escape($item->label); ?></a>
                        </td>
                        <td class="center nowrap">
                            <?php echo $this->escape($item->typefield); ?>
                        </td>
                    </tr>
           <?php } ?>
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
