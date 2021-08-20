<?php
/**
 * Visformsdata detail view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6
 */

//no direct access
defined('_JEXEC') or die('Restricted access');

$app = JFactory::getApplication();
if (empty($this->item)) {
	$app->enqueueMessage(JText::_('COM_VISFORMS_FORM_DATA_RECORD_SET_MISSING'), 'error');
	return;
}
if (empty($this->item->published)) {
	$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
	return;
}
$menuitems = $app->getMenu()->getItems('link', 'index.php?option=com_visforms&view=visformsdata&layout=data&id=' . $this->id);
$userId = JFactory::getUser()->get('id');
$this->labelHtmlTag = 'td';
$this->valueHtmlTag = 'td';
$this->labelClass = 'vfdvlabel';
$this->valueClass = 'vfdvvalue';
$this->extension = 'component';
$this->viewType = 'row';
$this->canCreatePdf = ($this->canDo->get('core.create.pdf') || ($this->canDo->get('core.create.own.pdf') && isset($this->item->created_by) && $this->item->created_by == $userId));
if (!empty($this->item->requiresJs)) {
	echo JLayoutHelper::render('visforms.custom.noscript', array('text' => 'COM_VISFORMS_NOSCRIPT_ALERT_DATA'), JPATH_ROOT . '/components/com_visforms/layouts');
}
?>
<div class="visforms visforms-data <?php echo $this->menu_params->get('pageclass_sfx'); ?>"><?php
    if ($this->menu_params->get('show_page_heading') == 1) {
		if (!$this->menu_params->get('page_heading') == "") { ?>
            <h1><?php echo $this->menu_params->get('page_heading'); ?></h1><?php
		} else if (!empty($this->form->frontdetailtitle)) {
			echo '<h1>' . $this->form->frontdetailtitle . '</h1>';
		} else if (!empty($this->form->fronttitle)) {
			echo '<h1>' . $this->form->fronttitle . '</h1>';
		} else {
			echo '<h1>' . $this->form->title . '</h1>';
		}
	}
	foreach ($menuitems as $item) {
		if (isset($item->id) && ($item->id == $this->itemid)) {
			$linkback = "index.php?option=com_visforms&view=visformsdata&layout=data&Itemid=" . $this->itemid . "&id=" . $this->id;
			echo '<a class="btn" href="' . JRoute::_($linkback) . '">';
			echo JText::_('COM_VISFORMS_BACK_TO_LIST');
			echo '</a>';
			break;
		}
	}
	if (!empty($this->canCreatePdf) && !empty($this->form->displaypdfexportbutton_detail) && !empty($this->form->singleRecordPdfTemplate)) { ?>
    <form action="<?php echo JRoute::_('index.php?option=com_visforms&view=visformsdata&layout=data&id=' . $this->id); ?>"
          method="post" name="adminForm" id="adminForm" style="display:inline-block;">
        <input class="btn" type="submit" value="<?php echo JText::_('COM_VISFORMS_DOWNLOAD_PDF'); ?>"/>
        <input type="hidden" name="task" value="visformsdata.renderPdf"/>
        <input type="hidden" name="return" value="<?php echo JHtmlVisforms::base64_url_encode(JUri::getInstance()->toString()); ?>"/>
        <input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>"/>
		<?php echo JHtml::_('form.token'); ?>
    </form>
	<?php }

	echo $this->loadTemplate('detailtable');
	?>
    <?php
    if ($this->form->poweredby == '1') { ?>
        <div id="vispoweredby"><a href="https://vi-solutions.de" target="_blank"><?php echo JText::_('COM_VISFORMS_POWERED_BY'); ?></a></div><?php
    } ?>
</div>