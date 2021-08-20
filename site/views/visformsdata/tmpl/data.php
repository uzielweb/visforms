<?php
/**
 * Visformsdata data view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
JHtmlVisforms::visformsTooltip();
if ($this->form->published != '1') {
	return;
}
if (!empty($this->form->displaycounter)) {
	$this->displayCounter = $this->counterOffest;
}
$this->labelHtmlTag = 'th';
$this->valueHtmlTag = 'td';
$this->labelClass = 'vfdvlabel';
$this->valueClass = 'vfdvvalue';
$this->extension = 'component';
$this->viewType = 'column';
$this->detailLinkLayout='detail';
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$orientation = $this->menu_params->get('orientation', '0');
$sublayout = (!empty($orientation)) ? 'datatableflat' : 'datatable';
echo JLayoutHelper::render('visforms.custom.noscript', array('text' => 'COM_VISFORMS_NOSCRIPT_ALERT_DATA'));
?>

<div class="visforms visforms-data <?php echo $this->menu_params->get('pageclass_sfx'); ?>"><?php
	if (!empty($this->menu_params->get('show_page_heading'))) {
		if (!empty($this->menu_params->get('page_heading'))) { ?>
            <h1><?php echo $this->menu_params->get('page_heading'); ?></h1><?php
		} 
		else {
			if (empty($this->form->fronttitle)) {
				echo '<h1>' . $this->form->title . '</h1>';
			} 
			else {
				echo '<h1>' . $this->form->fronttitle . '</h1>';
			}
		}
	}
	if (!empty($this->form->frontdescription)) {
		JPluginHelper::importPlugin('content');
		echo '<div class="category-desc">' . JHtml::_('content.prepare', $this->form->frontdescription) . '</div>';
	} ?>

    <form action="<?php echo JRoute::_('index.php?option=com_visforms&view=visformsdata&layout=data&id=' . $this->id . '&Itemid=' . $this->itemid); ?>"
          method="post" name="adminForm" id="<?php echo $this->context; ?>adminForm" class="visdata"><?php
		if (!empty($this->menu_params->get('show_filter'))) {
			// no template overrides for visforms search filter
			echo JLayoutHelper::render('visforms.searchtools.default', array('view' => $this, 'options' => array('context' => $this->context, 'hasLocationRadiusSearch' => $this->form->hasLocationRadiusSearch)), JPATH_ROOT . '/components/com_visforms/layouts');
	} ?>
    <div class="clr"> </div> <?php
        echo $this->loadTemplate($sublayout);
        if ($this->pagination->pagesTotal > 1) {
	echo '<div class="pagination"><p class="counter">' . $this->pagination->getPagesCounter() . '</p>' . $this->pagination->getPagesLinks() . '</div>';
		} ?>
    <div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="<?php echo $this->context; ?>filter_order" value="<?php echo $listOrder; ?>" />
        <input type="hidden" name="<?php echo $this->context; ?>filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
    </div>
    </form><?php
	if ($this->form->poweredby == '1') { ?>
        <div id="vispoweredby"><a href="https://vi-solutions.de" target="_blank"><?php echo JText::_('COM_VISFORMS_POWERED_BY'); ?></a></div><?php
	} ?>
</div>
<script>
    var vftableOrdering = function (order, dir, task, form) {
        if (typeof form === 'undefined') {
            form = document.getElementById('adminForm');
        }

        form.<?php echo $this->context; ?>filter_order.value = order;
        form.<?php echo $this->context; ?>filter_order_Dir.value = dir;
        Joomla.submitform(task, form);
    };
    var vttableFullOrdering<?php echo $this->context; ?> = function (element) {
        var idx = element.selectedIndex;
        var sel = element[idx].value;
        if (sel && (typeof sel === 'string')) {
            var opts = sel.split(' ');
            if (Array.isArray(opts) && opts.length === 2) {
                var order = opts[0].trim();
                var dirn = opts[1].trim();
                vftableOrdering(order, dirn, '', element.form);
            }
        }
    };
</script>