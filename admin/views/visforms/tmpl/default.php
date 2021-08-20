<?php
/**
 * Visforms default view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

?>

<script type="text/javascript">
    var exportForm = function() {
        // if data sets are checked we submit id's of check data sets as array cid[] and uncheck the boxes in the form, because the page is not reloaded on export
        //jQuery('#exportFormModal').modal('hide');
        var form = document.getElementById('adminForm');
        var stub = 'cb';
        var cid  = '';
        if (form) {
            var j = 0;
            for (var i = 0, n = form.elements.length; i < n; i++) {
                var e = form.elements[i];
                if (e.type == 'checkbox') {
                    if (e.id.indexOf(stub) == 0) {
                        if (e.checked == true) {
                            cid += '&cid[' + j + ']=' + e.value;
                            j++;
                            e.checked = false;
                        }
                    }
                }
            }
            exportOptions = getSelectedExportOptions('export[copy-fields]');
            exportOptions += getSelectedExportOptions('export[copy-data]');
            exportOptions += getSelectedExportOptions('export[copy-pdf-templates]');
            exportOptions += getSelectedExportOptions('export[copy-userid]');
            exportOptions += getSelectedExportOptions('export[copy-acl]');
            document.getElementById('export[copy-fields]c').checked=true;
            document.getElementById('export[copy-fields]n').checked=false;
            document.getElementById('export[copy-data]c').checked=false;
            document.getElementById('export[copy-data]n').checked=true;
            document.getElementById('export[copy-pdf-templates]c').checked=true;
            document.getElementById('export[copy-pdf-templates]n').checked=false;
            document.getElementById('export[copy-userid]c').checked=false;
            document.getElementById('export[copy-userid]n').checked=true;
            document.getElementById('export[copy-acl]c').checked=false;
            document.getElementById('export[copy-acl]n').checked=true;
            window.location = '<?php echo "$this->baseUrl&view=$this->editViewName&task=$this->editViewName.exportform" ;?>' + cid + exportOptions + '&<?php echo JSession::getFormToken();?>=1';
        }
    }
    var getSelectedExportOptions = function(eName) {
        var exportElement = document.getElementsByName(eName);
        for (var i=0; i<2; i++){
            if (exportElement[i].checked) {
                return '&' + eName + '=' + exportElement[i].value;
            }
        }
        return '';
    }
	Joomla.orderTable = function() {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $this->listOrdering; ?>') {
			dirn = 'asc';
		}
		else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>

<form action="<?php echo JRoute::_("$this->baseUrl&view=visforms");?>" method="post" name="adminForm" id="adminForm"><?php
    // sidebar
    if (!empty( $this->sidebar)) { ?>
        <div id="j-sidebar-container" class="span2"><?php echo $this->sidebar; ?></div>
        <div id="j-main-container" class="span10"><?php
    }
    else { ?>
        <div id="j-main-container"><?php
    }
    if (isset($this->update_message)) {
        echo $this->update_message;
    }
    // search tools bar
    echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
	<div class="clr"></div>
	<table class="table table-striped" id="articleList">
	<thead><tr>
        <th width="3%"  class="nowrap center hidden-phone"><?php echo JHtml::_('grid.checkall'); ?></th>
        <th width="40%" class="nowrap"><?php echo $this->getSortHeader('COM_VISFORMS_TITLE', 'a.title'); ?></th>
        <th width="5%"  class="nowrap center"><?php echo $this->getSortHeader('COM_VISFORMS_PUBLISHED', 'a.published'); ?></th>
        <th width="10%" class="nowrap center hidden-phone"><?php echo $this->getSortHeader('JGRID_HEADING_ACCESS', 'access_level'); ?></th>
        <th width="5%"  class="nowrap center hidden-phone"><?php echo $this->getSortHeader('COM_VISFORMS_FIELDS', 'nbfields'); ?></th>
        <th width="5%"  class="nowrap center hidden-phone"><?php echo $this->getSortHeader('COM_VISFORMS_AUTHOR', 'username'); ?></th>
        <th width="10%" class="nowrap center hidden-phone"><?php echo $this->getSortHeader('COM_VISFORMS_DATE', 'a.created'); ?></th>
        <th width="15%" class="nowrap center"><?php echo JText::_( 'COM_VISFORMS_DATA' ); ?></th><?php
        if($this->hasPdf) { ?>
            <th width="15%" class="nowrap center"><?php echo JText::_( 'COM_VISFORMS_PDF' ); ?></th><?php
        } ?>
        <th width="5%"  class="nowrap center"><?php echo $this->getSortHeader('COM_VISFORMS_HITS', 'a.hits'); ?></th>
        <th width="5%"  class="nowrap center hidden-phone"><?php echo $this->getSortHeader('JGRID_HEADING_LANGUAGE', 'language'); ?></th>
        <th width="3%"  class="nowrap center hidden-phone"><?php echo $this->getSortHeader('COM_VISFORMS_ID', 'a.id'); ?></th>
    </tr></thead><?php
    foreach ($this->items as $i => $item) {
        $item->max_ordering = 0; // ??
        $checked 	 = JHtml::_('grid.id', $i, $item->id );
        $linkEdit 	 = JRoute::_("$this->baseUrl&task=visform.edit&id=$item->id");
        $linkFields	 = JRoute::_("$this->baseUrl&view=visfields&fid=$item->id");
        $linkDatas 	 = JRoute::_("$this->baseUrl&view=visdatas&fid=$item->id");
        $linkPDFs 	 = JRoute::_("$this->baseUrl&view=vispdfs&fid=$item->id");
        $authoriseId = "$this->authoriseName.$item->id";
        $canCheckin	 = $this->user->authorise('core.manage',	 $this->componentName) || $item->checked_out == $this->userId || $item->checked_out == 0;
        $canCreate	 = $this->user->authorise('core.create',	 $authoriseId);
		$canEdit	 = $this->user->authorise('core.edit',	     $authoriseId);
		$canEditOwn	 = $this->user->authorise('core.edit.own',   $authoriseId) && $item->created_by == $this->userId;
		$canChange	 = $this->user->authorise('core.edit.state', $authoriseId) && $canCheckin;
		$published   =  JHtml::_('jgrid.published', $item->published, $i, 'visforms.', $canChange, 'cb'); ?>
        <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->id; ?>">
			<td class="center hidden-phone"><?php echo $checked; ?></td>
			<td class="has-context">
                <div class="pull-left"><?php
                    if ($item->checked_out) {
                        echo JHtml::_('jgrid.checkedout', $i, $this->user->name, $item->checked_out_time, 'visforms.', $canCheckin);
                    }
                    if ($canEdit || $canEditOwn) { ?>
                        <a href="<?php echo $linkEdit; ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>"><?php echo $this->escape($item->title); ?></a>
                        <p class="small"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->name)); ?></p><?php
                    }
                    else {
                        echo $this->escape($item->title); ?>
                        <p class="small"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->name)); ?></p><?php
                    } ?>
                </div>
			</td>
            <td class="center"><?php echo $published;?></td>
            <td class="small center hidden-phone"><?php echo $this->escape($item->access_level); ?></td>
            <td class="center hidden-phone">
                <a href="<?php echo $linkFields; ?>" title="<?php echo JText::_('COM_VISFORMS_FIELDS_OPEN_LIST'); ?>"><?php echo $item->nbfields; ?></a>
            </td>
			<td class="small center hidden-phone">
                <a href="<?php echo JRoute::_("index.php?option=com_users&task=user.edit&id=".(int) $item->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>">
								<?php echo $this->escape($item->username); ?></a>
			</td>
			<td class="nowrap small center hidden-phone"><?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?></td>
			<td class="center"><?php
                if ($item->saveresult == '1') { ?>
                    <a href="<?php echo $linkDatas; ?>" title="<?php echo JText::_('COM_VISFORMS_DATAS_OPEN_LIST'); ?>"><?php echo $this->getDatasTotal($item->id); ?></a><?php
                } ?>
			</td><?php
            if($this->hasPdf) { ?>
                <td class="center"><a href="<?php echo $linkPDFs; ?>" title="<?php echo JText::_('COM_VISFORMS_PDFS_OPEN_LIST'); ?>"><?php echo $this->getPdfsTotal($item->id); ?></a></td><?php
            } ?>
           	<td class="center"><?php echo $item->hits; ?></td>
			<td class="small center hidden-phone"><?php
                if ($item->language=='*') {
                    echo JText::alt('JALL', 'language');
                }
                else {
                    echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED');
                } ?>
            </td>
           	<td class="center hidden-phone"><?php echo $item->id; ?></td>
		</tr><?php
    }
    $layout = new JLayoutFile('td.terminating_line');
    echo $layout->render(); ?>
    </table><?php
    echo $this->pagination->getListFooter();
    // load the batch processing form
	if ($this->canDo->get('core.create')) {
		echo $this->loadTemplate('batch');
		echo $this->loadTemplate('export');
	}
    $layout = new JLayoutFile('div.form_hidden_inputs');
    echo $layout->render(); ?>
    </div>
</form><?php
if ($this->canDo->get('core.create')) {
	echo $this->loadTemplate('import');
}